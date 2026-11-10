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
        $selectedCategory = $category === null ? null : Category::query()->with('children')->where('slug', $category)->first();

        abort_if($category !== null && $selectedCategory === null, 404);

        $categoryIds = $selectedCategory
            ? ($selectedCategory->children->isNotEmpty()
                ? $selectedCategory->children->pluck('id')->push($selectedCategory->id)
                : collect([$selectedCategory->id]))
            : null;

        $search = trim((string) $request->query('q', ''));

        $artworks = Artwork::query()
            ->published()
            ->forSale()
            ->when($categoryIds, fn ($query) => $query->whereIn('category_id', $categoryIds))
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
