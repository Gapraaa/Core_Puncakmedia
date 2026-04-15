<?php

namespace App;

use App\Models\Invoice;
use Illuminate\Support\Collection;

class InvoiceTotalsService
{
    public function summarize(Invoice $invoice, Collection $items, Collection $payments): array
    {
        $subtotal = (int) $items->sum('total_price');
        $totalPaid = (int) $payments->sum('amount');
        $remainingBalance = max(0, $subtotal - $totalPaid);

        if ($subtotal === 0) {
            $paymentStatus = 'empty';
        } elseif ($totalPaid >= $subtotal) {
            $paymentStatus = 'lunas';
        } elseif ($payments->count() > 1) {
            $paymentStatus = 'cicil';
        } else {
            $paymentStatus = 'dp';
        }

        return [
            'subtotal' => $subtotal,
            'total_paid' => $totalPaid,
            'remaining_balance' => $remainingBalance,
            'payment_status' => $paymentStatus,
        ];
    }
}
