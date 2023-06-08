<?php

namespace App\Http\Controllers\Api;

use App\Enums\PatientCaseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppointmentFirstStep;
use App\Http\Requests\StoreTeethRecordRequest;
use App\Http\Requests\UpdateAfterTreatmentRequest;
use App\Http\Resources\AppointmentFirstStepResource;
use App\Http\Resources\TeethRecordResource;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\MedicalCase;
use App\Models\Patient;
use App\Models\PatientCase;
use App\Models\TeethComplaintList;
use App\Models\TeethRecord;
use Illuminate\Http\Request;

class TeethRecordController extends Controller
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
    public function store(StoreTeethRecordRequest $request)
    {
        $fields = $request->validated();
        $case = PatientCase::find($request->patientCase_id);
        // $this->authorize('create', [Record::class, $case]);
        $fields['report_id'] = $case->patient->teethReport->id;
        $record = $case->teethRecords()->create($fields);
        if ($record->description != null) {
            $complaint = TeethComplaintList::firstOrCreate([
                'complaint' => $record->description,
            ]);
        }
        return new TeethRecordResource($record);
    }

    /**
     * Display the specified resource.
     */
    public function show(TeethRecord $record)
    {
        return new TeethRecordResource($record);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TeethRecord $teethRecord)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TeethRecord $teethRecord)
    {
        //
    }

    public function CaseRecords(PatientCase $case)
    {
        // $this->authorize('viewAny', [Record::class, $case]);
        return TeethRecordResource::collection($case->teethRecords);
    }

    public function firstStep(StoreAppointmentFirstStep $request)
    {
        $fields = $request->validated();
        $patient = Patient::findOrFail($request->patient_id);
        $case = MedicalCase::find($request->case_id);
        $patientCase = PatientCase::where(['case_id' => $request->case_id, 'patient_id' => $patient->id])->first();
        // $this->authorize('create', [Record::class, $case]);
        if ($patientCase) {
            if ($patientCase->status == PatientCaseStatus::Closed) {
                $patientCase = $case->patientCases()->create($fields);
            }
            $fields['report_id'] = $patientCase->patient->teethReport->id;
            $record = $patientCase->teethRecords()->create($fields);
            $fields['description'] = $request->diagnosis;
            $diagnosis = $record->diagnosis()->create($fields);
            return response()->json([
                'patientCase_id' => $patientCase->id,
                'closable' => $case->case_name != Doctor::DefaultCase,
                'record_id' => $record->id,
                'diagnosis_id' => $diagnosis->id,
            ]);
        }
        $patientCase = $case->patientCases()->create($fields);
        $fields['report_id'] = $patientCase->patient->teethReport->id;
        $record = $patientCase->teethRecords()->create($fields);
        $fields['description'] = $request->diagnosis;
        $diagnosis = $record->diagnosis()->create($fields);
        return response()->json([
            'patientCase_id' => $patientCase->id,
            'closable' => $case->case_name != Doctor::DefaultCase,
            'record_id' => $record->id,
            'diagnosis_id' => $diagnosis->id,
        ]);
    }

    public function AfterTreatmentUpdate(TeethRecord $record, UpdateAfterTreatmentRequest $request)
    {
        $fields = $request->validated();
        $record->update($fields);
        return new TeethRecordResource($record);
    }
}
