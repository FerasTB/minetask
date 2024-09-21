<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMedicalServiceRequest;
use App\Http\Requests\UpdateMedicalServiceRequest;
use App\Http\Resources\MedicalServiceResource;
use App\Models\Doctor;
use App\Models\EmployeeSetting;
use App\Models\HasRole;
use App\Models\MedicalService;
use App\Models\Office;
use Illuminate\Http\Request;

class MedicalServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return MedicalServiceResource::collection(auth()->user()->doctor->services);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMedicalServiceRequest $request)
    {
        $fields = $request->validated();
        $office = Office::find($request->office_id);
        if (auth()->user()->currentRole->name == 'DentalDoctorTechnician') {
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
        $this->authorize('create', [MedicalService::class, $office]);
        $service = $office->services()->create($fields);
        return new MedicalServiceResource($service);
    }

    /**
     * Display the specified resource.
     */
    public function show(MedicalService $service)
    {
        return new MedicalServiceResource($service);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMedicalServiceRequest $request, MedicalService $service)
    {
        $fields = $request->validated();
        $office = Office::findOrFail($request->office_id);
        if (auth()->user()->currentRole->name == 'DentalDoctorTechnician') {
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
        $this->authorize('update', [$service, $doctor]);
        $service->update($fields);
        return new MedicalServiceResource($service);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MedicalService $medicalService)
    {
        //
    }

    public function officeService(Office $office)
    {
        $services = $office->services()->with('COA')->get();
        return MedicalServiceResource::collection($services);
    }

    public function doctorService(Office $office)
    {
        if (auth()->user()->currentRole->name == 'DentalDoctorTechnician') {
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
        $services = $office->services()
            ->where('doctor_id', $doctor->id)
            ->with('COA')
            ->get();
        return MedicalServiceResource::collection($services);
    }
}
