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
            'role'     => 'customer', // default role
        ]);

        $token = JWTAuth::fromUser($user);

        return jsonResponse('User registered successfully', true, [
            'user'  => new UserResource($user),
            'token' => $token
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $token = JWTAuth::attempt($credentials);

        if (!$token) {
            return jsonResponse('Invalid credentials', false, [], 401);
        }

        return jsonResponse('Login successful', true, [
            'user'  => new UserResource(JWTAuth::user()),
            'token' => $token
        ]);
    }

    public function refresh()
    {
        try {
            $token = JWTAuth::getToken();

            if (!$token) {
                return jsonResponse('Token not provided', false, [], 401);
            }

            $newToken = JWTAuth::refresh($token);

            return jsonResponse('Token refreshed successfully', true, [
                'token' => $newToken
            ]);
        } catch (\Exception $e) {
            return jsonResponse($e->getMessage(), false, [], 401);
        }
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return jsonResponse('Successfully logged out', true);
    }

    public function me()
    {
        return jsonResponse('User data retrieved', true, [
            'user' => new UserResource(JWTAuth::user())
        ]);
    }
}
