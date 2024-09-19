<?php

namespace App\Http\Controllers;

use App\Models\DoctorImage;
use App\Http\Requests\StoreDoctorImageRequest;
use App\Http\Requests\UpdateDoctorImageRequest;
use App\Http\Resources\DoctorImageResource;
use App\Http\Resources\DoctorInfoResource;
use App\Models\Office;
use App\Models\Patient;
use App\Models\TeethRecord;
use Illuminate\Support\Carbon;

class DoctorImageController extends Controller
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
    public function store(StoreDoctorImageRequest $request, Office $office)
    {
        $fields = $request->validated();
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
        $patient = Patient::findOrFail($request->patient_id);
        $fields['office_id'] = $office->id;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if (!$request->has('name')) {
                $fields['name'] = $file->getClientOriginalName();
            }
            // $extension = $file->extension();
            $name = Carbon::now();
            if ($request->has('teeth_record_id')) {
                $record = TeethRecord::findOrFail($request->teeth_record_id);
                $this->authorize('inOfficeAndHavePatientAndRecord', [DoctorImage::class, $patient, $office, $doctor, $record]);
                $fields['url'] = $file->storeAs(
                    'doctor/' . $office->id . '/' . $doctor->id . '/' . $patient->id . '/' . $record->id,
                    $name,
                );
                $image = $doctor->doctorImage()->create($fields);
                return new DoctorImageResource($image);
            }
            $this->authorize('inOfficeAndHavePatient', [DoctorImage::class, $patient, $office, $doctor]);
            $fields['url'] = $file->storeAs(
                'doctor/' . $office->id . '/' . $doctor->id . '/' . $patient->id,
                $name,
            );
            $image = $doctor->doctorImage()->create($fields);
            return new DoctorImageResource($image);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DoctorImage $image)
    {
        $this->authorize('view', $image);
        return response()->file(
            storage_path('app/' . $image->url),
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDoctorImageRequest $request, DoctorImage $doctorImage)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DoctorImage $doctorImage)
    {
        //
    }
}
