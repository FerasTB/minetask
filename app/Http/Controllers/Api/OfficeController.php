<?php

namespace App\Http\Controllers\Api;

use App\Enums\SubRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddDoctorToOfficeRequest;
use App\Http\Requests\StoreOfficeRequest;
use App\Http\Requests\UpdateOfficeRequest;
use App\Http\Resources\DoctorInOfficeResource;
use App\Models\HasRole;
use App\Models\Office;
use App\Models\User;
use Illuminate\Http\Request;

class OfficeController extends Controller
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
    public function store(StoreOfficeRequest $request)
    {
        $this->authorize('create', Office::class);
        $fields = $request->validated();
        $office = Office::create($fields);
        auth()->user()->roles()->create([
            'roleable_id' => $office->id,
            'roleable_type' => 'App\Models\Office',
            'sub_role' => SubRole::OfficeOwner,
        ]);
        return response()->json($office);
    }

    /**
     * Display the specified resource.
     */
    public function show(Office $office)
    {
        return $office->roles;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOfficeRequest $request, Office $office)
    {
        $this->authorize('officeOwner', $office);
        $fields = $request->validated();
        $office->update($fields);
        return response()->json($office);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Office $office)
    {
        //
    }

    public function AddDoctor(AddDoctorToOfficeRequest $request, Office $office)
    {
        $this->authorize('officeOwner', $office);
        $fields = $request->validated();
        $user = User::findOrFail($fields['user_id']);
        $fields['sub_role'] = SubRole::getValue($request->sub_role);
        $fields['roleable_id'] = $office->id;
        $fields['roleable_type'] = 'App\Models\Office';
        $relation = $user->roles()->create($fields);
        return response()->json($relation);
    }

    public function AllDoctorInOffice(Office $office)
    {
        return DoctorInOfficeResource::collection($office->roles);
    }
}
