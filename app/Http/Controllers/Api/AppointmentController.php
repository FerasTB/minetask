<?php

namespace App\Http\Controllers\Api;

use App\Enums\AppointmentStatus;
use App\Enums\PatientInClinicStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Http\Requests\UpdateAppointmentStatusRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Office;
use App\Models\OfficeRoom;
use App\Models\PatientCase;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->doctor) {
            $office = Office::findOrFail($request->office);
            $this->authorize('viewAny', [Appointment::class, $office]);
            if ($request->room) {
                $room = OfficeRoom::findOrFail($request->room);
                $this->authorize('viewAnyWithRoom', [Appointment::class, $office, $room]);
                $appointments = Appointment::where(['office_id' => $request->office, 'doctor_id' => $request->doctor, 'office_room_id' => $room->id])
                    ->with([
                        'patient',
                        'patient.doctorImage',
                        'doctor',
                        'office',
                        'case',
                        'room',
                        'case.case',
                        'case.teethRecords',
                        'record',
                        'record.diagnosis',
                        'record.diagnosis.drug',
                        'record.operations',
                        'record.diagnosis.teeth',
                        'record.operations.teeth',
                    ])
                    ->get();
                return AppointmentResource::collection($appointments);
            }
            $appointments = Appointment::where(['office_id' => $request->office, 'doctor_id' => $request->doctor])
                ->with([
                    'patient',
                    'patient.doctorImage',
                    'doctor',
                    'office',
                    'case',
                    'room',
                    'case.case',
                    'case.teethRecords',
                    'record',
                    'record.diagnosis',
                    'record.diagnosis.drug',
                    'record.operations',
                    'record.diagnosis.teeth',
                    'record.operations.teeth',
                ])
                ->get();
            return AppointmentResource::collection($appointments);
        }
        $office = Office::findOrFail($request->office);
        if ($request->room) {
            $room = OfficeRoom::findOrFail($request->room);
            $this->authorize('viewAnyWithRoom', [Appointment::class, $office, $room]);
            $appointments = Appointment::where(['office_id' => $request->office, 'doctor_id' => $request->doctor, 'office_room_id' => $room->id])
                ->with([
                    'patient',
                    'patient.doctorImage',
                    'doctor',
                    'office',
                    'case',
                    'room',
                    'case.case',
                    'case.teethRecords',
                    'record',
                    'record.diagnosis',
                    'record.diagnosis.drug',
                    'record.operations',
                    'record.diagnosis.teeth',
                    'record.operations.teeth',
                ])
                ->get();
            return AppointmentResource::collection($appointments);
        }
        $this->authorize('viewAny', [Appointment::class, $office]);
        $appointments = Appointment::where(['office_id' => $request->office, 'doctor_id' => auth()->user()->doctor->id])
            ->with([
                'patient',
                'patient.doctorImage',
                'doctor',
                'office',
                'case',
                'room',
                'case.case',
                'case.teethRecords',
                'record',
                'record.diagnosis',
                'record.diagnosis.drug',
                'record.operations',
                'record.diagnosis.teeth',
                'record.operations.teeth',
            ])
            ->get();
        return AppointmentResource::collection($appointments);
    }

    public function indexForPatient(Request $request)
    {
        $this->authorize('viewPatient', [Appointment::class]);
        return AppointmentResource::collection(auth()->user()->patient->appointments()
            ->with([
                'doctor',
                'office',
                'room',
            ])
            ->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAppointmentRequest $request)
    {
        $fields = $request->validated();
        if (!$request->doctor_id) {
            $doctor = auth()->user()->doctor;
            $fields['doctor_id'] = $doctor->id;
        } else {
            $doctor = Doctor::find($request->doctor_id);
        }
        if ($request->patientCase_id) {
            $patientCase = PatientCase::findOrFail($request->patientCase_id);
            $this->authorize('update', $patientCase);
        }
        $appointment = $doctor->appointments()->create($fields);
        $appointment = Appointment::find($appointment->id);
        $appointment->load(
            'patient',
            'patient.doctorImage',
            'doctor',
            'office',
            'room',
            'case',
            'case.case',
            'case.teethRecords',
            'record',
            'record.diagnosis',
            'record.diagnosis.drug',
            'record.operations',
            'record.diagnosis.teeth',
            'record.operations.teeth',
        );
        return new AppointmentResource($appointment);
    }

    /**
     * Display the specified resource.
     */
    public function show(Appointment $appointment)
    {
        $this->authorize('view', $appointment);
        return new AppointmentResource($appointment);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        $this->authorize('update', $appointment);
        $fields = $request->validated();
        $appointment->update($fields);
        return new AppointmentResource($appointment);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Appointment $appointment)
    {
        //
    }

    public function appointmentStatusUpdate(UpdateAppointmentStatusRequest $request, Appointment $appointment)
    {
        $this->authorize('update', $appointment);
        $fields = $request->validated();
        $fields['status'] = AppointmentStatus::getValue($request->status);
        if ($request->is_patient_in_clinic != null) {
            $fields['is_patient_in_clinic'] = PatientInClinicStatus::getValue($request->is_patient_in_clinic);
        }
        $appointment->update($fields);
        return response('Status Updated', 200);
    }
}
