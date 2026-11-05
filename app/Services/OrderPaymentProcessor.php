<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Artwork;
use App\Models\Order;
use App\Models\StockReservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderPaymentProcessor
{
    public function __construct(private readonly InvoiceService $invoiceService) {}

    /**
     * Mark an order paid only after the provider's signed webhook was validated.
     */
    public function confirm(Order $order, string $provider, string $providerReference, int $amountCents, string $currency, array $metadata = []): bool
    {
        return DB::transaction(function () use ($order, $provider, $providerReference, $amountCents, $currency, $metadata) {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->getKey());

            if ($amountCents !== $lockedOrder->total_cents || strtoupper($currency) !== $lockedOrder->currency) {
                return false;
            }

            if ($lockedOrder->status === OrderStatus::Paid) {
                return $lockedOrder->payment()->where('provider', $provider)
                    ->where('provider_reference', $providerReference)
                    ->exists();
            }

            if ($lockedOrder->status !== OrderStatus::Pending) {
                return false;
            }

            if ($lockedOrder->reserve_expires_at?->isPast()) {
                return false;
            }

            $reservations = $lockedOrder->reservations()
                ->whereNull('released_at')
                ->whereNull('consumed_at')
                ->where('expires_at', '>', now())
                ->orderBy('artwork_id')
                ->get();

            if ($reservations->count() !== $lockedOrder->items()->whereNotNull('artwork_id')->count()) {
                return false;
            }

            foreach ($reservations as $reservation) {
                $artwork = Artwork::query()->lockForUpdate()->findOrFail($reservation->artwork_id);
                $otherReservations = StockReservation::query()
                    ->where('artwork_id', $artwork->getKey())
                    ->where('order_id', '<>', $lockedOrder->getKey())
                    ->whereNull('released_at')
                    ->whereNull('consumed_at')
                    ->where('expires_at', '>', now())
                    ->sum('quantity');

                if ($artwork->stock - $otherReservations < $reservation->quantity) {
                    return false;
                }
            }

            foreach ($reservations as $reservation) {
                $artwork = Artwork::query()->lockForUpdate()->findOrFail($reservation->artwork_id);
                $artwork->decrement('stock', $reservation->quantity);
                $artwork->refresh();

                if ($artwork->stock === 0) {
                    $artwork->update(['is_sold' => true]);
                }

                $reservation->update(['consumed_at' => now()]);
            }

            $lockedOrder->payment()->updateOrCreate(
                ['provider' => $provider],
                [
                    'provider_reference' => $providerReference,
                    'status' => 'paid',
                    'amount_cents' => $amountCents,
                    'currency' => strtoupper($currency),
                    'metadata' => $metadata,
                    'captured_at' => now(),
                ],
            );
            $lockedOrder->update(['status' => OrderStatus::Paid, 'paid_at' => now()]);

            if ($this->invoiceService->issuerConfigured()) {
                $this->invoiceService->issueForOrder($lockedOrder);
            } else {
                Log::warning('Invoice issuance is pending business fiscal details.', [
                    'order_id' => $lockedOrder->getKey(),
                ]);
            }

            return true;
        });
    }
}
