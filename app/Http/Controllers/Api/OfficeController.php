<?php

namespace App\Http\Controllers\Api;

use App\Enums\COAType;
use App\Enums\OfficeType;
use App\Enums\Role;
use App\Enums\SubRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddDoctorToOfficeRequest;
use App\Http\Requests\StoreOfficeRequest;
use App\Http\Requests\UpdateOfficeRequest;
use App\Http\Resources\DoctorInOfficeResource;
use App\Http\Resources\OfficeResource;
use App\Http\Resources\OfficeThroughHasRoleResource;
use App\Models\COA;
use App\Models\Doctor;
use App\Models\HasRole;
use App\Models\Office;
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
        $offices = HasRole::where(['user_id' => $user->id, 'roleable_type' => 'App\Models\Office', 'sub_role' => SubRole::OfficeOwner])->get();
        if ($offices != []) {
            return OfficeThroughHasRoleResource::collection($offices);
        } else {
            return response()->noContent();
        }
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
            'type' => COAType::Asset,
        ]);
        $office->COAS()->create([
            'name' => COA::Cash,
            'type' => COAType::Asset,
        ]);
        $office->COAS()->create([
            'name' => COA::Payable,
            'type' => COAType::Liability,
        ]);
        if ($office->type == OfficeType::Separate) {
            $doctor = auth()->user()->doctor;
            $doctor->cases()->create([
                'case_name' => Doctor::DefaultCase,
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
        return response()->json($office);
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
                'type' => COAType::Asset,
                'office_id' => $office->id,
            ]);
            $doctor->COAS()->create([
                'name' => COA::Cash,
                'type' => COAType::Asset,
                'office_id' => $office->id,
            ]);
            $doctor->COAS()->create([
                'name' => COA::Payable,
                'type' => COAType::Liability,
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

    public function AllDoctorInOffice(Office $office)
    {
        return DoctorInOfficeResource::collection($office->roles);
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
