<?php

namespace App\Http\Controllers\Api\Admin\V1\Auth;

use App\Core\Audit\AuditRecorder;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\V1\Auth\RequestPasswordResetLinkRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;

class PasswordResetLinkController extends Controller
{
    public function __invoke(RequestPasswordResetLinkRequest $request, AuditRecorder $audit): JsonResponse
    {
        $email = mb_strtolower($request->string('email')->trim()->toString());
        $user = User::query()->businessVisible()->where('email', $email)->first();

        Password::sendResetLink([
            'email' => $user->email ?? 'blocked-password-reset@instance.invalid',
        ]);

        if ($user !== null) {
            $audit->record(
                action: 'auth.password_reset_requested',
                subject: $user,
                businessId: $user->business_id,
            );
        }

        return response()->json([
            'data' => [
                'message' => 'If the account is eligible, a password reset link has been sent.',
            ],
        ]);
    }
}
