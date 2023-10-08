<?php

namespace App\Http\Controllers\Api;

use App\Enums\COAGeneralType;
use App\Enums\COASubType;
use App\Enums\COAType;
use App\Enums\DentalDoctorTransaction;
use App\Enums\DoubleEntryType;
use App\Enums\OfficeType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\AcceptDentalLabInvoiceForDoctorRequest;
use App\Http\Requests\StoreDentalLabInvoiceForDoctorRequest;
use App\Http\Requests\StoreDentalLabInvoiceRequest;
use App\Http\Requests\StorePatientInvoiceRequest;
use App\Http\Requests\StoreSupplierInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\PatientInvoiceResource;
use App\Models\AccountingProfile;
use App\Models\COA;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Office;
use App\Models\Patient;
use App\Models\TransactionPrefix;
use App\Models\User;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        //
    }

    public function storePatientInvoice(StorePatientInvoiceRequest $request, Patient $patient)
    {
        $fields = $request->validated();
        $office = Office::findOrFail($request->office_id);
        $transactionNumber = TransactionPrefix::where(['office_id' => $office->id, 'doctor_id' => auth()->user()->doctor->id, 'type' => TransactionType::PatientInvoice])->first();
        if ($office->type == OfficeType::Combined) {
            $owner = User::findOrFail($office->owner->user_id);
            $profile = AccountingProfile::where([
                'patient_id' => $patient->id,
                'office_id' => $office->id, 'doctor_id' => $owner->doctor->id
            ])->first();
            $fields['running_balance'] = $this->patientBalance($profile->id, $fields['total_price']);
            $fields['invoice_number'] = $transactionNumber->last_transaction_number + 1;
            $invoice = $profile->invoices()->create($fields);
            $transactionNumber->update(['last_transaction_number' => $fields['invoice_number']]);
        } else {
            $profile = AccountingProfile::where([
                'patient_id' => $patient->id,
                'office_id' => $office->id, 'doctor_id' => $request->doctor_id
            ])->first();
            $fields['running_balance'] = $this->patientBalance($profile->id, $fields['total_price']);
            $fields['invoice_number'] = $transactionNumber->last_transaction_number + 1;
            $invoice = $profile->invoices()->create($fields);
            $transactionNumber->update(['last_transaction_number' => $fields['invoice_number']]);
        }
        return new PatientInvoiceResource($invoice);
    }

    public function storeSupplierInvoice(StoreSupplierInvoiceRequest $request)
    {
        $fields = $request->validated();
        $profile = AccountingProfile::findOrFail($request->supplier_account_id);
        $fields['running_balance'] = $this->supplierBalance($profile->id, $fields['total_price']);
        $transactionNumber = TransactionPrefix::where(['office_id' => $profile->office->id, 'doctor_id' => auth()->user()->doctor->id, 'type' => TransactionType::SupplierInvoice])->first();
        $fields['invoice_number'] = $transactionNumber->last_transaction_number + 1;
        $invoice = $profile->invoices()->create($fields);
        $transactionNumber->update(['last_transaction_number' => $fields['invoice_number']]);
        return new PatientInvoiceResource($invoice);
    }

    public function storeDentalLabInvoice(StoreDentalLabInvoiceForDoctorRequest $request, AccountingProfile $profile)
    {
        $fields = $request->validated();
        $this->authorize('storeDentalLabInvoiceForDoctor', [Invoice::class, $profile]);
        if ($request->invoice_id) {
            $invoice2 = Invoice::findOrFail($request->invoice_id);
            $this->authorize('acceptDentalLabInvoice', [$invoice]);
            $invoice2->update([
                'status' => TransactionStatus::Approved,
            ]);
        }
        $fields['running_balance'] = $this->labBalance($profile->id, $fields['total_price']);
        $transactionNumber = TransactionPrefix::where(['office_id' => $profile->office->id, 'doctor_id' => auth()->user()->doctor->id, 'type' => TransactionType::SupplierInvoice])->first();
        $fields['invoice_number'] = $transactionNumber->last_transaction_number + 1;
        $fields['type'] = DentalDoctorTransaction::PercherInvoice;
        $fields['status'] = TransactionStatus::Approved;
        $invoice = $profile->invoices()->create($fields);
        $transactionNumber->update(['last_transaction_number' => $fields['invoice_number']]);
        return new InvoiceResource($invoice->with(['doctor', 'office', 'items', 'lab'])->first());
    }

    public function acceptDentalLabInvoice(AcceptDentalLabInvoiceForDoctorRequest $request, Invoice $invoice)
    {
        $fields = $request->validated();
        $this->authorize('acceptDentalLabInvoice', [$invoice]);
        $expensesCoa = COA::findOrFail($request->coa);
        $payable = COA::where([
            'office_id' => $invoice->office->id,
            'doctor_id' => $invoice->doctor->id, 'sub_type' => COASubType::Payable
        ])->first();
        abort_unless($payable != null && $expensesCoa != null, 403);
        abort_unless($expensesCoa->doctor->id != auth()->user()->decoct->id, 403);
        abort_unless($expensesCoa->type == COAGeneralType::Expenses, 403);
        $account = $invoice->account;
        $fields['running_balance'] = $this->labBalance($account->id, $fields['total_price']);
        $transactionNumber = TransactionPrefix::where(['office_id' => $account->office->id, 'doctor_id' => auth()->user()->doctor->id, 'type' => TransactionType::SupplierInvoice])->first();
        $fields['invoice_number'] = $transactionNumber->last_transaction_number + 1;
        $fields['type'] = DentalDoctorTransaction::PercherInvoice;
        $fields['total_price'] = $invoice->total_price;
        $percherInvoice = $account->invoices()->create($fields);
        $transactionNumber->update(['last_transaction_number' => $fields['invoice_number']]);
        foreach ($invoice->items as $item) {
            $percherInvoice->items->create([
                'name' => $item->name,
                'description' => $item->description,
                'amount' => $item->amount,
                'total_price' => $item->total_price,
                'price_per_one' => $item->price_per_one,
            ]);
        }
        $doubleEntryFields['invoice_id'] = $invoice->id;
        $doubleEntryFields['total_price'] = $invoice->total_price;
        $doubleEntryFields['type'] = DoubleEntryType::Positive;
        $payable->doubleEntries()->create($doubleEntryFields);
        $expensesCoa->doubleEntries()->create($doubleEntryFields);
        $invoice->update([
            'status' => TransactionStatus::Approved,
        ]);
        return new InvoiceResource($invoice->with(['doctor', 'office', 'items', 'lab'])->first());
    }

    public function rejectDentalLabInvoice(Invoice $invoice)
    {
        $this->authorize('acceptDentalLabInvoice', [$invoice]);
        $invoice->update([
            'status' => TransactionStatus::Rejected,
        ]);
        return new InvoiceResource($invoice->with(['doctor', 'office', 'items', 'lab'])->first());
    }

    public static function patientBalance(int $id, int $thisTransaction)
    {
        $patient = AccountingProfile::findOrFail($id);
        $invoices = $patient->invoices()->get();
        $totalPositive = $invoices != null ?
            $invoices->sum('total_price') : 0;
        $receipts = $patient->receipts()->get();
        $totalNegative = $receipts != null ?
            $receipts->sum('total_price') : 0;
        $total = $totalPositive - $totalNegative + $thisTransaction + $patient->initial_balance;
        return $total;
    }

    public static function supplierBalance(int $id, int $thisTransaction)
    {
        $supplier = AccountingProfile::findOrFail($id);
        $invoices = $supplier->invoices()->get();
        $totalNegative = $invoices != null ?
            $invoices->sum('total_price') : 0;
        $receipts = $supplier->receipts()->get();
        $totalPositive = $receipts != null ?
            $receipts->sum('total_price') : 0;
        $total = $totalPositive - $totalNegative - $thisTransaction + $supplier->initial_balance;
        return $total;
    }

    public static function labBalance(int $id, int $thisTransaction)
    {
        $supplier = AccountingProfile::findOrFail($id);
        $invoices = $supplier->invoices()->whereIn('type', DentalDoctorTransaction::getValues())->get();
        $totalNegative = $invoices != null ?
            $invoices->sum('total_price') : 0;
        $receipts = $supplier->receipts()->whereIn('type', DentalDoctorTransaction::getValues())
            ->whereNot('status', TransactionStatus::Canceled)
            ->get();
        $totalPositive = $receipts != null ?
            $receipts->sum('total_price') : 0;
        $total = $totalPositive - $totalNegative - $thisTransaction + $supplier->initial_balance;
        return $total;
    }
}
