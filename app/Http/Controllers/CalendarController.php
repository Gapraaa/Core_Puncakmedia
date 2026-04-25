<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Booking;
use App\Models\VillaUnit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(Request $request): View
    {
        $initialMonth = trim((string) $request->string('month', now()->format('Y-m')));
        $monthDate = Carbon::createFromFormat('Y-m', $initialMonth)->startOfMonth();

        $units = VillaUnit::query()
            ->with([
                'villa.brands',
                'bookings' => fn ($query) => $query->orderBy('check_in'),
            ])
            ->whereHas('villa', function (Builder $query) use ($request): void {
                $query
                    ->when($request->filled('q'), function (Builder $innerQuery) use ($request): void {
                        $keyword = trim((string) $request->string('q'));

                        $innerQuery->where(function (Builder $searchQuery) use ($keyword): void {
                            $searchQuery
                                ->where('name', 'like', "%{$keyword}%")
                                ->orWhere('slug', 'like', "%{$keyword}%")
                                ->orWhere('location', 'like', "%{$keyword}%");
                        });
                    })
                    ->when($request->filled('brand_id'), function (Builder $innerQuery) use ($request): void {
                        $brandId = $request->integer('brand_id');

                        $innerQuery->whereHas('brands', fn (Builder $brandQuery) => $brandQuery->whereKey($brandId));
                    });
            })
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $keyword = trim((string) $request->string('q'));

                $query->where(function (Builder $innerQuery) use ($keyword): void {
                    $innerQuery
                        ->where('unit_name', 'like', "%{$keyword}%")
                        ->orWhere('unit_type', 'like', "%{$keyword}%");
                });
            })
            ->where('status', 'active')
            ->get()
            ->sortBy([
                fn (VillaUnit $unit) => $unit->villa?->name,
                fn (VillaUnit $unit) => $unit->unit_name,
            ])
            ->values();

        $cards = $units->map(function (VillaUnit $unit) use ($monthDate): array {
            $villa = $unit->villa;
            $brands = $villa?->brands?->pluck('name')->implode(', ') ?: '-';

            return [
                'villa' => $villa,
                'unit' => $unit,
                'title' => $villa && $villa->is_resort ? $villa->name . ' - ' . $unit->unit_name : $villa?->name,
                'subtitle' => $villa && $villa->is_resort ? 'UNIT RESORT' : 'VILLA',
                'search_blob' => collect([
                    $villa?->name,
                    $unit->unit_name,
                    $villa?->location,
                    $unit->unit_type,
                    $brands,
                ])->filter()->implode(' | '),
                'brands' => $brands,
                'location' => $villa?->location ?: '-',
                'capacity' => $unit->capacity,
                'booking_count' => $unit->bookings->count(),
                'initial_month' => $monthDate->format('Y-m'),
                'create_booking_url' => $villa ? route('bookings.create', [
                    'villa' => $villa,
                    'villa_unit_id' => $unit->id,
                ]) : null,
                'bookings' => $unit->bookings->map(fn (Booking $booking): array => [
                    'id' => $booking->id,
                    'booking_code' => $booking->booking_code,
                    'guest_name' => $booking->guest_name,
                    'payment_status' => $booking->payment_status,
                    'check_in' => $booking->check_in->format('Y-m-d'),
                    'check_out' => $booking->check_out->format('Y-m-d'),
                ])->values()->all(),
            ];
        });

        return view('pages.calendar.index', [
            'title' => 'Kalender Booking',
            'cards' => $cards,
            'brands' => Brand::query()->orderBy('name')->get(),
            'filters' => $request->only(['q', 'brand_id']),
            'weekdayLabels' => ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
        ]);
    }
}
