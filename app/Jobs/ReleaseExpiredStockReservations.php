<?php

namespace App\Jobs;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\StockReservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ReleaseExpiredStockReservations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        StockReservation::query()
            ->whereNull('released_at')
            ->whereNull('consumed_at')
            ->where('expires_at', '<=', now())
            ->select('order_id')
            ->distinct()
            ->orderBy('order_id')
            ->chunk(100, function ($reservations) {
                foreach ($reservations as $reservation) {
                    DB::transaction(function () use ($reservation) {
                        $order = Order::query()->lockForUpdate()->find($reservation->order_id);

                        if (! $order || $order->status !== OrderStatus::Pending) {
                            return;
                        }

                        $order->reservations()
                            ->whereNull('released_at')
                            ->whereNull('consumed_at')
                            ->where('expires_at', '<=', now())
                            ->update(['released_at' => now()]);

                        if (! $order->reservations()->whereNull('released_at')->whereNull('consumed_at')->exists()) {
                            $order->update(['status' => OrderStatus::Cancelled]);
                        }
                    });
                }
            });
    }
}
