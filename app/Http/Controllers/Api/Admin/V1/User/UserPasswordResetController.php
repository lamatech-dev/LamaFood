<?php

namespace App\Http\Controllers\Api\Admin\V1\User;

use App\Core\Audit\AuditRecorder;
use App\Core\Business\BusinessContextResolver;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class UserPasswordResetController extends Controller
{
    public function __invoke(Request $request, string $user, BusinessContextResolver $contexts, AuditRecorder $audit): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        $subject = User::query()
            ->businessVisible()
            ->whereBelongsTo($contexts->forUser($actor))
            ->whereKey($user)
            ->firstOrFail();

        if (! $subject->is_active) {
            throw new UnprocessableEntityHttpException('Activate this account before sending a password reset link.');
        }

        $status = Password::sendResetLink(['email' => $subject->email]);
        $audit->record('user.password_reset_initiated', $actor, $subject, $subject->business_id);

        return response()->json([
            'data' => [
                'message' => 'A password reset link was requested for this user.',
                'sent' => $status === Password::RESET_LINK_SENT,
            ],
        ]);
    }
}
