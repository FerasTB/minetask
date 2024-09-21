<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierItemRequest;
use App\Http\Requests\UpdateSupplierItemRequest;
use App\Http\Resources\SupplierItemResource;
use App\Models\AccountingProfile;
use App\Models\COA;
use App\Models\Doctor;
use App\Models\EmployeeSetting;
use App\Models\HasRole;
use App\Models\Office;
use App\Models\SupplierItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Office $office)
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
        $this->authorize('inOffice', [SupplierItem::class, $office]);
        return SupplierItemResource::collection(
            $doctor->supplierItem()
                ->where('office_id', $office->id)
                ->with(['doctor', 'office', 'COA'])
                ->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSupplierItemRequest $request, Office $office)
    {
        $fields = $request->validated();
        $fields['office_id'] = $office->id;
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
        // $fields['doctor_id'] = $doctor->id; 
        if ($request->doctor) {
            // $this->authorize('createForDoctor', [SupplierItem::class, $doctor]);
            $supplierItem = $doctor->supplierItem()->create($fields);
            return new SupplierItemResource($supplierItem);
        }
        $this->authorize('createForOffice', [SupplierItem::class, $office]);
        $supplierItem = $office->supplierItem()->create($fields);
        return new SupplierItemResource($supplierItem);
    }

    /**
     * Display the specified resource.
     */
    public function show(SupplierItem $supplierItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSupplierItemRequest $request, Office $office, SupplierItem $item)
    {
        $fields = $request->validated();
        if ($request->doctor_id) {
            $doctor = auth()->user()->doctor;
            $this->authorize('updateForDoctor', [$item, $doctor]);
            $item->update($fields);
            return new SupplierItemResource($item);
        }
        $this->authorize('updateForOffice', [$item, $office]);
        $item->update($fields);
        return new SupplierItemResource($item);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SupplierItem $supplierItem)
    {
        //
    }
}
