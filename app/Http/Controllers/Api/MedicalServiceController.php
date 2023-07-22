<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMedicalServiceRequest;
use App\Http\Resources\MedicalServiceResource;
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
    public function update(Request $request, MedicalService $medicalService)
    {
        //
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
}
