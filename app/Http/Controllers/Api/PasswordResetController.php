<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * @group Auth
     *
     * Forgot Password
     *
     * Sends a password reset link to the user's email.
     *
     * @unauthenticated
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $validated = $request->validated();

        // Rate limit: 1 attempt per minute per email
        $key = 'forgot-password:' . $validated['email'];

        if (RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = RateLimiter::availableIn($key);
            return $this->error("Please wait {$seconds} seconds before requesting another reset link.", 429);
        }

        RateLimiter::hit($key, 60); // 60 seconds decay

        // Generate a random token
        $token = Str::random(64);

        // Store the token in the database
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $validated['email']],   // find by this
            [                                // update or insert this
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // Send email with the plain token
        $user = User::where('email', $validated['email'])->first();
        $user->notify(new ResetPasswordNotification($token));

        return $this->success(null, 'Password reset link sent to your email');
    }

    /**
     * @group Auth
     *
     * Reset Password
     *
     * Resets the user password using the token from the email.
     *
     * @unauthenticated
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $validated = $request->validated();

        // Find token record
        $record = DB::table('password_reset_tokens')
            ->where('email', $validated['email'])
            ->first();

        // Check token exists
        if (!$record) {
            return $this->error('Invalid reset token', 400);
        }

        // Check token not expired
        if (Carbon::parse($record->created_at)->diffInMinutes(now()) > 60) {
            DB::table('password_reset_tokens')
                ->where('email', $validated['email'])
                ->delete();
            return $this->error('Reset token has expired', 400);
        }

        // Verify token
        if (!Hash::check($validated['token'], $record->token)) {
            return $this->error('Invalid reset token', 400);
        }

        // Update password
        User::where('email', $validated['email'])->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Delete token
        DB::table('password_reset_tokens')
            ->where('email', $validated['email'])
            ->delete();

        return $this->success(null, 'Password reset successfully');
    }
}