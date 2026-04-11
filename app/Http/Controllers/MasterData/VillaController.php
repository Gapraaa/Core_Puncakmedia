<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\VillaRequest;
use App\Models\Villa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class VillaController extends Controller
{
    public function index(Request $request): View
    {
        $villas = Villa::query()
            ->withCount(['units', 'bookings'])
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $keyword = trim((string) $request->string('q'));
                $query->where(function (Builder $innerQuery) use ($keyword): void {
                    $innerQuery
                        ->where('name', 'like', "%{$keyword}%")
                        ->orWhere('slug', 'like', "%{$keyword}%")
                        ->orWhere('location', 'like', "%{$keyword}%");
                });
            })
            ->when($request->filled('status'), fn (Builder $query): Builder => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.villas.index', [
            'title' => 'Villa',
            'villas' => $villas,
            'filters' => $request->only(['q', 'status']),
        ]);
    }

    public function create(): View
    {
        return view('pages.villas.create', [
            'title' => 'Buat Villa',
            'villa' => new Villa(['status' => 'draft']),
        ]);
    }

    public function show(Villa $villa): View
    {
        return view('pages.villas.show', [
            'title' => 'Detail Villa',
            'villa' => $villa->load(['units' => fn ($query) => $query->latest()->limit(10), 'brands', 'bookings' => fn ($query) => $query->latest()->limit(5)]),
        ]);
    }

    public function store(VillaRequest $request): RedirectResponse
    {
        Villa::query()->create($this->validatedData($request));

        return redirect()->route('villas.index')->with('success', 'Villa berhasil dibuat.');
    }

    public function edit(Villa $villa): View
    {
        return view('pages.villas.edit', [
            'title' => 'Edit Villa',
            'villa' => $villa,
        ]);
    }

    public function update(VillaRequest $request, Villa $villa): RedirectResponse
    {
        $villa->update($this->validatedData($request));

        return redirect()->route('villas.index')->with('success', 'Villa berhasil diperbarui.');
    }

    public function destroy(Villa $villa): RedirectResponse
    {
        $villa->delete();

        return redirect()->route('villas.index')->with('success', 'Villa berhasil dihapus.');
    }

    protected function validatedData(VillaRequest $request): array
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $data['is_resort'] = $request->boolean('is_resort');

        return $data;
    }
}
