<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminOrderController extends Controller
{
    public function show(Request $request, Order $order): View
    {
        abort_unless($request->user()?->isAdmin() && $request->user()->hasVerifiedEmail(), 403);
        $order->load(['items', 'payment', 'reservations']);

        return view('admin.order', compact('order'));
    }
}
