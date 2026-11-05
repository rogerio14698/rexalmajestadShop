<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Exhibition extends Model
{
    use HasTranslations;

    public array $translatable = ['title', 'description'];

    protected $fillable = [
        'title',
        'slug',
        'venue',
        'city',
        'country_code',
        'starts_at',
        'ends_at',
        'description',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'published_at' => 'datetime',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function setCountryCodeAttribute(?string $countryCode): void
    {
        $this->attributes['country_code'] = $countryCode === null ? null : strtoupper($countryCode);
    }
}
