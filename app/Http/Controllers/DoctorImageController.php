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
        $doctor = auth()->user()->doctor;
        $patient = Patient::findOrFail($request->patient_id);
        $fields['office_id'] = $office->id;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            // $extension = $file->extension();
            $name = $file->getClientOriginalName();
            if ($request->has('teeth_record_id')) {
                $record = TeethRecord::findOrFail($request->teeth_record_id);
                $this->authorize('inOfficeAndHavePatientAndRecord', [DoctorImage::class, $patient, $office, $record]);
                $fields['url'] = $file->storeAs(
                    'doctor/' . $office->id . '/' . $doctor->id . '/' . $patient->id . '/' . $record->id,
                    $name,
                );
                $image = $doctor->doctorImage->create($fields);
                return new DoctorImageResource($image);
            }
            $this->authorize('inOfficeAndHavePatient', [DoctorImage::class, $patient, $office]);
            $fields['url'] = $file->storeAs(
                'doctor/' . $office->id . '/' . $doctor->id . '/' . $patient->id,
                $name,
            );
            $image = $doctor->doctorImage->create($fields);
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
