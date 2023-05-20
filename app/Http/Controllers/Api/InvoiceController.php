<?php

namespace App\Http\Controllers\Api;

use App\Enums\DoubleEntryType;
use App\Enums\OfficeType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePatientInvoiceRequest;
use App\Http\Requests\StoreSupplierInvoiceRequest;
use App\Http\Resources\PatientInvoiceResource;
use App\Models\AccountingProfile;
use App\Models\COA;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Office;
use App\Models\Patient;
use Illuminate\Http\Request;

class InvoiceController extends Controller
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
    public function show(Invoice $invoice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        //
    }

    public function storePatientInvoice(StorePatientInvoiceRequest $request, Patient $patient)
    {
        $fields = $request->validated();
        $office = Office::findOrFail($request->office_id);
        if ($office->type == OfficeType::Combined) {
            $profile = AccountingProfile::where([
                'patient_id' => $patient->id,
                'office_id' => $office->id, 'doctor_id' => null
            ])->first();
            $invoice = $profile->invoices()->create($fields);
        } else {
            $profile = AccountingProfile::where([
                'patient_id' => $patient->id,
                'office_id' => $office->id, 'doctor_id' => $request->doctor_id
            ])->first();
            $invoice = $profile->invoices()->create($fields);
        }
        return new PatientInvoiceResource($invoice);
    }

    public function storeSupplierInvoice(StoreSupplierInvoiceRequest $request)
    {
        $fields = $request->validated();
        $office = Office::findOrFail($request->office_id);
        if ($office->type == OfficeType::Combined) {
            $profile = AccountingProfile::where([
                'supplier_name' => $request->supplier_name,
                'office_id' => $office->id, 'doctor_id' => null
            ])->first();
            $invoice = $profile->invoices()->create($fields);
            $payable = COA::where([
                'office_id' => $office->id,
                'doctor_id' => null, 'name' => COA::Payable
            ]);
        } else {
            $profile = AccountingProfile::where([
                'supplier_name' => $request->supplier_name,
                'office_id' => $office->id, 'doctor_id' => $request->doctor_id
            ])->first();
            $invoice = $profile->invoices()->create($fields);
            $doctor = Doctor::find($request->doctor_id);
            $payable = COA::where([
                'office_id' => $office->id,
                'doctor_id' => $doctor->id, 'name' => COA::Payable
            ]);
        }
        $doubleEntryFields['invoice_id'] = $invoice->id;
        $doubleEntryFields['total_price'] = $invoice->total_price;
        $doubleEntryFields['type'] = DoubleEntryType::Positive;
        $payable->doubleEntries()->create($doubleEntryFields);
        $expensesCoa = $profile->COA;
        $expensesCoa->doubleEntries()->create($doubleEntryFields);
        return new PatientInvoiceResource($invoice);
    }
}
