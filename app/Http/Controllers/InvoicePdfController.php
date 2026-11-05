<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePdfController extends Controller
{
    public function __invoke(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        return Pdf::loadView('users.invoice-pdf', compact('invoice'))
            ->setPaper('a4')
            ->download($invoice->number.'.pdf');
    }
}
