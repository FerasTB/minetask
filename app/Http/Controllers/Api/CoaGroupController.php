<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\CoaGroup;
use App\Http\Requests\StoreCoaGroupRequest;
use App\Http\Requests\UpdateCoaGroupRequest;
use App\Http\Resources\CoaGroupsResource;
use App\Models\Office;

class CoaGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Office $office)
    {
        $this->authorize('inOffice', [CoaGroup::class, $office]);
        $doctor = auth()->user()->doctor;
        return CoaGroupsResource::collection(
            $doctor->coaGroups()
                ->where('office_id', $office->id)
                ->with(['office', 'doctor', 'COAS'])
                ->get()
        );
    }

    public function indexOwner(Office $office)
    {
        $this->authorize('officeOwner', [CoaGroup::class, $office]);
        return CoaGroupsResource::collection($office->coaGroups()
            ->with('office', 'COAS')
            ->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCoaGroupRequest $request, Office $office)
    {
        $fields = $request->validated();
        if ($request->doctor_id) {
            $this->authorize('inOffice', [CoaGroup::class, $office]);
            $doctor = auth()->user()->doctor;
            $group = $doctor->coaGroups()->create($fields);
            return new CoaGroupsResource($group);
        }
        $this->authorize('officeOwner', [CoaGroup::class, $office]);
        $group = $office->coaGroups()->create($fields);
        return new CoaGroupsResource($group);
    }

    /**
     * Display the specified resource.
     */
    public function show(CoaGroup $coaGroup)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCoaGroupRequest $request, CoaGroup $coaGroup)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CoaGroup $coaGroup)
    {
        //
    }
}
