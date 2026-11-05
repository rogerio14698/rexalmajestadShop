<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class Invoice extends Model
{
    protected $fillable = [
        'number',
        'order_id',
        'corrects_invoice_id',
        'type',
        'issuer_snapshot',
        'customer_name',
        'customer_email',
        'customer_tax_id',
        'billing_address_snapshot',
        'items_snapshot',
        'currency',
        'net_cents',
        'tax_cents',
        'total_cents',
        'issued_at',
        'correction_reason',
    ];

    protected function casts(): array
    {
        return [
            'issuer_snapshot' => 'array',
            'billing_address_snapshot' => 'array',
            'items_snapshot' => 'array',
            'net_cents' => 'integer',
            'tax_cents' => 'integer',
            'total_cents' => 'integer',
            'issued_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Issued invoices are immutable.'));
        static::deleting(fn () => throw new LogicException('Issued invoices cannot be deleted.'));
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function corrects(): BelongsTo
    {
        return $this->belongsTo(self::class, 'corrects_invoice_id');
    }
}
