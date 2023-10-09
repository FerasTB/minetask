<?php

namespace App\Http\Controllers\Api\DentalLab;

use App\Http\Controllers\Controller;
use App\Models\DentalLabItem;
use App\Http\Requests\StoreDentalLabItemRequest;
use App\Http\Requests\UpdateDentalLabItemRequest;
use App\Http\Resources\DentalLabItemResource;
use App\Http\Resources\SupplierItemResource;
use App\Models\DentalLab;

class DentalLabItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(DentalLab $lab)
    {
        $this->authorize('inLab', [DentalLabItem::class, $lab]);
        $doctor = auth()->user()->doctor;
        return DentalLabItemResource::collection(
            $lab->supplierItem()
                ->with(['doctor', 'lab', 'COA'])
                ->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDentalLabItemRequest $request, DentalLab $lab)
    {
        $fields = $request->validated();
        $this->authorize('createForLab', [DentalLabItem::class, $lab]);
        $supplierItem = $lab->supplierItem()->create($fields);
        return new DentalLabItemResource($supplierItem);
    }

    /**
     * Display the specified resource.
     */
    public function show(DentalLabItem $dentalLabItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDentalLabItemRequest $request, DentalLab $lab, DentalLabItem $item)
    {
        $fields = $request->validated();
        $this->authorize('updateForLab', [$item, $lab]);
        $item->update($fields);
        return new DentalLabItemResource($item);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DentalLabItem $dentalLabItem)
    {
        //
    }
}
