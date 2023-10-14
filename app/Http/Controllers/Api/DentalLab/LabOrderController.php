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
        $order->load(['details', 'details.teeth']);
        return new LabOrderResource($order);
    }

    public function acceptOrderFromDoctor(AcceptLabOrderFromDoctorRequest $request, LabOrder $order)
    {
        $fields = $request->validated();
        $this->authorize('acceptFromDoctor', [$order]);
        $fields['status'] = LabOrderStatus::Approved;
        $order->updated($fields);
        $order->load(['details', 'details.teeth']);
        return new LabOrderResource($order);
    }

    public function updateOrderStatus(UpdateLabOrderStatusRequest $request, LabOrder $order)
    {
        $fields = $request->validated();
        $this->authorize('acceptFromDoctor', [$order]);
        $fields['status'] = LabOrderStatus::getValue($request->status);
        $order->updated($fields);
        $order->load(['details', 'details.teeth']);
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
