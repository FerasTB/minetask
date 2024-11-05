<?php

namespace App\Http\Controllers\Api;

use App\Enums\COAGeneralType;
use App\Enums\COASubType;
use App\Enums\DoubleEntryType;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDirectDoubleEntryInvoiceRequest;
use App\Http\Resources\DirectDoubleEntryInvoiceResource;
use App\Models\COA;
use App\Models\DirectDoubleEntryInvoice;
use App\Models\Doctor;
use App\Models\EmployeeSetting;
use App\Models\HasRole;
use App\Models\Office;
use App\Models\Role;
use App\Models\TransactionPrefix;
use Illuminate\Http\Request;

class DirectDoubleEntryInvoiceController extends Controller
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
    public function store(StoreDirectDoubleEntryInvoiceRequest $request, COA $coa)
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
        $coa2 = COA::findOrFail($request->COA_id);
        $office = Office::findOrFail($request->office_id);
        $this->authorize('create', [DirectDoubleEntryInvoice::class, $coa, $coa2, $doctor]);
        $transactionNumber = TransactionPrefix::where(['office_id' => $office->id, 'doctor_id' => $doctor->id, 'type' => TransactionType::PaymentVoucher])->first();
        $fields['receipt_number'] = $transactionNumber->last_transaction_number + 1;
        $directDE = $doctor->DirectDoubleEntryInvoice()->create($fields);
        $transactionNumber->update(['last_transaction_number' => $fields['receipt_number']]);
        $coa->directDoubleEntries()->create([
            'direct_double_entry_invoice_id' => $directDE->id,
            'total_price' => $directDE->total_price,
            'type' => DoubleEntryType::getValue($request->main_coa_type),
        ]);
        $coa2Type = $request->main_coa_type == "Positive" ? 'Negative' : 'Positive';
        $coa2->directDoubleEntries()->create([
            'direct_double_entry_invoice_id' => $directDE->id,
            'total_price' => $directDE->total_price,
            'type' => DoubleEntryType::getValue($coa2Type),
        ]);
        return new DirectDoubleEntryInvoiceResource($directDE);
    }

    public function storePositiveForCashAndRevenue(StoreDirectDoubleEntryInvoiceRequest $request, COA $coa)
    {
        $fields = $request->validated();
        $coa2 = COA::findOrFail($request->COA_id);
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
        $this->authorize('create', [DirectDoubleEntryInvoice::class, $coa, $coa2, $doctor]);
        abort_unless($coa->sub_type == COASubType::Cash || $coa->general_type == COAGeneralType::Revenue, 403);
        abort_unless($coa2->sub_type == COASubType::Cash || $coa2->general_type == COAGeneralType::Revenue, 403);
        $transactionNumber = TransactionPrefix::where(['office_id' => $office->id, 'doctor_id' => $doctor->id, 'type' => TransactionType::PatientReceipt])->first();
        $fields['receipt_number'] = $transactionNumber->last_transaction_number + 1;
        $directDE = $doctor->DirectDoubleEntryInvoice()->create($fields);
        $transactionNumber->update(['last_transaction_number' => $fields['receipt_number']]);
        $coa->directDoubleEntries()->create([
            'direct_double_entry_invoice_id' => $directDE->id,
            'total_price' => $directDE->total_price,
            'type' => DoubleEntryType::getValue('Positive'),
        ]);
        $coa2->directDoubleEntries()->create([
            'direct_double_entry_invoice_id' => $directDE->id,
            'total_price' => $directDE->total_price,
            'type' => DoubleEntryType::getValue('Positive'),
        ]);
        return new DirectDoubleEntryInvoiceResource($directDE);
    }

    /**
     * Display the specified resource.
     */
    public function show(DirectDoubleEntryInvoice $directDoubleEntryInvoice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DirectDoubleEntryInvoice $directDoubleEntryInvoice)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DirectDoubleEntryInvoice $directDoubleEntryInvoice)
    {
        //
    }
}
