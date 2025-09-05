<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Check if user is approved
            if ($user->status !== 'approved') {
                Auth::logout();
                return response()->json([
                    'message' => 'Your account is pending approval. Please wait for admin approval.'
                ], 403);
            }

            // Check if email is verified
            // if (!$user->hasVerifiedEmail()) {
            //     Auth::logout();
            //     return response()->json([
            //         'message' => 'Please verify your email address before logging in.'
            //     ], 403);
            // }

            // Create token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'user' => $user->load('roles'),
                'token' => $token
            ]);
        }

        return response()->json([
            'message' => 'Invalid credentials'
        ], 401);
    }
}
