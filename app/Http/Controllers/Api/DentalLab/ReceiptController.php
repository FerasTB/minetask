<?php

namespace App\Http\Controllers\Api\DentalLab;

use App\Enums\COASubType;
use App\Enums\DentalLabTransaction;
use App\Enums\DoubleEntryType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\AcceptDoctorReceiptForDentalLabRequest;
use App\Http\Requests\StoreDentalLabReceiptForDoctorRequest;
use App\Http\Requests\StorePatientReceiptRequest;
use App\Http\Requests\StoreSupplierReceiptForDentalLabRequest;
use App\Http\Requests\StoreSupplierReceiptRequest;
use App\Http\Resources\ReceiptResource;
use App\Models\AccountingProfile;
use App\Models\COA;
use App\Models\Receipt;
use App\Models\TransactionPrefix;
use Filament\Widgets\AccountWidget;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function storeDoctorReceipt(StoreDentalLabReceiptForDoctorRequest $request, AccountingProfile $profile)
    {
        $fields = $request->validated();
        abort_unless($profile->lab != null && $profile->doctor->dental_lab_id != null, 403);
        $transactionNumber = TransactionPrefix::where(['dental_lab_id' => $profile->lab->id, 'type' => TransactionType::PatientReceipt])->first();
        $fields['running_balance'] = $this->doctorBalance($profile->id, $fields['total_price']);
        $fields['receipt_number'] = $transactionNumber->last_transaction_number + 1;
        $receipt = $profile->receipts()->create($fields);
        $transactionNumber->update(['last_transaction_number' => $fields['receipt_number']]);
        $receivable = COA::where([
            'dental_lab_id' => $profile->lab->id,
            'sub_type' => COASubType::Receivable
        ])->first();
        $cash = COA::findOrFail($request->cash_coa);
        $doubleEntryFields['receipt_id'] = $receipt->id;
        $doubleEntryFields['total_price'] = $receipt->total_price;
        $doubleEntryFields['type'] = DoubleEntryType::Negative;
        $receivable->doubleEntries()->create($doubleEntryFields);
        $doubleEntryFields['type'] = DoubleEntryType::Positive;
        $cash->doubleEntries()->create($doubleEntryFields);
        return new ReceiptResource($receipt);
    }

    public function acceptDoctorReceipt(AcceptDoctorReceiptForDentalLabRequest $request, Receipt $receipt)
    {
        $fields = $request->validated();
        $this->authorize('acceptDoctorReceipt', [$receipt]);
        $receivable = COA::where([
            'dental_lab_id' => $receipt->lab->id,
            'sub_type' => COASubType::Receivable
        ])->first();
        $cash = COA::findOrFail($request->cash_coa);
        abort_unless($receivable != null && $cash != null, 403);
        abort_unless($cash->doctor->id != auth()->user()->decoct->id, 403);
        abort_unless($cash->type == COASubType::Cash, 403);
        $doubleEntryFields['receipt_id'] = $receipt->id;
        $doubleEntryFields['total_price'] = $receipt->total_price;
        $doubleEntryFields['type'] = DoubleEntryType::Negative;
        $receivable->doubleEntries()->create($doubleEntryFields);
        $doubleEntryFields['type'] = DoubleEntryType::Positive;
        $cash->doubleEntries()->create($doubleEntryFields);
        $receipt->update([
            'status' => TransactionStatus::Approved,
        ]);
        return new ReceiptResource($receipt);
    }

    public function storeSupplierReceipt(StoreSupplierReceiptForDentalLabRequest $request, AccountingProfile $profile)
    {
        $fields = $request->validated();
        $transactionNumber = TransactionPrefix::where(['dental_lab_id' => $profile->lab->id, 'doctor_id' => auth()->user()->doctor->id, 'type' => TransactionType::PaymentVoucher])->first();
        $fields['running_balance'] = $this->supplierBalance($profile->id, $fields['total_price']);
        $fields['receipt_number'] = $transactionNumber->last_transaction_number + 1;
        $receipt = $profile->receipts()->create($fields);
        $transactionNumber->update(['last_transaction_number' => $fields['receipt_number']]);
        $payable = COA::where([
            'dental_lab_id' => $profile->lab->id,
            'doctor_id' => $profile->doctor->id, 'sub_type' => COASubType::Payable
        ])->first();
        $cash = COA::findOrFail($request->cash_coa);
        abort_unless($payable != null && $cash != null, 403);
        abort_unless($cash->doctor->id != auth()->user()->decoct->id, 403);
        abort_unless($cash->type == COASubType::Cash, 403);
        $doubleEntryFields['receipt_id'] = $receipt->id;
        $doubleEntryFields['total_price'] = $receipt->total_price;
        $doubleEntryFields['type'] = DoubleEntryType::Negative;
        $payable->doubleEntries()->create($doubleEntryFields);
        $cash->doubleEntries()->create($doubleEntryFields);
        return new ReceiptResource($receipt);
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
