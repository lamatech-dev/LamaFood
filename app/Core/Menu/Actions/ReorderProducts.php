<?php

namespace App\Core\Menu\Actions;

use App\Core\Audit\AuditRecorder;
use App\Core\Menu\Models\MenuCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ReorderProducts
{
    public function __construct(private readonly AuditRecorder $audit) {}

    /** @param list<string> $publicIds */
    public function execute(MenuCategory $category, User $actor, array $publicIds): void
    {
        $existing = $category->products()->pluck('public_id')->sort()->values()->all();
        if ($existing !== collect($publicIds)->sort()->values()->all()) {
            throw ValidationException::withMessages(['products' => ['The order must contain every product in the category exactly once.']]);
        }

        DB::transaction(function () use ($category, $actor, $publicIds): void {
            foreach ($publicIds as $position => $publicId) {
                $category->products()->where('public_id', $publicId)->update(['position' => $position]);
            }
            $this->audit->record('menu.products.reordered', $actor, $category, $category->business_id, after: ['products' => $publicIds]);
        });
    }
}
