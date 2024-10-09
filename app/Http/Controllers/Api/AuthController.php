<?php

namespace App\Http\Controllers\Api;

use App\Enums\Role;
use App\Filament\Resources\DoctorResource;
use App\Http\Controllers\Controller;
use App\Http\Controllers\LanguagesController;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Resources\DoctorResource as ResourcesDoctorResource;
use App\Http\Resources\PatientResource;
use App\Http\Resources\UserResource;
use App\Models\Language;
use App\Models\ModelHasRole;
use App\Models\Patient;
use App\Models\Role as ModelsRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $request->validated();

        $user = User::where('phone', $request->phone)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'phone' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken("medcare_app")->plainTextToken;
        return response()->json([
            'status' => 'alright',
            'user' => new UserResource($user),
            'patient' => $user->patient ? new PatientResource($user->patient) : null,
            'doctor' => $user->doctor ? new ResourcesDoctorResource($user->doctor) : null,
            'token' => $token,
            'completed' => $user->patient != null || $user->doctor != null,
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $request->validated();
        $user = auth()->user();
        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'error' => ['The provided credentials are incorrect.'],
            ]);
        }
        $user->update([
            'password' => Hash::make($request->newPassword)
        ]);
        return response()->json([
            'status' => 'password changed',
        ]);
    }

    public function register(RegisterRequest $request)
    {
        $request->validated();
        if ($request->role) {
            $user = User::create([
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => Role::getValue($request->role),
            ]);
            $role = ModelHasRole::create([
                'role_id' => ModelsRole::Patient,
                'roleable_id' => auth()->id(),
                'roleable_type' => 'App\Models\User',
            ]);
            if (Role::getValue($request->role) == Role::Patient) {
                $patient = Patient::where('phone', $request->phone)->first();
                if ($patient) {
                    $patient->update([
                        'user_id' => $user->id,
                    ]);
                }
            }
        } else {
            $user = User::create([
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
            ]);
            $role = ModelHasRole::create([
                'role_id' => ModelsRole::Patient,
                'roleable_id' => auth()->id(),
                'roleable_type' => 'App\Models\User',
            ]);

            // assaign patient role to user
            $patientRole = Role::where('name', 'Patient')->first();

            abort_unless(!auth()->user()->hasRole($patientRole), 404);

            $role = ModelHasRole::create([
                'role_id' => $patientRole->id,
                'roleable_id' => auth()->id(),
                'roleable_type' => 'App\Models\User',
            ]);
            $user->update(['current_role_id' => $patientRole->id]);
        }
        $token = $user->createToken("medcare_app")->plainTextToken;
        $user = User::find($user->id);
        $info = $user->info()->create([
            'country' => 'Syria',
            'numberPrefix' => '+963',
        ]);
        $ArabicLanguage = Language::findOrFail(1);
        $EnglishLanguage = Language::findOrFail(2);
        LanguagesController::assertLanguage($ArabicLanguage, $user);
        LanguagesController::assertLanguage($EnglishLanguage, $user);
        return response()->json([
            'status' => 'alright',
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }
    public function logout(Request $request)
    {
        $user = User::where('phone', $request->phone)->first();
        if ($user) {
            $user->tokens()->delete();
        }
        return response()->noContent();
    }
}
