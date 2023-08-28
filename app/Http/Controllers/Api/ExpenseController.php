<?php

namespace App\Http\Controllers\Api;

use App\Enums\COAGeneralType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseAccountRequest;
use App\Http\Resources\COAResource;
use App\Models\Doctor;
use App\Models\Office;
use Illuminate\Http\Request;

class ExpenseController extends Controller
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
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function storeExpenseAccount(StoreExpenseAccountRequest $request)
    {
        $fields = $request->validated();
        $fields['general_type'] = COAGeneralType::Expenses;
        $fields['isExpense'] = true;
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
}
