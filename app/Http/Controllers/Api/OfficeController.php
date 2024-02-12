<?php

namespace App\Http\Controllers\Api;

use App\Enums\COAGeneralType;
use App\Enums\COASubType;
use App\Enums\COAType;
use App\Enums\HasRolePropertyType;
use App\Enums\OfficeType;
use App\Enums\Role;
use App\Enums\SubRole;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddDoctorToOfficeRequest;
use App\Http\Requests\AddEmployeeToOfficeRequest;
use App\Http\Requests\StoreOfficeRequest;
use App\Http\Requests\UpdateHasRolePropertyRequest;
use App\Http\Requests\UpdateOfficeRequest;
use App\Http\Resources\DoctorInOfficeResource;
use App\Http\Resources\EmployeeInOfficeResource;
use App\Http\Resources\OfficeResource;
use App\Http\Resources\OfficeThroughHasRoleResource;
use App\Models\COA;
use App\Models\Doctor;
use App\Models\HasRole;
use App\Models\ModelHasRole;
use App\Models\Office;
use App\Models\Patient;
use App\Models\Role as ModelsRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class OfficeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $offices = HasRole::where(['user_id' => $user->id, 'roleable_type' => 'App\Models\Office', 'sub_role' => SubRole::OfficeOwner])->with('roleable')->get();
        if ($offices != []) {
            return OfficeThroughHasRoleResource::collection($offices);
        } else {
            return response()->noContent();
        }
    }

    public function doctorOffice(Doctor $doctor)
    {
        $user = auth()->user();
        $offices = HasRole::where(['user_id' => $doctor->user->id, 'roleable_type' => 'App\Models\Office'])->with('roleable')->get();
        return OfficeThroughHasRoleResource::collection($offices);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOfficeRequest $request)
    {
        $this->authorize('create', Office::class);
        $fields = $request->validated();
        $fields['type'] = OfficeType::getValue($request->type);
        $office = Office::create($fields);
        auth()->user()->roles()->create([
            'roleable_id' => $office->id,
            'roleable_type' => 'App\Models\Office',
            'sub_role' => SubRole::OfficeOwner,
        ]);
        $office->COAS()->create([
            'name' => COA::Receivable,
            'type' => COAType::Current,
            'general_type' => COAGeneralType::Asset,
            'sub_type' => COASubType::Receivable,
        ]);
        $office->COAS()->create([
            'name' => COA::Cash,
            'type' => COAType::Current,
            'general_type' => COAGeneralType::Asset,
            'sub_type' => COASubType::Cash,
        ]);
        $office->COAS()->create([
            'name' => COA::Payable,
            'type' => COAType::Current,
            'general_type' => COAGeneralType::Liability,
            'sub_type' => COASubType::Payable,
        ]);
        $office->COAS()->create([
            'name' => COA::Capital,
            'type' => COAType::Capital,
            'general_type' => COAGeneralType::Equity,
        ]);
        $office->COAS()->create([
            'name' => COA::OwnerWithDraw,
            'type' => COAType::OwnerWithdraw,
            'general_type' => COAGeneralType::Equity,
        ]);
        $office->transactionPrefix()->create([
            'type' => TransactionType::PaymentVoucher,
            'prefix' => 'PVOC',
        ]);
        $office->transactionPrefix()->create([
            'type' => TransactionType::SupplierInvoice,
            'prefix' => 'SINV',
        ]);
        $doctor = auth()->user()->doctor;
        $doctor->cases()->create([
            'case_name' => Doctor::DefaultCase,
            'office_id' => $office->id,
        ]);
        if ($office->type == OfficeType::Separate) {
            // $doctor = auth()->user()->doctor;
            $doctor->COAS()->create([
                'name' => COA::Receivable,
                'type' => COAType::Current,
                'general_type' => COAGeneralType::Asset,
                'sub_type' => COASubType::Receivable,
                'office_id' => $office->id,
            ]);
            $doctor->COAS()->create([
                'name' => COA::Cash,
                'type' => COAType::Current,
                'general_type' => COAGeneralType::Asset,
                'sub_type' => COASubType::Cash,
                'office_id' => $office->id,
            ]);
            $doctor->COAS()->create([
                'name' => COA::Payable,
                'type' => COAType::Current,
                'general_type' => COAGeneralType::Liability,
                'sub_type' => COASubType::Payable,
                'office_id' => $office->id,
            ]);
            $doctor->COAS()->create([
                'name' => COA::Capital,
                'type' => COAType::Capital,
                'general_type' => COAGeneralType::Equity,
                'office_id' => $office->id,
            ]);
            $doctor->COAS()->create([
                'name' => COA::OwnerWithDraw,
                'type' => COAType::OwnerWithdraw,
                'general_type' => COAGeneralType::Equity,
                'office_id' => $office->id,
            ]);
            $doctor->transactionPrefix()->create([
                'type' => TransactionType::PatientInvoice,
                'prefix' => 'PINV',
                'office_id' => $office->id,
            ]);
            $doctor->transactionPrefix()->create([
                'type' => TransactionType::PatientReceipt,
                'prefix' => 'PREC',
                'office_id' => $office->id,
            ]);
            $doctor->transactionPrefix()->create([
                'type' => TransactionType::PaymentVoucher,
                'prefix' => 'PVOC',
                'office_id' => $office->id,
            ]);
            $doctor->transactionPrefix()->create([
                'type' => TransactionType::SupplierInvoice,
                'prefix' => 'SINV',
                'office_id' => $office->id,
            ]);
        }
        return new OfficeResource($office);
    }

    /**
     * Display the specified resource.
     */
    public function show(Office $office)
    {
        return $office->roles;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOfficeRequest $request, Office $office)
    {
        $this->authorize('officeOwner', $office);
        $fields = $request->validated();
        $office->update($fields);
        return new OfficeResource($office);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Office $office)
    {
        //
    }

    public function AddDoctor(AddDoctorToOfficeRequest $request, Office $office)
    {
        $this->authorize('officeOwner', $office);
        $fields = $request->validated();
        // $user = 0;
        // if ($fields['isNew']) {
        //     if ($fields['role']) {
        //         $user = User::create([
        //             'phone' => $request->phone,
        //             'password' => Hash::make($request->password),
        //             'role' => Role::getValue($request->role),
        //         ]);
        //     } else {
        //         $user = User::create([
        //             'phone' => $request->phone,
        //             'password' => Hash::make($request->password),
        //         ]);
        //     }
        //     $fields['sub_role'] = $request->role == 'Doctor' ? SubRole::DoctorInOffice : SubRole::OfficeSecretary;
        //     $fields['roleable_id'] = $office->id;
        //     $fields['roleable_type'] = 'App\Models\Office';
        //     $relation = $user->roles()->create($fields);
        //     return DoctorInOfficeResource::collection($office->roles);
        // }
        $doctor = Doctor::findOrFail($fields['doctor_id']);
        $user = $doctor->user;
        $fields['sub_role'] = SubRole::getValue($request->sub_role);
        $fields['roleable_id'] = $office->id;
        $fields['roleable_type'] = 'App\Models\Office';
        $relation = $user->roles()->create($fields);
        $office->cases()->create([
            'case_name' => Doctor::DefaultCase,
            'doctor_id' => auth()->user()->doctor->id,
        ]);
        if ($office->type == OfficeType::Separate) {
            $doctor = $user->doctor;
            $doctor->COAS()->create([
                'name' => COA::Receivable,
                'type' => COAType::Current,
                'general_type' => COAGeneralType::Asset,
                'sub_type' => COASubType::Receivable,
                'office_id' => $office->id,
            ]);
            $doctor->COAS()->create([
                'name' => COA::Cash,
                'type' => COAType::Current,
                'general_type' => COAGeneralType::Asset,
                'sub_type' => COASubType::Cash,
                'office_id' => $office->id,
            ]);
            $doctor->COAS()->create([
                'name' => COA::Payable,
                'type' => COAType::Current,
                'general_type' => COAGeneralType::Liability,
                'sub_type' => COASubType::Payable,
                'office_id' => $office->id,
            ]);
            $doctor->cases()->create([
                'case_name' => Doctor::DefaultCase,
                'office_id' => $office->id,
            ]);
        }
        $relation->setting()->create($fields);
        return new DoctorInOfficeResource($relation);
    }

    public function addEmployee(AddEmployeeToOfficeRequest $request, Office $office)
    {
        $fields = $request->validated();
        $this->authorize('officeOwner', $office);
        $patient = Patient::findOrFail($fields['patient_id']);
        $user = $patient->user;
        $roleInModel = Role::findOrFail(ModelsRole::DentalDoctorTechnician);
        if (!$user->hasRole($roleInModel)) {
            $role = ModelHasRole::create([
                'role_id' => $roleInModel->id,
                'roleable_id' => $user->id,
                'roleable_type' => 'App\Models\User',
            ]);
        }
        $fields['sub_role'] = SubRole::getValue($request->sub_role);
        $fields['roleable_id'] = $office->id;
        $fields['roleable_type'] = 'App\Models\Office';
        $relation = $user->roles()->create($fields);
        $relation->setting()->create($fields);
        $patientInfo = $relation->properties()->create([
            'type' => HasRolePropertyType::PatientInfo,
        ]);
        $appointment = $relation->properties()->create([
            'type' => HasRolePropertyType::Appointment,
        ]);
        $income = $relation->properties()->create([
            'type' => HasRolePropertyType::Income,
        ]);
        $user->update(['current_role_id' => $roleInModel->id]);
        return new EmployeeInOfficeResource($relation);
    }

    public function updateEmployeeProperty(UpdateHasRolePropertyRequest $request, Office $office, Patient $patient)
    {
        $fields = $request->validated();
        $this->authorize('officeOwner', $office);
        $user = $patient->user;
        $relation = HasRole::where(['roleable_type' => 'App\Models\Office', 'roleable_id' => $office->id, 'user_id' => $user->id])->first();
        $property = $relation->properties()->where([
            'type' => HasRolePropertyType::getValue($request->property_type),
        ]);
        $property->update([
            'read' => $fields['read'],
            'write' => $fields['write'],
            'edit' => $fields['edit'],
        ]);
        return new EmployeeInOfficeResource($relation);
    }

    public function AllDoctorInOffice(Office $office)
    {
        return EmployeeInOfficeResource::collection($office->roles);
    }

    public function MyOffices()
    {
        $user = auth()->user();
        $offices = HasRole::where(['user_id' => $user->id, 'roleable_type' => 'App\Models\Office'])->get();
        if ($offices != []) {
            return OfficeThroughHasRoleResource::collection($offices);
        } else {
            return response()->noContent();
        }
    }
}
