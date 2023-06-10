<?php

namespace App\Http\Controllers\Api;

use App\Enums\DoubleEntryType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDirectDoubleEntryInvoiceRequest;
use App\Http\Resources\DirectDoubleEntryInvoiceResource;
use App\Models\COA;
use App\Models\DirectDoubleEntryInvoice;
use App\Models\Doctor;
use Illuminate\Http\Request;

class DirectDoubleEntryInvoiceController extends Controller
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
    public function store(StoreDirectDoubleEntryInvoiceRequest $request, COA $coa)
    {
        $fields = $request->validated();
        $coa2 = COA::findOrFail($request->COA_id);
        $this->authorize('create', [DirectDoubleEntryInvoice::class, $coa, $coa2]);
        $doctor = auth()->user()->doctor;
        $directDE = $doctor->DirectDoubleEntryInvoice()->create($fields);
        $coa->directDoubleEntries()->create([
            'direct_double_entry_invoice_id' => $directDE->id,
            'total_price' => $directDE->total_price,
            'type' => DoubleEntryType::getValue($request->main_coa_type),
        ]);
        $coa2Type = $request->main_coa_type == "Positive" ? 'Negative' : 'Positive';
        $coa->directDoubleEntries()->create([
            'direct_double_entry_invoice_id' => $directDE->id,
            'total_price' => $directDE->total_price,
            'type' => DoubleEntryType::getValue($coa2Type),
        ]);
        return new DirectDoubleEntryInvoiceResource($directDE);
    }

    /**
     * Display the specified resource.
     */
    public function show(DirectDoubleEntryInvoice $directDoubleEntryInvoice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DirectDoubleEntryInvoice $directDoubleEntryInvoice)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DirectDoubleEntryInvoice $directDoubleEntryInvoice)
    {
        //
    }
}
