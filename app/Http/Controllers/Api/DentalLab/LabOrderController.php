<?php

namespace App\Http\Controllers\Api\DentalLab;

use App\Enums\AccountingProfileType;
use App\Enums\LabOrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AcceptLabOrderFromDoctorRequest;
use App\Http\Requests\StoreLabOrderForLabRequest;
use App\Http\Requests\UpdateLabOrderStatusRequest;
use App\Http\Resources\LabOrderResource;
use App\Models\AccountingProfile;
use App\Models\LabOrder;
use App\Models\Patient;
use App\Notifications\Orderstatus;
use Illuminate\Http\Request;

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
    public function store(StoreLabOrderForLabRequest $request, AccountingProfile $profile)
    {
        abort_unless($profile->type == AccountingProfileType::DentalLabDoctorAccount, 403);
        $fields = $request->validated();
        $this->authorize('storeForLab', [LabOrder::class, $profile]);
        $fields['status'] = LabOrderStatus::Approved;
        $order = $profile->labOrders()->create($fields);
        foreach ($fields['details'] as $detail) {
            $orderDetail = $order->details()->create($detail);
            foreach ($detail['teeth'] as $tooth) {
                $orderDetail->teeth()->create(['number_of_tooth' => $tooth]);
            }
        }
        $rank = 1;
        foreach ($fields['order_steps'] as $step) {
            $step['rank'] = $rank;
            if (array_key_exists('patient_id', $step) && $step['patient_id'] != null) {
                $patient = Patient::findOrFail($step['patient_id']);
                $step['user_id'] = $patient->user->id;
            } else {
                $step['user_id'] = auth()->id();
            }
            $OrderStep = $order->orderSteps()->create($step);
            if ($rank == 1) {
                $fields['current_step_id'] = $OrderStep->id;
            }
            $rank++;
        }
        $order->update($fields);
        $order->load(['details', 'details.teeth', 'orderSteps']);
        return new LabOrderResource($order);
    }

    public function acceptOrderFromDoctor(AcceptLabOrderFromDoctorRequest $request, LabOrder $order)
    {
        $fields = $request->validated();
        $this->authorize('acceptFromDoctor', [$order]);
        $fields['status'] = LabOrderStatus::Approved;
        $rank = 1;
        foreach ($fields['order_steps'] as $step) {
            $step['rank'] = $rank;
            if (array_key_exists('patient_id', $step) && $step['patient_id'] != null) {
                $patient = Patient::findOrFail($step['patient_id']);
                $step['user_id'] = $patient->user->id;
            } else {
                $step['user_id'] = auth()->id();
            }
            $OrderStep = $order->orderSteps()->create($step);
            if ($rank == 1) {
                $fields['current_step_id'] = $OrderStep->id;
            }
            $rank++;
        }
        $order->update($fields);
        $order->load(['details', 'details.teeth', 'orderSteps', 'account', 'office', 'account.lab', 'doctor']);
        return new LabOrderResource($order);
    }

    public function rejectOrderFromDoctor(Request $request, LabOrder $order)
    {
        $this->authorize('acceptFromDoctor', [$order]);
        $order->update(['status' => LabOrderStatus::Rejected]);
        if ($order->doctor->dental_lab_id == null) {
            $status = 'Rejected';
            if ($request->has('massage')) {
                $massage = $request->massage;
            } else {
                $massage = 'this order was rejected';
            }
            $order->doctor->notify(new OrderStatus($order, $status, $massage));
        }
    }

    public function updateOrderStatus(UpdateLabOrderStatusRequest $request, LabOrder $order)
    {
        $fields = $request->validated();
        $this->authorize('acceptFromDoctor', [$order]);
        $fields['status'] = LabOrderStatus::getValue($request->status);
        $order->updated($fields);
        $order->load(['details', 'details.teeth']);
        if ($order->doctor->dental_lab_id == null) {
            $status = $request->status;
            if ($request->has('massage')) {
                $massage = $request->massage;
            } else {
                $massage = 'this order was ' . $request->status;
            }
            $order->doctor->notify(new OrderStatus($order, $status, $massage));
        }
        return new LabOrderResource($order);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
