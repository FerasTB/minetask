<?php

namespace App\Http\Controllers\Api\DentalLab;

use App\Enums\AccountingProfileType;
use App\Enums\COASubType;
use App\Enums\COAType;
use App\Enums\DentalDoctorTransaction;
use App\Enums\DentalLabTransaction;
use App\Enums\DoubleEntryType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDentalLabInvoiceForDoctorRequest;
use App\Http\Requests\StorePatientInvoiceItemRequest;
use App\Http\Requests\StoreSupplierInvoiceForDentalLabRequest;
use App\Http\Requests\StoreSupplierInvoiceRequest;
use App\Http\Resources\DentalLabInvoiceForDoctorResource;
use App\Http\Resources\InvoiceItemsResource;
use App\Http\Resources\PatientInvoiceResource;
use App\Models\AccountingProfile;
use App\Models\COA;
use App\Models\DentalLab;
use App\Models\Invoice;
use App\Models\Receipt;
use App\Models\TransactionPrefix;
use App\Notifications\InvoiceCreated;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function storeDoctorInvoice(StoreDentalLabInvoiceForDoctorRequest $request, AccountingProfile $profile)
    {
        $fields = $request->validated();
        $this->authorize('createDentalLabInvoiceForDoctor', [
            Invoice::class, $profile,
        ]);
        $fields['running_balance'] = $this->doctorBalance($profile->id, $fields['total_price']);
        $transactionNumber = TransactionPrefix::where(['dental_lab_id' => $profile->lab->id, 'type' => TransactionType::PatientInvoice])->first();
        $fields['invoice_number'] = $transactionNumber->last_transaction_number + 1;
        if (!AccountingProfileController::isNotExistDoctor($profile->id)) {
            $fields['status'] = TransactionStatus::Draft;
        }
        $fields['type'] = DentalLabTransaction::SellInvoice;
        $invoice = $profile->invoices()->create($fields);
        $transactionNumber->update(['last_transaction_number' => $fields['invoice_number']]);
        $doctor = $profile->doctor;
        $type = 'InvoiceFromLab';
        $doctor->notify(new InvoiceCreated($invoice, $type));
        return new DentalLabInvoiceForDoctorResource($invoice);
    }

    public function storeDoctorInvoiceItem(StorePatientInvoiceItemRequest $request, Invoice $invoice)
    {
        $fields = $request->validated();
        abort_unless($invoice->lab != null, 403);
        $item = $invoice->items()->create($fields);
        $lab = $invoice->lab;
        $receivable = COA::where([
            'dental_lab_id' => $lab->id,
            'sub_type' => COASubType::Receivable
        ])->first();
        $COGS = COA::where([
            'dental_lab_id' => $lab->id,
            'type' => COAType::COGS,
        ])->first();
        $inventory = COA::where([
            'dental_lab_id' => $lab->id,
            'sub_type' => COASubType::Inventory,
        ])->first();
        $serviceCoa = COA::findOrFail($request->service_coa);
        $doubleEntryFields['COA_id'] = $receivable->id;
        $doubleEntryFields['invoice_item_id'] = $item->id;
        $doubleEntryFields['total_price'] = $item->total_price;
        $doubleEntryFields['type'] = DoubleEntryType::Positive;
        $receivable->doubleEntries()->create($doubleEntryFields);
        $doubleEntryFields['COA_id'] = $serviceCoa->COA_id;
        $serviceCoa->doubleEntries()->create($doubleEntryFields);
        if ($request->service_percentage > 0) {
            $doubleEntryFields['COA_id'] = $COGS->COA_id;
            $doubleEntryFields['total_price'] = $item->total_price * $request->service_percentage / 100;
            $COGS->doubleEntries()->create($doubleEntryFields);
            $doubleEntryFields['type'] = DoubleEntryType::Negative;
            $inventory->doubleEntries()->create($doubleEntryFields);
        }
        return new InvoiceItemsResource($item);
    }

    public static function doctorBalance(int $id, int $thisTransaction)
    {
        $supplier = AccountingProfile::findOrFail($id);
        $invoices = $supplier->invoices()->whereIn('type', DentalLabTransaction::getValues())
            ->whereNot('status', TransactionStatus::Canceled)
            ->get();
        $totalPositive = $invoices != null ?
            $invoices->sum('total_price') : 0;
        $receipts = $supplier->receipts()->whereIn('type', DentalLabTransaction::getValues())->get();
        $totalNegative = $receipts != null ?
            $receipts->sum('total_price') : 0;
        $total = $totalPositive - $totalNegative + $thisTransaction + $supplier->initial_balance;
        return $total;
    }

    public function storeSupplierInvoice(StoreSupplierInvoiceForDentalLabRequest $request, AccountingProfile $profile)
    {
        $fields = $request->validated();
        abort_unless($profile->type == AccountingProfileType::DentalLabSupplierAccount, 403);
        $fields['running_balance'] = $this->supplierBalance($profile->id, $fields['total_price']);
        $transactionNumber = TransactionPrefix::where(['dental_lab_id' => $profile->lab->id, 'type' => TransactionType::SupplierInvoice])->first();
        $fields['invoice_number'] = $transactionNumber->last_transaction_number + 1;
        $fields['type'] = DentalLabTransaction::PercherInvoice;
        $fields['status'] = TransactionStatus::Approved;
        $invoice = $profile->invoices()->create($fields);
        $transactionNumber->update(['last_transaction_number' => $fields['invoice_number']]);
        return new PatientInvoiceResource($invoice);
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
}
