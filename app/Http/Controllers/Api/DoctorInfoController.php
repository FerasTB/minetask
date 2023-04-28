<?php

namespace App\Http\Controllers\Api;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorInfoRequest;
use App\Http\Resources\DoctorInfoResource;
use App\Http\Resources\MyPatientsResource;
use App\Http\Resources\TeethRecordResource;
use App\Models\Doctor;
use App\Models\HasRole;
use App\Models\TeethRecord;
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

    public function showMyInfo()
    {
        if (auth()->user()->doctor) {
            return new DoctorInfoResource(auth()->user()->doctor);
        }
        return response('you have to complete your info', 404);
    }

    public function showMyPatient()
    {
        $doctor = auth()->user()->doctor;
        if ($doctor) {
            $roles = HasRole::where(['roleable_type' => 'App\Models\Patient', 'user_id' => auth()->id()])->get();
            return MyPatientsResource::collection($roles);
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

    public function myRecords()
    {
        $doctor = auth()->user()->doctor;
        return TeethRecordResource::collection($doctor->teethRecords);
    }

    public function myRecordsForPatient($patient)
    {
        $doctor = auth()->user()->doctor;
        return TeethRecordResource::collection($doctor->teethRecords)->where('patientCase.patient.id', $patient);
    }

    public function myRecordsForCase($case)
    {
        $doctor = auth()->user()->doctor;
        return TeethRecordResource::collection($doctor->teethRecords)->where('patientCase.case.case_name', $case);
    }

    // public function patientRecords(Doctor $doctor, $patient)
    // {
    //     return $doctor->teethRecords->where();
    // }
}
