<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'grade_level' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'grade_level' => $request->grade_level,
            'status' => User::STATUS_PENDING, // kailangan pa rin ng admin approval
        ]);

        // Assign student role
        $studentRole = Role::findOrCreate('student');
        $user->assignRole($studentRole);

        // Send email verification link
        // $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'User registered successfully. Please check your email to verify your account. Waiting for admin approval.',
            'user' => $user
        ], 201);
    }
}
