<?php

namespace App\Http\Controllers\Api;

use App\Enums\AccountingProfileType;
use App\Enums\DoctorRoleForPatient;
use App\Enums\Gender;
use App\Enums\MaritalStatus;
use App\Enums\OfficeType;
use App\Enums\ReportType;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\SetInitialBalanceForPatientRequest;
use App\Http\Requests\SetInitialBalanceRequest;
use App\Http\Requests\StorePatientInfoForPatientRequest;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientInfoForPatientRequest;
use App\Http\Resources\AccountingProfileResource;
use App\Http\Resources\DrugPatientIndexResource;
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
use Illuminate\Support\Facades\DB;

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
        // Check if the patient is a child
        $isChild = $request->has('is_child') && $request->is_child == true;

        // Set parent_id if it's a child
        if ($isChild) {
            $parent = Patient::findOrFail($request->parent_id);

            // Set father or mother name based on parent's gender
            if ($parent->gender == Gender::Male) {
                $fields['father_name'] = $parent->first_name . ' ' . $parent->last_name;
            } elseif ($parent->gender == Gender::Female) {
                $fields['mother_name'] = $parent->first_name . ' ' . $parent->last_name;
            }

            unset($fields['phone']); // Remove phone field for child
            $fields['parent_id'] = $parent->id;
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

    public function patientsDrug()
    {
        $this->authorize('viewRecord', Patient::class);
        $drugs = DB::table('drugs')
            ->join('diagnoses', 'diagnoses.id', '=', 'drugs.diagnosis_id')
            ->join('teeth_records', 'teeth_records.id', '=', 'diagnoses.record_id')
            ->join('patient_cases', 'patient_cases.id', '=', 'teeth_records.patientCase_id')
            ->join('medical_cases', 'medical_cases.id', '=', 'patient_cases.case_id')
            ->join('patients', 'patients.id', '=', 'patient_cases.patient_id')
            ->join('doctors', 'doctors.id', '=', 'medical_cases.doctor_id')
            ->join('offices', 'offices.id', '=', 'medical_cases.office_id')
            ->where('patient_cases.patient_id', auth()->user()->patient->id)
            ->get();
        return DrugPatientIndexResource::collection($drugs);
    }

    public function patientsInfo(StorePatientInfoForPatientRequest $request)
    {
        $fields = $request->validated();
        if ($request->marital) {
            $fields['marital'] = MaritalStatus::getValue($request->marital);
        }
        $patient = Patient::where('phone', $request->phone)->first();
        if ($patient || auth()->user()->patient) {
            return response()->noContent();
        }
        $patientInfo = auth()->user()->patient()->create($fields);
        $patientTeethReport = $patientInfo->report()->create([
            'report_type' => ReportType::TeethReport,
        ]);
        return response()->json($patientInfo);
    }

    public function updatePatientsInfo(UpdatePatientInfoForPatientRequest $request)
    {
        $this->authorize('updatePatientInfo');
        $fields = $request->validated();
        $patient = auth()->user()->patient;
        if ($request->marital) {
            $fields['marital'] = MaritalStatus::getValue($request->marital);
        }
        $patient->update($fields);
        return response()->json($patient);
    }

    public function setInitialBalance(SetInitialBalanceForPatientRequest $request, Office $office, Patient $patient)
    {
        $fields = $request->validated();
        $this->authorize('setInitialBalance', [$patient, $office]);
        $accounting = AccountingProfile::where([
            'doctor_id' => auth()->user()->doctor->id,
            'office_id' => $office->id,
            'patient_id' => $patient->id,
        ])->first();
        if ($accounting->initial_balance != 0 || $accounting == null) {
            return response('the initial balance only can be set once', 403);
        }
        $accounting->update($fields);
        return new AccountingProfileResource($accounting);
    }
}
