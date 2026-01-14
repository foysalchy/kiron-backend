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
     * Login - Cookie based authentication
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Attempt authentication
        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials'],
            ]);
        }

        $user = Auth::user();

        // Check if user is active
        // if (!$user->isActive()) {
        //     Auth::logout();
        //     throw ValidationException::withMessages([
        //         'email' => ['Your account is inactive'],
        //     ]);
        // }

        // Regenerate session
        $request->session()->regenerate();

        // Log login action

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'company_id' => $user->company_id,
                'role' => $user->role,
                'profile' => $user->profile,
                'profile_url' => $user->profile_url,
            ],
        ]);
    }

    /**
     * Register
     */
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

            // Auto login
            Auth::login($user);

            // Log registration
            LogHelper::created('user', $user->id, $user->company_id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'company_id' => $user->company_id,
                    'role' => $user->role,
                ],
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
        $user = $request->user()->load('company');

        return response()->json([
            'success' => true,
            'message' => 'Profile retrieved successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'alternative_phone' => $user->alternative_phone,
                'profile' => $user->profile,
                'profile_url' => $user->profile_url,
                'company_id' => $user->company_id,
                'role' => $user->role,
                'status' => $user->status,
                'company' => $user->company,
                'is_super_admin' => $user->isSuperAdmin(),
            ],
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
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'profile_url' => $user->profile_url,
                ],
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

            // Update password
            $user->update([
                'password' => Hash::make($request->password),
            ]);

            // Log password change
            LogHelper::custom('password_changed', 'user', $user->id, $user->company_id);

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully',
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
     * Logout - Cookie based
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        // Log logout action
        if ($user) {
            LogHelper::custom('logout', 'user', $user->id, $user->company_id);
        }

        // Logout and invalidate session
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Check authentication status
     */
    public function check(Request $request): JsonResponse
    {
        if ($request->user()) {
            return response()->json([
                'authenticated' => true,
                'user' => [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'company_id' => $request->user()->company_id,
                    'role' => $request->user()->role,
                    'is_super_admin' => $request->user()->isSuperAdmin(),
                ],
            ]);
        }

        return response()->json([
            'authenticated' => false,
        ], 401);
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

            // Logout
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

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