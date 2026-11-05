<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function dashboard(Request $request): View
    {
        $orders = $request->user()->orders()->latest()->limit(5)->get();

        return view('users.dashboard', compact('orders'));
    }

    public function orders(Request $request): View
    {
        $orders = $request->user()->orders()->latest()->paginate(12);

        return view('users.orders', compact('orders'));
    }

    public function order(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->getKey(), 404);
        $order->load(['items', 'invoice']);

        return view('users.order', compact('order'));
    }

    public function favorites(Request $request): View
    {
        $artworks = $request->user()->favorites()->published()->with('media')->latest('favorites.created_at')->paginate(12);

        return view('users.favorites', compact('artworks'));
    }

    public function invoices(Request $request): View
    {
        $invoices = Invoice::query()
            ->whereHas('order', fn ($query) => $query->where('user_id', $request->user()->getKey()))
            ->orWhereHas('corrects.order', fn ($query) => $query->where('user_id', $request->user()->getKey()))
            ->latest('issued_at')
            ->paginate(12);

        return view('users.invoices', compact('invoices'));
    }

    public function export(Request $request)
    {
        $user = $request->user()->load(['addresses', 'favorites', 'orders.items', 'orders.invoice']);
        $data = [
            'exported_at' => now()->toIso8601String(),
            'profile' => $user->only(['name', 'email', 'created_at']),
            'addresses' => $user->addresses,
            'favorites' => $user->favorites->map->only(['id', 'slug', 'title']),
            'orders' => $user->orders->map(fn (Order $order) => [
                'number' => $order->number,
                'status' => $order->status->value,
                'currency' => $order->currency,
                'total_cents' => $order->total_cents,
                'items' => $order->items,
                'invoice' => $order->invoice,
                'shipping_address' => $order->shipping_address_snapshot,
                'billing_address' => $order->billing_address_snapshot,
            ]),
            'invoices' => Invoice::query()
                ->whereHas('order', fn ($query) => $query->where('user_id', $user->getKey()))
                ->orWhereHas('corrects.order', fn ($query) => $query->where('user_id', $user->getKey()))
                ->get(),
        ];

        return response()->streamDownload(
            fn () => print(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)),
            'rex-almajestad-datos.json',
            ['Content-Type' => 'application/json; charset=UTF-8'],
        );
    }
}
