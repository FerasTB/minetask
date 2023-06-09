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
use App\Models\User;
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
            $owner = User::findOrFail($office->owner->user_id);
            $profile = AccountingProfile::where([
                'patient_id' => $patient->id,
                'office_id' => $office->id, 'doctor_id' => $owner->doctor->id
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
        $profile = AccountingProfile::findOrFail($request->supplier_account_id);
        $invoice = $profile->invoices()->create($fields);
        return new PatientInvoiceResource($invoice);
    }
}
