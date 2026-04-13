<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\VillaUnitRequest;
use App\Models\Villa;
use App\Models\VillaUnit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VillaUnitController extends Controller
{
    public function index(Request $request): View
    {
        $villaUnits = VillaUnit::query()
            ->with('villa')
            ->whereHas('villa', fn (Builder $q) => $q->where('is_resort', true))
            ->withCount(['seasonalPrices', 'bookings'])
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $keyword = trim((string) $request->string('q'));
                $query->where(function (Builder $innerQuery) use ($keyword): void {
                    $innerQuery
                        ->where('unit_name', 'like', "%{$keyword}%")
                        ->orWhere('unit_type', 'like', "%{$keyword}%")
                        ->orWhereHas('villa', function (Builder $villaQuery) use ($keyword): void {
                            $villaQuery->where('name', 'like', "%{$keyword}%");
                        });
                });
            })
            ->when($request->filled('villa_id'), fn (Builder $query): Builder => $query->where('villa_id', $request->integer('villa_id')))
            ->when($request->filled('status'), fn (Builder $query): Builder => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.villa-units.index', [
            'title' => 'Unit Resort',
            'villaUnits' => $villaUnits,
            'villas' => Villa::query()->where('is_resort', true)->orderBy('name')->get(),
            'filters' => $request->only(['q', 'villa_id', 'status']),
        ]);
    }

    public function create(): View
    {
        return view('pages.villa-units.create', [
            'title' => 'Buat Unit Resort',
            'villaUnit' => new VillaUnit(['status' => 'active']),
            'villas' => Villa::query()->where('is_resort', true)->orderBy('name')->get(),
        ]);
    }

    public function show(VillaUnit $villaUnit): View
    {
        return view('pages.villa-units.show', [
            'title' => 'Detail Unit Resort',
            'villaUnit' => $villaUnit->load(['villa', 'seasonalPrices' => fn ($query) => $query->latest()->limit(10), 'bookings' => fn ($query) => $query->latest()->limit(5)]),
        ]);
    }

    public function store(VillaUnitRequest $request): RedirectResponse
    {
        VillaUnit::query()->create($request->validated());

        return redirect()->route('villa-units.index')->with('success', 'Unit Resort berhasil dibuat.');
    }

    public function edit(VillaUnit $villaUnit): View
    {
        return view('pages.villa-units.edit', [
            'title' => 'Edit Unit Resort',
            'villaUnit' => $villaUnit,
            'villas' => Villa::query()->where('is_resort', true)->orderBy('name')->get(),
        ]);
    }

    public function update(VillaUnitRequest $request, VillaUnit $villaUnit): RedirectResponse
    {
        $villaUnit->update($request->validated());

        return redirect()->route('villa-units.index')->with('success', 'Unit Resort berhasil diperbarui.');
    }

    public function destroy(VillaUnit $villaUnit): RedirectResponse
    {
        $villaUnit->delete();

        return redirect()->route('villa-units.index')->with('success', 'Unit Resort berhasil dihapus.');
    }
}
