<?php

namespace App\Http\Controllers\Api\Admin\V1\Auth;

use App\Core\Audit\AuditRecorder;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\V1\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'data' => [
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'business_id' => $user->business_id,
                'roles' => $user->getRoleNames()->values()->all(),
                'permissions' => $user->getAllPermissions()->pluck('name')->values()->all(),
            ],
        ]);
    }

    public function store(LoginRequest $request, AuditRecorder $audit): JsonResponse
    {
        $identifier = $request->string('identifier')->toString();
        $user = User::query()
            ->where('email', $identifier)
            ->orWhere('username', $identifier)
            ->first();

        if ($user === null || ! Hash::check($request->string('password')->toString(), $user->password)) {
            throw ValidationException::withMessages([
                'identifier' => [__('auth.failed')],
            ]);
        }

        $token = $user->createToken($request->string('device_name')->toString());

        $audit->record(
            action: 'auth.login',
            actor: $user,
            subject: $user,
            businessId: $user->business_id,
            context: ['ip' => $request->ip(), 'device_name' => $request->string('device_name')->toString()],
        );

        return response()->json([
            'data' => [
                'token' => $token->plainTextToken,
                'user' => [
                    'name' => $user->name,
                    'username' => $user->username,
                ],
            ],
        ]);
    }

    public function destroy(Request $request, AuditRecorder $audit): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $audit->record(
            action: 'auth.logout',
            actor: $user,
            subject: $user,
            businessId: $user->business_id,
            context: ['ip' => $request->ip()],
        );

        $user->currentAccessToken()->delete();

        return response()->json(status: 204);
    }
}
