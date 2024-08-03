<?php

namespace App\Services;

use App\Enums\AccountingProfileType;
use App\Enums\DoctorRoleForPatient;
use App\Enums\MaritalStatus;
use App\Enums\OfficeType;
use App\Enums\ReportType;
use App\Enums\Role;
use App\Http\Resources\MyPatientsResource;
use App\Models\Doctor;
use App\Models\HasRole;
use App\Models\MedicalCase;
use App\Models\Office;
use App\Models\Patient;
use App\Models\User;

class PatientService
{
    public function createPatient(array $extractedData, int $doctorId, int $officeId)
    {
        $fields = $extractedData;
        if ($fields['marital'] != null) {
            $fields['marital'] = MaritalStatus::getValue($fields['marital']);
        }
        // Check if the patient is a child
        // $isChild = $request->has('is_child') && $request->is_child == true;

        // // Set parent_id if it's a child
        // if ($isChild) {
        //     $parent = Patient::findOrFail($request->parent_id);

        //     // Set father or mother name based on parent's gender
        //     if ($parent->gender == Gender::Male) {
        //         $fields['father_name'] = $parent->first_name . ' ' . $parent->last_name;
        //     } elseif ($parent->gender == Gender::Female) {
        //         $fields['mother_name'] = $parent->first_name . ' ' . $parent->last_name;
        //     }

        //     unset($fields['phone']); // Remove phone field for child
        //     $fields['parent_id'] = $parent->id;
        // }
        if (auth()->user()->role == Role::Patient) {
            $patient = Patient::where('phone', $fields['phone'])->first();
            if ($patient || auth()->user()->patient) {
                return response()->noContent();
            }
            $patientInfo = auth()->user()->patient()->create($fields);
            $patientTeethReport = $patientInfo->report()->create([
                'report_type' => ReportType::TeethReport,
            ]);
            return response()->json($patientInfo);
        } elseif (auth()->user()->role == Role::Doctor) {
            $patient = Patient::where('phone', $fields['phone'])->first();
            $office = Office::findOrFail($officeId);
            $ownerUser = User::find($office->owner->user_id);
            $ownerDoctor = $ownerUser->doctor;
            if ($office->type == OfficeType::Combined) {
                if ($patient) {
                    $fields['doctor_id'] = $ownerDoctor->id;
                    $temporary = $patient->temporaries()->create($fields);
                    $role = HasRole::create([
                        'user_id' => $ownerUser->id,
                        'roleable_type' => 'App\Models\Patient',
                        'roleable_id' => $patient->id,
                        'sub_role' => DoctorRoleForPatient::DoctorWithoutApprove
                    ]);
                    $patientAccountingProfile = $patient->accountingProfiles()->create([
                        'office_id' => $office->id,
                        'doctor_id' => $ownerDoctor->id,
                        'type' => AccountingProfileType::PatientAccount,
                    ]);
                    $case = MedicalCase::where(['case_name' => Doctor::DefaultCase, 'doctor_id' => $ownerDoctor->id])->first();
                    $defaultCase = $patient->cases()->create([
                        'case_id' => $case->id,
                    ]);
                    return new MyPatientsResource($role);
                } else {
                    $doctor = $ownerDoctor;
                    $patientInfo = $doctor->patients()->create($fields);
                    $role = $ownerUser->roles()->create([
                        'roleable_type' => 'App\Models\Patient',
                        'roleable_id' => $patientInfo->id,
                        'sub_role' => DoctorRoleForPatient::DoctorWithApprove
                    ]);
                    $patientTeethReport = $patientInfo->report()->create([
                        'report_type' => ReportType::TeethReport,
                    ]);
                    $patientAccountingProfile = $patientInfo->accountingProfiles()->create([
                        'office_id' => $office->id,
                        'doctor_id' => $doctor->id,
                        'type' => AccountingProfileType::PatientAccount,
                    ]);
                    $case = MedicalCase::where(['case_name' => Doctor::DefaultCase, 'doctor_id' => $doctor->id])->first();
                    $defaultCase = $patientInfo->cases()->create([
                        'case_id' => $case->id,
                    ]);
                    return new MyPatientsResource($role);
                }
            } elseif ($office->type == OfficeType::Separate) {
                if ($patient) {
                    $fields['doctor_id'] = auth()->user()->doctor->id;
                    $temporary = $patient->temporaries()->create($fields);
                    $role = HasRole::create([
                        'user_id' => auth()->user()->id,
                        'roleable_type' => 'App\Models\Patient',
                        'roleable_id' => $patient->id,
                        'sub_role' => DoctorRoleForPatient::DoctorWithoutApprove
                    ]);
                    $patientAccountingProfile = $patient->accountingProfiles()->create([
                        'office_id' => $office->id,
                        'doctor_id' => $doctorId,
                        'type' => AccountingProfileType::PatientAccount,
                    ]);
                    $case = MedicalCase::where(['case_name' => Doctor::DefaultCase, 'doctor_id' => $doctorId])->first();
                    $defaultCase = $patient->cases()->create([
                        'case_id' => $case->id,
                    ]);
                    return new MyPatientsResource($role);
                } else {
                    $doctor = auth()->user()->doctor;
                    $patientInfo = $doctor->patients()->create($fields);
                    $role = auth()->user()->roles()->create([
                        'roleable_type' => 'App\Models\Patient',
                        'roleable_id' => $patientInfo->id,
                        'sub_role' => DoctorRoleForPatient::DoctorWithApprove
                    ]);
                    $patientTeethReport = $patientInfo->report()->create([
                        'report_type' => ReportType::TeethReport,
                    ]);
                    $patientAccountingProfile = $patientInfo->accountingProfiles()->create([
                        'doctor_id' => auth()->user()->doctor->id,
                        'office_id' => $office->id,
                        'type' => AccountingProfileType::PatientAccount,
                    ]);
                    $case = MedicalCase::where(['case_name' => Doctor::DefaultCase, 'doctor_id' => auth()->user()->doctor->id])->first();
                    $defaultCase = $patientInfo->cases()->create([
                        'case_id' => $case->id,
                    ]);
                    return new MyPatientsResource($role);
                }
            }
        }
    }
}
