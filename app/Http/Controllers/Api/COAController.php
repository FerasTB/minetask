<?php

namespace App\Http\Controllers\Api;

use App\Enums\COAGeneralType;
use App\Enums\COAType;
use App\Enums\DoubleEntryType;
use App\Enums\OfficeType;
use App\Http\Controllers\Controller;
use App\Http\Requests\SetInitialBalanceRequest;
use App\Http\Requests\StoreCOARequest;
use App\Http\Resources\COAResource;
use App\Models\COA;
use App\Models\Doctor;
use App\Models\Office;
use Illuminate\Http\Request;

class COAController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $office = Office::findOrFail($request->office);
        $this->authorize('inOffice', [COA::class, $office]);
        if ($office->type == OfficeType::Separate) {
            $doctor = auth()->user()->doctor;
            return COAResource::collection(
                $doctor->COAS()
                    ->where('office_id', $office->id)
                    ->with([
                        'doctor', 'office'
                    ])
                    ->get()
            );
        } else {
            return COAResource::collection($office->COAS()->with(['office'])->get());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCOARequest $request)
    {
        $fields = $request->validated();
        $fields['general_type'] = COAGeneralType::getValue($request->general_type);
        if ($request->type) {
            $fields['type'] = COAType::getValue($request->type);
        }
        if ($request->sub_type) {
            $fields['sub_type'] = COAType::getValue($request->sub_type);
        }
        if ($request->doctor_id) {
            $doctor = Doctor::find($request->doctor_id);
            $this->authorize('createForDoctor', [COA::class, $doctor]);
            $coa = $doctor->COAS()->create($fields);
            return new COAResource($coa);
        }
        $office = Office::find($request->office_id);
        $this->authorize('createForOffice', [COA::class, $office]);
        $coa = $office->COAS()->create($fields);
        return new COAResource($coa);
    }

    /**
     * Display the specified resource.
     */
    public function show(COA $cOA)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, COA $cOA)
    {
        //
    }

    public function setInitialBalance(SetInitialBalanceRequest $request, COA $coa)
    {
        $fields = $request->validated();
        $this->authorize('update', [$coa]);
        if ($coa->initial_balance != 0) {
            return response('the initial balance only can be set once', 403);
        }
        $coa->update($fields);
        return new COAResource($coa);
    }

    public function coaOutcome(COA $coa)
    {
        $this->authorize('view', [$coa]);
        $positiveDoubleEntries = $coa->doubleEntries()->where('type', DoubleEntryType::Positive)->get();
        $totalPositive = $positiveDoubleEntries != null ?
            $positiveDoubleEntries->sum('amount') : 0;
        $negativeDoubleEntries = $coa->doubleEntries()->where('type', DoubleEntryType::Negative)->get();
        $totalNegative = $negativeDoubleEntries != null ?
            $negativeDoubleEntries->sum('amount') : 0;
        $positiveDirectDoubleEntries = $coa->directDoubleEntries()->where('type', DoubleEntryType::Positive)->get();
        $totalDirectPositive = $positiveDirectDoubleEntries != null ?
            $positiveDirectDoubleEntries->sum('total_price') : 0;
        $negativeDirectDoubleEntries = $coa->directDoubleEntries()->where('type', DoubleEntryType::Negative)->get();
        $totalDirectNegative = $negativeDirectDoubleEntries != null ?
            $negativeDirectDoubleEntries->sum('total_price') : 0;
        $total = $totalPositive + $totalDirectPositive - $totalNegative - $totalDirectNegative + $coa->initial_balance;
        return response()->json([
            'coa' => new COAResource($coa),
            'total' => $total,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(COA $cOA)
    {
        //
    }
}
