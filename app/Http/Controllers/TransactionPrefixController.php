<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Models\TransactionPrefix;
use App\Http\Requests\StoreTransactionPrefixRequest;
use App\Http\Requests\UpdateTransactionPrefixRequest;
use App\Http\Resources\MedicalCaseResource;
use App\Http\Resources\TeethComplaintListResource;
use App\Http\Resources\TransactionPrefixResource;
use App\Models\Doctor;
use App\Models\MedicalCase;
use App\Models\Office;
use App\Models\TeethComplaintList;
use Illuminate\Http\Request;

class TransactionPrefixController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Office $office)
    {
        $this->authorize('inOffice', [TransactionPrefix::class, $office]);
        return TransactionPrefixResource::collection(TransactionPrefix::where([
            'doctor_id' => auth()->user()->doctor->id,
            'office_id' => $office->id,
        ])->get());
    }

    public function getPrefixAndComplaintAndCases(Request $request)
    {
        $response = [];

        // Fetch MedicalCase data if office_id is present in the request
        if ($request->has('office_id')) {
            $office = Office::findOrFail($request->office_id);
            $this->authorize('viewAny', [MedicalCase::class, $office]);

            $cases = MedicalCase::where([
                'doctor_id' => auth()->user()->doctor->id,
                'office_id' => $office->id
            ])->with(['doctor', 'office'])->get();

            $response['medical_cases'] = MedicalCaseResource::collection($cases);
        }

        // Fetch TeethComplaintList data
        $teethComplaints = TeethComplaintList::all();
        $response['teeth_complaints'] = TeethComplaintListResource::collection($teethComplaints);

        // Fetch TransactionPrefix data if office_id is present in the request
        if ($request->has('office_id')) {
            $office = Office::findOrFail($request->office_id);
            $this->authorize('inOffice', [TransactionPrefix::class, $office]);

            $transactionPrefixes = TransactionPrefix::where([
                'doctor_id' => auth()->user()->doctor->id,
                'office_id' => $office->id,
            ])->get();

            $response['transaction_prefixes'] = TransactionPrefixResource::collection($transactionPrefixes);
        }

        return response()->json($response);
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
        return response('Done', 201);
    }
}
