<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ChangeRoleRequest;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LOGIN API
    |--------------------------------------------------------------------------
    */

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        // User not found
        if (!$user) {

            return response()->json([
                'status'  => false,
                'message' => 'User not found'
            ], 404);
        }

        // Password check
        if (!Hash::check($request->password, $user->password)) {

            return response()->json([
                'status'  => false,
                'message' => 'Invalid password'
            ], 401);
        }

        // Inactive user
        if (!$user->status) {

            return response()->json([
                'status'  => false,
                'message' => 'User inactive'
            ], 403);
        }

        // Delete old tokens
        $user->tokens()->delete();

        // Create token
        $token = $user->createToken('loan_collection_token')->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'Login successful',
            'token'   => $token,
            'user'    => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'mobile' => $user->mobile,
                'role'   => $user->role,
            ]
        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | LOGOUT API
    |--------------------------------------------------------------------------
    */

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Logout successful'
        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | PROFILE API
    |--------------------------------------------------------------------------
    */

    public function profile(Request $request)
    {
        return response()->json([
            'status' => true,
            'user'   => $request->user()
        ], 200);
    }

    //register 

    public function register(RegisterRequest $request)
    {
        $user = User::create([

            'name' => $request->name,

            'email' => $request->email,

            'mobile' => $request->mobile,

            'password' => bcrypt($request->password),

            'role' => $request->role ?? 'field_agent',

            'status' => 1
        ]);

        // Assign spatie role
        $user->assignRole($request->role ?? 'field_agent');

        return response()->json([

            'status' => true,

            'message' => 'User registered successfully',

            'data' => $user

        ], 201);
    }

    // change role 

    public function changeRole(ChangeRoleRequest $request)
    {
        $user = User::find($request->user_id);

        if (!$user) {

            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Update role column
        $user->role = $request->role;

        $user->save();

        // Remove old roles
        $user->syncRoles([]);

        // Assign new role
        $user->assignRole($request->role);

        return response()->json([

            'status' => true,

            'message' => 'Role updated successfully',

            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role
            ]

        ], 200);
    }
}