<?php

namespace App\Http\Controllers;

use App\BookingTotalsService;
use App\Http\Requests\PaymentRequest;
use App\Models\Booking;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(protected BookingTotalsService $totalsService)
    {
    }

    public function index(Request $request): View
    {
        $payments = Payment::query()
            ->with('booking')
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
            ->when($request->filled('payment_method'), fn (Builder $query): Builder => $query->where('payment_method', $request->string('payment_method')))
            ->when($request->filled('received_by'), fn (Builder $query): Builder => $query->where('received_by', $request->string('received_by')))
            ->latest('paid_at')
            ->paginate(10)
            ->withQueryString();

        return view('pages.payments.index', [
            'title' => 'Ledger Payment',
            'payments' => $payments,
            'filters' => $request->only(['q', 'payment_method', 'received_by']),
        ]);
    }

    public function create(): View
    {
        return view('pages.payments.create', [
            'title' => 'Catat Payment',
            'bookings' => Booking::query()->latest()->get(),
        ]);
    }

    public function store(PaymentRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $payment = Payment::query()->create([
                'booking_id' => $request->integer('booking_id'),
                'amount' => $request->integer('amount'),
                'payment_method' => $request->input('payment_method'),
                'received_by' => $request->input('received_by'),
                'note' => $request->input('note'),
                'proof_image' => $request->input('proof_image'),
                'paid_at' => Carbon::parse($request->input('paid_at')),
                'created_by' => null,
            ]);

            $booking = $payment->booking()->with(['items', 'payments', 'voucher'])->firstOrFail();
            $summary = $this->totalsService->summarize($booking, $booking->items, $booking->payments);
            $booking->update($summary);
        });

        return redirect()->route('payments.index')->with('success', 'Payment berhasil dicatat dan saldo booking telah diperbarui.');
    }
}
