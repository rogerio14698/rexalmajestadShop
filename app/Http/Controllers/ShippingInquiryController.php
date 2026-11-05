<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use App\Models\Category;
use App\Services\CheckoutService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ShippingInquiryController extends Controller
{
    public function create(string $category, string $slug): View
    {
        $artwork = $this->resolveArtwork($category, $slug);

        abort_unless($artwork->ask_shipping, 404);

        return view('public.shipping-inquiry', compact('artwork'));
    }

    public function store(Request $request, string $category, string $slug, CheckoutService $checkoutService): RedirectResponse
    {
        $artwork = $this->resolveArtwork($category, $slug);
        abort_unless($artwork->ask_shipping, 404);

        $details = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:254'],
            'country_code' => ['required', 'string', 'size:2'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'message' => ['nullable', 'string', 'max:2000'],
            'website' => ['nullable', 'size:0'],
        ]);

        $checkoutService->recordShippingInquiry($artwork, $details);

        return back()->with('status', 'Hemos recibido la consulta de envío.');
    }

    private function resolveArtwork(string $category, string $slug): Artwork
    {
        $categoryModel = Category::query()->where('slug', $category)->firstOrFail();

        return Artwork::query()
            ->published()
            ->where('category_id', $categoryModel->getKey())
            ->where('slug', $slug)
            ->firstOrFail();
    }
}
