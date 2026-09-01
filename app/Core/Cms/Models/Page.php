<?php

namespace App\Core\Cms\Models;

use App\Core\Business\Models\Business;
use App\Core\Cms\PageStatus;
use App\Models\User;
use Database\Factories\Core\Cms\Models\PageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @property PageStatus $status */
#[Fillable(['public_id', 'business_id', 'slug', 'template', 'status', 'revision', 'published_revision_id', 'published_at', 'sort_order', 'created_by', 'updated_by'])]
class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory;

    /** @return BelongsTo<Business, $this> */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /** @return HasMany<PageTranslation, $this> */
    public function translations(): HasMany
    {
        return $this->hasMany(PageTranslation::class);
    }

    /** @return HasMany<Block, $this> */
    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class)->orderBy('position');
    }

    /** @return HasMany<PageRevision, $this> */
    public function revisions(): HasMany
    {
        return $this->hasMany(PageRevision::class)->orderByDesc('revision');
    }

    /** @return BelongsTo<PageRevision, $this> */
    public function publishedRevision(): BelongsTo
    {
        return $this->belongsTo(PageRevision::class, 'published_revision_id');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected function casts(): array
    {
        return [
            'status' => PageStatus::class,
            'revision' => 'integer',
            'published_at' => 'immutable_datetime',
            'sort_order' => 'integer',
        ];
    }
}
