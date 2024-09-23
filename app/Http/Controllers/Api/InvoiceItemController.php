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
use App\Models\Doctor;
use App\Models\DoubleEntry;
use App\Models\EmployeeSetting;
use App\Models\HasRole;
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
                'doctor_id' => null,
                'sub_type' => COASubType::Receivable
            ])->first();
        } else {
            $doctor = $invoice->doctor;
            $receivable = COA::where([
                'office_id' => $office->id,
                'doctor_id' => $doctor->id,
                'sub_type' => COASubType::Receivable
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
        $description = $fields['description'] ?? null;
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

    public function removeBindingCharge(Request $request)
    {
        $fields = $request->validate([
            'item_id' => 'required|exists:invoice_items,id',
        ]);

        // Find the invoice item
        $invoiceItem = InvoiceItem::where('id', $fields['item_id'])->first();
        $invoice = $invoiceItem->invoice;
        // If the invoice item doesn't exist, return an error
        if (!$invoiceItem) {
            return response()->json(['error' => 'Invoice item not found.'], 404);
        }

        // Remove the invoice item from the invoice
        $invoiceItem->delete();

        // Optionally, you can update the invoice's total price here
        $invoice->total_price -= $invoiceItem->total_price;
        $invoice->save();

        return response()->json(['message' => 'Invoice item removed successfully.'], 200);
    }

    public function storeSupplierInvoiceItem(StoreSupplierInvoiceItemRequest $request, Invoice $invoice)
    {
        $fields = $request->validated();
        $item = $invoice->items()->create($fields);
        $office = $invoice->office;
        if ($office->type == OfficeType::Combined) {
            $payable = COA::where([
                'office_id' => $office->id,
                'doctor_id' => null,
                'sub_type' => COASubType::Payable
            ])->first();
        } else {
            $doctor = auth()->user()->doctor;
            $payable = COA::where([
                'office_id' => $office->id,
                'doctor_id' => $doctor->id,
                'sub_type' => COASubType::Payable
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
        if (auth()->user()->currentRole->name == 'DentalDoctorTechnician') {
            // Find the role based on user_id and office_id (roleable_id)
            $role = HasRole::where('user_id', auth()->id())
                ->where('roleable_id', $office->id)
                ->first();

            if (!$role) {
                // Return JSON response if no role is found
                return response()->json([
                    'error' => 'Role not found for the given user and office.',
                ], 403);
            }

            // Find the employee setting based on the has_role_id
            $employeeSetting = EmployeeSetting::where('has_role_id', $role->id)->first();

            if (!$employeeSetting) {
                // Return JSON response if no employee setting is found
                return response()->json([
                    'error' => 'Employee setting not found for the given role.',
                ], 403);
            }
            $doctor = Doctor::findOrFail($employeeSetting->doctor_id);
            $user = $doctor->user;
        } else {
            // Ensure a valid doctor is authenticated
            $doctor = auth()->user()->doctor;
            $user = auth()->user();
        }

        if (!$doctor) {
            return response('You have to complete your info', 404);
        }
        DB::beginTransaction();
        try {

            if ($office->type == OfficeType::Combined) {
                $payable = COA::where([
                    'office_id' => $office->id,
                    'doctor_id' => null,
                    'sub_type' => COASubType::Payable
                ])->first();
            } else {
                $payable = COA::where([
                    'office_id' => $office->id,
                    'doctor_id' => $doctor->id,
                    'sub_type' => COASubType::Payable
                ])->first();
            }
            $expensesCoa = COA::findOrFail($request->item_coa);
            abort_unless($payable != null && $expensesCoa != null, 403);
            abort_unless($expensesCoa->doctor->id == $doctor->id, 403);
            abort_unless($expensesCoa->general_type == COAGeneralType::Expenses, 403);
            $item = $invoice->items()->create($fields);
            $doubleEntryFields['invoice_item_id'] = $item->id;
            $doubleEntryFields['total_price'] = $item->total_price;
            $doubleEntryFields['type'] = DoubleEntryType::Positive;
            $this->createProfileDoubleEntry($invoice->accounting_profile_id, $item->id, $item->total_price, DoubleEntryType::Positive);
            $expensesCoa->doubleEntries()->create($doubleEntryFields);
            DB::commit();
            return new InvoiceResource($item->invoice()->with(['doctor', 'office', 'items', 'lab'])->first());
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating supplier invoice with items: ' . $e->getMessage());
            throw $e;
        }
    }

    private function createProfileDoubleEntry($accountingProfileId, $itemId, $totalPrice, $type)
    {
        $runningBalance = $this->calculateLapBalance($accountingProfileId, $type == DoubleEntryType::Positive ? $totalPrice : -$totalPrice);

        DoubleEntry::create([
            'accounting_profile_id' => $accountingProfileId,
            'invoice_item_id' => $itemId,
            'total_price' => $totalPrice,
            'type' => $type,
            'running_balance' => $runningBalance
        ]);
    }

    private function calculateLapBalance(int $id, int $thisTransaction)
    {
        $lap = AccountingProfile::findOrFail($id);
        $doubleEntries = $lap->doubleEntries()->get();
        $directDoubleEntries = $lap->directDoubleEntries()->get();

        // Sum the positive and negative entries from both doubleEntries and directDoubleEntries
        $totalPositive = $doubleEntries->where('type', DoubleEntryType::Positive)->sum('total_price') +
            $directDoubleEntries->where('type', DoubleEntryType::Positive)->sum('total_price');
        $totalNegative = $doubleEntries->where('type', DoubleEntryType::Negative)->sum('total_price') +
            $directDoubleEntries->where('type', DoubleEntryType::Negative)->sum('total_price');

        return $totalPositive - $totalNegative + $thisTransaction + $lap->initial_balance;
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
