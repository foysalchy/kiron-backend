<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\{RegisterRequest, UpdateProfileRequest, UpdatePasswordRequest};
use App\Helpers\{FileUploadHelper, LogHelper};
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\{Auth, Hash, DB};
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials'],
            ]);
        }

        $user = $request->user();

        // Delete old tokens (optional)
        $user->tokens()->delete();

        $token = $user->createToken('api-token')->plainTextToken;
        LogHelper::custom('login', 'user', $user->id, $user->company_id);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'token'   => $token,
            'user'    => $user,
        ]);
    }

  
    // Register
    
    public function register(RegisterRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();

            // Handle profile image upload
            if ($request->hasFile('profile')) {
                $data['profile'] = FileUploadHelper::uploadImage(
                    $request->file('profile'),
                    'users/profiles',
                    'public',
                    2048
                );
            }

            // Hash password
            $data['password'] = Hash::make($data['password']);

            // Create user
            $user = User::create($data);

            // Create token
            $token = $user->createToken('api-token')->plainTextToken;

            // Log registration
            LogHelper::created('user', $user->id, $user->company_id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'token'   => $token,
                'user'    => $user,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            // Delete uploaded profile if exists
            if (isset($data['profile'])) {
                FileUploadHelper::delete($data['profile']);
            }

            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get authenticated user profile
     */
    public function profile(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Profile retrieved successfully',
            'user' => $request->user()->load('company'),
        ]);
    }

    /**
     * Update personal information
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $user = $request->user();
            $data = $request->validated();

            // Handle profile image upload
            if ($request->hasFile('profile')) {
                $data['profile'] = FileUploadHelper::replace(
                    $request->file('profile'),
                    $user->profile,
                    'users/profiles'
                );
            }

            // Update user
            $user->update($data);

            // Log update
            LogHelper::updated('user', $user->id, $user->company_id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => $user->fresh(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            // Delete new uploaded profile if exists
            if (isset($data['profile'])) {
                FileUploadHelper::delete($data['profile']);
            }

            return response()->json([
                'success' => false,
                'message' => 'Profile update failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update password
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => ['Current password is incorrect'],
                ]);
            }

            $user->update([
                'password' => Hash::make($request->password),
            ]);

            // Delete all tokens (force re-login)
            $user->tokens()->delete();
            LogHelper::custom('password_changed', 'user', $user->id, $user->company_id);

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully. Please login again.',
            ]);

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Password update failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Logout
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        // Log logout action
        LogHelper::custom('logout', 'user', $user->id, $user->company_id);

        // Delete current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Logout from all devices
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $user = $request->user();

        // Log logout from all devices
        LogHelper::custom('logout_all', 'user', $user->id, $user->company_id);

        // Delete all tokens
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out from all devices successfully',
        ]);
    }

    /**
     * Delete account
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'password' => 'required|string',
            ]);

            $user = $request->user();

            // Verify password
            if (!Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'password' => ['Password is incorrect'],
                ]);
            }

            // Delete profile image
            FileUploadHelper::delete($user->profile);

            // Log deletion before deleting user
            LogHelper::deleted('user', $user->id, $user->company_id);

            // Delete all tokens
            $user->tokens()->delete();

            // Delete user
            $user->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Account deleted successfully',
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Account deletion failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}