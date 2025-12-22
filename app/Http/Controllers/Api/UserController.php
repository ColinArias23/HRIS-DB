<?php

namespace App\Http\Controllers\Api;

use App\Events\UserActivated;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Get pending users (HR only)
     * Frontend expects: { count: int, users: array }
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

        // Frontend expects this exact format
        return response()->json([
            'count' => $users->count(),
            'users' => $users
        ]);
    }

    /**
     * Activate user (HR only)
     * Frontend expects: { message: string, user: object }
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

        // Frontend expects this exact format
        return response()->json([
            'message' => 'User activated successfully',
            'user' => $user
        ]);
    }

    public function deactivateUser(Request $request, $id)
    {
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

    public function pendingUsersCount(Request $request)
    {
        if (!$request->user()->isHR()) {
            return response()->json(['message' => 'Unauthorized. HR access only.'], 403);
        }

        $count = User::pending()->count();
        return response()->json(['count' => $count]);
    }

    public function index(Request $request)
    {
        if (!$request->user()->isHR()) {
            return response()->json(['message' => 'Unauthorized. HR access only.'], 403);
        }

        $users = User::with('employee')->orderBy('created_at', 'desc')->get();
        return response()->json($users);
    }
}