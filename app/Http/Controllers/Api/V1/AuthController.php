<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Http\Requests\Auth\StoreUserRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\User\UserResource;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(StoreUserRequest $request)
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'customer',
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(['status' => true, 'mesage' => 'User registered successfully', 'user' => new UserResource($user), 'token' => $token], 201);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['status' => false, 'mesage' => 'Invalid credentials', 'user' => []], 401);
        }

        $user = JWTAuth::user();
        return response()->json(['status' => true, 'mesage' => 'User logged in successfully', 'user' => new UserResource($user), 'token' => $token], 200);
    }

    public function refresh()
    {
        try {
            $token = JWTAuth::getToken();
            if (!$token) return response()->json(['status' => false, 'mesage' => 'Token not provided'], 401);

            $newToken = JWTAuth::refresh($token);
            return response()->json(['status' => true, 'mesage' => 'Token refreshed successfully', 'token' => $newToken], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'mesage' => $e->getMessage()], 401);
        }
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['status' => true, 'mesage' => 'Logged out successfully'], 200);
    }

    public function me()
    {
        $user = JWTAuth::user();
        return response()->json(['status' => true, 'mesage' => 'User data retrieved', 'user' => new UserResource($user)], 200);
    }
}
