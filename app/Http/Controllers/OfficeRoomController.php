<?php

namespace App\Http\Controllers;

use App\Models\OfficeRoom;
use App\Http\Requests\StoreOfficeRoomRequest;
use App\Http\Requests\UpdateOfficeRoomRequest;
use App\Http\Resources\OfficeRoomResource;
use App\Models\Office;

class OfficeRoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Office $office)
    {
        $this->authorize('admin', [OfficeRoom::class, $office]);
        return OfficeRoomResource::collection($office->rooms);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOfficeRoomRequest $request, Office $office)
    {
        $fields = $request->validated();
        $this->authorize('admin', [OfficeRoom::class, $office]);
        $room = $office->rooms()->create($fields);
        return new OfficeRoomResource($room);
    }

    /**
     * Display the specified resource.
     */
    public function show(OfficeRoom $officeRoom)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OfficeRoom $officeRoom)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOfficeRoomRequest $request, OfficeRoom $officeRoom)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OfficeRoom $officeRoom)
    {
        //
    }
}
