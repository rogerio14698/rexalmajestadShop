<?php

namespace App\Services;

use App\Contracts\InvoiceRegistrar;
use App\Enums\OrderStatus;
use App\Models\Invoice;
use App\Models\InvoiceSequence;
use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceService
{
    public function __construct(private readonly InvoiceRegistrar $registrar) {}

    public function issuerConfigured(): bool
    {
        foreach (['legal_name', 'address', 'city', 'postal_code', 'country_code'] as $field) {
            if (blank(config('commerce.business.'.$field))) {
                return false;
            }
        }

        return true;
    }

    public function issueForOrder(Order $order): Invoice
    {
        if (! $this->issuerConfigured()) {
            throw ValidationException::withMessages([
                'invoice' => 'Completa los datos fiscales del emisor antes de emitir facturas.',
            ]);
        }

        return DB::transaction(function () use ($order) {
            $lockedOrder = Order::query()->lockForUpdate()->with('items')->findOrFail($order->getKey());

            if ($lockedOrder->status !== OrderStatus::Paid) {
                throw ValidationException::withMessages(['invoice' => 'Solo se puede facturar un pedido pagado.']);
            }

            $existingInvoice = Invoice::query()->where('order_id', $lockedOrder->getKey())->first();

            if ($existingInvoice) {
                return $existingInvoice;
            }

            $issuedAt = now();
            $year = (int) $issuedAt->format('Y');
            $sequence = $this->nextNumber($year);
            $billing = $lockedOrder->billing_address_snapshot;
            $invoice = Invoice::query()->create([
                'number' => sprintf('RMA-%d-%06d', $year, $sequence),
                'order_id' => $lockedOrder->getKey(),
                'type' => 'invoice',
                'issuer_snapshot' => $this->issuerSnapshot(),
                'customer_name' => $billing['recipient'],
                'customer_email' => $lockedOrder->email,
                'customer_tax_id' => $billing['tax_id'] ?? null,
                'billing_address_snapshot' => $billing,
                'items_snapshot' => $this->itemsSnapshot($lockedOrder, 1),
                'currency' => $lockedOrder->currency,
                'net_cents' => $lockedOrder->total_cents - $lockedOrder->tax_cents,
                'tax_cents' => $lockedOrder->tax_cents,
                'total_cents' => $lockedOrder->total_cents,
                'issued_at' => $issuedAt,
            ]);

            $this->registrar->register($invoice);

            return $invoice;
        });
    }

    public function createCorrection(Invoice $original, string $reason): Invoice
    {
        $reason = trim($reason);

        if ($reason === '' || mb_strlen($reason) > 255) {
            throw ValidationException::withMessages(['reason' => 'Indica un motivo de rectificación de hasta 255 caracteres.']);
        }

        return DB::transaction(function () use ($original, $reason) {
            $lockedOriginal = Invoice::query()->lockForUpdate()->findOrFail($original->getKey());

            if ($lockedOriginal->type !== 'invoice'
                || Invoice::query()->where('corrects_invoice_id', $lockedOriginal->getKey())->exists()) {
                throw ValidationException::withMessages([
                    'invoice' => 'Solo se permite una rectificativa total por factura original.',
                ]);
            }

            $issuedAt = now();
            $year = (int) $issuedAt->format('Y');

            $correction = Invoice::query()->create([
                'number' => sprintf('RMA-%d-%06d', $year, $this->nextNumber($year)),
                'order_id' => null,
                'corrects_invoice_id' => $lockedOriginal->getKey(),
                'type' => 'credit_note',
                'issuer_snapshot' => $lockedOriginal->issuer_snapshot,
                'customer_name' => $lockedOriginal->customer_name,
                'customer_email' => $lockedOriginal->customer_email,
                'customer_tax_id' => $lockedOriginal->customer_tax_id,
                'billing_address_snapshot' => $lockedOriginal->billing_address_snapshot,
                'items_snapshot' => $lockedOriginal->items_snapshot,
                'currency' => $lockedOriginal->currency,
                'net_cents' => $lockedOriginal->net_cents,
                'tax_cents' => $lockedOriginal->tax_cents,
                'total_cents' => $lockedOriginal->total_cents,
                'issued_at' => $issuedAt,
                'correction_reason' => $reason,
            ]);

            $this->registrar->register($correction);

            return $correction;
        });
    }

    /**
     * @return Collection<int, array{country: string, tax_class: string, rate_basis_points: int, net_cents: int, tax_cents: int, total_cents: int}>
     */
    public function vatReport(string $from, string $to): Collection
    {
        $invoices = Invoice::query()->whereBetween('issued_at', [$from.' 00:00:00', $to.' 23:59:59'])->get();
        $groups = [];

        foreach ($invoices as $invoice) {
            $sign = $invoice->type === 'credit_note' ? -1 : 1;

            foreach ($invoice->items_snapshot as $item) {
                $key = implode('|', [
                    $item['tax_country_code'],
                    $item['tax_class'],
                    $item['tax_rate_basis_points'],
                ]);
                $groups[$key] ??= [
                    'country' => $item['tax_country_code'],
                    'tax_class' => $item['tax_class'],
                    'rate_basis_points' => (int) $item['tax_rate_basis_points'],
                    'net_cents' => 0,
                    'tax_cents' => 0,
                    'total_cents' => 0,
                ];
                $groups[$key]['net_cents'] += $sign * (int) $item['net_cents'];
                $groups[$key]['tax_cents'] += $sign * (int) $item['tax_cents'];
                $groups[$key]['total_cents'] += $sign * (int) $item['total_cents'];
            }
        }

        return collect(array_values($groups));
    }

    private function nextNumber(int $year): int
    {
        DB::table('invoice_sequences')->insertOrIgnore([
            'year' => $year,
            'last_number' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $sequence = InvoiceSequence::query()->whereKey($year)->lockForUpdate()->firstOrFail();
        $sequence->increment('last_number');

        return $sequence->fresh()->last_number;
    }

    /**
     * @return array<string, string|null>
     */
    private function issuerSnapshot(): array
    {
        return [
            'legal_name' => config('commerce.business.legal_name'),
            'tax_id' => config('commerce.business.tax_id'),
            'address' => config('commerce.business.address'),
            'city' => config('commerce.business.city'),
            'postal_code' => config('commerce.business.postal_code'),
            'country_code' => strtoupper(config('commerce.business.country_code')),
            'email' => config('commerce.business.email'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function itemsSnapshot(Order $order, int $sign): array
    {
        return $order->items->map(fn ($item) => [
            'title' => $item->title,
            'quantity' => $item->quantity,
            'unit_price_cents' => $item->unit_price_cents,
            'net_cents' => $sign * $item->net_cents,
            'tax_cents' => $sign * $item->tax_cents,
            'total_cents' => $sign * $item->total_cents,
            'tax_rate_basis_points' => $item->tax_rate_basis_points,
            'tax_class' => $item->tax_class,
            'tax_country_code' => $item->tax_country_code,
        ])->all();
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
}
