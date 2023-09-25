<?php

namespace App\Http\Controllers\Api\DentalLab;

use App\Enums\COAGeneralType;
use App\Enums\COASubType;
use App\Enums\COAType;
use App\Enums\DentalLabType;
use App\Enums\SubRole;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Models\DentalLab;
use App\Http\Requests\StoreDentalLabRequest;
use App\Http\Requests\UpdateDentalLabRequest;
use App\Http\Resources\DentalLabResource;
use App\Http\Resources\DentalLabThroughHasRoleResource;
use App\Models\COA;
use App\Models\HasRole;

class DentalLabController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $labs = HasRole::where(['user_id' => $user->id, 'roleable_type' => 'App\Models\DentalLab', 'sub_role' => SubRole::DentalLabOwner])->with('roleable')->get();
        if ($labs != []) {
            return DentalLabThroughHasRoleResource::collection($labs);
        } else {
            return response()->noContent();
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDentalLabRequest $request)
    {
        $this->authorize('create', DentalLab::class);
        $fields = $request->validated();
        if ($request->type) {
            $fields['type'] = DentalLabType::getValue($request->type);
        }
        $lab = DentalLab::create($fields);
        auth()->user()->roles()->create([
            'roleable_id' => $lab->id,
            'roleable_type' => 'App\Models\DentalLab',
            'sub_role' => SubRole::DentalLabOwner,
        ]);
        $lab->COAS()->create([
            'name' => COA::Receivable,
            'type' => COAType::Current,
            'general_type' => COAGeneralType::Asset,
            'sub_type' => COASubType::Receivable,
        ]);
        $lab->COAS()->create([
            'name' => COA::Cash,
            'type' => COAType::Current,
            'general_type' => COAGeneralType::Asset,
            'sub_type' => COASubType::Cash,
        ]);
        $lab->COAS()->create([
            'name' => COA::Payable,
            'type' => COAType::Current,
            'general_type' => COAGeneralType::Liability,
            'sub_type' => COASubType::Payable,
        ]);
        $lab->COAS()->create([
            'name' => COA::Capital,
            'type' => COAType::Capital,
            'general_type' => COAGeneralType::Equity,
        ]);
        $lab->COAS()->create([
            'name' => COA::OwnerWithDraw,
            'type' => COAType::OwnerWithdraw,
            'general_type' => COAGeneralType::Equity,
        ]);
        $lab->transactionPrefix()->create([
            'type' => TransactionType::PaymentVoucher,
            'prefix' => 'PVOC',
        ]);
        $lab->transactionPrefix()->create([
            'type' => TransactionType::SupplierInvoice,
            'prefix' => 'SINV',
        ]);
        $lab->transactionPrefix()->create([
            'type' => TransactionType::PatientInvoice,
            'prefix' => 'DINV',
        ]);
        $lab->transactionPrefix()->create([
            'type' => TransactionType::PatientReceipt,
            'prefix' => 'DREC',
        ]);
        // $doctor = auth()->user()->doctor;
        // $doctor->cases()->create([
        //     'case_name' => Doctor::DefaultCase,
        //     'office_id' => $office->id,
        // ]);
        return new DentalLabResource($lab);
    }

    /**
     * Display the specified resource.
     */
    public function show(DentalLab $dentalLab)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDentalLabRequest $request, DentalLab $dentalLab)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DentalLab $dentalLab)
    {
        //
    }
}
