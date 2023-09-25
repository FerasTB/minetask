<?php

namespace App\Http\Controllers\Api\DentalLab;

use App\Enums\AccountingProfileType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDentalLabDoctorAccountingProfileRequest;
use App\Http\Resources\AccountingProfileResource;
use App\Http\Resources\DentalLabAccountingProfileResource;
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
            ->with('doctor', 'office', 'lab')->get();
        return DentalLabAccountingProfileResource::collection($accounts);
    }
}
