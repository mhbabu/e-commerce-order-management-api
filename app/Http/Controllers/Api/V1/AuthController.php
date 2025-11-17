<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Http\Requests\Auth\StoreUserRequest;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
   public function register(StoreUserRequest $request)
     {
         $user = User::create([
             'name' => $request->name,
             'email' => $request->email,
             'password' => Hash::make($request->password),
             'role' => $request->role ?? 'customer',
         ]);

       $token = JWTAuth::fromUser($user);

       return jsonResponse('User registered successfully', true, ['user' => $user, 'token' => $token], 201);
   }

   public function login(LoginRequest $request)
     {
         $credentials = $request->only('email', 'password');

         if (!$token = JWTAuth::attempt($credentials)) {
             return jsonResponse('Unauthorized', false, null, 401);
         }

       return jsonResponse('Login successful', true, ['token' => $token, 'user' => JWTAuth::user()]);
   }

   public function refresh()
    {
        return jsonResponse('Token refreshed', true, ['token' => JWTAuth::refresh()]);
    }

   public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return jsonResponse('Successfully logged out', true);
    }

   public function me()
    {
        return jsonResponse('User data retrieved', true, JWTAuth::user());
    }
}
