<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMedicalCaseRequest;
use App\Http\Requests\UpdateMedicalCaseRequest;
use App\Http\Resources\MedicalCaseResource;
use App\Models\MedicalCase;
use App\Models\Patient;
use Illuminate\Http\Request;

class CaseController extends Controller
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
    public function store(StoreMedicalCaseRequest $request)
    {
        $this->authorize('create', MedicalCase::class);
        $fields = $request->validated();
        $doctor = auth()->user()->doctor;
        $case = $doctor->cases()->create($fields);
        return new MedicalCaseResource($case);
    }

    /**
     * Display the specified resource.
     */
    public function show(MedicalCase $case)
    {
        $this->authorize('view', $case);
        return new MedicalCaseResource($case);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMedicalCaseRequest $request, MedicalCase $case)
    {
        $this->authorize('update', $case);
        $fields = $request->validated();
        $case->update($fields);
        return new MedicalCaseResource($case);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MedicalCase $medicalCase)
    {
        //
    }

    public function patientCases(Patient $patient)
    {
        $this->authorize('viewAny', [MedicalCase::class, $patient]);
        $doctor = auth()->user()->doctor;
        $cases = MedicalCase::where(['patient_id' => $patient->id, 'doctor_id' => $doctor->id])->get();
        return MedicalCaseResource::collection($cases);
    }
}