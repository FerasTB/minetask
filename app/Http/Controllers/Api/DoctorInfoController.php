<?php

namespace App\Http\Controllers\Api;

use App\Enums\AccountingProfileType;
use App\Enums\OfficeType;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\MarkAsReadRequest;
use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorInfoRequest;
use App\Http\Resources\AccountingProfileResource;
use App\Http\Resources\DoctorInfoResource;
use App\Http\Resources\DoctorPatientWithAppointmentResource;
use App\Http\Resources\DrugPatientIndexResource;
use App\Http\Resources\DrugResource;
use App\Http\Resources\InvoiceReceiptsResource;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\MyPatientCombinedThroughAccountingProfileResource;
use App\Http\Resources\MyPatientSeparateThroughAccountingProfileResource;
use App\Http\Resources\MyPatientsResource;
use App\Http\Resources\MyPatientThroughAccountingProfileResource;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\PatientCaseResource;
use App\Http\Resources\patientDefaultCaseResource;
use App\Http\Resources\ReceiptResource;
use App\Http\Resources\TeethRecordResource;
use App\Models\AccountingProfile;
use App\Models\Doctor;
use App\Models\Drug;
use App\Models\HasRole;
use App\Models\MedicalCase;
use App\Models\Office;
use App\Models\Patient;
use App\Models\PatientCase;
use App\Models\TeethRecord;
use App\Models\User;
use App\Policies\DoctorInfoPolicy;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isEmpty;

class DoctorInfoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $doctors = Doctor::whereNull('dental_lab_id')->get();
        return DoctorInfoResource::collection($doctors);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDoctorRequest $request)
    {
        $this->authorize('create', Doctor::class);
        if (!auth()->user()->doctor) {
            $fields = $request->validated();
            $doctorInfo = auth()->user()->doctor()->create($fields);
            return new DoctorInfoResource($doctorInfo);
        } else {
            return response('you have doctor info', 403);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Doctor $info)
    {
        $this->authorize('view', $info);
        $info->load('user');
        return new DoctorInfoResource($info);
    }

    public function showMyInfo()
    {
        if (auth()->user()->doctor) {
            return new DoctorInfoResource(auth()->user()->doctor);
        }
        return response('you have to complete your info', 404);
    }

    public function showMyPatient(Office $office)
    {
        $doctor = auth()->user()->doctor;
        if ($doctor) {
            if ($office->type == OfficeType::Combined) {
                $this->authorize('inOffice', [Doctor::class, $office]);
                $ownerUser = User::find($office->owner->user_id);
                $ownerDoctor = $ownerUser->doctor;
                $accounts = AccountingProfile::where(['doctor_id' => $ownerDoctor->id, 'office_id' => $office->id, 'type' => AccountingProfileType::PatientAccount])->with(['office', 'office.owner', 'office.owner.user', 'patient', 'patient.doctorImage'])->get();
                // if ($accounts != []) {
                //     return MyPatientCombinedThroughAccountingProfileResource::collection(Cache::remember('patient', 60 * 60 * 24, function () use ($accounts) {
                //         return $accounts;
                //     }));
                // }
                return MyPatientCombinedThroughAccountingProfileResource::collection($accounts);
                // return response()->noContent();
                // $roles = HasRole::where(['roleable_type' => 'App\Models\Patient', 'user_id' => $office->owner->user_id])->get();
                // return MyPatientsResource::collection($roles);
            } else {
                $accounts = AccountingProfile::where(['doctor_id' => auth()->user()->doctor->id, 'office_id' => $office->id, 'type' => AccountingProfileType::PatientAccount])->with(['office', 'patient', 'patient.doctorImage'])->get();
                // return $accounts;
                // if ($accounts != []) {
                //     return MyPatientSeparateThroughAccountingProfileResource::collection(Cache::remember('patient', 60 * 60 * 24, function () use ($accounts) {
                //         return $accounts;
                //     }));
                // }
                return MyPatientSeparateThroughAccountingProfileResource::collection($accounts); //here
                // return response()->noContent();
                // $roles = HasRole::where(['roleable_type' => 'App\Models\Patient', 'user_id' => auth()->id()])->get();
                // return MyPatientsResource::collection($roles);
            }
        }
        return response('you have to complete your info', 404);
    }

    public function showMyPatientAndProfile(Office $office = null)
    {

        // Ensure a valid doctor is authenticated
        $doctor = auth()->user()->doctor;
        if (!$doctor) {
            return response('You have to complete your info', 404);
        }

        $response = [];

        // Authorization and data retrieval for Combined office type
        if ($office->type == OfficeType::Combined) {
            $this->authorize('inOffice', [Doctor::class, $office]);

            $ownerUser = User::find($office->owner->user_id);
            $ownerDoctor = $ownerUser->doctor;

            $accountsCombined = AccountingProfile::where([
                'doctor_id' => $ownerDoctor->id,
                'office_id' => $office->id,
                'type' => AccountingProfileType::PatientAccount
            ])->with([
                'office', 'office.owner', 'office.owner.user', 'patient', 'patient.doctorImage', 'invoices', 'invoices.items', 'receipts', 'invoices.receipts', 'invoiceReceipt', 'invoiceReceipt.items', 'doubleEntries', 'directDoubleEntries',
                'patient.cases' => function ($query) use ($doctor, $office) {
                    $query->whereHas('medicalCase', function ($query) use ($doctor, $office) {
                        $query->where([
                            'case_name' => Doctor::DefaultCase,
                            'doctor_id' => $doctor->id,
                            'office_id' => $office->id
                        ]);
                    })->with([
                        'teethRecords',
                        'teethRecords.operations',
                        'teethRecords.diagnosis',
                        'teethRecords.operations.teeth',
                        'teethRecords.diagnosis.teeth'
                    ]);
                }
            ])->get();

            // $response['combined'] = MyPatientCombinedThroughAccountingProfileResource::collection($accountsCombined);
            $response['profile'] = AccountingProfileResource::collection($accountsCombined);
        } else {
            // return AccountingProfileResource::collection($doctor->accountingProfiles)->where(['office_id' => $office->id, 'type' => AccountingProfileType::PatientAccount]);
            // Authorization and data retrieval for Separate office type
            $this->authorize('inOffice', [AccountingProfile::class, $office]);

            $accountsSeparate = AccountingProfile::where([
                'doctor_id' => $doctor->id,
                'office_id' => $office->id,
                'type' => AccountingProfileType::PatientAccount
            ])->with([
                'office', 'patient', 'patient.doctorImage', 'invoices', 'invoices.items', 'receipts', 'invoices.receipts', 'invoiceReceipt', 'invoiceReceipt.items', 'doubleEntries', 'directDoubleEntries',
                'patient.cases' => function ($query) use ($doctor, $office) {
                    $query->whereHas('medicalCase', function ($query) use ($doctor, $office) {
                        $query->where([
                            'case_name' => Doctor::DefaultCase,
                            'doctor_id' => $doctor->id,
                            'office_id' => $office->id
                        ]);
                    })->with([
                        'teethRecords',
                        'teethRecords.operations',
                        'teethRecords.diagnosis',
                        'teethRecords.operations.teeth',
                        'teethRecords.diagnosis.teeth'
                    ]);
                }
            ])->get();
            // $response['separate'] = MyPatientSeparateThroughAccountingProfileResource::collection($accountsSeparate);
            $response['profile'] = AccountingProfileResource::collection($accountsSeparate);
        }
        // Process each profile to include the default case
        foreach ($response['profile'] as $accountProfile) {
            $patient = $accountProfile->patient;
            $defaultCase = $patient->cases->first();
            if ($defaultCase) {
                $accountProfile->default_case = new PatientDefaultCaseResource($defaultCase);
            }
        }
        // // Fetch default case for each patient
        // foreach ($response['profile'] as $accountProfile) {
        //     $patient = $accountProfile->patient;
        //     $defaultCase = MedicalCase::where([
        //         'case_name' => Doctor::DefaultCase,
        //         'doctor_id' => $doctor->id,
        //         'office_id' => $office->id
        //     ])->first();

        //     if ($defaultCase) {
        //         $PatientCase = PatientCase::where([
        //             'case_id' => $defaultCase->id,
        //             'patient_id' => $patient->id,
        //         ])->with([
        //             'teethRecords',
        //             'teethRecords.operations',
        //             'teethRecords.diagnosis',
        //             'teethRecords.operations.teeth',
        //             'teethRecords.diagnosis.teeth'
        //         ])->first();

        //         $accountProfile->default_case = new PatientDefaultCaseResource($PatientCase);
        //     }
        // }
        return response()->json($response);
    }

    public function drug(Office $office, Patient $patient)
    {
        $this->authorize('inOffice', [Doctor::class, $office]);
        // return auth()->user()->doctor->drugs;
        $drugs = DB::table('drugs')
            ->join('diagnoses', 'diagnoses.id', '=', 'drugs.diagnosis_id')
            ->join('teeth_records', 'teeth_records.id', '=', 'diagnoses.record_id')
            // ->join('reports', 'reports.id', '=', 'teeth_records.report_id')
            // ->join('patients', 'patients.id', '=', 'reports.patient_id')
            ->join('patient_cases', 'patient_cases.id', '=', 'teeth_records.patientCase_id')
            ->join('medical_cases', 'medical_cases.id', '=', 'patient_cases.case_id')
            ->join('patients', 'patients.id', '=', 'patient_cases.patient_id')
            ->join('doctors', 'doctors.id', '=', 'medical_cases.doctor_id')
            ->join('offices', 'offices.id', '=', 'medical_cases.office_id')
            ->where('patient_cases.patient_id', $patient->id)
            ->where('medical_cases.doctor_id', auth()->user()->doctor->id)
            ->where('medical_cases.office_id', $office->id)
            // ->groupBy('drug_name')
            ->get();
        // return $drugs;
        return DrugPatientIndexResource::collection($drugs);
        // return Drug::whereHas(['diagnosis.record.PatientCase.case.office' => function (Builder $query) use ($office) {
        //     $query->where('id', $office->id);
        // }])
        //     ->get();
    }

    public function activePatient(Office $office)
    {
        $this->authorize('inOffice', [Doctor::class, $office]);
        $doctor = auth()->user()->doctor;
        if ($doctor && $office->type == OfficeType::Separate) {
            // $patients = Patient::has('appointments')->with('appointments')->get();
            $patients = Patient::whereHas('appointments', function (Builder $query) use ($office) {
                $query->where('taken_date', '>', now()->subDays(30)->endOfDay())
                    ->where('doctor_id', auth()->user()->doctor->id)
                    ->where('office_id', $office->id);
            })->get();
            return DoctorPatientWithAppointmentResource::collection($patients);
            // return $doctor->appointments()->with('patient')->where('taken_date', '>', now()->subDays(30)->endOfDay())->get();
        } else if ($doctor && $office->type == OfficeType::Combined) {
            $this->authorize('officeOwner', [Doctor::class, $office]);
            $patients = Patient::whereHas('appointments', function (Builder $query) use ($office) {
                $query->where('taken_date', '>', now()->subDays(30)->endOfDay())
                    ->where('office_id', $office->id);
            })->get();
            return DoctorPatientWithAppointmentResource::collection($patients);
        }
        return response('you have to complete your info', 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDoctorInfoRequest $request, Doctor $info)
    {
        $this->authorize('update', $info);
        $fields = $request->validated();
        if ($fields == []) {
            return response('', Response::HTTP_NO_CONTENT);
        } else {
            $info->update($fields);
            return new DoctorInfoResource($info);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Doctor $doctor)
    {
        //
    }

    public function myReceipts(Office $office)
    {
        $doctor = auth()->user()->doctor;
        return ReceiptResource::collection($doctor->receipts()
            ->where('accounting_profiles.office_id', $office->id)
            ->with(['patient', 'doctor', 'office', 'lab', 'account'])
            ->get());
    }

    public function myInvoices(Office $office)
    {
        $doctor = auth()->user()->doctor;
        return InvoiceResource::collection($doctor->invoices()
            ->where('accounting_profiles.office_id', $office->id)
            ->with(['patient', 'doctor', 'office', 'lab', 'account'])
            ->get());
    }

    public function myInvoiceReceipt(Office $office)
    {
        $doctor = auth()->user()->doctor;
        return InvoiceReceiptsResource::collection($doctor->invoiceReceipts()
            ->where('accounting_profiles.office_id', $office->id)
            ->with(['patient', 'doctor', 'office', 'account', 'items'])
            ->get());
    }

    public function myRecords()
    {
        $doctor = auth()->user()->doctor;
        return TeethRecordResource::collection($doctor->teethRecords);
    }

    public function officeRecords(Office $office)
    {
        $this->authorize('inOffice', [Doctor::class, $office]);
        return TeethRecordResource::collection($office->teethRecords);
    }

    public function myRecordsForPatient($patient)
    {
        $doctor = auth()->user()->doctor;
        return TeethRecordResource::collection($doctor->teethRecords)->where('patientCase.patient.id', $patient);
    }

    public function myRecordsForCase($case)
    {
        $doctor = auth()->user()->doctor;
        return TeethRecordResource::collection($doctor->teethRecords)->where('patientCase.case.id', $case);
    }

    public function unreadNotification()
    {
        $doctor = auth()->user()->doctor;
        return NotificationResource::collection($doctor->unreadNotifications);
    }

    public function markAsRead(MarkAsReadRequest $request)
    {
        $doctor = auth()->user()->doctor;
        if ($request->has('id')) {
            $doctor->unreadNotifications
                ->where('id', $request->id)
                ->markAsRead();
            return response()->noContent();
        }
        $doctor->unreadNotifications
            ->markAsRead();
        return response()->noContent();
    }

    public function allNotification()
    {
        $doctor = auth()->user()->doctor;
        return NotificationResource::collection($doctor->notifications);
    }
}
