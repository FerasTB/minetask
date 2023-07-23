<?php

namespace App\Http\Controllers\Api;

use App\Enums\DoubleEntryType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceReceiptsRequest;
use App\Http\Resources\InvoiceReceiptsResource;
use App\Models\AccountingProfile;
use App\Models\COA;
use App\Models\Doctor;
use App\Models\InvoiceReceipt;
use App\Models\Patient;
use Illuminate\Http\Request;

class InvoiceReceiptsController extends Controller
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(InvoiceReceipt $invoiceReceipt)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InvoiceReceipt $invoiceReceipt)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InvoiceReceipt $invoiceReceipt)
    {
        //
    }

    public function storeForPatient(StoreInvoiceReceiptsRequest $request, Patient $patient)
    {
        $fields = $request->validated();
        $account = AccountingProfile::where(['doctor_id' => $request->doctor_id, 'patient_id' => $patient->id])->first();
        // $this->authorize('patientAccount', [InvoiceReceipt::class, $account]);
        $invoice = $account->invoiceReceipt()->create($fields);
        $cash_coa = COA::findOrFail($request->cash_coa);
        $this->authorize('myCOA', [InvoiceReceipt::class, $cash_coa]);
        $doubleEntryFields['COA_id'] = $cash_coa->id;
        $doubleEntryFields['invoice_id'] = $invoice->id;
        $doubleEntryFields['total_price'] = $invoice->total_price;
        $doubleEntryFields['type'] = DoubleEntryType::Positive;
        $cash_coa->doubleEntries()->create($doubleEntryFields);
        return new InvoiceReceiptsResource($invoice);
    }
}
