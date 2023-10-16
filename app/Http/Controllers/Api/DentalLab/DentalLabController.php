<?php

namespace App\Http\Controllers\Api\DentalLab;

use App\Enums\COAGeneralType;
use App\Enums\COASubType;
use App\Enums\COAType;
use App\Enums\DentalLabType;
use App\Enums\Role as EnumsRole;
use App\Enums\SubRole;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Models\DentalLab;
use App\Http\Requests\StoreDentalLabRequest;
use App\Http\Requests\UpdateDentalLabRequest;
use App\Http\Resources\DentalLabResource;
use App\Http\Resources\DentalLabThroughHasRoleResource;
use App\Http\Resources\NotificationResource;
use App\Models\COA;
use App\Models\HasRole;
use App\Models\ModelHasRole;
use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class DentalLabController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $labs = HasRole::where(['user_id' => $user->id, 'roleable_type' => 'App\Models\DentalLab', 'sub_role' => SubRole::DentalLabOwner])->with('roleable')->get();
        if ($labs != []) {
            return DentalLabThroughHasRoleResource::collection($labs);
        } else {
            return response()->noContent();
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDentalLabRequest $request)
    {
        $this->authorize('create', DentalLab::class);
        $fields = $request->validated();
        if ($request->type) {
            $fields['type'] = DentalLabType::getValue($request->type);
        }
        $lab = DentalLab::create($fields);
        auth()->user()->roles()->create([
            'roleable_id' => $lab->id,
            'roleable_type' => 'App\Models\DentalLab',
            'sub_role' => SubRole::DentalLabOwner,
        ]);
        $lab->COAS()->create([
            'name' => COA::Receivable,
            'type' => COAType::Current,
            'general_type' => COAGeneralType::Asset,
            'sub_type' => COASubType::Receivable,
        ]);
        $lab->COAS()->create([
            'name' => COA::Cash,
            'type' => COAType::Current,
            'general_type' => COAGeneralType::Asset,
            'sub_type' => COASubType::Cash,
        ]);
        $lab->COAS()->create([
            'name' => COA::Payable,
            'type' => COAType::Current,
            'general_type' => COAGeneralType::Liability,
            'sub_type' => COASubType::Payable,
        ]);
        $lab->COAS()->create([
            'name' => COA::Capital,
            'type' => COAType::Capital,
            'general_type' => COAGeneralType::Equity,
        ]);
        $lab->COAS()->create([
            'name' => COA::OwnerWithDraw,
            'type' => COAType::OwnerWithdraw,
            'general_type' => COAGeneralType::Equity,
        ]);
        $lab->COAS()->create([
            'name' => COA::Inventory,
            'type' => COAType::Current,
            'general_type' => COAGeneralType::Asset,
            'sub_type' => COASubType::Inventory,
        ]);
        $lab->COAS()->create([
            'name' => COA::COGS,
            'type' => COAType::COGS,
            'general_type' => COAGeneralType::Expenses,
        ]);
        $lab->transactionPrefix()->create([
            'type' => TransactionType::PaymentVoucher,
            'prefix' => 'PVOC',
        ]);
        $lab->transactionPrefix()->create([
            'type' => TransactionType::SupplierInvoice,
            'prefix' => 'SINV',
        ]);
        $lab->transactionPrefix()->create([
            'type' => TransactionType::PatientInvoice,
            'prefix' => 'DINV',
        ]);
        $lab->transactionPrefix()->create([
            'type' => TransactionType::PatientReceipt,
            'prefix' => 'DREC',
        ]);
        // $doctor = auth()->user()->doctor;
        // $doctor->cases()->create([
        //     'case_name' => Doctor::DefaultCase,
        //     'office_id' => $office->id,
        // ]);
        return new DentalLabResource($lab);
    }

    /**
     * Display the specified resource.
     */
    public function show(DentalLab $dentalLab)
    {
        //
    }

    public function addEmployee(DentalLab $lab, Patient $patient)
    {
        $this->authorize('LabOwner', [$lab]);
        abort_unless($patient->user != null, 403);
        $user = $patient->user;
        $role = HasRole::where(['roleable_id' => $lab->id, 'roleable_type' => 'App\Models\DentalLab', 'user_id' => $user->id])->first();
        abort_unless($role == null, 403);
        $role = Role::findOfFail(Role::DentalLabTechnician);
        if (!$user->hasRole($role)) {
            $role = ModelHasRole::create([
                'role_id' => $role->id,
                'roleable_id' => $user->id,
                'roleable_type' => 'App\Models\User',
            ]);
        }
        $user->roles()->create([
            'roleable_id' => $lab->id,
            'roleable_type' => 'App\Models\DentalLab',
            'sub_role' => SubRole::DentalLabTechnician,
        ]);
        $user->update(['current_role_id' => $role->id]);
        return response('done', 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDentalLabRequest $request, DentalLab $dentalLab)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DentalLab $dentalLab)
    {
        //
    }

    public function addInventory(DentalLab $lab)
    {
        $lab->COAS()->create([
            'name' => COA::Inventory,
            'type' => COAType::Current,
            'general_type' => COAGeneralType::Asset,
            'sub_type' => COASubType::Inventory,
        ]);
        $lab->COAS()->create([
            'name' => COA::COGS,
            'type' => COAType::COGS,
            'general_type' => COAGeneralType::Expenses,
        ]);
    }

    public function unreadNotification(DentalLab $lab)
    {
        $this->authorize('inLab', $lab);
        return NotificationResource::collection($lab->unreadNotifications);
    }

    public function markAsRead(Request $request, DentalLab $lab)
    {
        $this->authorize('inLab', $lab);
        $lab->unreadNotifications
            ->when($request->id, function ($query) use ($request) {
                return $query->where('id', $request->id);
            })
            ->markAsRead();
        return response()->noContent();
    }

    public function allNotification(DentalLab $lab)
    {
        $this->authorize('inLab', $lab);
        return NotificationResource::collection($lab->notifications);
    }

    public function allUsers(DentalLab $lab)
    {
        $this->authorize('LabOwner', [$lab]);
        return $lab->allUsers;
    }
}
