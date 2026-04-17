<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\SeasonalPriceRequest;
use App\Models\Villa;
use App\Models\SeasonalPrice;
use App\Models\VillaUnit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeasonalPriceController extends Controller
{
    public function index(Request $request): View
    {
        $villas = Villa::query()
            ->withCount([
                'units',
                'units as high_season_count' => fn (Builder $query) => $query
                    ->join('seasonal_prices', 'seasonal_prices.villa_unit_id', '=', 'villa_units.id'),
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

        return view('pages.seasonal-prices.index', [
            'title' => 'Harga High Season',
            'villas' => $villas,
            'filters' => $request->only(['q', 'type']),
        ]);
    }

    public function create(): View
    {
        return view('pages.seasonal-prices.create', [
            'title' => 'Buat Harga High Season',
            'seasonalPrice' => new SeasonalPrice(),
            'selectedVilla' => null,
            'selectedVillaUnit' => null,
            'villaUnits' => VillaUnit::query()->with('villa')->orderBy('unit_name')->get(),
        ]);
    }

    public function showVilla(Villa $villa): RedirectResponse|View
    {
        if ($villa->is_resort) {
            return redirect()->route('seasonal-prices.units', $villa);
        }

        $villaUnit = $villa->units()->firstOrFail();

        return $this->unitPriceListView($villa, $villaUnit, request());
    }

    public function showVillaUnits(Villa $villa, Request $request): View
    {
        abort_unless($villa->is_resort, 404);

        $villaUnits = $villa->units()
            ->withCount('seasonalPrices')
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

        return view('pages.seasonal-prices.units', [
            'title' => 'Pilih Unit High Season',
            'villa' => $villa->loadCount(['units']),
            'villaUnits' => $villaUnits,
            'filters' => $request->only(['q', 'status']),
        ]);
    }

    public function showUnit(Villa $villa, VillaUnit $villaUnit, Request $request): View
    {
        abort_unless($villaUnit->villa_id === $villa->id, 404);

        return $this->unitPriceListView($villa, $villaUnit, $request);
    }

    public function createForVilla(Villa $villa): RedirectResponse|View
    {
        if ($villa->is_resort) {
            return redirect()->route('seasonal-prices.units', $villa);
        }

        $villaUnit = $villa->units()->firstOrFail();

        return $this->createForSelectedUnit($villa, $villaUnit);
    }

    public function createForUnit(Villa $villa, VillaUnit $villaUnit): View
    {
        abort_unless($villaUnit->villa_id === $villa->id, 404);

        return $this->createForSelectedUnit($villa, $villaUnit);
    }

    public function store(SeasonalPriceRequest $request): RedirectResponse
    {
        $seasonalPrice = SeasonalPrice::query()->create($request->validated());

        $this->auditLog(
            module: 'master-data',
            action: 'create',
            description: 'Harga high season baru berhasil dibuat.',
            subject: $seasonalPrice,
            after: $seasonalPrice->only(['villa_unit_id', 'start_date', 'end_date', 'price', 'note']),
        );

        return redirect($this->seasonalPriceRedirectRoute($seasonalPrice))
            ->with('success', 'Harga high season berhasil dibuat.');
    }

    public function edit(SeasonalPrice $seasonalPrice): View
    {
        $seasonalPrice->load('villaUnit.villa');

        return view('pages.seasonal-prices.edit', [
            'title' => 'Edit Harga High Season',
            'seasonalPrice' => $seasonalPrice,
            'selectedVilla' => $seasonalPrice->villaUnit?->villa,
            'selectedVillaUnit' => $seasonalPrice->villaUnit,
            'villaUnits' => VillaUnit::query()->with('villa')->orderBy('unit_name')->get(),
        ]);
    }

    public function update(SeasonalPriceRequest $request, SeasonalPrice $seasonalPrice): RedirectResponse
    {
        $before = $seasonalPrice->only(['villa_unit_id', 'start_date', 'end_date', 'price', 'note']);
        $seasonalPrice->update($request->validated());

        $this->auditLog(
            module: 'master-data',
            action: 'update',
            description: 'Harga high season berhasil diperbarui.',
            subject: $seasonalPrice,
            before: $before,
            after: $seasonalPrice->fresh()->only(['villa_unit_id', 'start_date', 'end_date', 'price', 'note']),
        );

        return redirect($this->seasonalPriceRedirectRoute($seasonalPrice))
            ->with('success', 'Harga high season berhasil diperbarui.');
    }

    public function destroy(SeasonalPrice $seasonalPrice): RedirectResponse
    {
        $redirectTo = $this->seasonalPriceRedirectRoute($seasonalPrice);
        $before = $seasonalPrice->only(['villa_unit_id', 'start_date', 'end_date', 'price', 'note']);
        $seasonalPrice->delete();

        $this->auditLog(
            module: 'master-data',
            action: 'delete',
            description: 'Harga high season dihapus dari sistem.',
            subject: $seasonalPrice,
            before: $before,
        );

        return redirect($redirectTo)->with('success', 'Harga high season berhasil dihapus.');
    }

    protected function unitPriceListView(Villa $villa, VillaUnit $villaUnit, Request $request): View
    {
        $seasonalPrices = SeasonalPrice::query()
            ->where('villa_unit_id', $villaUnit->id)
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $keyword = trim((string) $request->string('q'));
                $query->where('note', 'like', "%{$keyword}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.seasonal-prices.list', [
            'title' => 'Daftar Harga High Season',
            'villa' => $villa,
            'villaUnit' => $villaUnit->loadCount('seasonalPrices'),
            'seasonalPrices' => $seasonalPrices,
            'filters' => $request->only(['q']),
        ]);
    }

    protected function createForSelectedUnit(Villa $villa, VillaUnit $villaUnit): View
    {
        return view('pages.seasonal-prices.create', [
            'title' => 'Buat Harga High Season',
            'seasonalPrice' => new SeasonalPrice(['villa_unit_id' => $villaUnit->id]),
            'selectedVilla' => $villa,
            'selectedVillaUnit' => $villaUnit,
            'villaUnits' => VillaUnit::query()->with('villa')->orderBy('unit_name')->get(),
        ]);
    }

    protected function seasonalPriceRedirectRoute(SeasonalPrice $seasonalPrice): string
    {
        $seasonalPrice->loadMissing('villaUnit.villa');
        $villaUnit = $seasonalPrice->villaUnit;
        $villa = $villaUnit?->villa;

        if (! $villaUnit || ! $villa) {
            return route('seasonal-prices.index');
        }

        return $villa->is_resort
            ? route('seasonal-prices.unit', [$villa, $villaUnit])
            : route('seasonal-prices.villa', $villa);
    }
}
