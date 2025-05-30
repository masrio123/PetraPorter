<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Tenant;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }

        $userData = $user->toArray();
        $userData['tenant_id'] = null;
        $userData['role'] =  $user->getRoleNames()->first();

        if($user->hasRole("tenant")){
            $tenant = Tenant::where('user_id', $user->id)->first();
            if ($tenant) {
                $userData['tenant_id'] = $tenant->id;
            }
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        unset($userData['email_verified_at']);
        unset($userData['created_at']);
        unset($userData['updated_at']);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $userData
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }
}
