<?php

namespace App\Http\Controllers\Api;

use App\Events\UserActivated;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/users/pending",
     *     tags={"Users"},
     *     summary="Get pending users (HR only)",
     *     security={{"sanctum":{}}}
     * )
     */
    public function pendingUsers(Request $request)
    {
        // Check if user is HR
        if (!$request->user()->isHR()) {
            return response()->json(['message' => 'Unauthorized. HR access only.'], 403);
        }

        $users = User::pending()
                    ->orderBy('created_at', 'desc')
                    ->get();

        return response()->json([
            'count' => $users->count(),
            'users' => $users
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/users/pending/count",
     *     tags={"Users"},
     *     summary="Get pending users count (HR only)",
     *     security={{"sanctum":{}}}
     * )
     */
    public function pendingUsersCount(Request $request)
    {
        // Check if user is HR
        if (!$request->user()->isHR()) {
            return response()->json(['message' => 'Unauthorized. HR access only.'], 403);
        }

        $count = User::pending()->count();

        return response()->json(['count' => $count]);
    }

    /**
     * @OA\Post(
     *     path="/api/users/{id}/activate",
     *     tags={"Users"},
     *     summary="Activate user (HR only)",
     *     security={{"sanctum":{}}}
     * )
     */
    public function activateUser(Request $request, $id)
    {
        // Check if user is HR
        if (!$request->user()->isHR()) {
            return response()->json(['message' => 'Unauthorized. HR access only.'], 403);
        }

        $user = User::findOrFail($id);
        $user->status = 'Active';
        $user->save();

        // Broadcast real-time notification to user
        broadcast(new UserActivated($user))->toOthers();

        return response()->json([
            'message' => 'User activated successfully',
            'user' => $user
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/users/{id}/deactivate",
     *     tags={"Users"},
     *     summary="Deactivate user (HR only)",
     *     security={{"sanctum":{}}}
     * )
     */
    public function deactivateUser(Request $request, $id)
    {
        // Check if user is HR
        if (!$request->user()->isHR()) {
            return response()->json(['message' => 'Unauthorized. HR access only.'], 403);
        }

        $user = User::findOrFail($id);
        $user->status = 'Inactive';
        $user->save();

        return response()->json([
            'message' => 'User deactivated successfully',
            'user' => $user
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/users",
     *     tags={"Users"},
     *     summary="Get all users (HR only)",
     *     security={{"sanctum":{}}}
     * )
     */
    public function index(Request $request)
    {
        // Check if user is HR
        if (!$request->user()->isHR()) {
            return response()->json(['message' => 'Unauthorized. HR access only.'], 403);
        }

        $users = User::with('employee')->orderBy('created_at', 'desc')->get();

        return response()->json($users);
    }
}