<?php

namespace App\Http\Controllers\Api;

use App\Enums\AccountingProfileType;
use App\Enums\COAType;
use App\Enums\OfficeType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCoaAccountingProfileRequest;
use App\Http\Requests\StoreSupplierAccountingProfileRequest;
use App\Http\Resources\AccountingProfileResource;
use App\Models\AccountingProfile;
use App\Models\Doctor;
use App\Models\HasRole;
use App\Models\Office;
use App\Models\User;
use Illuminate\Http\Request;

class AccountingProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $office = Office::findOrFail($request->office);
        $this->authorize('inOffice', [AccountingProfile::class, $office]);
        if ($office->type == OfficeType::Separate) {
            $doctor = auth()->user()->doctor;
            // return $doctor->accountingProfiles;
            return AccountingProfileResource::collection($doctor->accountingProfiles);
        } else {
            return AccountingProfileResource::collection($office->accountingProfiles);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(AccountingProfile $accounting)
    {
        return new AccountingProfileResource($accounting);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AccountingProfile $accountingProfile)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AccountingProfile $accountingProfile)
    {
        //
    }

    public function storeSupplier(StoreSupplierAccountingProfileRequest $request)
    {
        $fields = $request->validated();
        $fields['type'] = AccountingProfileType::getValue($request->type);
        if ($request->doctor_id) {
            $doctor = Doctor::find($request->doctor_id);
            $this->authorize('createForDoctor', [AccountingProfile::class, $doctor]);
            $profile = $doctor->accountingProfiles()->create($fields);
            return new AccountingProfileResource($profile);
        }
        $office = Office::find($request->office_id);
        $this->authorize('createForOffice', [AccountingProfile::class, $office]);
        $profile = $office->accountingProfiles()->create($fields);
        return new AccountingProfileResource($profile);
    }

    // public function storeCoa(StoreCoaAccountingProfileRequest $request)
    // {
    //     $fields = $request->validated();
    //     if ($request->doctor_id) {
    //         $doctor = Doctor::find($request->doctor_id);
    //         $this->authorize('createForDoctor', [AccountingProfile::class, $doctor]);
    //         $profile = $doctor->accountingProfiles()->create($fields);
    //         return $profile;
    //     }
    //     $office = Office::find($request->office_id);
    //     $this->authorize('createForOffice', [AccountingProfile::class, $office]);
    //     $profile = $office->accountingProfiles()->create($fields);
    //     return $profile;
    // }

    public function patientProfile(Request $request)
    {
        $office = Office::findOrFail($request->office);
        $this->authorize('inOffice', [AccountingProfile::class, $office]);
        if ($office->type == OfficeType::Separate) {
            $doctor = auth()->user()->doctor;
            // return $doctor->accountingProfiles;
            $accounts = AccountingProfile::where(['doctor_id' => auth()->user()->doctor->id, 'office_id' => $office->id, 'type' => AccountingProfileType::PatientAccount])->get();
            // return AccountingProfileResource::collection($doctor->accountingProfiles)->where(['office_id' => $office->id, 'type' => AccountingProfileType::PatientAccount]);
            return AccountingProfileResource::collection($accounts);
        } else {
            $ownerUser = User::find($office->owner->user_id);
            $ownerDoctor = $ownerUser->doctor;
            $accounts = AccountingProfile::where(['doctor_id' => $ownerDoctor->id, 'office_id' => $office->id, 'type' => AccountingProfileType::PatientAccount])->get();
            // return AccountingProfileResource::collection($office->accountingProfiles)->where('type', AccountingProfileType::PatientAccount);
            return AccountingProfileResource::collection($accounts);
        }
    }

    public function supplierProfile(Request $request)
    {
        $office = Office::findOrFail($request->office);
        $this->authorize('inOffice', [AccountingProfile::class, $office]);
        if ($office->type == OfficeType::Separate) {
            $doctor = auth()->user()->doctor;
            return AccountingProfileResource::collection($doctor->accountingProfiles)->where('type', AccountingProfileType::SupplierAccount);
        } else {
            return AccountingProfileResource::collection($office->accountingProfiles)->where('type', AccountingProfileType::SupplierAccount);
        }
    }

    public function expensesProfile(Request $request)
    {
        $office = Office::findOrFail($request->office);
        $this->authorize('inOffice', [AccountingProfile::class, $office]);
        if ($office->type == OfficeType::Separate) {
            $doctor = auth()->user()->doctor;
            return AccountingProfileResource::collection($doctor->accountingProfiles)->where('type', AccountingProfileType::ExpensesAccount);
        } else {
            return AccountingProfileResource::collection($office->accountingProfiles)->where('type', AccountingProfileType::ExpensesAccount);
        }
    }
}
