<?php

namespace App\Http\Controllers\Api\DentalLab;

use App\Enums\AccountingProfileType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDentalLabDoctorAccountingProfileRequest;
use App\Http\Requests\StoreDentalLabNotExistDoctorAccountingProfileRequest;
use App\Http\Requests\StoreDentalLabSupplierAccountingProfileRequest;
use App\Http\Requests\StoreSupplierAccountingProfileRequest;
use App\Http\Resources\AccountingProfileResource;
use App\Http\Resources\DentalLabAccountingProfileResource;
use App\Models\DentalLab;
use App\Models\Doctor;
use App\Models\Office;
use Illuminate\Http\Request;

class AccountingProfileController extends Controller
{
    public function storeSupplier(StoreDentalLabSupplierAccountingProfileRequest $request)
    {
        $fields = $request->validated();
        $fields['type'] = AccountingProfileType::getValue($request->type);
        $lab = DentalLab::find($request->lab_id);
        $this->authorize('createForLab', [AccountingProfile::class, $lab]);
        $profile = $lab->accountingProfiles()->create($fields);
        return new AccountingProfileResource($profile);
    }

    public function StoreAccountProfileForDoctor(StoreDentalLabDoctorAccountingProfileRequest $request, DentalLab $lab, Doctor $doctor)
    {
        $fields = $request->validated();
        $office = Office::findOrFail($request->office_id);
        $this->authorize('createDoctorAccountForLab', [AccountingProfile::class, $lab, $doctor, $office]);
        $fields['dental_lab_id'] = $lab->id;
        $fields['doctor_id'] = $doctor->id;
        $fields['type'] = AccountingProfileType::getValue($request->type);
        abort_unless(!$lab->hasDoctorAccount($doctor, $office), 403);
        $account = $lab->accountingProfiles()->create($fields);
        $account->load(['doctor', 'office', 'lab']);
        return new DentalLabAccountingProfileResource($account);
    }

    public function StoreAccountProfileForNotExistDoctor(StoreDentalLabNotExistDoctorAccountingProfileRequest $request, DentalLab $lab, Doctor $doctor)
    {
        $fields = $request->validated();
        $this->authorize('createNotExistDoctorAccountForLab', [AccountingProfile::class, $lab, $doctor]);
        $fields['dental_lab_id'] = $lab->id;
        $fields['doctor_id'] = $doctor->id;
        $fields['type'] = AccountingProfileType::getValue($request->type);
        abort_unless(!$lab->hasNotExistDoctorAccount($doctor), 403);
        $account = $lab->accountingProfiles()->create($fields);
        $account->load(['doctor', 'lab']);
        return new DentalLabAccountingProfileResource($account);
    }
}
