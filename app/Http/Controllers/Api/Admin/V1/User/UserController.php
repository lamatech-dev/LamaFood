<?php

namespace App\Http\Controllers\Api\Admin\V1\User;

use App\Core\Audit\AuditRecorder;
use App\Core\Authorization\FoundationRole;
use App\Core\Business\BusinessContextResolver;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\V1\User\StoreUserRequest;
use App\Http\Requests\Api\Admin\V1\User\UpdateUserRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;

class UserController extends Controller
{
    public function index(Request $request, BusinessContextResolver $contexts): JsonResponse
    {
        return response()->json([
            'data' => [
                'users' => $this->query($request, $contexts)->with('roles')->orderBy('name')->get()->map($this->serialize(...))->all(),
                'allowed_roles' => $this->allowedRoles(),
            ],
        ]);
    }

    public function store(StoreUserRequest $request, BusinessContextResolver $contexts, AuditRecorder $audit): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        $business = $contexts->forUser($actor);
        app(PermissionRegistrar::class)->setPermissionsTeamId($business->id);

        $user = DB::transaction(function () use ($request, $actor, $business, $audit): User {
            $user = User::query()->create([
                'business_id' => $business->id,
                'name' => $request->string('name')->trim()->toString(),
                'username' => $request->filled('username') ? $request->string('username')->trim()->toString() : null,
                'email' => mb_strtolower($request->string('email')->trim()->toString()),
                'password' => Str::random(64),
                'is_active' => $request->boolean('is_active', true),
            ]);
            $user->syncRoles($request->string('role')->toString());
            $audit->record('user.created', $actor, $user, $business->id, after: $this->serialize($user));

            return $user;
        });

        $resetStatus = null;
        if ($user->is_active) {
            $resetStatus = Password::sendResetLink(['email' => $user->email]);
            $audit->record('user.password_setup_requested', $actor, $user, $business->id);
        }

        return response()->json([
            'data' => [
                'user' => $this->serialize($user->load('roles')),
                'password_setup_sent' => $resetStatus === Password::RESET_LINK_SENT,
            ],
        ], 201);
    }

    public function show(Request $request, string $user, BusinessContextResolver $contexts): JsonResponse
    {
        return response()->json(['data' => $this->serialize($this->find($request, $contexts, $user)->load('roles'))]);
    }

    public function update(UpdateUserRequest $request, string $user, BusinessContextResolver $contexts, AuditRecorder $audit): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        $subject = $this->find($request, $contexts, $user)->load('roles');
        $role = $request->string('role')->toString();
        $isActive = $request->boolean('is_active');
        $currentRole = $subject->getRoleNames()->first();

        $this->guardAccessChange($request, $contexts, $actor, $subject, $currentRole, $role, $isActive);
        $before = $this->serialize($subject);

        DB::transaction(function () use ($request, $subject, $role, $isActive, $currentRole): void {
            $subject->update([
                'name' => $request->string('name')->trim()->toString(),
                'username' => $request->filled('username') ? $request->string('username')->trim()->toString() : null,
                'email' => mb_strtolower($request->string('email')->trim()->toString()),
                'is_active' => $isActive,
            ]);
            $subject->syncRoles($role);

            if (! $isActive || $role !== $currentRole) {
                $this->revokeSessions($subject);
            }
        });

        $subject = $subject->fresh('roles');
        $audit->record('user.updated', $actor, $subject, $subject->business_id, before: $before, after: $this->serialize($subject));

        return response()->json(['data' => $this->serialize($subject)]);
    }

    public function destroy(Request $request, string $user, BusinessContextResolver $contexts, AuditRecorder $audit): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        $subject = $this->find($request, $contexts, $user)->load('roles');
        $currentRole = $subject->getRoleNames()->first();
        $this->guardAccessChange($request, $contexts, $actor, $subject, $currentRole, (string) $currentRole, false);
        $before = $this->serialize($subject);

        DB::transaction(function () use ($subject): void {
            $subject->update(['is_active' => false]);
            $this->revokeSessions($subject);
        });

        $subject = $subject->fresh('roles');
        $audit->record('user.deactivated', $actor, $subject, $subject->business_id, before: $before, after: $this->serialize($subject));

        return response()->json(['data' => $this->serialize($subject)]);
    }

    /** @return Builder<User> */
    private function query(Request $request, BusinessContextResolver $contexts): Builder
    {
        /** @var User $actor */
        $actor = $request->user();
        $business = $contexts->forUser($actor);
        app(PermissionRegistrar::class)->setPermissionsTeamId($business->id);

        return User::query()
            ->businessVisible()
            ->whereDoesntHave('roles', static fn (Builder $query): Builder => $query->where('name', FoundationRole::LamatechSuperAdmin->value))
            ->whereBelongsTo($business);
    }

    private function find(Request $request, BusinessContextResolver $contexts, string $id): User
    {
        return $this->query($request, $contexts)->whereKey($id)->firstOrFail();
    }

    private function guardAccessChange(Request $request, BusinessContextResolver $contexts, User $actor, User $subject, ?string $currentRole, string $newRole, bool $isActive): void
    {
        if ($actor->is($subject) && ($currentRole !== $newRole || ! $isActive)) {
            throw ValidationException::withMessages([
                'role' => ['You cannot change your own role or deactivate your own account.'],
            ]);
        }

        if ($currentRole === FoundationRole::BusinessOwner->value && ($newRole !== $currentRole || ! $isActive)) {
            $hasAnotherOwner = $this->query($request, $contexts)
                ->whereKeyNot($subject->id)
                ->where('is_active', true)
                ->role(FoundationRole::BusinessOwner->value)
                ->exists();

            if (! $hasAnotherOwner) {
                throw ValidationException::withMessages([
                    'role' => ['At least one active Business Owner must remain.'],
                ]);
            }
        }
    }

    private function revokeSessions(User $user): void
    {
        $user->tokens()->delete();
        DB::table('sessions')->where('user_id', $user->id)->delete();
    }

    /** @return array<int, array{value: string, label: string}> */
    private function allowedRoles(): array
    {
        return [
            ['value' => FoundationRole::BusinessOwner->value, 'label' => 'مالک کسب‌وکار'],
            ['value' => FoundationRole::ContentEditor->value, 'label' => 'ویرایشگر محتوا'],
        ];
    }

    /** @return array<string, mixed> */
    private function serialize(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'is_active' => $user->is_active,
            'role' => $user->getRoleNames()->first(),
            'created_at' => $user->created_at?->toIso8601String(),
        ];
    }
}
