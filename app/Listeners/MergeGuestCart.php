<?php

namespace App\Listeners;

use App\Services\CartService;
use Illuminate\Auth\Events\Login;

class MergeGuestCart
{
    public function __construct(private readonly CartService $cartService) {}

    public function handle(Login $event): void
    {
        $token = request()->session()->pull('cart_token');
        $this->cartService->mergeGuestCart($event->user, $token);
    }
}
