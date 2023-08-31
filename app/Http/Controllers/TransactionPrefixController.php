<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Models\TransactionPrefix;
use App\Http\Requests\StoreTransactionPrefixRequest;
use App\Http\Requests\UpdateTransactionPrefixRequest;
use App\Models\Doctor;
use App\Models\Office;

class TransactionPrefixController extends Controller
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
    public function store(StoreTransactionPrefixRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(TransactionPrefix $transactionPrefix)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTransactionPrefixRequest $request, TransactionPrefix $transactionPrefix)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TransactionPrefix $transactionPrefix)
    {
        //
    }

    public function temprary(Office $office, Doctor $doctor)
    {
        $office->transactionPrefix()->create([
            'type' => TransactionType::PaymentVoucher,
            'prefix' => 'PVOC',
        ]);
        $office->transactionPrefix()->create([
            'type' => TransactionType::SupplierInvoice,
            'prefix' => 'SINV',
        ]);
        $doctor->transactionPrefix()->create([
            'type' => TransactionType::PatientInvoice,
            'prefix' => 'PINV',
            'office_id' => $office->id,
        ]);
        $doctor->transactionPrefix()->create([
            'type' => TransactionType::PatientReceipt,
            'prefix' => 'PREC',
            'office_id' => $office->id,
        ]);
        $doctor->transactionPrefix()->create([
            'type' => TransactionType::PaymentVoucher,
            'prefix' => 'PVOC',
            'office_id' => $office->id,
        ]);
        $doctor->transactionPrefix()->create([
            'type' => TransactionType::SupplierInvoice,
            'prefix' => 'SINV',
            'office_id' => $office->id,
        ]);
    }
}
