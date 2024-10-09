<?php

namespace App\Http\Controllers\Api;

use App\Enums\AccountingProfileType;
use App\Enums\DentalLabType;
use App\Enums\SubRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDentalLabForDoctorRequest;
use App\Http\Requests\StoreDentalLabRequest;
use App\Http\Resources\DentalLabResource;
use App\Models\AccountingProfile;
use App\Models\COA;
use App\Models\CoaGroup;
use App\Models\DentalLab;
use App\Models\Doctor;
use App\Models\EmployeeSetting;
use App\Models\HasRole;
use App\Models\Office;
use Illuminate\Http\Request;

class DentalLabControlle extends Controller
{
    public function store(StoreDentalLabForDoctorRequest $request, Office $office)
    {
        if (auth()->user()->currentRole->name == 'DentalDoctorTechnician') {
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
        $this->authorize('createForDoctor', [DentalLab::class, $office]);

        $fields = $request->validated();
        $fields['type'] = DentalLabType::Draft;
        if ($request->coa_id) {
            $coa = COA::findOrFail($request->coa_id);
            abort_unless($coa->doctor_id == $doctor->id && $coa->office_id == $office->id, 403);
        }
        $lab = DentalLab::create($fields);
        $doctor->user->roles()->create([
            'roleable_id' => $lab->id,
            'roleable_type' => 'App\Models\DentalLab',
            'sub_role' => SubRole::DentalLabDraft,
        ]);
        $account = $lab->accountingProfiles()->create([
            'office_id' => $office->id,
            'doctor_id' => $doctor->id,
            'COA_id' => $request->coa_id,
            'secondary_initial_balance' => $request->secondary_initial_balance != null ? $request->secondary_initial_balance : 0,
            'note' => $request->note,
            'type' => AccountingProfileType::DentalLabDoctorAccount,
        ]);
        $account = AccountingProfile::findOrFail($account->id);
        $account->load(['invoices', 'invoices.items', 'receipts', 'lab', 'labOrders', 'labOrders.details', 'labOrders.details.teeth', 'labOrders.orderSteps']);
        return new DentalLabResource($lab);
    }
}
