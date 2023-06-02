<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierItemRequest;
use App\Http\Resources\SupplierItemResource;
use App\Models\AccountingProfile;
use App\Models\SupplierItem;
use Illuminate\Http\Request;

class SupplierItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(AccountingProfile $supplier)
    {
        return SupplierItemResource::collection($supplier->supplierItem);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSupplierItemRequest $request, AccountingProfile $supplier)
    {
        $fields = $request->validated();
        $this->authorize('create', [SupplierItem::class, $supplier]);
        $supplierItem = $supplier->supplierItem()->create($fields);
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
    public function update(Request $request, SupplierItem $supplierItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SupplierItem $supplierItem)
    {
        //
    }
}
