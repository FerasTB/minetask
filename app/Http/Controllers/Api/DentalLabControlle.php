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
use App\Models\Office;
use Illuminate\Http\Request;

class DentalLabControlle extends Controller
{
    public function store(StoreDentalLabForDoctorRequest $request, Office $office)
    {
        $this->authorize('createForDoctor', [DentalLab::class, $office]);
        $fields = $request->validated();
        $fields['type'] = DentalLabType::Draft;
        if ($request->coa_id) {
            $coa = COA::findOrFail($request->coa_id);
            abort_unless($coa->doctor_id == auth()->user()->doctor->id && $coa->office_id == $office->id, 403);
        }
        $lab = DentalLab::create($fields);
        auth()->user()->roles()->create([
            'roleable_id' => $lab->id,
            'roleable_type' => 'App\Models\DentalLab',
            'sub_role' => SubRole::DentalLabDraft,
        ]);
        $account = $lab->accountingProfiles()->create([
            'office_id' => $office->id,
            'doctor_id' => auth()->user()->doctor->id,
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
