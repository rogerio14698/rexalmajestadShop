<?php

namespace App\Http\Controllers;

use App\Exceptions\CommerceConfigurationException;
use App\Models\Artwork;
use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService) {}

    public function show(Request $request): View
    {
        $cart = $this->cartService->forRequest($request)->load('items');

        return view('public.shop.cart', compact('cart'));
    }

    public function add(Request $request, Artwork $artwork): RedirectResponse
    {
        abort_unless($artwork->published_at !== null && $artwork->published_at->lessThanOrEqualTo(now()), 404);

        if ($artwork->ask_shipping) {
            return redirect()->route('shipping-inquiry.create', [
                'category' => $artwork->category?->slug,
                'slug' => $artwork->slug,
            ]);
        }

        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:99']]);

        try {
            $this->cartService->add($this->cartService->forRequest($request), $artwork, (int) $data['quantity']);
        } catch (CommerceConfigurationException $exception) {
            throw ValidationException::withMessages(['quantity' => $exception->getMessage()]);
        }

        return redirect()->route('cart.show')->with('status', 'La obra se ha añadido al carrito.');
    }

    public function update(Request $request, CartItem $item): RedirectResponse
    {
        $cart = $this->cartService->forRequest($request);
        abort_unless($item->cart_id === $cart->getKey(), 404);

        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:99']]);
        $artwork = $item->artwork;

        if (! $artwork->canPurchase() || (int) $data['quantity'] > $this->cartService->availableQuantity($artwork)) {
            throw ValidationException::withMessages(['quantity' => 'La cantidad solicitada supera el stock disponible.']);
        }

        $item->update(['quantity' => (int) $data['quantity']]);

        return back()->with('status', 'Carrito actualizado.');
    }

    public function remove(Request $request, CartItem $item): RedirectResponse
    {
        $cart = $this->cartService->forRequest($request);
        abort_unless($item->cart_id === $cart->getKey(), 404);
        $item->delete();

        return back()->with('status', 'La obra se ha quitado del carrito.');
    }
}
