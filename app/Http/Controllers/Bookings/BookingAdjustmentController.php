<?php

namespace App\Http\Controllers\Bookings;

use App\BookingPricingService;
use App\BookingTotalsService;
use App\Http\Controllers\Controller;
use App\Http\Requests\BookingAdjustmentRequest;
use App\Models\Addon;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BookingAdjustmentController extends Controller
{
    public function __construct(
        protected BookingPricingService $pricingService,
        protected BookingTotalsService $totalsService,
    ) {
    }

    public function create(Booking $booking): View
    {
        return view('pages.bookings.adjust', [
            'title' => 'Penyesuaian Booking',
            'booking' => $booking->load(['villa', 'villaUnit', 'items', 'voucher']),
            'addons' => Addon::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(BookingAdjustmentRequest $request, Booking $booking): RedirectResponse
    {
        $before = [
            'check_out' => optional($booking->check_out)->format('Y-m-d'),
            'grand_total' => $booking->grand_total,
            'remaining_balance' => $booking->remaining_balance,
            'items_count' => $booking->items()->count(),
        ];

        DB::transaction(function () use ($request, $booking): void {
            $booking->load(['villaUnit.seasonalPrices', 'items', 'payments', 'voucher']);

            $newCheckOut = $request->input('extend_check_out');

            if ($newCheckOut !== null) {
                $currentCheckOut = $booking->check_out->copy();
                $targetCheckOut = Carbon::parse($newCheckOut);
                $extendedItems = $this->pricingService->buildNightItems($booking->villaUnit, $currentCheckOut, $targetCheckOut);

                foreach ($extendedItems as $item) {
                    $item['item_type'] = 'extend_night';
                    $item['notes'] = 'Extend booking';
                    $booking->items()->create($item);
                }

                $booking->update(['check_out' => $targetCheckOut->toDateString()]);
            }

            $selectedAddonIds = collect($request->input('selected_addons', []));
            $addons = Addon::query()->whereIn('id', $selectedAddonIds)->get();
            $nightsCount = max(1, $booking->check_in->diffInDays($booking->fresh()->check_out));

            foreach ($addons as $addon) {
                $quantity = $addon->charge_type === 'per_night' ? $nightsCount : 1;

                $booking->items()->create([
                    'item_type' => 'addon_adjustment',
                    'item_name' => $addon->name,
                    'reference_date' => null,
                    'quantity' => $quantity,
                    'unit_price' => (int) $addon->price,
                    'total_price' => (int) $addon->price * $quantity,
                    'notes' => 'Penyesuaian add-on',
                ]);
            }

            $booking->refresh()->load(['items', 'payments', 'voucher']);
            $summary = $this->totalsService->summarize($booking, $booking->items, $booking->payments);
            $booking->update($summary);
        });

        $booking->refresh();

        $this->auditLog(
            module: 'booking',
            action: 'adjust',
            description: 'Booking berhasil disesuaikan.',
            subject: $booking,
            before: $before,
            after: [
                'check_out' => optional($booking->check_out)->format('Y-m-d'),
                'grand_total' => $booking->grand_total,
                'remaining_balance' => $booking->remaining_balance,
                'items_count' => $booking->items()->count(),
            ],
            properties: [
                'extend_check_out' => $request->input('extend_check_out'),
                'selected_addons' => implode(', ', $request->input('selected_addons', [])),
            ],
        );

        return redirect()->route('bookings.show', $booking)->with('success', 'Booking berhasil disesuaikan dan totalnya sudah dihitung ulang.');
    }
}
