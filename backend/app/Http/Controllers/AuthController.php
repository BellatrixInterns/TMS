<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $this->validate422($request, [
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username is required.',
            'password.required' => 'Password is required.',
        ]);

        $user = User::where('username', $data['username'])->first();
        if (!$user || !Hash::check($data['password'], $user->password_hash)) {
            return response()->json(['success' => false, 'message' => 'Invalid username or password.'], 401);
        }

        return response()->json([
            'success' => true,
            'token'   => $user->createToken('spa')->plainTextToken,
            'user'    => ['id' => $user->id, 'full_name' => $user->full_name],
        ]);
    }

    public function me(Request $request)
    {
        $u = $request->user();
        return response()->json(['success' => true, 'user' => ['id' => $u->id, 'full_name' => $u->full_name]]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true]);
    }
}