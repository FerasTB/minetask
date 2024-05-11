<?php

namespace App\Http\Controllers\Api;

use App\Enums\PatientCaseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePatientCaseStatusRequest;
use App\Http\Requests\StorePatientCaseRequest;
use App\Http\Resources\PatientCaseResource;
use App\Models\MedicalCase;
use App\Models\Patient;
use App\Models\PatientCase;
use Illuminate\Http\Request;

class PatientCaseController extends Controller
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
    public function store(StorePatientCaseRequest $request)
    {
        $fields = $request->validated();
        $case = MedicalCase::find($request->case_id);
        $patient = Patient::find($request->patient_id);
        $this->authorize('create', [PatientCase::class, $case, $patient]);
        $patientCase = $case->patientCases()->create($fields);
        $patientCase = PatientCase::find($patientCase->id);
        return new PatientCaseResource($patientCase);
    }

    /**
     * Display the specified resource.
     */
    public function show(PatientCase $patientCase)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PatientCase $patientCase)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PatientCase $patientCase)
    {
        //
    }

    public function patientCases(Patient $patient)
    {
        // $this->authorize('viewAny', [MedicalCase::class, $patient]);
        $doctor = auth()->user()->doctor;
        $cases = $doctor->PatientCases()->where('patient_id', $patient->id)
            ->with(['case', 'teethRecords', 'teethRecords.operations', 'teethRecords.diagnosis', 'teethRecords.operations.teeth'])->get();
        return PatientCaseResource::collection($cases);
    }

    public function ChangeStatus(ChangePatientCaseStatusRequest $request, PatientCase $case)
    {
        $this->authorize('update', [$case]);
        $fields = $request->validated();
        $status = PatientCaseStatus::getValue($fields['status']);
        $case->update([
            'status' => $status,
        ]);
        return new PatientCaseResource($case);
    }
}
