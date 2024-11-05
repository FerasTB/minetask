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
use App\Http\Resources\PatientCaseResource;
use App\Http\Resources\ToothResource;
use App\Models\AccountingProfile;
use App\Models\Doctor;
use App\Models\EmployeeSetting;
use App\Models\HasRole;
use App\Models\Office;
use App\Models\Operation;
use App\Models\Patient;
use App\Models\PatientCase;
use App\Models\Record;
use App\Models\Role;
use App\Models\TeethRecord;
use App\Models\User;
use GuzzleHttp\Psr7\Response;
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
        $office = Office::findOrFail($request->office_id);

        if (in_array(auth()->user()->currentRole->name, Role::Technicians)) {
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
        $record = TeethRecord::findOrFail($request->record_id);
        $patientCase = $record->PatientCase;
        $patient = $patientCase->patient;
        $doctor = $patientCase->case->doctor;
        abort_unless($doctor->id == $doctor->id, 403);
        foreach ($fields['operations'] as $operation_fields) {
            $operation = $record->operations()->create($operation_fields);
            $tooth = $operation->teeth()->create($operation_fields);
        }
        $cases = $doctor->PatientCases()->where('patient_id', $patient->id)
            ->with(['case', 'teethRecords', 'teethRecords.operations', 'teethRecords.diagnosis', 'teethRecords.operations.teeth', 'teethRecords.diagnosis.teeth'])->get();
        return PatientCaseResource::collection($cases);
    }

    public function createDraftInvoice(Request $request, Patient $patient, $doctor, Office $office)
    {
        $doctor = auth()->user()->doctor;
        if ($office->type == OfficeType::Combined) {
            $owner = User::findOrFail($office->owner->user_id);
            $profile = AccountingProfile::where([
                'patient_id' => $patient->id,
                'office_id' => $office->id,
                'doctor_id' => $owner->doctor->id
            ])->first();
        } else {
            $profile = AccountingProfile::where([
                'patient_id' => $patient->id,
                'office_id' => $office->id,
                'doctor_id' => $doctor->id
            ])->first();
        }

        $fields['type'] = DentalDoctorTransaction::SellInvoice;
        $fields['status'] = TransactionStatus::Draft;
        if (!$request->has('date_of_invoice')) {
            $fields['date_of_invoice'] = now();
        }
        $invoice = $profile->invoices()->create($fields);
        foreach ($fields['operations'] as $operation_fields) {
            $operation_fields['description'] = $operation_fields['operation_description'];
            $operation_fields['name'] = $operation_fields['operation_name'];
            $operation_fields['amount'] = 1;
            // $operation_fields['operation_id'] = $operation->id;
            $item = $invoice->items()->create($operation_fields);
        }
        return response()->json(['message' => 'binding charge created'], 201);
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
        $office = $operation->record->PatientCase->case->office;
        if (in_array(auth()->user()->currentRole->name, Role::Technicians)) {
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
        $this->authorize('view', $operation, $doctor);
        return new OperationResource($operation);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOperationRequest $request, Operation $operation)
    {
        $office = $operation->record->PatientCase->case->office;
        if (in_array(auth()->user()->currentRole->name, Role::Technicians)) {
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
        $this->authorize('update', [$operation, $doctor]);
        $fields = $request->validated();
        if ($request->has('number_of_tooth')) {
            $tooth = $operation->teeth()->first();
            if ($tooth) {
                $tooth->update([
                    'number_of_tooth' => $fields['number_of_tooth'],
                ]);
            } else {
                return response()->json(['message' => 'something went wrong'], 403);
            }
        }
        if ($request->has('operation_name')) {
            $operation->operation_name = $request->input('operation_name');
        }

        if ($request->has('operation_description')) {
            $operation->operation_description = $request->input('operation_description');
        }
        $operation->save();
        return new OperationResource($operation);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Operation $operation)
    {
        $office = $operation->record->PatientCase->case->office;
        if (in_array(auth()->user()->currentRole->name, Role::Technicians)) {
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
        $this->authorize('update', [$operation, $doctor]);
        $operation->load('record');
        // Check if the related record's is_closed is false
        if ($operation->record && boolval($operation->record->is_closed) == false) {
            // Delete related teeth
            $operation->teeth()->delete();

            // Delete the operation
            $operation->delete();

            return response()->json(['message' => 'Operation and its associated teeth have been deleted.'], 200);
        }

        // If the record is closed or doesn't exist, return an error
        return response()->json(['message' => 'Operation cannot be deleted because the record is closed or does not exist.'], 403);
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
