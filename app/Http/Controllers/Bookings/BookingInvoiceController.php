<?php

namespace App\Http\Controllers\Bookings;

use App\BookingTotalsService;
use App\Http\Controllers\Controller;
use App\Http\Requests\SplitInvoiceRequest;
use App\InvoiceTotalsService;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingInvoiceController extends Controller
{
    public function __construct(
        protected BookingTotalsService $totalsService,
        protected InvoiceTotalsService $invoiceTotalsService,
    ) {
    }

    public function split(SplitInvoiceRequest $request, Booking $booking): RedirectResponse
    {
        DB::transaction(function () use ($request, $booking): void {
            $itemIds = collect($request->validated('item_ids'));

            $items = $booking->items()
                ->whereIn('id', $itemIds)
                ->get();

            if ($items->isEmpty()) {
                return;
            }

            $invoice = $booking->invoices()->create([
                'invoice_number' => sprintf('INV-SPLIT-%s-%s', now()->format('YmdHis'), Str::upper(Str::random(4))),
                'label' => Str::upper($request->validated('label')),
                'invoice_type' => 'separate',
            ]);

            foreach ($items as $item) {
                $item->update(['invoice_id' => $invoice->id]);
            }

            $booking->load('invoices.items', 'invoices.payments');

            foreach ($booking->invoices as $currentInvoice) {
                $currentInvoice->update(
                    $this->invoiceTotalsService->summarize($currentInvoice, $currentInvoice->items, $currentInvoice->payments)
                );
            }

            $booking->load(['items', 'payments', 'voucher']);
            $booking->update($this->totalsService->summarize($booking, $booking->items, $booking->payments));
        });

        return redirect()->route('bookings.show', $booking)->with('success', 'Invoice terpisah berhasil dibuat.');
    }
}
