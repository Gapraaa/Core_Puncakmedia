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
        $villas = Villa::query()
            ->where('is_resort', true)
            ->withCount([
                'units',
                'bookings',
                'units as active_units_count' => fn (Builder $query) => $query->where('status', 'active'),
            ])
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $keyword = trim((string) $request->string('q'));
                $query->where(fn (Builder $innerQuery) => $innerQuery
                    ->where('name', 'like', "%{$keyword}%")
                    ->orWhere('slug', 'like', "%{$keyword}%")
                    ->orWhere('location', 'like', "%{$keyword}%"));
            })
            ->when($request->filled('status'), fn (Builder $query): Builder => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.villa-units.index', [
            'title' => 'Unit Resort',
            'villas' => $villas,
            'filters' => $request->only(['q', 'villa_id', 'status']),
        ]);
    }

    public function create(): View
    {
        $selectedVilla = Villa::query()
            ->where('is_resort', true)
            ->when(request()->filled('villa_id'), fn (Builder $query): Builder => $query->whereKey(request()->integer('villa_id')))
            ->first();

        return view('pages.villa-units.create', [
            'title' => 'Buat Unit Resort',
            'villaUnit' => new VillaUnit([
                'status' => 'active',
                'villa_id' => $selectedVilla?->id,
            ]),
            'selectedVilla' => $selectedVilla,
            'villas' => Villa::query()->where('is_resort', true)->orderBy('name')->get(),
        ]);
    }

    public function listByVilla(Request $request, Villa $villa): View
    {
        abort_unless($villa->is_resort, 404);

        $villaUnits = $villa->units()
            ->withCount(['seasonalPrices', 'bookings'])
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

        return view('pages.villa-units.list', [
            'title' => 'Daftar Unit Resort',
            'villa' => $villa->loadCount(['units', 'bookings']),
            'villaUnits' => $villaUnits,
            'filters' => $request->only(['q', 'status']),
        ]);
    }

    public function createForVilla(Villa $villa): View
    {
        abort_unless($villa->is_resort, 404);

        return view('pages.villa-units.create', [
            'title' => 'Buat Unit Resort',
            'villaUnit' => new VillaUnit([
                'status' => 'active',
                'villa_id' => $villa->id,
            ]),
            'selectedVilla' => $villa,
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
        $villaUnit = VillaUnit::query()->create($request->validated());

        return redirect()->route('villa-units.list', $villaUnit->villa_id)->with('success', 'Unit Resort berhasil dibuat.');
    }

    public function edit(VillaUnit $villaUnit): View
    {
        return view('pages.villa-units.edit', [
            'title' => 'Edit Unit Resort',
            'villaUnit' => $villaUnit,
            'selectedVilla' => $villaUnit->villa,
            'villas' => Villa::query()->where('is_resort', true)->orderBy('name')->get(),
        ]);
    }

    public function update(VillaUnitRequest $request, VillaUnit $villaUnit): RedirectResponse
    {
        $villaUnit->update($request->validated());

        return redirect()->route('villa-units.list', $villaUnit->villa_id)->with('success', 'Unit Resort berhasil diperbarui.');
    }

    public function destroy(VillaUnit $villaUnit): RedirectResponse
    {
        $villaId = $villaUnit->villa_id;
        $villaUnit->delete();

        return redirect()->route('villa-units.list', $villaId)->with('success', 'Unit Resort berhasil dihapus.');
    }
}
