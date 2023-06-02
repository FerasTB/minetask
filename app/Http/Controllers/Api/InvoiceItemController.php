<?php

namespace App\Http\Controllers\Api;

use App\Enums\DoubleEntryType;
use App\Enums\OfficeType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceItemRequest;
use App\Http\Requests\StorePatientInvoiceItemRequest;
use App\Http\Requests\StoreSupplierInvoiceItemRequest;
use App\Http\Resources\InvoiceItemsResource;
use App\Http\Resources\InvoiceResource;
use App\Models\AccountingProfile;
use App\Models\COA;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;

class InvoiceItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Invoice $invoice)
    {
        return InvoiceItemsResource::collection($invoice->items);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInvoiceItemRequest $request, Invoice $invoice)
    {
        $fields = $request->validated();
        $item = $invoice->items()->create($fields);
        return new InvoiceItemsResource($item);
    }

    /**
     * Display the specified resource.
     */
    public function show(InvoiceItem $Item)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InvoiceItem $invoiceItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InvoiceItem $invoiceItem)
    {
        //
    }

    public function storePatientInvoiceItem(StorePatientInvoiceItemRequest $request, Invoice $invoice)
    {
        $fields = $request->validated();
        $item = $invoice->items()->create($fields);
        $office = $invoice->office;
        if ($office->type == OfficeType::Combined) {
            $receivable = COA::where([
                'office_id' => $office->id,
                'doctor_id' => null, 'name' => COA::Receivable
            ])->first();
        } else {
            $doctor = $invoice->doctor;
            $receivable = COA::where([
                'office_id' => $office->id,
                'doctor_id' => $doctor->id, 'name' => COA::Receivable
            ])->first();
        }
        $doubleEntryFields['COA_id'] = $receivable->id;
        $doubleEntryFields['invoice_item_id'] = $item->id;
        $doubleEntryFields['total_price'] = $item->total_price;
        $doubleEntryFields['type'] = DoubleEntryType::Positive;
        $receivable->doubleEntries()->create($doubleEntryFields);
        $serviceCoa = COA::findOrFail($request->service_coa);
        $doubleEntryFields['COA_id'] = $serviceCoa->COA_id;
        $serviceCoa->doubleEntries()->create($doubleEntryFields);
        return new InvoiceItemsResource($item);
    }

    public function storeSupplierInvoiceItem(StoreSupplierInvoiceItemRequest $request, Invoice $invoice)
    {
        $fields = $request->validated();
        $item = $invoice->items()->create($fields);
        $office = $invoice->office;
        if ($office->type == OfficeType::Combined) {
            $payable = COA::where([
                'office_id' => $office->id,
                'doctor_id' => null, 'name' => COA::Payable
            ])->first();
        } else {
            $doctor = auth()->user()->doctor;
            $payable = COA::where([
                'office_id' => $office->id,
                'doctor_id' => $doctor->id, 'name' => COA::Payable
            ])->first();
        }
        $doubleEntryFields['invoice_item_id'] = $item->id;
        $doubleEntryFields['total_price'] = $item->total_price;
        $doubleEntryFields['type'] = DoubleEntryType::Positive;
        $payable->doubleEntries()->create($doubleEntryFields);
        $expensesCoa = COA::findOrFail($request->item_coa);
        $expensesCoa->doubleEntries()->create($doubleEntryFields);
        return new InvoiceResource($item);
    }
}
