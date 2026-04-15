<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Villa;
use App\Models\VillaUnit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $villas = Villa::query()
            ->withCount([
                'units',
                'bookings',
                'bookings as invoices_count' => fn (Builder $query) => $query
                    ->join('invoices', 'invoices.booking_id', '=', 'bookings.id'),
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

        return view('pages.invoices.index', [
            'title' => 'Invoice',
            'villas' => $villas,
            'filters' => $request->only(['q', 'type']),
        ]);
    }

    public function showVilla(Villa $villa): RedirectResponse|View
    {
        if ($villa->is_resort) {
            return redirect()->route('invoices.units', $villa);
        }

        $villaUnit = $villa->units()->firstOrFail();

        return $this->unitInvoiceListView($villa, $villaUnit, request());
    }

    public function showVillaUnits(Villa $villa, Request $request): View
    {
        abort_unless($villa->is_resort, 404);

        $villaUnits = $villa->units()
            ->withCount([
                'bookings',
                'bookings as invoices_count' => fn (Builder $query) => $query
                    ->join('invoices', 'invoices.booking_id', '=', 'bookings.id'),
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

        return view('pages.invoices.units', [
            'title' => 'Pilih Unit Invoice',
            'villa' => $villa->loadCount('units'),
            'villaUnits' => $villaUnits,
            'filters' => $request->only(['q', 'status']),
        ]);
    }

    public function showUnit(Villa $villa, VillaUnit $villaUnit, Request $request): View
    {
        abort_unless($villaUnit->villa_id === $villa->id, 404);

        return $this->unitInvoiceListView($villa, $villaUnit, $request);
    }

    public function show(Invoice $invoice): View
    {
        return view('pages.invoices.show', [
            'title' => 'Detail Invoice',
            'invoice' => $invoice->load([
                'booking.brand',
                'booking.villa',
                'booking.villaUnit',
                'items',
                'payments',
            ]),
        ]);
    }

    protected function unitInvoiceListView(Villa $villa, VillaUnit $villaUnit, Request $request): View
    {
        $invoices = Invoice::query()
            ->with(['booking.villaUnit', 'payments'])
            ->whereHas('booking', fn (Builder $query) => $query->where('villa_unit_id', $villaUnit->id))
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $keyword = trim((string) $request->string('q'));
                $query->where(function (Builder $innerQuery) use ($keyword): void {
                    $innerQuery
                        ->where('invoice_number', 'like', "%{$keyword}%")
                        ->orWhere('label', 'like', "%{$keyword}%")
                        ->orWhereHas('booking', function (Builder $bookingQuery) use ($keyword): void {
                            $bookingQuery
                                ->where('booking_code', 'like', "%{$keyword}%")
                                ->orWhere('guest_name', 'like', "%{$keyword}%");
                        });
                });
            })
            ->when($request->filled('payment_status'), fn (Builder $query): Builder => $query->where('payment_status', $request->string('payment_status')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.invoices.list', [
            'title' => 'Daftar Invoice',
            'villa' => $villa,
            'villaUnit' => $villaUnit->loadCount('bookings'),
            'invoices' => $invoices,
            'filters' => $request->only(['q', 'payment_status']),
        ]);
    }
}
