<?php

namespace App\Http\Controllers;

use App\BookingPricingService;
use App\BookingTotalsService;
use App\InvoiceTotalsService;
use App\Http\Requests\BookingRequest;
use App\Models\Addon;
use App\Models\AddonOption;
use App\Models\Booking;
use App\Models\Brand;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Villa;
use App\Models\VillaUnit;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        protected BookingPricingService $pricingService,
        protected BookingTotalsService $totalsService,
        protected InvoiceTotalsService $invoiceTotalsService,
    ) {
    }

    public function indexVillas(Request $request): View
    {
        return view('pages.bookings.villas', [
            'title' => 'Daftar Booking per Villa',
            'villas' => $this->getVillaSelectionPaginator($request),
            'filters' => $request->only('q'),
            'mode' => 'list',
            'pageTitle' => 'Daftar Booking Villa',
            'pageDescription' => 'Pilih villa untuk melihat antrean per reservasi dan ringkasan booking yang sudah masuk.',
            'searchAction' => route('bookings.index'),
            'primaryActionLabel' => 'Lihat Booking',
            'secondaryActionLabel' => 'Buat Booking',
            'primaryActionRouteName' => 'bookings.list',
            'secondaryActionRouteName' => 'bookings.create',
            'emptyStateMessage' => 'Belum ada data villa.',
        ]);
    }

    public function selectVillaForCreate(Request $request): View
    {
        return view('pages.bookings.villas', [
            'title' => 'Pilih Villa untuk Booking Baru',
            'villas' => $this->getVillaSelectionPaginator($request),
            'filters' => $request->only('q'),
            'mode' => 'create',
            'pageTitle' => 'Buat Booking Baru',
            'pageDescription' => 'Pilih villa terlebih dahulu agar booking baru langsung dibuat dalam konteks villa yang benar.',
            'searchAction' => route('bookings.selection'),
            'primaryActionLabel' => 'Buat Booking',
            'secondaryActionLabel' => 'Lihat Booking',
            'primaryActionRouteName' => 'bookings.create',
            'secondaryActionRouteName' => 'bookings.list',
            'emptyStateMessage' => 'Belum ada data villa yang bisa dipakai untuk membuat booking.',
        ]);
    }

    public function index(Request $request, Villa $villa): View
    {
        $bookings = $villa->bookings()
            ->with(['brand', 'villaUnit'])
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
            ->when($request->filled('date_from'), fn (Builder $query): Builder => $query->whereDate('check_in', '>=', $request->string('date_from')))
            ->when($request->filled('date_to'), fn (Builder $query): Builder => $query->whereDate('check_in', '<=', $request->string('date_to')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.bookings.index', [
            'title' => 'Daftar Booking - ' . $villa->name,
            'villa' => $villa,
            'bookings' => $bookings,
            'filters' => $request->only(['q', 'payment_status', 'booking_status', 'date_from', 'date_to']),
        ]);
    }

    public function create(Request $request, Villa $villa): View
    {
        $villaUnits = $villa->units()
            ->with('seasonalPrices')
            ->orderBy('unit_name')
            ->get();
        $requestedUnitId = $request->filled('villa_unit_id')
            ? $villaUnits->firstWhere('id', (int) $request->integer('villa_unit_id'))?->id
            : null;
        $prefillCheckIn = $request->date('check_in')?->format('Y-m-d');
        $prefillCheckOut = $request->date('check_out')?->format('Y-m-d');
        $selectedUnitId = old('villa_unit_id', $requestedUnitId ?: ($villa->is_resort ? null : $villaUnits->first()?->id));
        $vouchers = Voucher::query()->where('is_active', true)->orderBy('code')->get();
        $addons = Addon::query()
            ->where('is_active', true)
            ->with(['options' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('name')
            ->get();
        $addonChoiceGroups = $addons->map(function (Addon $addon): array {
            $choices = $addon->options->map(fn (AddonOption $option): array => [
                'id' => 'option:' . $option->id,
                'name' => $option->name,
                'price' => (int) $option->price,
                'charge_basis' => $option->charge_basis,
                'unit_label' => $option->unit_label,
            ])->values();

            if ($choices->isEmpty()) {
                $choices = collect([[
                    'id' => 'addon:' . $addon->id,
                    'name' => $addon->name,
                    'price' => (int) $addon->price,
                    'charge_basis' => $addon->charge_type === 'per_night' ? 'per_item_per_night' : 'per_stay',
                    'unit_label' => 'pcs',
                ]]);
            }

            return [
                'id' => $addon->id,
                'name' => $addon->name,
                'choices' => $choices->all(),
            ];
        })->values();

        return view('pages.bookings.create', [
            'title' => 'Buat Booking - ' . $villa->name,
            'villa' => $villa,
            'brands' => Brand::query()->orderBy('name')->get(),
            'villaUnits' => $villaUnits,
            'vouchers' => $vouchers,
            'addons' => $addons,
            'addonChoiceGroups' => $addonChoiceGroups,
            'bookingPreviewConfig' => [
                'villa' => [
                    'id' => $villa->id,
                    'name' => $villa->name,
                    'is_resort' => $villa->is_resort,
                ],
                'units' => $villaUnits->map(fn (VillaUnit $unit): array => [
                    'id' => $unit->id,
                    'name' => $unit->unit_name,
                    'price_weekday' => (int) $unit->price_weekday,
                    'price_semi_weekend' => (int) $unit->price_semi_weekend,
                    'price_weekend' => (int) $unit->price_weekend,
                    'seasonal_prices' => $unit->seasonalPrices->map(fn ($price): array => [
                        'start_date' => $price->start_date->format('Y-m-d'),
                        'end_date' => $price->end_date->format('Y-m-d'),
                        'price' => (int) $price->price,
                        'note' => $price->note,
                    ])->values()->all(),
                ])->values()->all(),
                'addons' => $addonChoiceGroups->flatMap(function (array $group): array {
                    return collect($group['choices'])->map(fn (array $choice): array => $choice + ['category_name' => $group['name']])->all();
                })->values()->all(),
                'vouchers' => $vouchers->map(fn (Voucher $voucher): array => [
                    'id' => $voucher->id,
                    'code' => $voucher->code,
                    'amount' => (int) $voucher->amount,
                    'discount_type' => $voucher->discount_type,
                    'minimum_transaction' => (int) $voucher->minimum_transaction,
                ])->values()->all(),
                'initialUnitId' => $selectedUnitId,
                'initialSelectedAddonIds' => old('selected_addon_choices', old('selected_addons', [])),
                'initialAddonQuantities' => old('addon_choice_quantities', old('addon_quantities', [])),
                'initialCheckIn' => old('check_in', $prefillCheckIn ?? ''),
                'initialCheckOut' => old('check_out', $prefillCheckOut ?? ''),
                'initialVoucherId' => old('voucher_id', ''),
                'initialShowAddons' => collect(old('selected_addon_choices', old('selected_addons', [])))->isNotEmpty(),
                'todayDate' => now()->format('Y-m-d'),
                'initialManualDiscountAmount' => (int) old('manual_discount_amount', 0),
                'initialMarkupAmount' => (int) old('markup_amount', 0),
                'initialDpAmount' => (int) old('dp_amount', 0),
            ],
        ]);
    }

    public function store(BookingRequest $request, Villa $villa): RedirectResponse
    {
        $data = $request->validated();

        $booking = DB::transaction(function () use ($data, $request, $villa): Booking {
            $villaUnit = $villa->units()->with('seasonalPrices')->findOrFail($data['villa_unit_id']);
            $checkIn = Carbon::parse($data['check_in']);
            $checkOut = Carbon::parse($data['check_out']);
            $nightItems = $this->pricingService->buildNightItems($villaUnit, $checkIn, $checkOut);
            $nightsCount = max(1, $checkIn->diffInDays($checkOut));
            $addonItems = $this->buildAddonItems($data, $nightsCount);

            $markupAmount = (int) ($data['markup_amount'] ?? 0);

            $markupItems = $markupAmount > 0
                ? collect([[
                    'item_type' => 'markup',
                    'item_name' => 'Markup harga',
                    'reference_date' => null,
                    'quantity' => 1,
                    'unit_price' => $markupAmount,
                    'total_price' => $markupAmount,
                    'notes' => $data['markup_reason'] ?? 'Markup manual saat booking',
                ]])
                : collect();

            $items = $nightItems->concat($addonItems)->concat($markupItems)->values();

            $bookingDraft = new Booking([
                'manual_discount_amount' => (int) ($data['manual_discount_amount'] ?? 0),
                'voucher_id' => $data['voucher_id'] ?? null,
            ]);

            if (! empty($data['voucher_id'])) {
                $bookingDraft->setRelation('voucher', Voucher::query()->find($data['voucher_id']));
            }

            $summary = $this->totalsService->summarize($bookingDraft, $items, collect());

            if ((int) $request->integer('dp_amount') > (int) $summary['grand_total']) {
                throw ValidationException::withMessages([
                    'dp_amount' => 'Nominal DP tidak boleh lebih besar dari grand total booking.',
                ]);
            }

            $booking = Booking::query()->create([
                'invoice_no' => $this->generateInvoiceNumber(),
                'booking_code' => $this->generateBookingCode(),
                'guest_name' => $data['guest_name'],
                'guest_phone' => $data['guest_phone'],
                'brand_id' => $data['brand_id'],
                'villa_id' => $villa->id,
                'villa_unit_id' => $data['villa_unit_id'],
                'check_in' => $data['check_in'],
                'check_out' => $data['check_out'],
                'final_payment_due_date' => $this->calculateFinalPaymentDueDate($checkIn, now()),
                'voucher_id' => $data['voucher_id'] ?? null,
                'manual_discount_amount' => (int) ($data['manual_discount_amount'] ?? 0),
                'manual_discount_reason' => $data['manual_discount_reason'] ?? null,
                'guest_link_token' => Str::random(40),
                'booking_status' => 'confirmed',
                'created_by' => $request->user()?->id,
            ]);

            $mainInvoice = $booking->invoices()->create([
                'invoice_number' => $booking->invoice_no,
                'label' => 'INVOICE UTAMA',
                'invoice_type' => 'combined',
            ]);

            foreach ($items as $item) {
                $booking->items()->create($item + ['invoice_id' => $mainInvoice->id]);
            }

            // Create DP payment
            Payment::query()->create([
                'booking_id' => $booking->id,
                'invoice_id' => $mainInvoice->id,
                'amount' => $request->integer('dp_amount'),
                'payment_method' => $data['payment_method'],
                'received_by' => $data['received_by'],
                'note' => $data['payment_note'] ?? 'Down Payment (DP)',
                'proof_image' => null,
                'paid_at' => now(),
                'created_by' => $request->user()?->id,
            ]);

            $mainInvoice->load(['items', 'payments']);
            $mainInvoice->update($this->invoiceTotalsService->summarize($mainInvoice, $mainInvoice->items, $mainInvoice->payments));

            $booking->load(['items', 'payments', 'voucher']);
            $summary = $this->totalsService->summarize($booking, $booking->items, $booking->payments);
            $booking->update($summary);

            return $booking->fresh(['brand', 'villa', 'villaUnit']);
        });

        $mainInvoice = $booking->invoices()->oldest()->first();

        $this->auditLog(
            module: 'booking',
            action: 'create',
            description: 'Booking baru berhasil dibuat.',
            subject: $booking,
            after: [
                'booking_code' => $booking->booking_code,
                'guest_name' => $booking->guest_name,
                'guest_phone' => $booking->guest_phone,
                'villa' => $booking->villa?->name,
                'unit' => $booking->villaUnit?->unit_name,
                'check_in' => optional($booking->check_in)->format('Y-m-d'),
                'check_out' => optional($booking->check_out)->format('Y-m-d'),
                'final_payment_due_date' => optional($booking->final_payment_due_date)->format('Y-m-d'),
                'grand_total' => $booking->grand_total,
                'total_paid' => $booking->total_paid,
                'remaining_balance' => $booking->remaining_balance,
            ],
            properties: [
                'invoice_number' => $mainInvoice?->invoice_number,
            ],
        );

        return redirect()
            ->route('bookings.show', $booking)
            ->with('success', 'Booking berhasil dibuat dengan DP tercatat.')
            ->with('auto_download_document_url', $mainInvoice ? route('documents.invoices.show', ['invoice' => $mainInvoice, 'download' => 1]) : null);
    }

    public function show(Booking $booking): View
    {
        return view('pages.bookings.show', [
            'title' => 'Detail Booking',
            'booking' => $booking->load([
                'brand',
                'villa',
                'villaUnit',
                'items.invoice',
                'payments.invoice',
                'voucher',
                'invoices.items',
                'invoices.payments',
            ]),
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

    protected function calculateFinalPaymentDueDate(Carbon $checkIn, Carbon $dpPaidAt): Carbon
    {
        $dpDate = $dpPaidAt->copy()->startOfDay();
        $checkInDate = $checkIn->copy()->startOfDay();
        $sevenDaysBeforeCheckIn = $checkInDate->copy()->subDays(7);

        if ($dpDate->lte($sevenDaysBeforeCheckIn)) {
            return $checkInDate->copy()->subDays(3);
        }

        return $checkInDate;
    }

    protected function buildAddonItems(array $data, int $nightsCount)
    {
        $selectedChoices = collect($data['selected_addon_choices'] ?? []);
        $choiceQuantities = collect($data['addon_choice_quantities'] ?? []);

        if ($selectedChoices->isEmpty() && ! empty($data['selected_addons'])) {
            $addons = Addon::query()->whereIn('id', $data['selected_addons'])->get();
            $legacyQuantities = collect($data['addon_quantities'] ?? []);

            return $addons->map(function (Addon $addon) use ($legacyQuantities, $nightsCount): array {
                $baseQuantity = max(1, (int) ($legacyQuantities->get((string) $addon->id) ?? $legacyQuantities->get($addon->id) ?? 1));
                $quantity = $addon->charge_type === 'per_night' ? $baseQuantity * $nightsCount : $baseQuantity;
                $totalPrice = (int) $addon->price * $quantity;

                return [
                    'item_type' => 'addon',
                    'item_name' => $addon->name,
                    'reference_date' => null,
                    'quantity' => $quantity,
                    'unit_price' => (int) $addon->price,
                    'total_price' => $totalPrice,
                    'notes' => $addon->charge_type === 'per_night'
                        ? "Add-on per malam ({$baseQuantity} pcs x {$nightsCount} malam)"
                        : "Add-on per stay ({$baseQuantity} pcs)",
                ];
            });
        }

        return $selectedChoices->map(function (string $choiceId) use ($choiceQuantities, $nightsCount): ?array {
            [$type, $id] = array_pad(explode(':', $choiceId, 2), 2, null);
            $baseQuantity = max(1, (int) ($choiceQuantities->get($choiceId) ?? 1));

            if ($type === 'option' && $id) {
                $option = AddonOption::query()->with('addon')->find($id);

                if (! $option) {
                    return null;
                }

                return $this->makeAddonItemFromChoice($option->name, (int) $option->price, $option->charge_basis, $option->unit_label, $baseQuantity, $nightsCount, $option->addon?->name);
            }

            if ($type === 'addon' && $id) {
                $addon = Addon::query()->find($id);

                if (! $addon) {
                    return null;
                }

                $chargeBasis = $addon->charge_type === 'per_night' ? 'per_item_per_night' : 'per_stay';

                return $this->makeAddonItemFromChoice($addon->name, (int) $addon->price, $chargeBasis, 'pcs', $baseQuantity, $nightsCount, $addon->name);
            }

            return null;
        })->filter()->values();
    }

    protected function makeAddonItemFromChoice(string $name, int $price, string $chargeBasis, string $unitLabel, int $baseQuantity, int $nightsCount, ?string $categoryName = null): array
    {
        $quantity = match ($chargeBasis) {
            'per_night' => $nightsCount,
            'per_item_per_night', 'per_person_per_night' => $baseQuantity * $nightsCount,
            default => $baseQuantity,
        };

        $notes = match ($chargeBasis) {
            'per_night' => "Per malam ({$nightsCount} malam)",
            'per_item_per_night' => "{$baseQuantity} {$unitLabel} x {$nightsCount} malam",
            'per_person_per_night' => "{$baseQuantity} {$unitLabel} x {$nightsCount} malam",
            'per_person_per_stay' => "{$baseQuantity} {$unitLabel} per stay",
            'per_item' => "{$baseQuantity} {$unitLabel}",
            default => "{$baseQuantity} {$unitLabel} per stay",
        };

        return [
            'item_type' => 'addon',
            'item_name' => $categoryName && $categoryName !== $name ? "{$categoryName} - {$name}" : $name,
            'reference_date' => null,
            'quantity' => $quantity,
            'unit_price' => $price,
            'total_price' => $price * $quantity,
            'notes' => $notes,
        ];
    }

    protected function getVillaSelectionPaginator(Request $request)
    {
        return Villa::query()
            ->with(['brands', 'units'])
            ->withCount('bookings')
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $keyword = trim((string) $request->string('q'));
                $query->where(function (Builder $innerQuery) use ($keyword): void {
                    $innerQuery
                        ->where('name', 'like', "%{$keyword}%")
                        ->orWhere('location', 'like', "%{$keyword}%");
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();
    }
}
