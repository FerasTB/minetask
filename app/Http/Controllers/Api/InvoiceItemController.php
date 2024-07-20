<?php

namespace App\Http\Controllers\Api;

use App\Enums\COAGeneralType;
use App\Enums\COASubType;
use App\Enums\DentalDoctorTransaction;
use App\Enums\DoubleEntryType;
use App\Enums\OfficeType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDraftPatientInvoiceItemRequest;
use App\Http\Requests\StoreInvoiceItemRequest;
use App\Http\Requests\StorePatientInvoiceItemRequest;
use App\Http\Requests\StorePatientInvoiceReceiptItemRequest;
use App\Http\Requests\StoreSupplierInvoiceItemRequest;
use App\Http\Resources\InvoiceItemsResource;
use App\Http\Resources\InvoiceResource;
use App\Models\AccountingProfile;
use App\Models\COA;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceReceipt;
use App\Models\TeethRecord;
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
                'doctor_id' => null, 'sub_type' => COASubType::Receivable
            ])->first();
        } else {
            $doctor = $invoice->doctor;
            $receivable = COA::where([
                'office_id' => $office->id,
                'doctor_id' => $doctor->id, 'sub_type' => COASubType::Receivable
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

    public function addBindingCharge(StoreDraftPatientInvoiceItemRequest $request, TeethRecord $record)
    {
        $fields = $request->validated();

        // Check for existing draft invoice
        $invoice = Invoice::where('teeth_record_id', $record->id)
            ->where('status', TransactionStatus::Draft)
            ->first();

        // If no draft invoice exists, create a new one
        if (!$invoice) {
            $invoice = Invoice::create([
                'teeth_record_id' => $record->id,
                'status' => TransactionStatus::Draft,
                'total_price' => 0,
                'accounting_profile_id' => $fields['accounting_profile_id'],
                'type' => DentalDoctorTransaction::SellInvoice,
                'data_of_invoice' => now(),
            ]);
        }
        $description = $fields['description'] ?
            $fields['description'] :
            null;
        // Create a new invoice item
        $invoiceItem = new InvoiceItem([
            'name' => $fields['name'],
            'description' => $description,
            'amount' => $fields['amount'],
            'total_price' => $fields['total_price'],
            'price_per_one' => $fields['price_per_one'],
            'coa_id' => $fields['service_coa'],
        ]);

        // Save the invoice item to the invoice
        $invoice->items()->save($invoiceItem);

        return response()->json(['invoice' => $invoice, 'invoiceItem' => $invoiceItem], 201);
    }

    public function storeSupplierInvoiceItem(StoreSupplierInvoiceItemRequest $request, Invoice $invoice)
    {
        $fields = $request->validated();
        $item = $invoice->items()->create($fields);
        $office = $invoice->office;
        if ($office->type == OfficeType::Combined) {
            $payable = COA::where([
                'office_id' => $office->id,
                'doctor_id' => null, 'sub_type' => COASubType::Payable
            ])->first();
        } else {
            $doctor = auth()->user()->doctor;
            $payable = COA::where([
                'office_id' => $office->id,
                'doctor_id' => $doctor->id, 'sub_type' => COASubType::Payable
            ])->first();
        }
        $doubleEntryFields['invoice_item_id'] = $item->id;
        $doubleEntryFields['total_price'] = $item->total_price;
        $doubleEntryFields['type'] = DoubleEntryType::Positive;
        $payable->doubleEntries()->create($doubleEntryFields);
        $expensesCoa = COA::findOrFail($request->item_coa);
        $expensesCoa->doubleEntries()->create($doubleEntryFields);
        return new InvoiceResource($item->invoice()->with(['doctor', 'office', 'items'])->first());
    }

    public function storeDentalLabInvoiceItem(StoreSupplierInvoiceItemRequest $request, Invoice $invoice)
    {
        $fields = $request->validated();
        $office = $invoice->office;
        if ($office->type == OfficeType::Combined) {
            $payable = COA::where([
                'office_id' => $office->id,
                'doctor_id' => null, 'sub_type' => COASubType::Payable
            ])->first();
        } else {
            $doctor = auth()->user()->doctor;
            $payable = COA::where([
                'office_id' => $office->id,
                'doctor_id' => $doctor->id, 'sub_type' => COASubType::Payable
            ])->first();
        }
        $expensesCoa = COA::findOrFail($request->item_coa);
        abort_unless($payable != null && $expensesCoa != null, 403);
        abort_unless($expensesCoa->doctor->id == auth()->user()->doctor->id, 403);
        abort_unless($expensesCoa->general_type == COAGeneralType::Expenses, 403);
        $item = $invoice->items()->create($fields);
        $doubleEntryFields['invoice_item_id'] = $item->id;
        $doubleEntryFields['total_price'] = $item->total_price;
        $doubleEntryFields['type'] = DoubleEntryType::Positive;
        $payable->doubleEntries()->create($doubleEntryFields);
        $expensesCoa->doubleEntries()->create($doubleEntryFields);
        return new InvoiceResource($item->invoice()->with(['doctor', 'office', 'items', 'lab'])->first());
    }

    public function storePatientInvoiceReceiptItem(StorePatientInvoiceReceiptItemRequest $request, InvoiceReceipt $invoice)
    {
        $fields = $request->validated();
        $item = $invoice->items()->create($fields);
        $serviceCoa = COA::findOrFail($request->service_coa);
        $this->authorize('myCOA', [InvoiceItem::class, $serviceCoa]);
        $doubleEntryFields['invoice_item_id'] = $item->id;
        $doubleEntryFields['total_price'] = $item->total_price;
        $doubleEntryFields['type'] = DoubleEntryType::Positive;
        $doubleEntryFields['COA_id'] = $serviceCoa->COA_id;
        $serviceCoa->doubleEntries()->create($doubleEntryFields);
        return new InvoiceItemsResource($item);
    }
}
