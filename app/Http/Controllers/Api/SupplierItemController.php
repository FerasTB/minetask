<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierItemRequest;
use App\Http\Requests\UpdateSupplierItemRequest;
use App\Http\Resources\SupplierItemResource;
use App\Models\AccountingProfile;
use App\Models\COA;
use App\Models\Office;
use App\Models\SupplierItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Office $office)
    {
        $this->authorize('inOffice', [SupplierItem::class, $office]);
        $doctor = auth()->user()->doctor;
        return SupplierItemResource::collection(
            $doctor->supplierItem()
                ->where('office_id', $office->id)
                ->with(['doctor', 'office', 'COA'])
                ->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSupplierItemRequest $request, Office $office)
    {
        $fields = $request->validated();
        $fields['office_id'] = $office->id;
        if ($request->doctor_id) {
            $doctor = auth()->user()->doctor;
            $this->authorize('createForDoctor', [SupplierItem::class, $doctor]);
            $supplierItem = $doctor->supplierItem()->create($fields);
            return new SupplierItemResource($supplierItem);
        }
        $this->authorize('createForOffice', [SupplierItem::class, $office]);
        $supplierItem = $office->supplierItem()->create($fields);
        return new SupplierItemResource($supplierItem);
    }

    /**
     * Display the specified resource.
     */
    public function show(SupplierItem $supplierItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSupplierItemRequest $request, Office $office, SupplierItem $item)
    {
        $fields = $request->validated();
        if ($request->doctor_id) {
            $doctor = auth()->user()->doctor;
            $this->authorize('updateForDoctor', [$item, $doctor]);
            $item->update($fields);
            return new SupplierItemResource($item);
        }
        $this->authorize('updateForOffice', [$item, $office]);
        $item->update($fields);
        return new SupplierItemResource($item);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SupplierItem $supplierItem)
    {
        //
    }
}
