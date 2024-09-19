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
use App\Models\EmployeeSetting;
use App\Models\HasRole;
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
        // Authorize first before any querying
        $office = Office::findOrFail($request->office);
        if (auth()->user()->currentRole->name == 'DentalDoctorTechnician') {
            // Find the role based on user_id and office_id (roleable_id)
            $role = HasRole::where('user_id', auth()->id())
                ->where('roleable_id', $office->id)
                ->first();

            if (!$role) {
                // Return JSON response if no role is found
                return response()->json([
                    'error' => 'Role not found for the given user and office.',
                ], 403);
            }

            // Find the employee setting based on the has_role_id
            $employeeSetting = EmployeeSetting::where('has_role_id', $role->id)->first();

            if (!$employeeSetting) {
                // Return JSON response if no employee setting is found
                return response()->json([
                    'error' => 'Employee setting not found for the given role.',
                ], 403);
            }
            $doctor = Doctor::findOrFail($employeeSetting->doctor_id);
            $user = $doctor->user;
        } else {
            // Ensure a valid doctor is authenticated
            $doctor = auth()->user()->doctor;
            $user = auth()->user();
        }

        if (!$doctor) {
            return response('You have to complete your info', 404);
        }
        $this->authorize('viewAny', [Appointment::class, $office]);

        // Determine if a specific room is requested
        $roomCondition = $request->room ? ['office_room_id' => $request->room] : [];
        $doctorCondition = $request->doctor ? ['doctor_id' => $request->doctor] : [];

        // Build the base query with eager loading
        $appointmentsQuery = Appointment::where(array_merge(
            ['office_id' => $request->office],
            $doctorCondition,
            $roomCondition
        ))
            ->with([
                'patient' => function ($query) {
                    $query->with(['doctorImage', 'roles' => function ($query) {
                        $query->where('roleable_type', 'App\Models\Patient')
                            ->where('user_id', auth()->id()); // Specific user ID
                    }, 'temporaries' => function ($query, $doctor) {
                        $query->where('doctor_id', $doctor->id);
                    }]);
                },
                'doctor',
                'office',
                'room',
                'record' => function ($query) {
                    $query->with('diagnosis.drug', 'diagnosis.teeth', 'operations.teeth');
                }
            ]);

        // Fetch the appointments
        $appointments = $appointmentsQuery->get();

        return AppointmentResource::collection($appointments)->map(function ($accountProfile) use ($user) {
            return new AppointmentResource($accountProfile, $user);
        });
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
        $office = Office::findOrFail($request->office_id);
        if (!$request->doctor_id) {
            $doctor = auth()->user()->doctor;
            $fields['doctor_id'] = $doctor->id;
        } else {
            $doctor = Doctor::find($request->doctor_id);
        }
        $this->authorize('createForDoctor', [Appointment::class, $office, $doctor]);
        if ($request->patientCase_id) {
            $patientCase = PatientCase::findOrFail($request->patientCase_id);
            $this->authorize('update', $patientCase);
        }
        if ($request->office_room_id) {
            $room = OfficeRoom::findOrFail($request->office_room_id);
            abort_unless($room->office_id == $office->id, 403);
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
