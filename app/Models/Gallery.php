<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Galería temática: agrupa obras relacionadas con una misma temática
 * (p. ej. «Grecia», «Zodiaco», «Retratos»).
 */
class Gallery extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Gallery $gallery) {
            if (empty($gallery->published_at)) {
                $gallery->published_at = now();
            }
        });
    }

    public function artworks(): HasMany
    {
        return $this->hasMany(Artwork::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }
}
