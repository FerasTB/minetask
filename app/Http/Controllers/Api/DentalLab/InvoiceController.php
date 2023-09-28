<?php

namespace App\Http\Controllers\Api\DentalLab;

use App\Enums\COASubType;
use App\Enums\DoubleEntryType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDentalLabInvoiceForDoctorRequest;
use App\Http\Requests\StorePatientInvoiceItemRequest;
use App\Http\Requests\StoreSupplierInvoiceRequest;
use App\Http\Resources\DentalLabInvoiceForDoctorResource;
use App\Http\Resources\InvoiceItemsResource;
use App\Http\Resources\PatientInvoiceResource;
use App\Models\AccountingProfile;
use App\Models\COA;
use App\Models\Invoice;
use App\Models\TransactionPrefix;
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
        $transactionNumber = TransactionPrefix::where(['lab_id' => $profile->lab->id, 'type' => TransactionType::PatientInvoice])->first();
        $fields['invoice_number'] = $transactionNumber->last_transaction_number + 1;
        if (!AccountingProfileController::isNotExistDoctor($profile->id)) {
            $fields['status'] = TransactionStatus::Draft;
        }
        $invoice = $profile->invoices()->create($fields);
        $transactionNumber->update(['last_transaction_number' => $fields['invoice_number']]);
        return new DentalLabInvoiceForDoctorResource($invoice);
    }

    public function storeDoctorInvoiceItem(StorePatientInvoiceItemRequest $request, Invoice $invoice)
    {
        $fields = $request->validated();
        abort_unless($invoice->account->lab != null, 403);
        $item = $invoice->items()->create($fields);
        $lab = $invoice->lab;
        $receivable = COA::where([
            'office_id' => $lab->id,
            'sub_type' => COASubType::Receivable
        ])->first();
        $serviceCoa = COA::findOrFail($request->service_coa);
        $doubleEntryFields['COA_id'] = $receivable->id;
        $doubleEntryFields['invoice_item_id'] = $item->id;
        $doubleEntryFields['total_price'] = $item->total_price;
        $doubleEntryFields['type'] = DoubleEntryType::Positive;
        $receivable->doubleEntries()->create($doubleEntryFields);
        $doubleEntryFields['COA_id'] = $serviceCoa->COA_id;
        $serviceCoa->doubleEntries()->create($doubleEntryFields);
        return new InvoiceItemsResource($item);
    }

    public static function doctorBalance(int $id, int $thisTransaction)
    {
        $supplier = AccountingProfile::findOrFail($id);
        $invoices = $supplier->invoices()->where('status', TransactionStatus::Approved)->get();
        $totalNegative = $invoices != null ?
            $invoices->sum('total_price') : 0;
        $receipts = $supplier->receipts()->where('status', TransactionStatus::Approved)->get();
        $totalPositive = $receipts != null ?
            $receipts->sum('total_price') : 0;
        $total = $totalPositive - $totalNegative - $thisTransaction + $supplier->initial_balance;
        return $total;
    }
}
