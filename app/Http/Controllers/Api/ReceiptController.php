<?php

namespace App\Http\Controllers\Api;

use App\Enums\DoubleEntryType;
use App\Enums\OfficeType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePatientReceiptRequest;
use App\Http\Requests\StoreReceiptRequest;
use App\Http\Requests\StoreSupplierReceiptRequest;
use App\Http\Resources\ReceiptResource;
use App\Models\AccountingProfile;
use App\Models\COA;
use App\Models\Doctor;
use App\Models\Office;
use App\Models\Patient;
use App\Models\Receipt;
use Illuminate\Http\Request;

class ReceiptController extends Controller
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
    public function store(StoreReceiptRequest $request, Patient $patient)
    {
        // $fields = $request->validated();
        // $office = Office::findOrFail($request->office_id);
        // if ($office->type == OfficeType::Combined) {
        //     $profile = AccountingProfile::where([
        //         'patient_id' => $patient->id,
        //         'office_id' => $office->id, 'doctor_id' => null
        //     ])->first();
        //     $receipt = $profile->receipts()->create($fields);
        //     $payable = $office->payable;
        //     $cash = $office->cash;
        // } else {
        //     $profile = AccountingProfile::where([
        //         'patient_id' => $patient->id,
        //         'office_id' => $office->id, 'doctor_id' => $request->doctor_id
        //     ])->first();
        //     $receipt = $profile->receipts()->create($fields);
        //     $doctor = Doctor::find($request->doctor_id);
        //     $payable = $doctor->payable;
        //     $cash = $doctor->cash;
        // }
        // $doubleEntryFields['receipt_id'] = $receipt->id;
        // $doubleEntryFields['total_price'] = $receipt->total_price;
        // $doubleEntryFields['type'] = DoubleEntryType::Negative;
        // $payable->doubleEntries()->create($doubleEntryFields);
        // $cash->doubleEntries()->create($doubleEntryFields);
        // return new PatientInvoiceResource($invoice);
    }

    /**
     * Display the specified resource.
     */
    public function show(Receipt $receipt)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Receipt $receipt)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Receipt $receipt)
    {
        //
    }

    public function storeSupplierReceipt(StoreSupplierReceiptRequest $request)
    {
        $fields = $request->validated();
        $office = Office::findOrFail($request->office_id);
        if ($office->type == OfficeType::Combined) {
            $profile = AccountingProfile::where([
                'supplier_name' => $request->supplier_name,
                'office_id' => $office->id, 'doctor_id' => null
            ])->first();
            $receipt = $profile->receipts()->create($fields);
            $payable = COA::where([
                'office_id' => $office->id,
                'doctor_id' => null, 'name' => COA::Payable
            ]);
            $cash = COA::where([
                'office_id' => $office->id,
                'doctor_id' => null, 'name' => COA::Cash
            ]);
        } else {
            $profile = AccountingProfile::where([
                'supplier_name' => $request->supplier_name,
                'office_id' => $office->id, 'doctor_id' => $request->doctor_id
            ])->first();
            $receipt = $profile->receipts()->create($fields);
            $doctor = Doctor::find($request->doctor_id);
            $payable = COA::where([
                'office_id' => $office->id,
                'doctor_id' => $doctor->id, 'name' => COA::Payable
            ]);
            $cash = COA::where([
                'office_id' => $office->id,
                'doctor_id' => $doctor->id, 'name' => COA::Cash
            ]);
        }
        $doubleEntryFields['receipt_id'] = $receipt->id;
        $doubleEntryFields['total_price'] = $receipt->total_price;
        $doubleEntryFields['type'] = DoubleEntryType::Negative;
        $payable->doubleEntries()->create($doubleEntryFields);
        $cash->doubleEntries()->create($doubleEntryFields);
        return new ReceiptResource($receipt);
    }

    public function storePatientReceipt(StorePatientReceiptRequest $request, Patient $patient)
    {
        $fields = $request->validated();
        $office = Office::findOrFail($request->office_id);
        if ($office->type == OfficeType::Combined) {
            $profile = AccountingProfile::where([
                'patient_id' => $patient->id,
                'office_id' => $office->id, 'doctor_id' => null
            ])->first();
            $receipt = $profile->receipts()->create($fields);
            $receivable = COA::where([
                'office_id' => $office->id,
                'doctor_id' => null, 'name' => COA::Receivable
            ])->first();
            $cash = COA::where([
                'office_id' => $office->id,
                'doctor_id' => null, 'name' => COA::Cash
            ])->first();
        } else {
            $profile = AccountingProfile::where([
                'patient_id' => $patient->id,
                'office_id' => $office->id, 'doctor_id' => $request->doctor_id
            ])->first();
            $receipt = $profile->receipts()->create($fields);
            $doctor = Doctor::find($request->doctor_id);
            $receivable = COA::where([
                'office_id' => $office->id,
                'doctor_id' => $doctor->id, 'name' => COA::Receivable
            ])->first();
            $cash = COA::where([
                'office_id' => $office->id,
                'doctor_id' => $doctor->id, 'name' => COA::Cash
            ])->first();
        }
        $doubleEntryFields['receipt_id'] = $receipt->id;
        $doubleEntryFields['total_price'] = $receipt->total_price;
        $doubleEntryFields['type'] = DoubleEntryType::Negative;
        $receivable->doubleEntries()->create($doubleEntryFields);
        $doubleEntryFields['type'] = DoubleEntryType::Positive;
        $cash->doubleEntries()->create($doubleEntryFields);
        return new ReceiptResource($receipt);
    }
}
