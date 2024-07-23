<?php

namespace App\Http\Controllers\Api;

use App\Enums\AccountingProfileType;
use App\Enums\COASubType;
use App\Enums\DentalDoctorTransaction;
use App\Enums\DentalLabType;
use App\Enums\DoubleEntryType;
use App\Enums\OfficeType;
use App\Enums\SubRole;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Http\Controllers\Api\DentalLab\AccountingProfileController;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddReceiptToInvoiceRequest;
use App\Http\Requests\StoreDentalLabReceiptForDoctorRequest;
use App\Http\Requests\StorePatientReceiptRequest;
use App\Http\Requests\StoreReceiptRequest;
use App\Http\Requests\StoreSupplierReceiptRequest;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\ReceiptResource;
use App\Models\AccountingProfile;
use App\Models\COA;
use App\Models\Doctor;
use App\Models\DoubleEntry;
use App\Models\HasRole;
use App\Models\Invoice;
use App\Models\InvoiceReceipt;
use App\Models\Office;
use App\Models\Patient;
use App\Models\Receipt;
use App\Models\Role;
use App\Models\TransactionPrefix;
use App\Notifications\ReceiptCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReceiptController extends Controller
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
    public function store(StoreReceiptRequest $request, Patient $patient)
    {
        // $fields = $request->validated();
        // $office = Office::findOrFail($request->office_id);
        // if ($office->type == OfficeType::Combined) {
        //     $profile = AccountingProfile::where([
        //         'patient_id' => $patient->id,
        //         'office_id' => $office->id, 'doctor_id' => null
        //     ])->first();
        //     $receipt = $profile->receipts()->create($fields);
        //     $payable = $office->payable;
        //     $cash = $office->cash;
        // } else {
        //     $profile = AccountingProfile::where([
        //         'patient_id' => $patient->id,
        //         'office_id' => $office->id, 'doctor_id' => $request->doctor_id
        //     ])->first();
        //     $receipt = $profile->receipts()->create($fields);
        //     $doctor = Doctor::find($request->doctor_id);
        //     $payable = $doctor->payable;
        //     $cash = $doctor->cash;
        // }
        // $doubleEntryFields['receipt_id'] = $receipt->id;
        // $doubleEntryFields['total_price'] = $receipt->total_price;
        // $doubleEntryFields['type'] = DoubleEntryType::Negative;
        // $payable->doubleEntries()->create($doubleEntryFields);
        // $cash->doubleEntries()->create($doubleEntryFields);
        // return new PatientInvoiceResource($invoice);
    }

    /**
     * Display the specified resource.
     */
    public function show(Receipt $receipt)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Receipt $receipt)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Receipt $receipt)
    {
        //
    }

    public function storeSupplierReceipt(StoreSupplierReceiptRequest $request)
    {
        $fields = $request->validated();
        // if ($request->invoice_id) {
        //     $invoice = Invoice::findOrFail($request->invoice_id);
        //     $this->authorize('myInvoice', [Receipt::class, $invoice]);
        // }
        $office = Office::findOrFail($request->office_id);
        $profile = AccountingProfile::findOrFail($request->supplier_account_id);
        $transactionNumber = TransactionPrefix::where(['office_id' => $office->id, 'doctor_id' => auth()->user()->doctor->id, 'type' => TransactionType::PaymentVoucher])->first();
        $fields['running_balance'] = $this->supplierBalance($profile->id, $fields['total_price']);
        if ($office->type == OfficeType::Combined) {
            // $profile = AccountingProfile::where([
            //     'supplier_name' => $request->supplier_name,
            //     'office_id' => $office->id, 'doctor_id' => null
            // ])->first();
            $fields['receipt_number'] = $transactionNumber->last_transaction_number + 1;
            $fields['type'] = DentalDoctorTransaction::PaymentVoucher;
            if (!$request->has('date_of_payment')) {
                $fields['date_of_payment'] = now();
            }
            $receipt = $profile->receipts()->create($fields);
            $transactionNumber->update(['last_transaction_number' => $fields['receipt_number']]);
            // $receipt->invoices()->attach($invoice, ['total_price' => $receipt->total_price]);
            $payable = COA::where([
                'office_id' => $office->id,
                'doctor_id' => null, 'sub_type' => COASubType::Payable
            ])->first();
            $cash = COA::findOrFail($request->cash_coa);
        } else {
            // $profile = AccountingProfile::where([
            //     'supplier_name' => $request->supplier_name,
            //     'office_id' => $office->id, 'doctor_id' => $request->doctor_id
            // ])->first();
            $fields['receipt_number'] = $transactionNumber->last_transaction_number + 1;
            $fields['type'] = DentalDoctorTransaction::PaymentVoucher;
            $receipt = $profile->receipts()->create($fields);
            $transactionNumber->update(['last_transaction_number' => $fields['receipt_number']]);
            // $receipt->invoices()->attach($invoice, ['total_price' => $receipt->total_price]);
            $doctor = Doctor::find($request->doctor_id);
            $payable = COA::where([
                'office_id' => $office->id,
                'doctor_id' => $doctor->id, 'sub_type' => COASubType::Payable
            ])->first();
            $cash = COA::findOrFail($request->cash_coa);
        }
        $doubleEntryFields['receipt_id'] = $receipt->id;
        $doubleEntryFields['total_price'] = $receipt->total_price;
        $doubleEntryFields['type'] = DoubleEntryType::Negative;
        $payable->doubleEntries()->create($doubleEntryFields);
        $cash->doubleEntries()->create($doubleEntryFields);
        return new ReceiptResource($receipt);
    }

    public function storeDentalLabReceipt(StoreDentalLabReceiptForDoctorRequest $request, AccountingProfile $profile)
    {
        $fields = $request->validated();
        abort_unless($profile->type == AccountingProfileType::DentalLabDoctorAccount, 403);
        $this->authorize('createReceiptForDentalLab', [Receipt::class, $profile]);
        $office = $profile->office;
        $cash = COA::findOrFail($request->cash_coa);
        abort_unless($cash->sub_type == COASubType::Cash && $cash->office_id == $profile->office_id, 403);
        $transactionNumber = TransactionPrefix::where(['office_id' => $office->id, 'doctor_id' => $profile->doctor->id, 'type' => TransactionType::PaymentVoucher])->first();
        $fields['running_balance'] = $this->labBalance($profile->id, $fields['total_price']);
        $fields['receipt_number'] = $transactionNumber->last_transaction_number + 1;
        $fields['type'] = DentalDoctorTransaction::PaymentVoucher;
        $role = HasRole::where(['roleable_id' => $profile->lab->id, 'roleable_type' => 'App\Models\DentalLab', 'user_id' => auth()->id()])->first();
        if ($role != null && $role->sub_role == SubRole::DentalLabDraft) {
            $fields['status'] = TransactionStatus::Approved;
        } else {
            $fields['status'] = TransactionStatus::Draft;
        }
        if (!$request->has('date_of_payment')) {
            $fields['date_of_payment'] = now();
        }
        $receipt = $profile->receipts()->create($fields);
        if ($profile->lab->type == DentalLabType::Real) {
            $type = 'ReceiptFromDoctor';
            $profile->lab->notify(new ReceiptCreated($receipt, $type));
        }
        $transactionNumber->update(['last_transaction_number' => $fields['receipt_number']]);
        if ($profile->office->type == OfficeType::Combined) {
            $payable = COA::where([
                'office_id' => $profile->office->id,
                'doctor_id' => null, 'sub_type' => COASubType::Payable
            ])->first();
        } else {
            $doctor = $profile->doctor;
            $payable = COA::where([
                'office_id' => $office->id,
                'doctor_id' => $doctor->id, 'sub_type' => COASubType::Payable
            ])->first();
        }
        $doubleEntryFields['receipt_id'] = $receipt->id;
        $doubleEntryFields['total_price'] = $receipt->total_price;
        $doubleEntryFields['type'] = DoubleEntryType::Negative;
        $payable->doubleEntries()->create($doubleEntryFields);
        $cash->doubleEntries()->create($doubleEntryFields);
        $receipt->load('lab');
        return new ReceiptResource($receipt);
    }

    // public function storePatientReceipt(StorePatientReceiptRequest $request, Patient $patient)
    // {
    //     $fields = $request->validated();
    //     $office = Office::findOrFail($request->office_id);
    //     $transactionNumber = TransactionPrefix::where(['office_id' => $office->id, 'doctor_id' => auth()->user()->doctor->id, 'type' => TransactionType::PatientReceipt])->first();
    //     if ($office->type == OfficeType::Combined) {
    //         $profile = AccountingProfile::where([
    //             'patient_id' => $patient->id,
    //             'office_id' => $office->id, 'doctor_id' => null
    //         ])->first();
    //         $fields['running_balance'] = $this->patientBalance($profile->id, $fields['total_price']);
    //         $fields['receipt_number'] = $transactionNumber->last_transaction_number + 1;
    //         $fields['type'] = DentalDoctorTransaction::ResetVoucher;
    //         if (!$request->has('date_of_payment')) {
    //             $fields['date_of_payment'] = now();
    //         }
    //         $receipt = $profile->receipts()->create($fields);
    //         $transactionNumber->update(['last_transaction_number' => $fields['receipt_number']]);
    //         $receivable = COA::where([
    //             'office_id' => $office->id,
    //             'doctor_id' => null, 'sub_type' => COASubType::Receivable
    //         ])->first();
    //         $cash = COA::findOrFail($request->cash_coa);
    //     } else {
    //         $profile = AccountingProfile::where([
    //             'patient_id' => $patient->id,
    //             'office_id' => $office->id, 'doctor_id' => $request->doctor_id
    //         ])->first();
    //         $fields['running_balance'] = $this->patientBalance($profile->id, $fields['total_price']);
    //         $fields['receipt_number'] = $transactionNumber->last_transaction_number + 1;
    //         $fields['type'] = DentalDoctorTransaction::ResetVoucher;
    //         $receipt = $profile->receipts()->create($fields);
    //         $transactionNumber->update(['last_transaction_number' => $fields['receipt_number']]);
    //         $doctor = Doctor::find($request->doctor_id);
    //         $receivable = COA::where([
    //             'office_id' => $office->id,
    //             'doctor_id' => $doctor->id, 'sub_type' => COASubType::Receivable
    //         ])->first();
    //         $cash = COA::findOrFail($request->cash_coa);
    //     }
    //     $doubleEntryFields['receipt_id'] = $receipt->id;
    //     $doubleEntryFields['total_price'] = $receipt->total_price;
    //     $doubleEntryFields['type'] = DoubleEntryType::Negative;
    //     $receivable->doubleEntries()->create($doubleEntryFields);
    //     $doubleEntryFields['type'] = DoubleEntryType::Positive;
    //     $cash->doubleEntries()->create($doubleEntryFields);
    //     return new ReceiptResource($receipt);
    // }

    public function storePatientReceipt(StorePatientReceiptRequest $request, Patient $patient)
    {
        DB::beginTransaction();
        try {
            $fields = $request->validated();
            $office = Office::findOrFail($request->office_id);
            $transactionNumber = TransactionPrefix::where([
                'office_id' => $office->id,
                'doctor_id' => auth()->user()->doctor->id,
                'type' => TransactionType::PatientReceipt
            ])->firstOrFail();

            $profile = $this->getAccountingProfile($office, $patient, $request->doctor_id);
            $fields = $this->setReceiptFields($request, $profile, $transactionNumber, $fields);

            $receipt = $profile->receipts()->create($fields);
            $transactionNumber->update(['last_transaction_number' => $fields['receipt_number']]);


            $cash = COA::findOrFail($request->cash_coa);

            $this->createDoubleEntry($cash, $receipt, DoubleEntryType::Positive);
            $this->createProfileDoubleEntry($receipt->accounting_profile_id, $receipt->id, $receipt->total_price, DoubleEntryType::Negative);

            DB::commit();
            return new ReceiptResource($receipt);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating patient receipt: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getAccountingProfile($office, $patient, $doctorId = null)
    {
        return AccountingProfile::where([
            'patient_id' => $patient->id,
            'office_id' => $office->id,
            'doctor_id' => $office->type == OfficeType::Combined ? null : $doctorId
        ])->firstOrFail();
    }

    private function setReceiptFields($request, $profile, $transactionNumber, $fields)
    {
        $fields['running_balance'] = $this->calculatePatientBalance($profile->id, -$fields['total_price']);
        $fields['receipt_number'] = $transactionNumber->last_transaction_number + 1;
        $fields['type'] = DentalDoctorTransaction::ResetVoucher;
        $fields['date_of_payment'] = $request->has('date_of_payment') ? $request->date_of_payment : now();
        return $fields;
    }

    private function createDoubleEntry($coa, $receipt, $type)
    {
        $doubleEntryFields = [
            'COA_id' => $coa->id,
            'receipt_id' => $receipt->id,
            'total_price' => $receipt->total_price,
            'type' => $type,
            // 'accounting_profile_id' => $receipt->accounting_profile_id,
            'running_balance' => $this->calculateCOABalance($coa->id, $receipt->total_price, $type)
        ];

        $coa->doubleEntries()->create($doubleEntryFields);
    }

    private function createProfileDoubleEntry($accountingProfileId, $itemId, $totalPrice, $type)
    {
        $runningBalance = $this->calculatePatientBalance($accountingProfileId, $type == DoubleEntryType::Positive ? $totalPrice : -$totalPrice);

        DoubleEntry::create([
            'accounting_profile_id' => $accountingProfileId,
            'receipt_id' => $itemId,
            'total_price' => $totalPrice,
            'type' => $type,
            'running_balance' => $runningBalance
        ]);
    }

    private function calculatePatientBalance(int $id, int $thisTransaction)
    {
        $patient = AccountingProfile::findOrFail($id);
        $doubleEntries = $patient->doubleEntries()->get();

        $totalPositive = $doubleEntries->where('type', DoubleEntryType::Positive)->sum('total_price');
        $totalNegative = $doubleEntries->where('type', DoubleEntryType::Negative)->sum('total_price');

        return $totalPositive - $totalNegative + $thisTransaction + $patient->initial_balance;
    }

    private function calculateCOABalance(int $coaId, int $thisTransaction, string $type)
    {
        $doubleEntries = DoubleEntry::where('COA_id', $coaId)->get();

        $totalPositive = $doubleEntries->where('type', DoubleEntryType::Positive)->sum('total_price');
        $totalNegative = $doubleEntries->where('type', DoubleEntryType::Negative)->sum('total_price');

        return ($totalPositive - $totalNegative) + ($type == DoubleEntryType::Positive ? $thisTransaction : -$thisTransaction);
    }



    public function addReceiptToInvoice(AddReceiptToInvoiceRequest $request, Receipt $receipt, Invoice $invoice)
    {
        $this->authorize('myInvoice', [Receipt::class, $invoice]);
        $receipt->invoices()->attach($invoice, ['total_price' => $receipt->total_price]);
        return $invoice;
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
        $total = $totalPositive - $totalNegative - $thisTransaction + $patient->initial_balance;
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
        $total = $totalPositive - $totalNegative + $thisTransaction + $supplier->initial_balance;
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
        $total = $totalPositive - $totalNegative + $thisTransaction + $supplier->secondary_initial_balance;
        return $total;
    }
}
