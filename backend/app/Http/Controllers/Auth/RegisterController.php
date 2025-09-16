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
            'studentNo' => 'required|string|max:255|unique:users,studentID',
            'contact' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'hno' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'grade_level' => 'nullable|string|max:255',
        ], [
            'studentNo.unique' => 'This student number is already registered.',
            'email.unique' => 'This email address is already registered.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'studentID' => $request->studentNo,
                'contact' => $request->contact,
                'email' => $request->email,
                'hno' => $request->hno,
                'street' => $request->street,
                'city' => $request->city,
                'prov' => $request->province, // Note: database uses 'prov', request uses 'province'
                'grade_level' => $request->grade_level,
                'password' => Hash::make($request->password),
                'status' => User::STATUS_PENDING,
            ]);

            // Assign student role
            $studentRole = Role::findOrCreate('student');
            $user->assignRole($studentRole);

            // Send email verification link (uncomment if needed)
            // $user->sendEmailVerificationNotification();

            return response()->json([
                'success' => true,
                'message' => 'Registration successful. Please wait for admin approval.',
                'user' => $user
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
