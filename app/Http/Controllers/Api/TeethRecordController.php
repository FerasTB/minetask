<?php

namespace App\Http\Controllers\Api;

use App\Enums\AppointmentStatus;
use App\Enums\DentalDoctorTransaction;
use App\Enums\OfficeType;
use App\Enums\PatientCaseStatus;
use App\Enums\TransactionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppointmentFirstStep;
use App\Http\Requests\StoreAppointmentNewFirstStep;
use App\Http\Requests\StoreTeethRecordRequest;
use App\Http\Requests\UpdateAfterTreatmentRequest;
use App\Http\Resources\AppointmentFirstStepResource;
use App\Http\Resources\OperationResource;
use App\Http\Resources\PatientCaseResource;
use App\Http\Resources\TeethRecordResource;
use App\Models\AccountingProfile;
use App\Models\Appointment;
use App\Models\Diagnosis;
use App\Models\DiagnosisList;
use App\Models\Doctor;
use App\Models\MedicalCase;
use App\Models\Office;
use App\Models\Operation;
use App\Models\Patient;
use App\Models\PatientCase;
use App\Models\TeethComplaintList;
use App\Models\TeethRecord;
use App\Models\User;
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

    public function storeWholeRecord(StoreAppointmentFirstStep $request)
    {
        $fields = $request->validated();
        $patient = Patient::findOrFail($request->patient_id);
        // $case = MedicalCase::find($request->case_id);
        $patientCase = PatientCase::findOrFail($fields['patientCase']);
        $case = MedicalCase::find($patientCase->case->id);
        $doctor = auth()->user()->doctor;
        abort_unless($case->doctor_id == $doctor->id, 403);
        if ($request->appointment_id) {
            $appointment = Appointment::findOrFail($request->appointment_id);
            abort_unless($appointment->doctor_id == $doctor->id, 403);
        } else {
            $office = $case->office;
            $doctor = auth()->user()->doctor;
            $appointment = $office->appointments()->create([
                'start_time' => '03:00:00',
                'end_time' => '04:00:00',
                'taken_date' => '2007-07-07',
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'status' => AppointmentStatus::Done,
                'step' => 4,
            ]);
            $fields['appointment_id'] = $appointment->id;
        }
        // $patientCase = PatientCase::where(['case_id' => $request->case_id, 'patient_id' => $patient->id])->first();
        // // $this->authorize('create', [Record::class, $case]);
        // if ($patientCase) {
        //     if ($patientCase->status == PatientCaseStatus::Closed) {
        //         $patientCase = $case->patientCases()->create($fields);
        //     }
        //     $fields['report_id'] = $patientCase->patient->teethReport->id;
        //     $record = $patientCase->teethRecords()->create($fields);
        //     if ($record->description != null) {
        //         $complaint = TeethComplaintList::firstOrCreate([
        //             'complaint' => $record->description,
        //         ]);
        //     }
        //     $fields['description'] = $request->diagnosis;
        //     $diagnosis = $record->diagnosis()->create($fields);
        //     if ($diagnosis->description != null) {
        //         $diagnosis = DiagnosisList::firstOrCreate([
        //             'description' => $diagnosis->description,
        //         ]);
        //     }
        //     $appointment->update([
        //         'patientCase_id' => $patientCase->id,
        //     ]);
        //     return response()->json([
        //         'patientCase_id' => $patientCase->id,
        //         'closable' => $case->case_name != Doctor::DefaultCase,
        //         'record_id' => $record->id,
        //         'diagnosis_id' => $record->diagnosis->id,
        //     ]);
        // }
        // $patientCase = $case->patientCases()->create($fields);
        $fields['report_id'] = $patientCase->patient->teethReport->id;
        $record = $patientCase->teethRecords()->create($fields);
        if ($record->description != null) {
            $complaint = TeethComplaintList::firstOrCreate([
                'complaint' => $record->description,
            ]);
        }
        $fields['description'] = $request->diagnosis;
        $diagnosis = $record->diagnosis()->create($fields);
        if ($diagnosis->description != null) {
            $diagnosis = DiagnosisList::firstOrCreate([
                'description' => $diagnosis->description,
            ]);
        }
        $appointment->update([
            'patientCase_id' => $patientCase->id,
        ]);
        if ($fields['with_draft']) {
            $office = Office::findOrFail($request->office_id);
            if ($office->type == OfficeType::Combined) {
                $owner = User::findOrFail($office->owner->user_id);
                $profile = AccountingProfile::where([
                    'patient_id' => $patient->id,
                    'office_id' => $office->id, 'doctor_id' => $owner->doctor->id
                ])->first();
                $fields['type'] = DentalDoctorTransaction::SellInvoice;
                $fields['status'] = TransactionStatus::Draft;
                if (!$request->has('date_of_invoice')) {
                    $fields['date_of_invoice'] = now();
                }
                $invoice = $profile->invoices()->create($fields);
            } else {
                $profile = AccountingProfile::where([
                    'patient_id' => $patient->id,
                    'office_id' => $office->id, 'doctor_id' => $request->doctor_id
                ])->first();
                $fields['type'] = DentalDoctorTransaction::SellInvoice;
                $fields['status'] = TransactionStatus::Draft;
                $invoice = $profile->invoices()->create($fields);
            }
            $withDraft = True;
        }
        if ($request->has('operations')) {
            foreach ($fields['operations'] as $operation) {
                $operation = $record->operations()->create($fields);
                foreach ($operation['teeth'] as $tooth) {
                    $tooth = $operation->teeth()->create(['number_of_tooth' => $tooth]);
                }
                if ($withDraft) {
                    $fields['description'] = $fields['operation_description'];
                    $fields['name'] = $fields['operation_name'];
                    $item = $invoice->items()->create($fields);
                }
            }
        }
        $diagnosis = Diagnosis::find($diagnosis->id);
        foreach ($fields['diagnosis_teeth'] as $diagnosis_tooth) {
            $tooth = $diagnosis->teeth()->create(['number_of_tooth' => $diagnosis_tooth]);
        }

        return response()->json([
            'patientCase_id' => $patientCase->id,
            'closable' => $case->case_name != Doctor::DefaultCase,
            'record_id' => $record->id,
            'diagnosis_id' => $record->diagnosis->id,
        ]);
    }

    public function firstStep(StoreAppointmentNewFirstStep $request)
    {
        $fields = $request->validated();
        $doctor = auth()->user()->doctor;
        $case = MedicalCase::find($request->case_id);
        abort_unless($case->doctor_id == $doctor->id, 403);
        $patientCase = $case->patientCases()->create($fields);
        $cases = $doctor->PatientCases()->where('patient_id', $fields['patient_id'])
            ->with(['case', 'teethRecords', 'teethRecords.operations', 'teethRecords.diagnosis'])->get();
        return PatientCaseResource::collection($cases);
    }

    public function AfterTreatmentUpdate(TeethRecord $record, UpdateAfterTreatmentRequest $request)
    {
        $fields = $request->validated();
        $record->update($fields);
        return new TeethRecordResource($record);
    }
}
