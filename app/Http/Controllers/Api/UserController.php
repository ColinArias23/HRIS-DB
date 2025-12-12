<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function pendingUsers()
    {
        $users = User::where('status', 'Inactive')->get();
        return response()->json($users);
    }

    public function activateUser($id)
    {
        $user = User::findOrFail($id);
        $user->status = 'Active';
        $user->save();

        return response()->json([
            'message' => 'User activated successfully',
            'user' => $user
        ]);
    }
}
