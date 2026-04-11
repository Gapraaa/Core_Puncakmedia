<?php

namespace App\Http\Controllers;

use App\BookingPricingService;
use App\BookingTotalsService;
use App\Http\Requests\BookingRequest;
use App\Models\Addon;
use App\Models\Booking;
use App\Models\Brand;
use App\Models\Villa;
use App\Models\VillaUnit;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        protected BookingPricingService $pricingService,
        protected BookingTotalsService $totalsService,
    ) {
    }

    public function index(Request $request): View
    {
        $bookings = Booking::query()
            ->with(['brand', 'villa', 'villaUnit'])
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $keyword = trim((string) $request->string('q'));

                $query->where(function (Builder $innerQuery) use ($keyword): void {
                    $innerQuery
                        ->where('booking_code', 'like', "%{$keyword}%")
                        ->orWhere('invoice_no', 'like', "%{$keyword}%")
                        ->orWhere('guest_name', 'like', "%{$keyword}%")
                        ->orWhere('guest_phone', 'like', "%{$keyword}%");
                });
            })
            ->when($request->filled('payment_status'), fn (Builder $query): Builder => $query->where('payment_status', $request->string('payment_status')))
            ->when($request->filled('booking_status'), fn (Builder $query): Builder => $query->where('booking_status', $request->string('booking_status')))
            ->when($request->filled('brand_id'), fn (Builder $query): Builder => $query->where('brand_id', $request->integer('brand_id')))
            ->when($request->filled('date_from'), fn (Builder $query): Builder => $query->whereDate('check_in', '>=', $request->string('date_from')))
            ->when($request->filled('date_to'), fn (Builder $query): Builder => $query->whereDate('check_in', '<=', $request->string('date_to')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.bookings.index', [
            'title' => 'Daftar Booking',
            'bookings' => $bookings,
            'brands' => Brand::query()->orderBy('name')->get(),
            'filters' => $request->only(['q', 'payment_status', 'booking_status', 'brand_id', 'date_from', 'date_to']),
        ]);
    }

    public function create(): View
    {
        return view('pages.bookings.create', [
            'title' => 'Buat Booking',
            'brands' => Brand::query()->orderBy('name')->get(),
            'villas' => Villa::query()->orderBy('name')->get(),
            'villaUnits' => VillaUnit::query()->with('villa')->orderBy('unit_name')->get(),
            'vouchers' => Voucher::query()->where('is_active', true)->orderBy('code')->get(),
            'addons' => Addon::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(BookingRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $booking = DB::transaction(function () use ($data): Booking {
            $villaUnit = VillaUnit::query()->with('seasonalPrices')->findOrFail($data['villa_unit_id']);
            $selectedAddonIds = collect($data['selected_addons'] ?? []);
            $addons = Addon::query()->whereIn('id', $selectedAddonIds)->get();

            $checkIn = Carbon::parse($data['check_in']);
            $checkOut = Carbon::parse($data['check_out']);
            $nightItems = $this->pricingService->buildNightItems($villaUnit, $checkIn, $checkOut);
            $nightsCount = max(1, $checkIn->diffInDays($checkOut));

            $addonItems = $addons->map(function (Addon $addon) use ($nightsCount): array {
                $quantity = $addon->charge_type === 'per_night' ? $nightsCount : 1;
                $totalPrice = (int) $addon->price * $quantity;

                return [
                    'item_type' => 'addon',
                    'item_name' => $addon->name,
                    'reference_date' => null,
                    'quantity' => $quantity,
                    'unit_price' => (int) $addon->price,
                    'total_price' => $totalPrice,
                    'notes' => $addon->charge_type === 'per_night' ? 'Add-on per malam' : 'Add-on per stay',
                ];
            });

            $items = $nightItems->concat($addonItems)->values();

            $booking = Booking::query()->create([
                'invoice_no' => $this->generateInvoiceNumber(),
                'booking_code' => $this->generateBookingCode(),
                'guest_name' => $data['guest_name'],
                'guest_phone' => $data['guest_phone'],
                'brand_id' => $data['brand_id'],
                'villa_id' => $data['villa_id'],
                'villa_unit_id' => $data['villa_unit_id'],
                'check_in' => $data['check_in'],
                'check_out' => $data['check_out'],
                'voucher_id' => $data['voucher_id'] ?? null,
                'manual_discount_amount' => (int) ($data['manual_discount_amount'] ?? 0),
                'manual_discount_reason' => $data['manual_discount_reason'] ?? null,
                'guest_link_token' => Str::random(40),
                'created_by' => null,
            ]);

            foreach ($items as $item) {
                $booking->items()->create($item);
            }

            $booking->load(['items', 'payments', 'voucher']);
            $summary = $this->totalsService->summarize($booking, $booking->items, $booking->payments);
            $booking->update($summary);

            return $booking->fresh(['brand', 'villa', 'villaUnit']);
        });

        return redirect()->route('bookings.show', $booking)->with('success', 'Booking berhasil dibuat dengan kalkulasi item dan total awal.');
    }

    public function show(Booking $booking): View
    {
        return view('pages.bookings.show', [
            'title' => 'Detail Booking',
            'booking' => $booking->load(['brand', 'villa', 'villaUnit', 'items', 'payments', 'voucher']),
        ]);
    }

    protected function generateInvoiceNumber(): string
    {
        return 'INV-' . now()->format('Ymd-His') . '-' . strtoupper(Str::random(4));
    }

    protected function generateBookingCode(): string
    {
        return 'BOOK-' . strtoupper(Str::random(8));
    }
}
