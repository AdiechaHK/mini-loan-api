<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegistrationRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //
    public function register(RegistrationRequest $request) {
        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => bcrypt($request->password)
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;
        return response(compact('user', 'token'), 201);
    }

    public function login(LoginRequest $request) {

        // Verify if user exists with given email address
        $user = User::whereEmail($request->email)->first();
        if(!$user) {
            return response(["message" => "Sorry, No user found with given email address. Please check your email address or register as new user."], 404);
        }

        // Verify provided password is correct or not.
        if(!Hash::check($request->password, $user->password)) {
            return response(['message' => "Sorry, provided password is incorrect."], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        return response(compact('user', 'token'), 200);
    }

    public function logout() {
        auth()->user()->tokens()->delete();
        return response(['message' => "Logged out successfully."], 200);        
    }

    public function user() {
        return response(['user' => auth()->user()], 200);
    }

}
