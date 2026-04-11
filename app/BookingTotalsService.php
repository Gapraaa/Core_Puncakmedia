<?php

namespace App;

use App\Models\Booking;
use App\Models\Voucher;
use Illuminate\Support\Collection;

class BookingTotalsService
{
    public function calculateSubtotal(Collection $bookingItems): int
    {
        return (int) $bookingItems->sum('total_price');
    }

    public function calculateVoucherDiscount(?Voucher $voucher, int $subtotal): int
    {
        if ($voucher === null || ! $voucher->is_active || $subtotal < (int) $voucher->minimum_transaction) {
            return 0;
        }

        if ($voucher->valid_until !== null && $voucher->valid_until->isPast()) {
            return 0;
        }

        if ($voucher->discount_type === 'percentage') {
            return (int) round($subtotal * ((int) $voucher->amount / 100));
        }

        return min($subtotal, (int) $voucher->amount);
    }

    public function calculateGrandTotal(int $subtotal, int $voucherDiscount, int $manualDiscount): int
    {
        return max(0, $subtotal - $voucherDiscount - $manualDiscount);
    }

    public function calculateTotalPaid(Collection $payments): int
    {
        return (int) $payments->sum('amount');
    }

    public function calculateRemainingBalance(int $grandTotal, int $totalPaid): int
    {
        return $grandTotal - $totalPaid;
    }

    public function determinePaymentStatus(int $grandTotal, int $totalPaid): string
    {
        if ($grandTotal <= 0 || $totalPaid <= 0) {
            return 'unpaid';
        }

        if ($totalPaid >= $grandTotal) {
            return 'paid';
        }

        return 'partial';
    }

    public function determineBookingStatus(Booking $booking, Collection $payments): string
    {
        $hasPayment = $payments->isNotEmpty();

        if (! $hasPayment) {
            return 'draft';
        }

        if ($booking->payment_status === 'paid') {
            return 'confirmed';
        }

        return 'pending_payment';
    }

    public function summarize(Booking $booking, Collection $bookingItems, Collection $payments): array
    {
        $subtotal = $this->calculateSubtotal($bookingItems);
        $voucherDiscount = $this->calculateVoucherDiscount($booking->voucher, $subtotal);
        $manualDiscount = (int) $booking->manual_discount_amount;
        $grandTotal = $this->calculateGrandTotal($subtotal, $voucherDiscount, $manualDiscount);
        $totalPaid = $this->calculateTotalPaid($payments);
        $remainingBalance = $this->calculateRemainingBalance($grandTotal, $totalPaid);
        $paymentStatus = $this->determinePaymentStatus($grandTotal, $totalPaid);

        $booking->payment_status = $paymentStatus;

        return [
            'total_before_discount' => $subtotal,
            'voucher_discount_amount' => $voucherDiscount,
            'manual_discount_amount' => $manualDiscount,
            'grand_total' => $grandTotal,
            'total_paid' => $totalPaid,
            'remaining_balance' => $remainingBalance,
            'payment_status' => $paymentStatus,
            'booking_status' => $this->determineBookingStatus($booking, $payments),
        ];
    }
}
