<?php

namespace App\Http\Controllers\Api;

use App\Enums\AccountingProfileType;
use App\Enums\DentalLabType;
use App\Enums\DoctorRoleForPatient;
use App\Enums\LabOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\LabOrder;
use App\Http\Requests\StoreLabOrderRequest;
use App\Http\Requests\UpdateLabOrderRequest;
use App\Http\Resources\LabOrderResource;
use App\Models\AccountingProfile;
use App\Models\HasRole;
use App\Models\Patient;
use App\Notifications\OrderCreated;

class LabOrderController extends Controller
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
    public function store(StoreLabOrderRequest $request, AccountingProfile $profile)
    {
        abort_unless($profile->type == AccountingProfileType::DentalLabDoctorAccount, 403);
        $fields = $request->validated();
        $patient = Patient::findOrFail($request->patient_id);
        $role = HasRole::where(['roleable_id' => $patient->id, 'roleable_type' => 'App\Models\Patient', 'user_id' => auth()->id()])->first();
        abort_unless($role != null, 403);
        $this->authorize('storeForDoctor', [LabOrder::class, $profile]);
        if ($role->sub_role == DoctorRoleForPatient::DoctorWithApprove) {
            $fields['patient_name'] = $patient->first_name . ' ' . $patient->last_name;
        } else {
            $temp = $patient->temporaries()->where('doctor_id', auth()->user()->doctor->id)->first();
            $fields['patient_name'] = $temp->first_name . ' ' . $temp->last_name;
        }
        if ($profile->lab->type == DentalLabType::Draft) {
            $fields['status'] = LabOrderStatus::Approved;
        }
        $order = $profile->labOrders()->create($fields);
        foreach ($fields['details'] as $detail) {
            $orderDetail = $order->details()->create($detail);
            foreach ($detail['teeth'] as $tooth) {
                $orderDetail->teeth()->create(['number_of_tooth' => $tooth]);
            }
        }
        if ($profile->lab->type == DentalLabType::Draft) {
            $lab = $profile->lab;
            $type = 'NewOrder';
            $lab->notify(new OrderCreated($order, $type));
        }
        return new LabOrderResource($order);
    }

    /**
     * Display the specified resource.
     */
    public function show(LabOrder $labOrder)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLabOrderRequest $request, LabOrder $labOrder)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LabOrder $labOrder)
    {
        //
    }
}
