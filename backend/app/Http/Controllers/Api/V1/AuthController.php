<?php

// app/Http/Controllers/Api/V1/AuthController.php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'role' => 'customer',
            ]);

            // Create cart for new user
            Cart::create(['user_id' => $user->id]);

            // Generate JWT token
            $token = JWTAuth::fromUser($user);

            DB::commit();

            return response()->json([
                'message' => 'Registration successful',
                'user' => new UserResource($user),
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Registration failed',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        try {
            // Check if user exists and is active
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                throw ValidationException::withMessages([
                    'email' => ['Email tidak terdaftar.']
                ]);
            }

            if (!$user->is_active) {
                throw ValidationException::withMessages([
                    'email' => ['Akun Anda telah dinonaktifkan. Silakan hubungi customer service.']
                ]);
            }

            // Attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt($credentials)) {
                throw ValidationException::withMessages([
                    'email' => ['Email atau password tidak valid.']
                ]);
            }

            // Update last login
            $user->update(['last_login_at' => now()]);

            return response()->json([
                'message' => 'Login successful',
                'user' => new UserResource($user),
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Login failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Login failed',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get authenticated user
     */
    public function me(): JsonResponse
    {
        try {
            $user = Auth::user();

            return response()->json([
                'user' => new UserResource($user->load(['addresses', 'cart.items.product']))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch user data',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Refresh JWT token
     */
    public function refresh(): JsonResponse
    {
        try {
            $token = JWTAuth::refresh();

            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Token refresh failed',
                'error' => 'Invalid token'
            ], 401);
        }
    }

    /**
     * Logout user
     */
    public function logout(): JsonResponse
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'message' => 'Successfully logged out'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Logout failed',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
        ]);

        try {
            $user = Auth::user();
            $user->update($request->only(['name', 'phone']));

            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => new UserResource($user)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Profile update failed',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Change password
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();

            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => ['Password saat ini tidak benar.']
                ]);
            }

            // Update password
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return response()->json([
                'message' => 'Password berhasil diubah'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Password change failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Password change failed',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Send password reset link
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        try {
            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status === Password::RESET_LINK_SENT) {
                return response()->json([
                    'message' => 'Link reset password telah dikirim ke email Anda.'
                ]);
            }

            return response()->json([
                'message' => 'Gagal mengirim link reset password.',
                'error' => __($status)
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send reset link',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Reset password using token
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        try {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ])->save();
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return response()->json([
                    'message' => 'Password berhasil direset.'
                ]);
            }

            return response()->json([
                'message' => 'Reset password gagal.',
                'error' => __($status)
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Password reset failed',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Verify email
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|integer',
            'hash' => 'required|string',
        ]);

        try {
            $user = User::findOrFail($request->id);

            if (!hash_equals((string) $request->hash, sha1($user->email))) {
                return response()->json([
                    'message' => 'Invalid verification link'
                ], 400);
            }

            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'message' => 'Email already verified'
                ]);
            }

            $user->markEmailAsVerified();

            return response()->json([
                'message' => 'Email verified successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Email verification failed',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Resend email verification
     */
    public function resendVerification(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        try {
            $user = User::where('email', $request->email)->first();

            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'message' => 'Email already verified'
                ]);
            }

            $user->sendEmailVerificationNotification();

            return response()->json([
                'message' => 'Verification email sent'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send verification email',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
