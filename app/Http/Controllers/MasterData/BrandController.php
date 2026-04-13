<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\BrandRequest;
use App\Models\Brand;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function index(Request $request): View
    {
        $brands = Brand::query()
            ->withCount(['villas', 'bookings'])
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $keyword = trim((string) $request->string('q'));
                $query->where(function (Builder $innerQuery) use ($keyword): void {
                    $innerQuery
                        ->where('name', 'like', "%{$keyword}%")
                        ->orWhere('slug', 'like', "%{$keyword}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.brands.index', [
            'title' => 'Brand',
            'brands' => $brands,
            'filters' => $request->only(['q']),
        ]);
    }

    public function create(): View
    {
        return view('pages.brands.create', [
            'title' => 'Buat Brand',
            'brand' => new Brand(),
        ]);
    }

    public function show(Brand $brand): View
    {
        return view('pages.brands.show', [
            'title' => 'Detail Brand',
            'brand' => $brand->load(['villas' => fn ($query) => $query->latest()->limit(5), 'bookings' => fn ($query) => $query->latest()->limit(5)]),
        ]);
    }

    public function store(BrandRequest $request): RedirectResponse
    {
        Brand::query()->create($this->validatedData($request));

        return redirect()->route('brands.index')->with('success', 'Brand berhasil dibuat.');
    }

    public function edit(Brand $brand): View
    {
        return view('pages.brands.edit', [
            'title' => 'Edit Brand',
            'brand' => $brand,
        ]);
    }

    public function update(BrandRequest $request, Brand $brand): RedirectResponse
    {
        $brand->update($this->validatedData($request));

        return redirect()->route('brands.index')->with('success', 'Brand berhasil diperbarui.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        $brand->delete();

        return redirect()->route('brands.index')->with('success', 'Brand berhasil dihapus.');
    }

    protected function validatedData(BrandRequest $request): array
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);

        return $data;
    }
}
