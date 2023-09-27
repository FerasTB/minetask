<?php

namespace App\Http\Controllers\Api\DentalLab;

use App\Enums\AccountingProfileType;
use App\Enums\COAGeneralType;
use App\Enums\COASubType;
use App\Enums\COAType;
use App\Enums\DoubleEntryType;
use App\Http\Controllers\Controller;
use App\Http\Requests\SetInitialBalanceRequest;
use App\Http\Requests\StoreCOARequest;
use App\Http\Requests\StoreDentalLabCOARequest;
use App\Http\Requests\UpdateCoaRequest;
use App\Http\Resources\COAResource;
use App\Http\Resources\DentalLabCOAResource;
use App\Models\AccountingProfile;
use App\Models\COA;
use App\Models\DentalLab;
use Illuminate\Http\Request;

class CoaController extends Controller
{

    public function index(DentalLab $lab)
    {
        $this->authorize('labOwner', [COA::class, $lab]);
        return DentalLabCOAResource::collection(
            $lab->COAS()
                ->with([
                    'lab', 'doubleEntries',
                ])
                ->get()
        );
    }

    public function store(StoreDentalLabCOARequest $request, DentalLab $lab)
    {
        $fields = $request->validated();
        $fields['general_type'] = COAGeneralType::getValue($request->general_type);
        if ($request->type) {
            $fields['type'] = COAType::getValue($request->type);
        }
        if ($request->sub_type) {
            $fields['sub_type'] = COASubType::getValue($request->sub_type);
        }
        $this->authorize('createForLab', [COA::class, $lab]);
        $coa = $lab->COAS()->create($fields);
        return new COAResource($coa);
    }

    public function update(UpdateCoaRequest $request, COA $coa)
    {
        $this->authorize('updateForOffice', [$coa, $coa->office]);
        $fields = $request->validated();
        $coa->update($fields);
        return new DentalLabCOAResource($coa);
    }

    public function setInitialBalance(SetInitialBalanceRequest $request, COA $coa)
    {
        if ($coa->sub_type && ($coa->sub_type == COASubType::Payable || $coa->sub_type == COASubType::Receivable)) {
            return response('you can set initial balance for this coa type', 403);
        }
        $fields = $request->validated();
        $this->authorize('updateBalanceForLab', [$coa]);
        if ($coa->initial_balance != 0) {
            return response('the initial balance only can be set once', 403);
        }
        $coa->update($fields);
        return new DentalLabCOAResource($coa);
    }

    public static function coaOutcomeInt(int $id)
    {
        $coa = COA::findOrFail($id);
        $positiveDoubleEntries = $coa->doubleEntries()->where('type', DoubleEntryType::Positive)->get();
        $totalPositive = $positiveDoubleEntries != null ?
            $positiveDoubleEntries->sum('total_price') : 0;
        $negativeDoubleEntries = $coa->doubleEntries()->where('type', DoubleEntryType::Negative)->get();
        $totalNegative = $negativeDoubleEntries != null ?
            $negativeDoubleEntries->sum('total_price') : 0;
        $positiveDirectDoubleEntries = $coa->directDoubleEntries()->where('type', DoubleEntryType::Positive)->get();
        $totalDirectPositive = $positiveDirectDoubleEntries != null ?
            $positiveDirectDoubleEntries->sum('total_price') : 0;
        $negativeDirectDoubleEntries = $coa->directDoubleEntries()->where('type', DoubleEntryType::Negative)->get();
        $totalDirectNegative = $negativeDirectDoubleEntries != null ?
            $negativeDirectDoubleEntries->sum('total_price') : 0;
        // coa payable or receivable cant have initial balance
        if ($coa->sub_type && $coa->sub_type == COASubType::Payable) {
            $subAccount = AccountingProfile::where(['dental_lab_id' => $coa->office->id, 'type' => AccountingProfileType::DentalLabSupplierAccount])->get();
            $totalBalanceAccount = $subAccount != null ?
                $subAccount->sum('initial_balance') : 0;
            $total = $totalPositive + $totalDirectPositive - $totalNegative - $totalDirectNegative + $totalBalanceAccount;
            return $total;
        }
        if ($coa->sub_type && $coa->sub_type == COASubType::Receivable) {
            $subAccount = AccountingProfile::where(['dental_lab_id' => $coa->office->id, 'type' => AccountingProfileType::DentalLabDoctorAccount])->get();
            $totalBalanceAccount = $subAccount != null ?
                $subAccount->sum('initial_balance') : 0;
            $total = $totalPositive + $totalDirectPositive - $totalNegative - $totalDirectNegative + $totalBalanceAccount;
            return $total;
        }
        $total = $totalPositive + $totalDirectPositive - $totalNegative - $totalDirectNegative + $coa->initial_balance;
        return $total;
    }
}
