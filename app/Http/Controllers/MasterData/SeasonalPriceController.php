<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\SeasonalPriceRequest;
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
        $seasonalPrices = SeasonalPrice::query()
            ->with('villaUnit.villa')
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $keyword = trim((string) $request->string('q'));
                $query->where(function (Builder $innerQuery) use ($keyword): void {
                    $innerQuery
                        ->where('note', 'like', "%{$keyword}%")
                        ->orWhereHas('villaUnit', function (Builder $unitQuery) use ($keyword): void {
                            $unitQuery
                                ->where('unit_name', 'like', "%{$keyword}%")
                                ->orWhereHas('villa', fn (Builder $villaQuery): Builder => $villaQuery->where('name', 'like', "%{$keyword}%"));
                        });
                });
            })
            ->when($request->filled('villa_unit_id'), fn (Builder $query): Builder => $query->where('villa_unit_id', $request->integer('villa_unit_id')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.seasonal-prices.index', [
            'title' => 'Harga Musiman',
            'seasonalPrices' => $seasonalPrices,
            'villaUnits' => VillaUnit::query()->with('villa')->orderBy('unit_name')->get(),
            'filters' => $request->only(['q', 'villa_unit_id']),
        ]);
    }

    public function create(): View
    {
        return view('pages.seasonal-prices.create', [
            'title' => 'Buat Harga Musiman',
            'seasonalPrice' => new SeasonalPrice(),
            'villaUnits' => VillaUnit::query()->with('villa')->orderBy('unit_name')->get(),
        ]);
    }

    public function show(SeasonalPrice $seasonalPrice): View
    {
        return view('pages.seasonal-prices.show', [
            'title' => 'Detail Harga Musiman',
            'seasonalPrice' => $seasonalPrice->load('villaUnit.villa'),
        ]);
    }

    public function store(SeasonalPriceRequest $request): RedirectResponse
    {
        SeasonalPrice::query()->create($request->validated());

        return redirect()->route('seasonal-prices.index')->with('success', 'Harga musiman berhasil dibuat.');
    }

    public function edit(SeasonalPrice $seasonalPrice): View
    {
        return view('pages.seasonal-prices.edit', [
            'title' => 'Edit Harga Musiman',
            'seasonalPrice' => $seasonalPrice,
            'villaUnits' => VillaUnit::query()->with('villa')->orderBy('unit_name')->get(),
        ]);
    }

    public function update(SeasonalPriceRequest $request, SeasonalPrice $seasonalPrice): RedirectResponse
    {
        $seasonalPrice->update($request->validated());

        return redirect()->route('seasonal-prices.index')->with('success', 'Harga musiman berhasil diperbarui.');
    }

    public function destroy(SeasonalPrice $seasonalPrice): RedirectResponse
    {
        $seasonalPrice->delete();

        return redirect()->route('seasonal-prices.index')->with('success', 'Harga musiman berhasil dihapus.');
    }
}
