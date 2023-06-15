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
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->doctor) {
            $office = Office::find($request->office);
            $this->authorize('viewAny', [Appointment::class, $office]);
            $appointments = Appointment::where(['office_id' => $request->office, 'doctor_id' => $request->doctor])
                ->with([
                    'patient',
                    'doctor',
                    'office',
                    'case',
                    'case.case',
                    'case.teethRecords',
                    'record',
                    'record.diagnosis',
                    'record.operations',
                    'record.diagnosis.teeth',
                    'record.operations.teeth',
                ])
                ->get();
            return AppointmentResource::collection($appointments);
        }
        $office = Office::find($request->office);
        $this->authorize('viewAny', [Appointment::class, $office]);
        $appointments = Appointment::where(['office_id' => $request->office, 'doctor_id' => auth()->user()->doctor->id])
            ->with([
                'patient',
                'doctor',
                'office',
                'case',
                'case.case',
                'case.teethRecords',
                'record',
                'record.diagnosis',
                'record.operations',
                'record.diagnosis.teeth',
                'record.operations.teeth',
            ])
            ->get();
        return AppointmentResource::collection($appointments);
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
        $appointment = $doctor->appointments()->create($fields);
        $appointment = Appointment::find($appointment->id);
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
