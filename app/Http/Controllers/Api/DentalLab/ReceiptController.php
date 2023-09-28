<?php

namespace App\Http\Controllers\Api\DentalLab;

use App\Enums\COASubType;
use App\Enums\DoubleEntryType;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDentalLabReceiptForDoctorRequest;
use App\Http\Requests\StorePatientReceiptRequest;
use App\Http\Resources\ReceiptResource;
use App\Models\AccountingProfile;
use App\Models\COA;
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
        $fields['running_balance'] = AccountingProfileController::doctorBalance($profile->id, $fields['total_price']);
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
}
