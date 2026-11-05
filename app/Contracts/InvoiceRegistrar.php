<?php

namespace App\Contracts;

use App\Models\Invoice;

interface InvoiceRegistrar
{
    public function register(Invoice $invoice): void;
}
