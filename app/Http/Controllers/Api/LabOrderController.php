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
use App\Models\Doctor;
use App\Models\EmployeeSetting;
use App\Models\HasRole;
use App\Models\Office;
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
        $office = Office::findOrFail($request->office_id);
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
        $patient = Patient::findOrFail($request->patient_id);
        $role = HasRole::where(['roleable_id' => $patient->id, 'roleable_type' => 'App\Models\Patient', 'user_id' => $doctor->user->id])->first();
        abort_unless($role != null, 403);
        $this->authorize('storeForDoctor', [LabOrder::class, $profile, $doctor]);
        if ($role->sub_role == DoctorRoleForPatient::DoctorWithApprove) {
            $fields['patient_name'] = $patient->first_name . ' ' . $patient->last_name;
        } else {
            $temp = $patient->temporaries()->where('doctor_id', $doctor->id)->first();
            $fields['patient_name'] = $temp->first_name . ' ' . $temp->last_name;
        }
        if ($profile->lab->type == DentalLabType::Draft) {
            $fields['status'] = LabOrderStatus::Approved;
        } else {
            $fields['status'] = LabOrderStatus::Draft;
        }
        $order = $profile->labOrders()->create($fields);
        foreach ($fields['details'] as $detail) {
            $orderDetail = $order->details()->create($detail);
            foreach ($detail['teeth'] as $tooth) {
                $orderDetail->teeth()->create(['number_of_tooth' => $tooth]);
            }
        }
        if ($profile->lab->type == DentalLabType::Real) {
            $lab = $profile->lab;
            $type = 'NewOrder';
            $lab->notify(new OrderCreated($order, $type));
        }
        $order->load(['details', 'details.teeth']);
        // $order = LabOrder::find($order->id)->with(['details', 'details.teeth']);
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
