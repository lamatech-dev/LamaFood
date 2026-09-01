<?php

namespace App\Core\Menu\Actions;

use App\Core\Audit\AuditRecorder;
use App\Core\Business\Models\Business;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ReorderMenuCategories
{
    public function __construct(private readonly AuditRecorder $audit) {}

    /** @param list<string> $publicIds */
    public function execute(Business $business, User $actor, array $publicIds): void
    {
        $existing = $business->menuCategories()->pluck('public_id')->sort()->values()->all();
        if ($existing !== collect($publicIds)->sort()->values()->all()) {
            throw ValidationException::withMessages(['categories' => ['The order must contain every category exactly once.']]);
        }

        DB::transaction(function () use ($business, $actor, $publicIds): void {
            foreach ($publicIds as $position => $publicId) {
                $business->menuCategories()->where('public_id', $publicId)->update(['position' => $position]);
            }
            $this->audit->record('menu.categories.reordered', $actor, $business, $business->id, after: ['categories' => $publicIds]);
        });
    }
}
