<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        $order = $invoice->order ?? $invoice->corrects?->order;

        return $order?->user_id === $user->getKey();
    }
}
