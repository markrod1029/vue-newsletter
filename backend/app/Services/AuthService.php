<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthService
{
    public function attemptLogin(array $credentials): array
    {
        if (!Auth::attempt($credentials)) {
            return [
                'success' => false,
                'message' => 'Invalid credentials',
                'status' => 401
            ];
        }

        /** @var User $user */
        $user = Auth::user();

        // Check approval
        if ($user->status !== 'approved') {
            Auth::logout();
            return [
                'success' => false,
                'message' => 'Your account is pending approval. Please wait for admin approval.',
                'status' => 403
            ];
        }

        // Optional: check email verification
        // if (!$user->hasVerifiedEmail()) {
        //     Auth::logout();
        //     return [
        //         'success' => false,
        //         'message' => 'Please verify your email address before logging in.',
        //         'status' => 403
        //     ];
        // }

        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'success' => true,
            'message' => 'Login successful',
            'status' => 200,
            'user' => $user->load('roles'),
            'token' => $token,
        ];
    }
}
