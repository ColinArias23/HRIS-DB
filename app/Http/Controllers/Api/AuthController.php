<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    if ($user->status !== 'Active') {
        return response()->json([
            'message' => 'Your account is not yet activated by HR.',
        ], 403);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'user' => $user,
        'token' => $token,
        'message' => 'Login successful'
    ]);
}


   public function register(Request $request)
{
    $validated = $request->validate([
        'firstName' => 'required|string|max:255',
        'lastName' => 'required|string|max:255',
        'middleName' => 'nullable|string|max:255',
        'suffix' => 'nullable|string|max:10',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6|confirmed',
    ]);

   $user = User::create([
    'name' => $validated['firstName'] . ' ' . $validated['lastName'],
    'firstName' => $validated['firstName'],
    'lastName' => $validated['lastName'],
    'middleName' => $validated['middleName'] ?? null,
    'suffix' => $validated['suffix'] ?? null,
    'email' => $validated['email'],
    'password' => bcrypt($validated['password']),
    'status' => 'Inactive',
]);

    return response()->json([
        'message' => 'Account created. Waiting for HR approval.',
        'user' => $user,
    ], 201);
}


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}