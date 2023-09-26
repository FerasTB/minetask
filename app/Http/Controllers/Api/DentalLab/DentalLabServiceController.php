<?php

namespace App\Http\Controllers\Api\DentalLab;

use App\Http\Controllers\Controller;
use App\Models\DentalLabService;
use App\Http\Requests\StoreDentalLabServiceRequest;
use App\Http\Requests\UpdateDentalLabServiceRequest;
use App\Http\Resources\DentalLabServiceResource;
use App\Models\DentalLab;
use Database\Seeders\DentalLabSeeder;

class DentalLabServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(DentalLab $lab)
    {
        return DentalLabService::collection($lab->service);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDentalLabServiceRequest $request, DentalLab $lab)
    {
        $fields = $request->validated();
        $this->authorize('create', [DentalLabService::class, $lab]);
        $service = $lab->services()->create($fields);
        return new DentalLabServiceResource($service);
    }

    /**
     * Display the specified resource.
     */
    public function show(DentalLabService $service)
    {
        return new DentalLabServiceResource($service);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDentalLabServiceRequest $request, DentalLab $lab, DentalLabService $service)
    {
        $fields = $request->validated();
        $this->authorize('update', [$service, $lab]);
        $service->update($fields);
        return new DentalLabServiceResource($service);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DentalLabService $dentalLabService)
    {
        //
    }

    public function labService(DentalLab $lab)
    {
        $services = $lab->services()->with('COA')->get();
        return DentalLabServiceResource::collection($services);
    }
}
