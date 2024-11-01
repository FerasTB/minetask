<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OfficeResource;
use App\Http\Resources\PatientResource;
use App\Http\Resources\UserResource;
use App\Models\Office;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function getUsers()
    {
        $users = User::with(['patient', 'doctor', 'currentRole'])->get();
        return UserResource::collection($users);
    }

    public function getPatients()
    {
        $patients = Patient::with(['user', 'info'])->get();
        return PatientResource::collection($patients);
    }

    public function getOffices()
    {
        $offices = Office::with(['rooms', 'owner', 'owner.user', 'owner.user.doctor'])->get();
        return OfficeResource::collection($offices);
    }
}
