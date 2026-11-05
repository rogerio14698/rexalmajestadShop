<?php

namespace App\Services;

use App\Contracts\InvoiceRegistrar;
use App\Models\Invoice;

class NullInvoiceRegistrar implements InvoiceRegistrar
{
    public function register(Invoice $invoice): void
    {
    }
}
