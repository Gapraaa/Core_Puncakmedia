<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\VillaRequest;
use App\Models\Brand;
use App\Models\Villa;
use App\Models\VillaUnit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class VillaController extends Controller
{
    public function index(Request $request): View
    {
        $villas = Villa::query()
            ->with(['brands'])
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
            'villaUnit' => new VillaUnit(),
            'brands' => Brand::query()->orderBy('name')->get(),
        ]);
    }

    public function show(Villa $villa): View
    {
        return view('pages.villas.show', [
            'title' => 'Detail Villa',
            'villa' => $villa->load([
                'units' => fn ($query) => $query->latest()->limit(10),
                'brands',
                'bookings' => fn ($query) => $query->latest()->limit(5),
                'primaryFacilities',
                'additionalFacilities',
            ]),
        ]);
    }

    public function store(VillaRequest $request): RedirectResponse
    {
        $villa = DB::transaction(function () use ($request): Villa {
            $villa = Villa::query()->create($this->validatedData($request));
            if ($request->has('brand_ids')) {
                $villa->brands()->sync($request->input('brand_ids'));
            }

            $this->syncFacilities($villa, 'primary', $request->input('facilities', []));
            $this->syncFacilities($villa, 'additional', $request->input('additional_facilities', []));

            // Villa biasa: auto-create 1 unit
            if (! $request->boolean('is_resort')) {
                $villa->units()->create([
                    'unit_name' => $villa->name,
                    'unit_type' => 'private',
                    'capacity' => $request->integer('unit_capacity'),
                    'price_weekday' => $request->integer('price_weekday'),
                    'price_semi_weekend' => $request->integer('price_semi_weekend'),
                    'price_weekend' => $request->integer('price_weekend'),
                    'status' => 'active',
                ]);
            }
            return $villa;
        });

        $this->auditLog(
            module: 'master-data',
            action: 'create',
            description: 'Villa baru berhasil dibuat.',
            subject: $villa,
            after: $villa->fresh()->only(['name', 'slug', 'location', 'is_resort', 'status']),
            properties: [
                'brand_ids' => implode(', ', $request->input('brand_ids', [])),
                'facilities' => implode(', ', $this->cleanFacilityItems($request->input('facilities', []))),
                'additional_facilities' => implode(', ', $this->cleanFacilityItems($request->input('additional_facilities', []))),
            ],
        );

        return redirect()->route('villas.index')->with('success', 'Villa berhasil dibuat.');
    }

    public function edit(Villa $villa): View
    {
        // Load unit pertama untuk villa biasa
        $villaUnit = $villa->is_resort ? new VillaUnit() : ($villa->units()->first() ?? new VillaUnit());

        return view('pages.villas.edit', [
            'title' => 'Edit Villa',
            'villa' => $villa->load(['primaryFacilities', 'additionalFacilities']),
            'villaUnit' => $villaUnit,
            'brands' => Brand::query()->orderBy('name')->get(),
            'selectedBrands' => $villa->brands()->pluck('brands.id')->toArray(),
        ]);
    }

    public function update(VillaRequest $request, Villa $villa): RedirectResponse
    {
        $before = $villa->only(['name', 'slug', 'location', 'is_resort', 'status']);

        DB::transaction(function () use ($request, $villa): void {
            $villa->update($this->validatedData($request));
            $villa->brands()->sync($request->input('brand_ids', []));
            $this->syncFacilities($villa, 'primary', $request->input('facilities', []));
            $this->syncFacilities($villa, 'additional', $request->input('additional_facilities', []));

            // Villa biasa: sync unit pertama
            if (! $request->boolean('is_resort')) {
                $unit = $villa->units()->first();

                $unitData = [
                    'unit_name' => $villa->name,
                    'unit_type' => 'private',
                    'capacity' => $request->integer('unit_capacity'),
                    'price_weekday' => $request->integer('price_weekday'),
                    'price_semi_weekend' => $request->integer('price_semi_weekend'),
                    'price_weekend' => $request->integer('price_weekend'),
                    'status' => 'active',
                ];

                if ($unit) {
                    $unit->update($unitData);
                } else {
                    $villa->units()->create($unitData);
                }
            }
        });

        $this->auditLog(
            module: 'master-data',
            action: 'update',
            description: 'Villa berhasil diperbarui.',
            subject: $villa,
            before: $before,
            after: $villa->fresh()->only(['name', 'slug', 'location', 'is_resort', 'status']),
            properties: [
                'brand_ids' => implode(', ', $request->input('brand_ids', [])),
                'facilities' => implode(', ', $this->cleanFacilityItems($request->input('facilities', []))),
                'additional_facilities' => implode(', ', $this->cleanFacilityItems($request->input('additional_facilities', []))),
            ],
        );

        return redirect()->route('villas.index')->with('success', 'Villa berhasil diperbarui.');
    }

    public function destroy(Villa $villa): RedirectResponse
    {
        $before = $villa->only(['name', 'slug', 'location', 'is_resort', 'status']);
        $villa->delete();

        $this->auditLog(
            module: 'master-data',
            action: 'delete',
            description: 'Villa dihapus dari sistem.',
            subject: $villa,
            before: $before,
        );

        return redirect()->route('villas.index')->with('success', 'Villa berhasil dihapus.');
    }

    protected function validatedData(VillaRequest $request): array
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $data['is_resort'] = $request->boolean('is_resort');

        // Remove unit fields and relation fields from villa data
        unset($data['unit_capacity'], $data['price_weekday'], $data['price_semi_weekend'], $data['price_weekend'], $data['brand_ids'], $data['facilities'], $data['additional_facilities']);

        return $data;
    }

    protected function syncFacilities(Villa $villa, string $type, array $items): void
    {
        $villa->facilities()->where('type', $type)->delete();

        $payload = collect($this->cleanFacilityItems($items))
            ->values()
            ->map(fn (string $name, int $index): array => [
                'type' => $type,
                'name' => $name,
                'sort_order' => $index,
            ])
            ->all();

        if (! empty($payload)) {
            $villa->facilities()->createMany($payload);
        }
    }

    protected function cleanFacilityItems(array $items): array
    {
        return collect($items)
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
