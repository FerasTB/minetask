<?php

namespace App\Http\Controllers\Api;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
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
            'token' => $token,
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
        } else {
            $user = User::create([
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
            ]);
        }
        $token = $user->createToken("medcare_app")->plainTextToken;
        $user = User::find($user->id);
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
