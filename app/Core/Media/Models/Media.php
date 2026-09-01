<?php

namespace App\Core\Media\Models;

use App\Core\Business\Models\Business;
use App\Core\Media\MediaStatus;
use App\Core\Menu\Models\Product;
use App\Models\User;
use Database\Factories\Core\Media\Models\MediaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['public_id', 'business_id', 'disk', 'path', 'optimized_path', 'thumbnail_path', 'mime', 'size', 'width', 'height', 'checksum', 'status', 'uploaded_by'])]
class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use HasFactory;

    /** @return BelongsTo<Business, $this> */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /** @return BelongsTo<User, $this> */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /** @return HasMany<MediaTranslation, $this> */
    public function translations(): HasMany
    {
        return $this->hasMany(MediaTranslation::class);
    }

    /** @return HasMany<MediaUsage, $this> */
    public function usages(): HasMany
    {
        return $this->hasMany(MediaUsage::class);
    }

    /** @return HasMany<Product, $this> */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'primary_media_id');
    }

    protected function casts(): array
    {
        return [
            'status' => MediaStatus::class,
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }
}
