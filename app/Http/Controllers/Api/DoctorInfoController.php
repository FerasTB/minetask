<?php

namespace App\Http\Controllers\Api;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorInfoRequest;
use App\Http\Resources\DoctorInfoResource;
use App\Http\Resources\MyPatientsResource;
use App\Models\Doctor;
use App\Models\HasRole;
use App\Policies\DoctorInfoPolicy;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use function PHPUnit\Framework\isEmpty;

class DoctorInfoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $doctors = Doctor::all();
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
        return new DoctorInfoResource($info);
    }

    public function showMyPatient(Doctor $doctor)
    {
        $roles = HasRole::where(['roleable_type' => 'App\Models\Patient', 'user_id' => auth()->id()])->get();
        // return response()->json($roles);
        return MyPatientsResource::collection($roles);
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
}
