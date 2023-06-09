<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceReceiptsRequest;
use App\Http\Resources\InvoiceReceiptsResource;
use App\Models\AccountingProfile;
use App\Models\Doctor;
use App\Models\InvoiceReceipt;
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

    public function storeForPatient(StoreInvoiceReceiptsRequest $request, AccountingProfile $account)
    {
        $fields = $request->validated();
        // $doctor = Doctor::findOrFail($request->doctor_id);
        // $this->authorize('patientAccount', [InvoiceReceipt::class, $account]);
        $invoice = $account->invoiceReceipt()->create($fields);
        return new InvoiceReceiptsResource($invoice);
    }
}
