<?php

namespace App\Services;

use App\Exceptions\CommerceConfigurationException;
use App\Models\Artwork;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\StockReservation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartService
{
    public function forRequest(Request $request): Cart
    {
        if ($request->user()) {
            return Cart::query()->firstOrCreate(
                ['user_id' => $request->user()->getKey()],
                ['guest_token' => null],
            );
        }

        $token = $request->session()->get('cart_token');

        if (! $token) {
            $token = (string) Str::uuid();
            $request->session()->put('cart_token', $token);
        }

        return Cart::query()->firstOrCreate(['guest_token' => $token]);
    }

    public function add(Cart $cart, Artwork $artwork, int $quantity): CartItem
    {
        if ($quantity < 1 || ! $artwork->canPurchase()) {
            throw new CommerceConfigurationException('Esta obra no está disponible para compra.');
        }

        return DB::transaction(function () use ($cart, $artwork, $quantity) {
            Cart::query()->lockForUpdate()->findOrFail($cart->getKey());
            $lockedArtwork = Artwork::query()->lockForUpdate()->findOrFail($artwork->getKey());
            $item = CartItem::query()->firstOrNew([
                'cart_id' => $cart->getKey(),
                'artwork_id' => $lockedArtwork->getKey(),
            ]);
            $desiredQuantity = ($item->exists ? $item->quantity : 0) + $quantity;

            if ($desiredQuantity > $this->availableQuantity($lockedArtwork)) {
                throw new CommerceConfigurationException('La cantidad solicitada supera el stock disponible.');
            }

            $item->quantity = $desiredQuantity;
            $item->save();

            return $item;
        });
    }

    public function mergeGuestCart(User $user, ?string $token): void
    {
        if (! $token) {
            return;
        }

        DB::transaction(function () use ($user, $token) {
            $guestCart = Cart::query()->where('guest_token', $token)->with('items')->lockForUpdate()->first();

            if (! $guestCart) {
                return;
            }

            $userCart = Cart::query()->firstOrCreate(['user_id' => $user->getKey()]);

            foreach ($guestCart->items as $item) {
                $existing = $userCart->items()->where('artwork_id', $item->artwork_id)->first();
                $quantity = min(
                    $item->quantity + ($existing?->quantity ?? 0),
                    $this->availableQuantity($item->artwork),
                );

                if ($quantity < 1 || ! $item->artwork->canPurchase()) {
                    continue;
                }

                $userCart->items()->updateOrCreate(
                    ['artwork_id' => $item->artwork_id],
                    ['quantity' => $quantity],
                );
            }

            $guestCart->delete();
        });
    }

    public function availableQuantity(Artwork $artwork): int
    {
        $reserved = StockReservation::query()
            ->where('artwork_id', $artwork->getKey())
            ->whereNull('released_at')
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->sum('quantity');

        return max(0, $artwork->stock - $reserved);
    }
}
