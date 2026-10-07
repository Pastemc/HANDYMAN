<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Handyman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:client,handyman',
            // Additional fields for handyman
            'bio' => 'required_if:role,handyman|nullable|string',
            'hourly_rate' => 'required_if:role,handyman|nullable|numeric|min:0',
            'years_experience' => 'required_if:role,handyman|nullable|integer|min:0',
            'certification' => 'nullable|string',
            'skills' => 'nullable|array',
            'service_categories' => 'required_if:role,handyman|nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'status' => 'active',
            ]);

            // Assign role
            $role = Role::where('name', $request->role)->first();
            if ($role) {
                $user->roles()->attach($role->id);
            }

            // If handyman, create profile
            if ($request->role === 'handyman') {
                $handyman = Handyman::create([
                    'user_id' => $user->id,
                    'bio' => $request->bio,
                    'hourly_rate' => $request->hourly_rate,
                    'years_experience' => $request->years_experience,
                    'certification' => $request->certification,
                    'skills' => $request->skills ?? [],
                    'rating' => 0,
                    'total_jobs' => 0,
                    'approval_status' => 'pending',
                ]);

                // Associate service categories
                if ($request->service_categories) {
                    $handyman->serviceCategories()->sync($request->service_categories);
                }
            }

            // Generate token
            $token = $user->createToken('auth_token')->plainTextToken;

            // Load relationships
            $user->load('roles', 'handyman.serviceCategories');

            return response()->json([
                'message' => 'User registered successfully',
                'user' => $user,
                'token' => $token,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error registering user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect'
            ], 401);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Your account is inactive. Please contact the administrator.'
            ], 403);
        }

        // Revoke previous tokens
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Load relationships
        $user->load('roles', 'handyman.serviceCategories');

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        try {
            // Delete current access token
            $request->user()->currentAccessToken()->delete();
            
            return response()->json([
                'message' => 'Logged out successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error logging out',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current authenticated user
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $user->load('roles', 'handyman.serviceCategories');

        return response()->json([
            'user' => $user
        ], 200);
    }
}