<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ResetCodeMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;

class PasswordResetController extends Controller
{
    /**
     * Send a 6-digit password reset code via email.
     *
     * POST /api/password/forgot
     *
     * Rate limit: 3 requests per hour per email
     */
    public function forgot(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = strtolower(trim($request->email));

        // Rate limit: max 3 per hour per email
        $rateLimitKey = 'password-forgot:' . $email;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'success' => false,
                'message' => 'Too many reset requests. Please try again in ' . ceil($seconds / 60) . ' minutes.',
            ], 429);
        }

        RateLimiter::hit($rateLimitKey, 3600); // 1 hour decay

        // Check if user exists (only mobile users)
        $user = User::where('email', $email)->where('role', 'user')->first();

        if (!$user) {
            // Return success even if user doesn't exist (prevents email enumeration)
            return response()->json([
                'success' => true,
                'message' => 'If an account with that email exists, a reset code has been sent.',
            ]);
        }

        // Generate 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store code in password_reset_tokens table (delete any existing entry first)
        DB::table('password_reset_tokens')->where('email', $email)->delete();
        DB::table('password_reset_tokens')->insert([
            'email'      => $email,
            'token'      => Hash::make($code),
            'created_at' => now(),
        ]);

        // Send email with code
        try {
            Mail::to($email)->send(new ResetCodeMail($code));
            Log::info('Password reset code sent', ['email' => $email]);
        } catch (\Exception $e) {
            Log::error('Failed to send password reset code', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send reset code. Please try again later.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'If an account with that email exists, a reset code has been sent.',
        ]);
    }

    /**
     * Reset user's password using the 6-digit code.
     *
     * POST /api/password/reset
     *
     * Rate limit: 5 attempts per hour per email
     */
    public function reset(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'code'     => 'required|string|size:6',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[a-z]/',      // at least one lowercase letter
                'regex:/[A-Z]/',      // at least one uppercase letter
                'regex:/[0-9]/',      // at least one number
            ],
        ], [
            'password.regex' => 'Password must contain at least one lowercase letter, one uppercase letter, and one number.',
        ]);

        $email = strtolower(trim($request->email));

        // Rate limit: max 5 per hour per email
        $rateLimitKey = 'password-reset:' . $email;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'success' => false,
                'message' => 'Too many reset attempts. Please try again in ' . ceil($seconds / 60) . ' minutes.',
            ], 429);
        }

        RateLimiter::hit($rateLimitKey, 3600); // 1 hour decay

        // Look up the reset record
        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired reset code.',
            ], 422);
        }

        // Check 15-minute expiry
        $createdAt = \Carbon\Carbon::parse($record->created_at);
        if ($createdAt->addMinutes(15)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            return response()->json([
                'success' => false,
                'message' => 'Reset code has expired. Please request a new one.',
            ], 422);
        }

        // Verify code against hashed value
        if (!Hash::check($request->code, $record->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired reset code.',
            ], 422);
        }

        // Find the user
        $user = User::where('email', $email)->where('role', 'user')->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        // Update password
        $user->forceFill([
            'password' => Hash::make($request->password),
        ])->save();

        // Delete the used code
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        // Clear rate limiters on success
        RateLimiter::clear($rateLimitKey);
        RateLimiter::clear('password-forgot:' . $email);

        Log::info('Password reset successful', ['email' => $email]);

        return response()->json([
            'success' => true,
            'message' => 'Password has been reset successfully. You can now log in with your new password.',
        ]);
    }
}
