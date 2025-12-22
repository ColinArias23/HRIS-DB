<?php

namespace App\Http\Controllers\Api;

use App\Events\UserRegistered;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Authentication"},
     *     summary="Register new user (pending approval)",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"firstName", "lastName", "email", "password"},
     *             @OA\Property(property="firstName", type="string", example="Juan"),
     *             @OA\Property(property="lastName", type="string", example="Dela Cruz"),
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Registration successful, pending approval")
     * )
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:255',
            'middleName' => 'nullable|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'firstName' => $request->firstName,
            'middleName' => $request->middleName,
            'lastName' => $request->lastName,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'employee',
            'status' => 'Inactive', // Pending approval
        ]);

        // Broadcast real-time notification to HR
        broadcast(new UserRegistered($user))->toOthers();

        return response()->json([
            'message' => 'Registration successful! Please wait for HR approval.',
            'user' => $user
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Authentication"},
     *     summary="Login user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Login successful"),
     *     @OA\Response(response=403, description="Account pending approval")
     * )
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Check if account is active
        if (!$user->isActive()) {
            return response()->json([
                'message' => 'Your account is pending approval. Please wait for HR to activate your account.',
                'status' => 'pending'
            ], 403);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        // Load employee relationship for avatar
        $user->load('employee');

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Authentication"},
     *     summary="Logout user",
     *     security={{"sanctum":{}}}
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout successful']);
    }

    /**
     * @OA\Get(
     *     path="/api/user",
     *     tags={"Authentication"},
     *     summary="Get authenticated user",
     *     security={{"sanctum":{}}}
     * )
     */
    public function user(Request $request)
    {
        $user = $request->user()->load('employee');
        return response()->json(['user' => $user]);
    }
}