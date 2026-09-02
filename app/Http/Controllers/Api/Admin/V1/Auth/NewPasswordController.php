<?php

namespace App\Http\Controllers\Api\Admin\V1\Auth;

use App\Core\Audit\AuditRecorder;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\V1\Auth\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class NewPasswordController extends Controller
{
    public function __invoke(ResetPasswordRequest $request, AuditRecorder $audit): JsonResponse
    {
        $email = mb_strtolower($request->string('email')->trim()->toString());
        $eligibleUser = User::query()->businessVisible()->where('email', $email)->first();

        if ($eligibleUser === null) {
            throw ValidationException::withMessages([
                'email' => ['This password reset link is invalid or has expired.'],
            ]);
        }

        $status = Password::reset(
            $request->safe()->only(['email', 'password', 'password_confirmation', 'token']),
            function (User $user, string $password) use ($audit): void {
                abort_if($user->isGodfather(), 403);

                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();
                $user->tokens()->delete();

                $audit->record(
                    action: 'auth.password_reset_completed',
                    subject: $user,
                    businessId: $user->business_id,
                );
                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => ['This password reset link is invalid or has expired.'],
            ]);
        }

        return response()->json([
            'data' => [
                'message' => 'Your password has been reset. You may now sign in.',
            ],
        ]);
    }
}
