<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Sección de la página /portfolio: la historia de la artista,
 * con una obra destacada opcional por sección. CRUD desde el admin.
 */
class PortfolioSection extends Model
{
    protected $fillable = [
        'heading',
        'body',
        'artwork_id',
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

    public function artwork(): BelongsTo
    {
        return $this->belongsTo(Artwork::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }
}
