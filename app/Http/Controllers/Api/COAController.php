<?php

namespace App\Http\Controllers\Api;

use App\Enums\OfficeType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCOARequest;
use App\Http\Resources\COAResource;
use App\Models\COA;
use App\Models\Doctor;
use App\Models\Office;
use Illuminate\Http\Request;

class COAController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $office = Office::findOrFail($request->office);
        if ($office->type == OfficeType::Separate) {
            $doctor = auth()->user()->doctor;
            return COAResource::collection($doctor->COAS);
        } else {
            return COAResource::collection($office->COAS);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCOARequest $request)
    {
        $fields = $request->validated();
        if ($request->doctor_id) {
            $doctor = Doctor::find($request->doctor_id);
            $this->authorize('createForDoctor', [AccountingProfile::class, $doctor]);
            $profile = $doctor->accountingProfiles()->create($fields);
            return $profile;
        }
        $office = Office::find($request->office_id);
        $this->authorize('createForOffice', [AccountingProfile::class, $office]);
        $profile = $office->accountingProfiles()->create($fields);
        return $profile;
    }

    /**
     * Display the specified resource.
     */
    public function show(COA $cOA)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, COA $cOA)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(COA $cOA)
    {
        //
    }
}
