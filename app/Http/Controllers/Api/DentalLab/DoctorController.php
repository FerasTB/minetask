<?php

namespace App\Http\Controllers\Api\DentalLab;

use App\Enums\AccountingProfileType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDentalLabDoctorAccountingProfileRequest;
use App\Http\Requests\StoreDoctorForDentalLabRequest;
use App\Http\Resources\AccountingProfileResource;
use App\Http\Resources\DentalLabAccountingProfileResource;
use App\Http\Resources\DoctorResource;
use App\Models\DentalLab;
use App\Models\Doctor;
use App\Models\Office;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function allDoctor(DentalLab $lab)
    {
        $this->authorize('inLab', $lab);
        $accounts = $lab->accountingProfiles()
            ->where(['type' => AccountingProfileType::DentalLabDoctorAccount])
            ->with('doctor', 'office', 'lab', 'invoices', 'receipts')->get();
        return DentalLabAccountingProfileResource::collection($accounts);
    }

    public function labDoctor(DentalLab $lab)
    {
        $this->authorize('inLab', $lab);
        $accounts = $lab->doctors;
        return DoctorResource::collection($accounts);
    }

    public function storeDoctor(StoreDoctorForDentalLabRequest $request, DentalLab $lab)
    {
        $this->authorize('inLab', $lab);
        $fields = $request->validated();
        $fields['dental_lab_id'] = $lab->id;
        $doctor = $lab->doctors()->create($fields);
        return new DoctorResource($doctor);
    }
}
