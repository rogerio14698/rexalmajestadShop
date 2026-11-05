<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class SiteSetting extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'key',
        'label',
        'group',
        'value',
        'artwork_id',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile()->useDisk('public');
    }

    public function registerMediaConversions(?\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        $this->addMediaConversion('webp')
            ->format('webp')
            ->width(1920)
            ->height(1080)
            ->performOnCollections('image')
            ->nonQueued();
    }

    public function artwork(): BelongsTo
    {
        return $this->belongsTo(Artwork::class);
    }

    /**
     * Obtiene la URL de la imagen del banner o configuración.
     * Si tiene una imagen subida directamente la devuelve,
     * o bien la imagen de la obra asociada.
     */
    public function getImageUrl(): ?string
    {
        $mediaUrl = $this->getFirstMediaUrl('image', 'webp') ?: $this->getFirstMediaUrl('image');
        if ($mediaUrl !== '') {
            return $mediaUrl;
        }

        if ($this->artwork) {
            $artworkUrl = $this->artwork->getFirstMediaUrl('artwork', 'webp') ?: $this->artwork->getFirstMediaUrl('artwork');
            if ($artworkUrl !== '') {
                return $artworkUrl;
            }
        }

        return null;
    }

    public static function getHeroImageUrl(string $key, ?string $fallbackUrl = null): ?string
    {
        $setting = static::query()->with('artwork.media')->where('key', $key)->first();

        return $setting?->getImageUrl() ?: $fallbackUrl;
    }
}
