<?php

namespace App\Http\Controllers\Api;

use App\Enums\DentalDoctorTransaction;
use App\Enums\OfficeType;
use App\Enums\TransactionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOperationRequest;
use App\Http\Requests\StoreOperationWithInvoiceRequest;
use App\Http\Requests\StoreToothRequest;
use App\Http\Requests\UpdateOperationRequest;
use App\Http\Resources\OperationResource;
use App\Http\Resources\ToothResource;
use App\Models\AccountingProfile;
use App\Models\Office;
use App\Models\Operation;
use App\Models\PatientCase;
use App\Models\Record;
use App\Models\TeethRecord;
use App\Models\User;
use Illuminate\Http\Request;
use PHPUnit\Framework\Constraint\Operator;

class OperationController extends Controller
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
    public function store(StoreOperationRequest $request)
    {
        $fields = $request->validated();
        $record = TeethRecord::findOrFail($request->record_id);
        $patientCase = $record->PatientCase;
        $patient = $patientCase->patient;
        $doctor = $patientCase->case->doctor;
        abort_unless($doctor->id == auth()->user()->doctor->id, 403);
        // $office = Office::findOrFail($request->office_id);
        // if ($office->type == OfficeType::Combined) {
        //     $owner = User::findOrFail($office->owner->user_id);
        //     $profile = AccountingProfile::where([
        //         'patient_id' => $patient->id,
        //         'office_id' => $office->id, 'doctor_id' => $owner->doctor->id
        //     ])->first();
        //     $fields['type'] = DentalDoctorTransaction::SellInvoice;
        //     $fields['status'] = TransactionStatus::Draft;
        //     if (!$request->has('date_of_invoice')) {
        //         $fields['date_of_invoice'] = now();
        //     }
        //     $invoice = $profile->invoices()->create($fields);
        // } else {
        //     $profile = AccountingProfile::where([
        //         'patient_id' => $patient->id,
        //         'office_id' => $office->id, 'doctor_id' => $request->doctor_id
        //     ])->first();
        //     $fields['type'] = DentalDoctorTransaction::SellInvoice;
        //     $fields['status'] = TransactionStatus::Draft;
        //     $invoice = $profile->invoices()->create($fields);
        // }
        foreach ($fields['operations'] as $operation) {
            $operation = $record->operations()->create($fields);
            $tooth = $operation->teeth()->create(['number_of_tooth' => $operation['tooth']]);
            // $fields['description'] = $fields['operation_description'];
            // $fields['name'] = $fields['operation_name'];
            // $item = $invoice->items()->create($fields);
        }
    }

    public function storeWithInvoice(StoreOperationWithInvoiceRequest $request)
    {
        $fields = $request->validated();
        $record = TeethRecord::findOrFail($request->record_id);
        $this->authorize('create', [Operation::class, $record]);
        foreach ($fields['operations'] as $operation) {
            $operation = $record->operations()->create($fields);
            foreach ($operation['teeth'] as $tooth) {
                $tooth = $operation->teeth()->create(['number_of_tooth' => $tooth]);
            }
        }
        return new OperationResource($operation);
    }

    /**
     * Display the specified resource.
     */
    public function show(Operation $operation)
    {
        $this->authorize('view', $operation);
        return new OperationResource($operation);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOperationRequest $request, Operation $operation)
    {
        $this->authorize('update', $operation);
        $fields = $request->validated();
        $operation->update($fields);
        return new OperationResource($operation);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Operation $operation)
    {
        //
    }

    public function RecordOperation(TeethRecord $record)
    {
        $this->authorize('viewAny', [Operation::class, $record]);
        return OperationResource::collection($record->operations);
    }

    public function addTooth(StoreToothRequest $request, Operation $operation)
    {
        $fields = $request->validated();
        $tooth = $operation->teeth()->create($fields);
        return new ToothResource($tooth);
    }
}
