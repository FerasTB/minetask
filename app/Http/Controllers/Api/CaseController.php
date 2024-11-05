<?php

namespace App\Http\Controllers\Api;

use App\Enums\OfficeType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMedicalCaseRequest;
use App\Http\Requests\UpdateMedicalCaseRequest;
use App\Http\Resources\MedicalCaseResource;
use App\Models\Doctor;
use App\Models\EmployeeSetting;
use App\Models\HasRole;
use App\Models\MedicalCase;
use App\Models\Office;
use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class CaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $office = Office::findOrFail($request->office_id);

        if (in_array(auth()->user()->currentRole->name, Role::Technicians)) {
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
        $this->authorize('viewAny', [MedicalCase::class, $office]);
        $case = MedicalCase::where([
            'doctor_id' => $doctor->id,
            'office_id' => $office->id
        ])->with(['doctor', 'office'])->get();
        return MedicalCaseResource::collection($case);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMedicalCaseRequest $request)
    {
        // Validate the request fields
        $fields = $request->validated();

        // Find the office and authorize the action
        $office = Office::findOrFail($request->office_id);
        $this->authorize('create', [MedicalCase::class, $office]);

        // Get the authenticated doctor's ID
        $doctor = auth()->user()->doctor;

        // Check if the case_name is unique for this doctor
        $existingCase = MedicalCase::where('doctor_id', $doctor->id)
            ->where('case_name', $fields['case_name'])
            ->first();

        // If a case with the same name already exists for the doctor, return an error response
        if ($existingCase) {
            return response()->json([
                'error' => 'The case_name must be unique for this doctor.'
            ], 422); // Unprocessable Entity
        }

        // Create the new medical case
        $case = $doctor->cases()->create($fields);

        // Return the newly created medical case as a resource
        return new MedicalCaseResource($case);
    }

    /**
     * Display the specified resource.
     */
    public function show(MedicalCase $case)
    {
        $this->authorize('view', $case);
        return new MedicalCaseResource($case);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMedicalCaseRequest $request, MedicalCase $case)
    {
        $this->authorize('update', $case);
        $fields = $request->validated();
        $case->update($fields);
        return new MedicalCaseResource($case);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MedicalCase $medicalCase)
    {
        //
    }

    public function patientCases(Patient $patient)
    {
        $this->authorize('viewAny', [MedicalCase::class, $patient]);
        $doctor = auth()->user()->doctor;
        $cases = MedicalCase::where(['patient_id' => $patient->id, 'doctor_id' => $doctor->id])->get();
        return MedicalCaseResource::collection($cases);
    }
}
