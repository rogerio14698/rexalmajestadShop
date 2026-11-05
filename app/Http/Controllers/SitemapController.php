<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use App\Models\Category;
use App\Models\Exhibition;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function sitemap(): Response
    {
        $urls = [
            ['loc' => route('home'), 'lastmod' => now()->toDateString()],
            ['loc' => route('shop.index'), 'lastmod' => now()->toDateString()],
            ['loc' => route('about'), 'lastmod' => now()->toDateString()],
            ['loc' => route('exhibitions.index'), 'lastmod' => now()->toDateString()],
            ['loc' => route('portfolio'), 'lastmod' => now()->toDateString()],
        ];

        foreach (Category::query()->orderBy('position')->get() as $category) {
            $urls[] = [
                'loc' => route('artworks.category', ['category' => $category->slug]),
                'lastmod' => now()->toDateString(),
            ];
        }

        foreach (Artwork::query()->published()->with('category')->get(['id', 'category_id', 'slug', 'updated_at']) as $artwork) {
            if ($artwork->category === null) {
                continue;
            }

            $urls[] = [
                'loc' => route('artworks.show', ['category' => $artwork->category->slug, 'slug' => $artwork->slug]),
                'lastmod' => $artwork->updated_at->toDateString(),
            ];
        }

        foreach (Exhibition::query()->published()->get(['slug', 'updated_at']) as $exhibition) {
            $urls[] = [
                'loc' => route('exhibitions.show', ['slug' => $exhibition->slug]),
                'lastmod' => $exhibition->updated_at->toDateString(),
            ];
        }

        return response()
            ->view('seo.sitemap', compact('urls'))
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        $body = implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin/',
            'Disallow: /cuenta/',
            'Disallow: /carrito',
            'Disallow: /checkout',
            'Sitemap: '.route('sitemap'),
            '',
        ]);

        return response($body)->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
