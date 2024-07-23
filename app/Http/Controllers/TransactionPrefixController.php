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
use App\Models\Tooth;
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

    public function getPrefixAndComplaintAndCasesAndTeeth(Office $office)
    {
        $response = [];

        // Fetch MedicalCase data if office_id is present in the request
        if ($office) {
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
        if ($office) {
            $this->authorize('inOffice', [TransactionPrefix::class, $office]);

            $transactionPrefixes = TransactionPrefix::where([
                'doctor_id' => auth()->user()->doctor->id,
                'office_id' => $office->id,
            ])->get();

            $response['transaction_prefixes'] = TransactionPrefixResource::collection($transactionPrefixes);
        }

        // Define the specific valid ranges of tooth numbers
        $validToothNumbers = array_merge(
            range(11, 18),
            range(21, 28),
            range(31, 38),
            range(41, 48)
        );

        $toothNames = [];

        foreach ($validToothNumbers as $number) {
            $toothModel = new Tooth();
            $toothModel->number_of_tooth = $number;
            $toothNames[$number] = $toothModel->tooth_name; // Using the attribute accessor
        }
        $response['toothNames'] = $toothNames;

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
            'type' => TransactionType::JournalVoucher,
            'prefix' => 'JV',
        ]);
        $doctor->transactionPrefix()->create([
            'type' => TransactionType::JournalVoucher,
            'prefix' => 'JV',
            'office_id' => $office->id,
        ]);
        return response('Done', 201);
    }
}
