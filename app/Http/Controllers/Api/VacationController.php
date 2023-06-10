<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVacationRequest;
use App\Http\Resources\VacationResource;
use App\Models\Doctor;
use App\Models\Office;
use App\Models\vacation;
use Illuminate\Http\Request;

class VacationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->doctor) {
            $office = Office::find($request->office);
            $vacation = vacation::where(['office_id' => $request->office, 'doctor_id' => $request->doctor])->with(['doctor', 'office'])->get();
            return VacationResource::collection($vacation);
        }
        $office = Office::find($request->office);
        $vacation = vacation::where(['office_id' => $request->office, 'doctor_id' => auth()->user()->doctor->id])->with(['doctor', 'office'])->get();
        return VacationResource::collection($vacation);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVacationRequest $request)
    {
        $fields = $request->validated();
        if (!$request->doctor_id) {
            $doctor = auth()->user()->doctor;
            $fields['doctor_id'] = $doctor->id;
        } else {
            $doctor = Doctor::find($request->doctor_id);
        }
        $vacation = $doctor->vacations()->create($fields);
        return new VacationResource($vacation);
    }

    /**
     * Display the specified resource.
     */
    public function show(vacation $vacation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, vacation $vacation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(vacation $vacation)
    {
        //
    }
}
