<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddonRequest;
use App\Models\Addon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AddonController extends Controller
{
    public function index(Request $request): View
    {
        $addons = Addon::query()
            ->when($request->filled('q'), fn (Builder $query): Builder => $query->where('name', 'like', '%' . trim((string) $request->string('q')) . '%'))
            ->when($request->filled('charge_type'), fn (Builder $query): Builder => $query->where('charge_type', $request->string('charge_type')))
            ->when($request->filled('is_active'), function (Builder $query) use ($request): Builder {
                return $query->where('is_active', $request->string('is_active') === '1');
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.addons.index', [
            'title' => 'Add-on',
            'addons' => $addons,
            'filters' => $request->only(['q', 'charge_type', 'is_active']),
        ]);
    }

    public function create(): View
    {
        return view('pages.addons.create', [
            'title' => 'Buat Add-on',
            'addon' => new Addon(['is_active' => true, 'charge_type' => 'per_stay']),
        ]);
    }

    public function show(Addon $addon): View
    {
        return view('pages.addons.show', [
            'title' => 'Detail Add-on',
            'addon' => $addon,
        ]);
    }

    public function store(AddonRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        Addon::query()->create($data);

        return redirect()->route('addons.index')->with('success', 'Add-on berhasil dibuat.');
    }

    public function edit(Addon $addon): View
    {
        return view('pages.addons.edit', [
            'title' => 'Edit Add-on',
            'addon' => $addon,
        ]);
    }

    public function update(AddonRequest $request, Addon $addon): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $addon->update($data);

        return redirect()->route('addons.index')->with('success', 'Add-on berhasil diperbarui.');
    }

    public function destroy(Addon $addon): RedirectResponse
    {
        $addon->delete();

        return redirect()->route('addons.index')->with('success', 'Add-on berhasil dihapus.');
    }
}
