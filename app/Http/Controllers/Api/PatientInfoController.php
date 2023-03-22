<?php

namespace App\Http\Controllers\Api;

use App\Enums\DoctorRoleForPatient;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePatientRequest;
use App\Models\HasRole;
use App\Models\Patient;
use App\Models\TemporaryInformation;
use Illuminate\Http\Request;

class PatientInfoController extends Controller
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
    public function store(StorePatientRequest $request)
    {
        $fields = $request->validated();
        if (auth()->user()->role == Role::Patient) {
            // $this->authorize('create', Patient::class);
            $patientInfo = auth()->user()->patient()->create($fields);
            return response()->json($patientInfo);
        } elseif (auth()->user()->role == Role::Doctor) {
            $patient = Patient::where('phone', $request->phone)->first();
            if ($patient) {
                // $fields['patient_id'] = $patient->id;
                $fields['doctor_id'] = auth()->user()->doctor->id;
                $temporary = $patient->temporaries()->create($fields);
                $role = HasRole::create([
                    'user_id' => auth()->id(),
                    'roleable_type' => 'App\Models\Patient',
                    'roleable_id' => $patient->id,
                    'sub_role' => DoctorRoleForPatient::DoctorWithoutApprove
                ]);
                return response()->json($temporary);
            } else {
                $doctor = auth()->user()->doctor;
                $patientInfo = $doctor->patients()->create($fields);
                $role = auth()->user()->roles()->create([
                    'roleable_type' => 'App\Models\Patient',
                    'roleable_id' => $patientInfo->id,
                    'sub_role' => DoctorRoleForPatient::DoctorWithApprove
                ]);
                return response()->json($patientInfo);
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Patient $patient)
    {
        // 
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Patient $patient)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patient)
    {
        //
    }
}