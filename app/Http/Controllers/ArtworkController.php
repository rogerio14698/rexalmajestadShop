<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use App\Models\Category;
use Illuminate\Contracts\View\View;

class ArtworkController extends Controller
{
    public function index(string $category): View
    {
        $selectedCategory = Category::query()->where('slug', $category)->firstOrFail();

        $artworks = Artwork::query()
            ->published()
            ->where('category_id', $selectedCategory->getKey())
            ->with(['media', 'category'])
            ->orderByDesc('year')
            ->orderByDesc('created_at')
            ->get();

        return view('public.catalogo', compact('artworks', 'selectedCategory'));
    }

    public function show(string $category, string $slug): View
    {
        $selectedCategory = Category::query()->where('slug', $category)->firstOrFail();

        $artwork = Artwork::query()
            ->published()
            ->with(['media', 'category', 'gallery'])
            ->where('category_id', $selectedCategory->getKey())
            ->where('slug', $slug)
            ->firstOrFail();

        // Obras relacionadas: primero las de la misma galería temática y, si faltan, otras de la misma categoría.
        $relatedArtworks = Artwork::query()
            ->published()
            ->with(['media', 'category'])
            ->where('id', '<>', $artwork->getKey())
            ->when($artwork->gallery_id, function ($query) use ($artwork) {
                $query->orderByRaw('CASE WHEN gallery_id = ? THEN 0 ELSE 1 END', [$artwork->gallery_id])
                    ->where(function ($query) use ($artwork) {
                        $query->where('gallery_id', $artwork->gallery_id)
                            ->orWhere('category_id', $artwork->category_id);
                    });
            }, function ($query) use ($artwork) {
                $query->where('category_id', $artwork->category_id);
            })
            ->orderByDesc('year')
            ->limit(3)
            ->get();

        return view('public.artwork', compact('artwork', 'relatedArtworks'));
    }
}
