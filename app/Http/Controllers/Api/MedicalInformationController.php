<?php

namespace App\Http\Controllers\Api;

use App\Enums\DoctorRoleForPatient;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMedicalInformationRequest;
use App\Http\Requests\UpdateMedicalInformationRequest;
use App\Http\Resources\MedicalInformationResource;
use App\Models\Doctor;
use App\Models\HasRole;
use App\Models\MedicalInformation;
use App\Models\Patient;
use Illuminate\Http\Request;

class MedicalInformationController extends Controller
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
    public function store(StoreMedicalInformationRequest $request)
    {
        $fields = $request->validated();
        $patient = Patient::findOrFail($request->patient_id);
        $doctor = Doctor::findOrFail($request->doctor_id);
        $role = HasRole::where(['roleable_id' => $request->patient_id, 'roleable_type' => 'App\Models\Patient', 'user_id' => auth()->id()])->first();
        if ($role->sub_role == DoctorRoleForPatient::DoctorWithApprove && !$patient->medicalInformation) {
            $fields['is_temporary'] = false;
            $medicalInformation = $patient->allMedicalInformation()->create($fields);
            return new MedicalInformationResource($medicalInformation);
        }
        if ($role->sub_role == DoctorRoleForPatient::DoctorWithoutApprove) {
            $fields['is_temporary'] = true;
            $medicalInformation = $patient->allMedicalInformation()->create($fields);
            return new MedicalInformationResource($medicalInformation);
        }
        if ($role->sub_role == DoctorRoleForPatient::DoctorWithApprove) {
            $medicalInformation = $patient->medicalInformation;
            $medicalInformation->update($fields);
            return new MedicalInformationResource($medicalInformation);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(MedicalInformation $info)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMedicalInformationRequest $request, MedicalInformation $info)
    {
        $fields = $request->validated();
        $info->update($fields);
        return new MedicalInformationResource($info);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MedicalInformation $medicalInformation)
    {
        //
    }
}
