<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Artwork extends Model implements HasMedia
{
    use HasFactory;
    use HasTranslations;
    use InteractsWithMedia;

    public array $translatable = ['title', 'description', 'meta_title', 'meta_description'];

    protected $fillable = [
        'title',
        'slug',
        'category_id',
        'year',
        'technique',
        'height_cm',
        'width_cm',
        'depth_cm',
        'weight_grams',
        'series',
        'gallery_id',
        'description',
        'price_cents',
        'stock',
        'is_for_sale',
        'is_sold',
        'tax_class',
        'ask_shipping',
        'meta_title',
        'meta_description',
        'og_image',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'height_cm' => 'decimal:2',
            'width_cm' => 'decimal:2',
            'depth_cm' => 'decimal:2',
            'weight_grams' => 'integer',
            'price_cents' => 'integer',
            'stock' => 'integer',
            'is_for_sale' => 'boolean',
            'is_sold' => 'boolean',
            'ask_shipping' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Artwork $artwork) {
            if (empty($artwork->published_at)) {
                $artwork->published_at = now();
            }
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('artwork')->useDisk('public');
    }

    public function registerMediaConversions(?\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        $this->addMediaConversion('webp')
            ->format('webp')
            ->width(1800)
            ->height(1800)
            ->performOnCollections('artwork')
            ->nonQueued();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function scopeForSale(Builder $query): Builder
    {
        return $query->where('is_for_sale', true);
    }

    public function canPurchase(): bool
    {
        return $this->is_for_sale && ! $this->is_sold && ! $this->ask_shipping && $this->stock > 0;
    }

    public function categoryLabel(): string
    {
        return $this->category?->name ?? '';
    }

    /**
     * Categoría de la obra (editable desde el admin).
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    /**
     * Galería temática a la que pertenece la obra.
     */
    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}
