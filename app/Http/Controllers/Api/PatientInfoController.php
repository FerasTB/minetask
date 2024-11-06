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
use App\Http\Requests\UpdateDoctorsAccessRequest;
use App\Http\Requests\UpdatePatientInfoForPatientRequest;
use App\Http\Resources\AccountingProfileResource;
use App\Http\Resources\DrugPatientIndexResource;
use App\Http\Resources\MedicalInformationResource;
use App\Http\Resources\MyDoctorThroughAccountingProfileResource;
use App\Http\Resources\MyPatientsResource;
use App\Http\Resources\TeethRecordResource;
use App\Models\AccountingProfile;
use App\Models\Doctor;
use App\Models\EmployeeSetting;
use App\Models\HasRole;
use App\Models\MedicalCase;
use App\Models\Office;
use App\Models\Patient;
use App\Models\Role as ModelsRole;
use App\Models\TemporaryInformation;
use App\Models\User;
use Exception;
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
        $office = Office::findOrFail($request->office_id);
        if (in_array(auth()->user()->currentRole->name, ModelsRole::Technicians)) {
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
        DB::beginTransaction();

        try {

            if (!$request->has('numberPrefix')) {
                $fields['numberPrefix'] = '+963';
            }

            if (!$request->has('country')) {
                $fields['country'] = 'Syria';
            }

            if ($request->marital) {
                $fields['marital'] = MaritalStatus::getValue($request->marital);
            }
            // Check if the patient is a child
            $isChild = $request->has('is_child') && boolval($request->is_child) == true;

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

            if (auth()->user()->currentRole->name == 'Patient') {
                $patient = Patient::whereHas('info', function ($query) use ($fields) {
                    $query->where('numberPrefix', $fields['numberPrefix'])
                        ->where('phone', $fields['phone']);
                })->first();

                if ($patient || auth()->user()->patient) {
                    DB::commit(); // Commit the transaction
                    return response()->noContent();
                }

                $patientInfo = auth()->user()->patient()->create($fields);

                // Create or update the user info with phone prefix and country
                $patientInfo->info()->create([
                    'numberPrefix' => $fields['numberPrefix'],
                    'country' => $fields['country'],
                ]);

                $patientTeethReport = $patientInfo->report()->create([
                    'report_type' => ReportType::TeethReport,
                ]);

                DB::commit(); // Commit the transaction

                return response()->json($patientInfo);
            } elseif (in_array(auth()->user()->currentRole->name, ModelsRole::AddPatient)) {
                if ($isChild) {
                    $patient = Patient::where('parent_id', $request->parent_id)->first();
                } else {
                    $patient = Patient::whereHas('info', function ($query) use ($fields) {
                        $query->where('numberPrefix', $fields['numberPrefix'])
                            ->where('phone', $fields['phone']);
                    })->first();
                }


                $ownerUser = User::find($office->owner->user_id);
                $ownerDoctor = $ownerUser->doctor;

                if ($office->type == OfficeType::Combined) {
                    if ($patient) {
                        $fields['doctor_id'] = $ownerDoctor->id;
                        $oldTemporary = $patient->temporaries()->where('doctor_id', $fields['doctor_id'])->first();
                        abort_unless($oldTemporary == null, 403, 'the user is already exist');
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

                        DB::commit(); // Commit the transaction

                        return new MyPatientsResource($role, $doctor);
                    } else {
                        $doctor = $ownerDoctor;
                        $patientInfo = $doctor->patients()->create($fields);

                        // Create UserInfo for the new patient
                        $patientInfo->info()->create([
                            'numberPrefix' => $fields['numberPrefix'],
                            'country' => $fields['country'],
                        ]);


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

                        DB::commit(); // Commit the transaction

                        return new MyPatientsResource($role, $doctor);
                    }
                } elseif ($office->type == OfficeType::Separate) {
                    if ($patient) {
                        $fields['doctor_id'] = $doctor->id;
                        $oldTemporary = $patient->temporaries()->where('doctor_id', $fields['doctor_id'])->first();
                        abort_unless($oldTemporary == null, 403, 'the user is already exist');
                        $temporary = $patient->temporaries()->create($fields);

                        $role = HasRole::create([
                            'user_id' => $doctor->user->id,
                            'roleable_type' => 'App\Models\Patient',
                            'roleable_id' => $patient->id,
                            'sub_role' => DoctorRoleForPatient::DoctorWithoutApprove
                        ]);

                        $patientAccountingProfile = $patient->accountingProfiles()->create([
                            'office_id' => $office->id,
                            'doctor_id' => $doctor->id,
                            'type' => AccountingProfileType::PatientAccount,
                        ]);

                        $case = MedicalCase::where(['case_name' => Doctor::DefaultCase, 'doctor_id' => $doctor->id])->first();

                        $defaultCase = $patient->cases()->create([
                            'case_id' => $case->id,
                        ]);

                        DB::commit(); // Commit the transaction

                        return new MyPatientsResource($role, $doctor);
                    } else {
                        $patientInfo = $doctor->patients()->create($fields);
                        $patientInfo->info()->create([
                            'numberPrefix' => $fields['numberPrefix'],
                            'country' => $fields['country'],
                        ]);


                        $role = $user->roles()->create([
                            'roleable_type' => 'App\Models\Patient',
                            'roleable_id' => $patientInfo->id,
                            'sub_role' => DoctorRoleForPatient::DoctorWithApprove
                        ]);

                        $patientTeethReport = $patientInfo->report()->create([
                            'report_type' => ReportType::TeethReport,
                        ]);

                        $patientAccountingProfile = $patientInfo->accountingProfiles()->create([
                            'doctor_id' => $doctor->id,
                            'office_id' => $office->id,
                            'type' => AccountingProfileType::PatientAccount,
                        ]);

                        $case = MedicalCase::where(['case_name' => Doctor::DefaultCase, 'doctor_id' => $doctor->id])->first();

                        $defaultCase = $patientInfo->cases()->create([
                            'case_id' => $case->id,
                        ]);

                        DB::commit(); // Commit the transaction

                        return new MyPatientsResource($role, $doctor);
                    }
                }
            }
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage() ?? 'Something went wrong, please try again later.'], 500);
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

    public function getPatientInfo(Request $request)
    {
        // $fields = $request->validated();
        if (!$request->has('numberPrefix')) {
            $fields['numberPrefix'] = '+963';
        }

        if (!$request->has('country')) {
            $fields['country'] = 'Syria';
        }
        $patient = Patient::whereHas('info', function ($query) use ($fields) {
            $query->where('numberPrefix', $fields['numberPrefix'])
                ->where('phone', auth()->user()->phone)
                ->where('user_id', null);
        })->first();

        if (!$patient) {
            return response()->json(['error' => 'Patient profile not found'], 404);
        }

        return response()->json($patient);
    }

    public function completePatientInfo(StorePatientInfoForPatientRequest $request)
    {
        $fields = $request->validated();
        if ($request->marital) {
            $fields['marital'] = MaritalStatus::getValue($request->marital);
        }
        if (!$request->has('numberPrefix')) {
            $fields['numberPrefix'] = '+963';
        }

        if (!$request->has('country')) {
            $fields['country'] = 'Syria';
        }
        $patient = Patient::whereHas('info', function ($query) use ($fields) {
            $query->where('numberPrefix', $fields['numberPrefix'])
                ->where('phone', $fields['phone']);
        })->first();
        if ($patient) {
            $patient->update([
                'first_name'   => $fields['first_name'],
                'father_name'  => $fields['father_name'],
                'marital'      => $fields['marital'],
                'mother_name'  => $fields['mother_name'],
                'last_name'    => $fields['last_name'],
                'birth_date'   => $fields['birth_date'],
                'gender'       => $fields['gender'],
                'user_id' => auth()->id(),
            ]);
        } else {
            $patient = auth()->user()->patient()->create($fields);
            $patientTeethReport = $patient->report()->create([
                'report_type' => ReportType::TeethReport,
            ]);
            // Create or update the user info with phone prefix and country
            $patient->info()->create([
                'numberPrefix' => $fields['numberPrefix'],
                'country' => $fields['country'],
            ]);
        }
        return response()->json($patient);
    }

    public function listDoctors(Request $request)
    {
        $user = auth()->user();
        $patient = $user->patient;

        if (!$patient) {
            return response()->json(['error' => 'Patient profile not found'], 404);
        }

        $doctorRoles = HasRole::where('roleable_type', Patient::class)
            ->where('roleable_id', $patient->id)
            ->with('user.doctor')
            ->get();

        $doctors = $doctorRoles->map(function ($role) {
            $doctorUser = $role->user;
            $doctor = $doctorUser->doctor;

            return [
                'doctor_id' => $doctor->id,
                'name' => $doctorUser->full_name,
                'phone' => $doctorUser->phone,
                'approved' => $role->sub_role != DoctorRoleForPatient::DoctorWithoutApprove,
                'role_id' => $role->id,
            ];
        });

        return response()->json(['doctors' => $doctors]);
    }

    public function updateDoctorsAccess(UpdateDoctorsAccessRequest $request)
    {
        $user = auth()->user();
        $patient = $user->patient;

        if (!$patient) {
            return response()->json(['error' => 'Patient profile not found'], 404);
        }

        $doctorsData = $request->input('doctors');
        $responses = [];
        $errors = [];

        // Use a transaction to ensure all updates succeed or fail together
        DB::beginTransaction();

        try {
            foreach ($doctorsData as $doctorData) {
                $doctorId = $doctorData['doctor_id'];
                $doctor = Doctor::findOrFail($doctorId);
                $approve = $doctorData['approve'];

                $role = HasRole::where('roleable_type', Patient::class)
                    ->where('roleable_id', $patient->id)
                    ->where('user_id', $doctor->user->id)
                    ->first();

                if (!$role) {
                    // Collect error but continue processing other doctors
                    $errors[] = [
                        'doctor_id' => $doctorId,
                        'error' => 'Access request not found for this doctor.',
                    ];
                    continue;
                }

                // Update the sub_role based on approval
                $role->sub_role = $approve
                    ? DoctorRoleForPatient::DoctorWithApprove
                    : DoctorRoleForPatient::DoctorWithoutApprove;

                $role->save();

                $responses[] = [
                    'doctor_id' => $doctorId,
                    'message' => 'Doctor access updated successfully.',
                ];
            }

            // If there are errors, rollback the transaction
            if (!empty($errors)) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Some doctor access updates failed.',
                    'successes' => $responses,
                    'errors' => $errors,
                ], 207); // 207 Multi-Status
            }

            DB::commit();

            return response()->json([
                'message' => 'All doctor access updates succeeded.',
                'successes' => $responses,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'An error occurred while updating doctor access.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }


    // public function showProfile(Request $request)
    // {
    //     $user = auth()->user();
    //     $patient = $user->patient;

    //     if (!$patient) {
    //         return response()->json(['error' => 'Patient profile not found'], 404);
    //     }

    //     // Eager load relationships
    //     $patient->load([
    //         'info',
    //         'cases.medicalCase',
    //         'cases.teethRecords.operations',
    //         'cases.teethRecords.diagnosis',
    //         'notes.doctor.user',
    //     ]);

    //     // Get doctors with access
    //     $doctorRoles = HasRole::where('roleable_type', Patient::class)
    //         ->where('roleable_id', $patient->id)
    //         ->with('user.doctor')
    //         ->get();

    //     $doctors = $doctorRoles->map(function ($role) {
    //         $doctorUser = $role->user;
    //         $doctor = $doctorUser->doctor;

    //         return [
    //             'doctor_id' => $doctor->id,
    //             'name' => $doctorUser->name,
    //             'email' => $doctorUser->email,
    //             'approved' => $role->sub_role != DoctorRoleForPatient::DoctorWithoutApprove,
    //         ];
    //     });

    //     return new Pattient([
    //         'patient' => $patient,
    //         'doctors' => $doctors,
    //     ]);
    // }

    public function setInitialBalance(SetInitialBalanceForPatientRequest $request, Office $office, Patient $patient)
    {
        if (in_array(auth()->user()->currentRole->name, ModelsRole::Technicians)) {
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
        $fields = $request->validated();
        $this->authorize('setInitialBalance', [$patient, $office, $doctor->user]);
        $accounting = AccountingProfile::where([
            'doctor_id' => $doctor->id,
            'office_id' => $office->id,
            'patient_id' => $patient->id,
        ])->first();
        if ($accounting->initial_balance != 0 || $accounting == null) {
            return response('the initial balance only can be set once', 403);
        }
        $accounting->update($fields);
        return new AccountingProfileResource($accounting, $doctor->user);
    }
}
