<?php

namespace App\Core\Cms\Models;

use App\Models\User;
use Database\Factories\Core\Cms\Models\PageRevisionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * @property array<string, mixed> $snapshot_json
 */
#[Fillable(['page_id', 'revision', 'snapshot_json', 'published_by'])]
class PageRevision extends Model
{
    /** @use HasFactory<PageRevisionFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /** @return BelongsTo<Page, $this> */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /** @return BelongsTo<User, $this> */
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Published page revisions are immutable.'));
        static::deleting(fn () => throw new LogicException('Published page revisions are immutable.'));
    }

    protected function casts(): array
    {
        return [
            'revision' => 'integer',
            'snapshot_json' => 'array',
        ];
    }
}
