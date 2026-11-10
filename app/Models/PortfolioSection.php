<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Sección de la página /bio: biografía, statement, currículum
 * (exposiciones en solitario, colectivas, premios, prensa...), con galería de imágenes.
 */
class PortfolioSection extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'type',
        'heading',
        'subtitle',
        'year_label',
        'body',
        'artwork_id',
        'external_url',
        'link_label',
        'position',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery')->useDisk('public');
    }

    public function registerMediaConversions(?\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        $this->addMediaConversion('webp')
            ->format('webp')
            ->width(1800)
            ->height(1800)
            ->performOnCollections('gallery')
            ->nonQueued();
    }

    public function artwork(): BelongsTo
    {
        return $this->belongsTo(Artwork::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }
}

