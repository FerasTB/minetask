<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDebtRequest;
use App\Http\Resources\DebtResource;
use App\Models\AccountingProfile;
use App\Models\Debt;
use Illuminate\Http\Request;

class DebtController extends Controller
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
    public function store(StoreDebtRequest $request)
    {
        $fields = $request->validated();
        $accountingProfile = AccountingProfile::where(['patient_id' => $request->patient_id, 'doctor_id' => auth()->user()->doctor->id])->first();
        $debt = $accountingProfile->debts()->create($fields);
        return new DebtResource($debt);
    }

    /**
     * Display the specified resource.
     */
    public function show(Debt $debt)
    {
        return new DebtResource($debt);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Debt $debt)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Debt $debt)
    {
        //
    }
}
