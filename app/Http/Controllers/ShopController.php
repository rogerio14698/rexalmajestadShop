<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->query('category');
        $selectedCategory = $category === null ? null : Category::query()->where('slug', $category)->first();

        abort_if($category !== null && $selectedCategory === null, 404);

        $search = trim((string) $request->query('q', ''));

        $artworks = Artwork::query()
            ->published()
            ->forSale()
            ->when($selectedCategory, fn ($query) => $query->where('category_id', $selectedCategory->getKey()))
            ->when($search !== '', function ($query) use ($search) {
                // Busca por nombre, año, material/técnica o serie con un solo campo.
                $like = '%'.$search.'%';
                $query->where(fn ($query) => $query
                    ->where('title', 'like', $like)
                    ->orWhere('technique', 'like', $like)
                    ->orWhere('series', 'like', $like)
                    ->orWhere('year', 'like', $like));
            })
            ->with(['media', 'category', 'gallery'])
            ->orderByDesc('year')
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        $categories = Category::query()->orderBy('position')->get();

        return view('public.shop.index', compact('artworks', 'selectedCategory', 'categories', 'search'));
    }
}
