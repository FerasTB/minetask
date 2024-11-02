<?php

namespace App\Http\Controllers\Api;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdminOfficeResource;
use App\Http\Resources\OfficeResource;
use App\Http\Resources\PatientResource;
use App\Http\Resources\UserResource;
use App\Models\ModelHasRole;
use App\Models\Office;
use App\Models\Patient;
use App\Models\Role as ModelsRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
        return AdminOfficeResource::collection($offices);
    }

    public function storePatient(Request $request)
    {
        // Validate request (you can create a specific Form Request if needed)
        $request->validate([
            'phone' => 'required|unique:users,phone',
            'password' => 'required|min:6',
            // Add other necessary patient fields
        ]);

        // Create User
        $user = User::create([
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => Role::Patient,
        ]);

        // Assign Role
        $role = ModelHasRole::create([
            'role_id' => ModelsRole::Patient,
            'roleable_id' => auth()->id(),
            'roleable_type' => 'App\Models\User',
        ]);
        // Create Patient Profile
        $patient = Patient::create([
            'user_id' => $user->id,
            // Add other patient-specific fields from $request
        ]);

        // Optionally, assign default info
        $user->info()->create([
            'country' => 'Syria',
            'numberPrefix' => '+963',
        ]);

        // Create Token
        $token = $user->createToken("medcare_app")->plainTextToken;

        return response()->json([
            'status' => 'success',
            'user' => new UserResource($user->load(['patient', 'currentRole'])),
            'token' => $token,
        ], 201);
    }
}
