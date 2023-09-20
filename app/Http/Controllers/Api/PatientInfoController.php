<?php

namespace App\Http\Controllers\Api;

use App\Enums\AccountingProfileType;
use App\Enums\DoctorRoleForPatient;
use App\Enums\MaritalStatus;
use App\Enums\OfficeType;
use App\Enums\ReportType;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePatientRequest;
use App\Http\Resources\MedicalInformationResource;
use App\Http\Resources\MyDoctorThroughAccountingProfileResource;
use App\Http\Resources\MyPatientsResource;
use App\Http\Resources\TeethRecordResource;
use App\Models\AccountingProfile;
use App\Models\Doctor;
use App\Models\HasRole;
use App\Models\MedicalCase;
use App\Models\Office;
use App\Models\Patient;
use App\Models\TemporaryInformation;
use App\Models\User;
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
        if ($request->marital) {
            $fields['marital'] = MaritalStatus::getValue($request->marital);
        }
        if (auth()->user()->role == Role::Patient) {
            $patient = Patient::where('phone', $request->phone)->first();
            if ($patient || auth()->user()->patient) {
                return response()->noContent();
            }
            $patientInfo = auth()->user()->patient()->create($fields);
            $patientTeethReport = $patientInfo->report()->create([
                'report_type' => ReportType::TeethReport,
            ]);
            return response()->json($patientInfo);
        } elseif (auth()->user()->role == Role::Doctor) {
            $patient = Patient::where('phone', $request->phone)->first();
            $office = Office::findOrFail($request->office_id);
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
                        'doctor_id' => auth()->user()->doctor->id,
                        'type' => AccountingProfileType::PatientAccount,
                    ]);
                    $case = MedicalCase::where(['case_name' => Doctor::DefaultCase, 'doctor_id' => auth()->user()->doctor->id])->first();
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

    public function showMedicalInformation(Patient $patient)
    {
        $role = HasRole::where(['roleable_id' => $patient->id, 'roleable_type' => 'App\Models\Patient', 'user_id' => auth()->id()])->first();
        if ($role) {
            if ($role->sub_role == DoctorRoleForPatient::DoctorWithApprove) {
                return new MedicalInformationResource($patient->medicalInformation);
            }
            if ($role->sub_role == DoctorRoleForPatient::DoctorWithoutApprove) {
                return new MedicalInformationResource($patient->allMedicalInformation->where('doctor_id', auth()->user()->doctor->id));
            }
        } else {
            return response('medical info not found', 404);
        }
    }

    public function patientsRecord()
    {
        $this->authorize('viewRecord', Patient::class);
        $patient = auth()->user()->patient;
        return TeethRecordResource::collection($patient->teethRecords);
    }

    public function patientsDoctor()
    {
        $this->authorize('viewRecord', Patient::class);
        $patient = auth()->user()->patient;
        $accounts = AccountingProfile::where(['patient_id' => $patient->id, 'type' => AccountingProfileType::PatientAccount])->with(['office', 'patient', 'doctor'])->get();
        return MyDoctorThroughAccountingProfileResource::collection($accounts);
    }
}
