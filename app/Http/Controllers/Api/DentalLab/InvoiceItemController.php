<?php

namespace App\Http\Controllers\Api\DentalLab;

use App\Enums\COAGeneralType;
use App\Enums\COASubType;
use App\Enums\DoubleEntryType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierInvoiceItemRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\COA;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceItemController extends Controller
{
    public function storeSupplierInvoiceItem(StoreSupplierInvoiceItemRequest $request, Invoice $invoice)
    {
        $fields = $request->validated();
        $item = $invoice->items()->create($fields);
        $lab = $invoice->lab;
        $payable = COA::where([
            'dental_lab_id' => $lab->id,
            'doctor_id' => $invoice->doctor->id, 'sub_type' => COASubType::Payable
        ])->first();
        $inventory = COA::where([
            'dental_lab_id' => $lab->id,
            'doctor_id' => $invoice->doctor->id, 'sub_type' => COASubType::Inventory
        ])->first();
        $expensesCoa = COA::findOrFail($request->item_coa);
        abort_unless($payable != null && $expensesCoa != null && $inventory != null, 403);
        abort_unless($expensesCoa->doctor->id == auth()->user()->decoct->id, 403);
        abort_unless($expensesCoa->type == COAGeneralType::Expenses, 403);
        $doubleEntryFields['invoice_item_id'] = $item->id;
        $doubleEntryFields['total_price'] = $item->total_price;
        $doubleEntryFields['type'] = DoubleEntryType::Positive;
        $payable->doubleEntries()->create($doubleEntryFields);
        $inventory->doubleEntries()->create($doubleEntryFields);
        $expensesCoa->doubleEntries()->create($doubleEntryFields);
        return new InvoiceResource($item->invoice()->with(['doctor', 'office', 'items', 'lab'])->first());
    }
}
