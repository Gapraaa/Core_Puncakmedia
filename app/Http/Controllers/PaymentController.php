<?php

namespace App\Http\Controllers;

use App\BookingTotalsService;
use App\InvoiceTotalsService;
use App\Http\Requests\PaymentRequest;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Villa;
use App\Models\VillaUnit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        protected BookingTotalsService $totalsService,
        protected InvoiceTotalsService $invoiceTotalsService,
    )
    {
    }

    public function index(Request $request): View
    {
        $villas = Villa::query()
            ->withCount([
                'units',
                'bookings as payments_count' => fn (Builder $query) => $query
                    ->join('payments', 'payments.booking_id', '=', 'bookings.id'),
            ])
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $keyword = trim((string) $request->string('q'));
                $query->where(fn (Builder $innerQuery) => $innerQuery
                    ->where('name', 'like', "%{$keyword}%")
                    ->orWhere('slug', 'like', "%{$keyword}%")
                    ->orWhere('location', 'like', "%{$keyword}%"));
            })
            ->when($request->filled('type'), function (Builder $query) use ($request): void {
                $type = $request->string('type')->toString();

                if ($type === 'resort') {
                    $query->where('is_resort', true);
                }

                if ($type === 'villa') {
                    $query->where('is_resort', false);
                }
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.payments.index', [
            'title' => 'Daftar Pembayaran',
            'villas' => $villas,
            'filters' => $request->only(['q', 'type']),
        ]);
    }

    public function showVilla(Villa $villa, Request $request): RedirectResponse|View
    {
        if ($villa->is_resort) {
            return redirect()->route('payments.units', $villa);
        }

        $villaUnit = $villa->units()->firstOrFail();

        return $this->unitPaymentListView($villa, $villaUnit, $request);
    }

    public function showVillaUnits(Villa $villa, Request $request): View
    {
        abort_unless($villa->is_resort, 404);

        $villaUnits = $villa->units()
            ->withCount([
                'bookings',
                'bookings as payments_count' => fn (Builder $query) => $query
                    ->join('payments', 'payments.booking_id', '=', 'bookings.id'),
            ])
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $keyword = trim((string) $request->string('q'));
                $query->where(fn (Builder $innerQuery) => $innerQuery
                    ->where('unit_name', 'like', "%{$keyword}%")
                    ->orWhere('unit_type', 'like', "%{$keyword}%"));
            })
            ->when($request->filled('status'), fn (Builder $query): Builder => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.payments.units', [
            'title' => 'Pilih Unit Pembayaran',
            'villa' => $villa->loadCount('units'),
            'villaUnits' => $villaUnits,
            'filters' => $request->only(['q', 'status']),
        ]);
    }

    public function showUnit(Villa $villa, VillaUnit $villaUnit, Request $request): View
    {
        abort_unless($villaUnit->villa_id === $villa->id, 404);

        return $this->unitPaymentListView($villa, $villaUnit, $request);
    }

    /**
     * Store payment dari halaman detail booking (cicilan / pelunasan).
     */
    public function store(PaymentRequest $request, Booking $booking): RedirectResponse
    {
        $payment = DB::transaction(function () use ($request, $booking): Payment {
            $invoice = $booking->invoices()
                ->when($request->filled('invoice_id'), fn ($query) => $query->whereKey($request->integer('invoice_id')))
                ->first() ?? $booking->invoices()->oldest()->first();

            $allowedAmount = $invoice instanceof Invoice
                ? (int) $invoice->remaining_balance
                : (int) $booking->remaining_balance;

            if ($request->integer('amount') > $allowedAmount) {
                throw ValidationException::withMessages([
                    'amount' => 'Nominal pembayaran tidak boleh lebih besar dari sisa tagihan yang belum dibayar.',
                ]);
            }

            $payment = Payment::query()->create([
                'booking_id' => $booking->id,
                'invoice_id' => $invoice?->id,
                'amount' => $request->integer('amount'),
                'payment_method' => $request->input('payment_method'),
                'received_by' => $request->input('received_by'),
                'note' => $request->input('note'),
                'proof_image' => $request->input('proof_image'),
                'paid_at' => Carbon::parse($request->input('paid_at', now())),
                'created_by' => $request->user()?->id,
            ]);

            if ($invoice instanceof Invoice) {
                $invoice->load(['items', 'payments']);
                $invoice->update($this->invoiceTotalsService->summarize($invoice, $invoice->items, $invoice->payments));
            }

            $booking->load(['items', 'payments', 'voucher']);
            $summary = $this->totalsService->summarize($booking, $booking->items, $booking->payments);
            $booking->update($summary);
            return $payment;
        });

        $this->auditLog(
            module: 'pembayaran',
            action: 'create',
            description: 'Pembayaran booking berhasil dicatat.',
            subject: $payment,
            after: [
                'booking_code' => $booking->booking_code,
                'invoice_number' => $payment->invoice?->invoice_number,
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
                'received_by' => $payment->received_by,
                'paid_at' => optional($payment->paid_at)->format('Y-m-d H:i:s'),
            ],
        );

        return redirect()
            ->route('bookings.show', $booking)
            ->with('success', 'Pembayaran berhasil dicatat dan saldo booking diperbarui.')
            ->with('auto_download_document_url', route('documents.payments.receipt', ['payment' => $payment, 'download' => 1]));
    }

    protected function unitPaymentListView(Villa $villa, VillaUnit $villaUnit, Request $request): View
    {
        $payments = Payment::query()
            ->with(['booking', 'invoice'])
            ->whereHas('booking', fn (Builder $query) => $query->where('villa_unit_id', $villaUnit->id))
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $keyword = trim((string) $request->string('q'));

                $query->where(function (Builder $innerQuery) use ($keyword): void {
                    $innerQuery
                        ->where('note', 'like', "%{$keyword}%")
                        ->orWhere('proof_image', 'like', "%{$keyword}%")
                        ->orWhereHas('booking', function (Builder $bookingQuery) use ($keyword): void {
                            $bookingQuery
                                ->where('booking_code', 'like', "%{$keyword}%")
                                ->orWhere('guest_name', 'like', "%{$keyword}%");
                        });
                });
            })
            ->when($request->filled('invoice_id'), fn (Builder $query): Builder => $query->where('invoice_id', $request->integer('invoice_id')))
            ->when($request->filled('payment_method'), fn (Builder $query): Builder => $query->where('payment_method', $request->string('payment_method')))
            ->when($request->filled('received_by'), fn (Builder $query): Builder => $query->where('received_by', $request->string('received_by')))
            ->latest('paid_at')
            ->paginate(10)
            ->withQueryString();

        return view('pages.payments.list', [
            'title' => 'Daftar Pembayaran',
            'villa' => $villa,
            'villaUnit' => $villaUnit->loadCount('bookings'),
            'payments' => $payments,
            'filters' => $request->only(['q', 'payment_method', 'received_by']),
        ]);
    }
}
