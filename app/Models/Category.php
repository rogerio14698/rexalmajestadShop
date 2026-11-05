<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Categoría de obra (óleo, dibujo, grabado...), editable desde el admin.
 */
class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }

    public function artworks(): HasMany
    {
        return $this->hasMany(Artwork::class);
    }
}
