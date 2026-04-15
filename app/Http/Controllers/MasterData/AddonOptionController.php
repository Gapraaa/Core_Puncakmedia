<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddonOptionRequest;
use App\Models\Addon;
use App\Models\AddonOption;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AddonOptionController extends Controller
{
    public function create(Addon $addon): View
    {
        return view('pages.addon-options.create', [
            'title' => 'Buat Opsi Add-on',
            'addon' => $addon,
            'addonOption' => new AddonOption(['is_active' => true, 'charge_basis' => 'per_item', 'unit_label' => 'pcs']),
        ]);
    }

    public function store(AddonOptionRequest $request, Addon $addon): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $addon->options()->create($data);

        return redirect()->route('addons.show', $addon)->with('success', 'Opsi add-on berhasil dibuat.');
    }

    public function edit(Addon $addon, AddonOption $addonOption): View
    {
        abort_unless($addonOption->addon_id === $addon->id, 404);

        return view('pages.addon-options.edit', [
            'title' => 'Edit Opsi Add-on',
            'addon' => $addon,
            'addonOption' => $addonOption,
        ]);
    }

    public function update(AddonOptionRequest $request, Addon $addon, AddonOption $addonOption): RedirectResponse
    {
        abort_unless($addonOption->addon_id === $addon->id, 404);

        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $addonOption->update($data);

        return redirect()->route('addons.show', $addon)->with('success', 'Opsi add-on berhasil diperbarui.');
    }

    public function destroy(Addon $addon, AddonOption $addonOption): RedirectResponse
    {
        abort_unless($addonOption->addon_id === $addon->id, 404);

        $addonOption->delete();

        return redirect()->route('addons.show', $addon)->with('success', 'Opsi add-on berhasil dihapus.');
    }
}
