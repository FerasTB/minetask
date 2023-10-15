<?php

namespace App\Http\Controllers\Api\DentalLab;

use App\Enums\LabOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\LabOrderStep;
use App\Http\Requests\StoreLabOrderStepRequest;
use App\Http\Requests\UpdateLabOrderStepRequest;
use App\Http\Resources\LabOrderResource;
use App\Notifications\Orderstatus;

class LabOrderStepController extends Controller
{

    public function markStepAsFinished(LabOrderStep $step)
    {
        abort_unless($step->id == $step->order->current_step_id, 403);
        $step->update(['isFinished' => true]);
        $order = $step->order;
        $rank = $order->steps()->where('rank', $step->rank + 1)->first();
        if ($rank == null) {
            $order->update(['status' => LabOrderStatus::Finished]);
            if ($order->doctor->dental_lab_id == null) {
                $status = 'Finished';
                $order->doctor->notify(new OrderStatus($order, $status));
            }
            $order->load(['orderSteps']);
            return new LabOrderResource($order);
        }
        $order->update(['current_step_id' => $rank->id]);
        $order->load(['orderSteps']);
        return new LabOrderResource($order);
    }
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
    public function store(StoreLabOrderStepRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(LabOrderStep $labOrderStep)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLabOrderStepRequest $request, LabOrderStep $labOrderStep)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LabOrderStep $labOrderStep)
    {
        //
    }
}
