@php
    $initialFacilities = old('facilities', isset($villa) && $villa->relationLoaded('primaryFacilities') ? $villa->primaryFacilities->pluck('name')->all() : []);
    $initialAdditionalFacilities = old('additional_facilities', isset($villa) && $villa->relationLoaded('additionalFacilities') ? $villa->additionalFacilities->pluck('name')->all() : []);
    $initialBrandIds = old('brand_ids', $selectedBrands ?? []);
    $facilityOptions = [
        'Karaoke',
        'Private Pool',
        'Smart TV',
        'Wifi',
        'Billiard',
        'Chill Area',
        'Halaman',
        'Kitchen Set',
        'BBQ Space',
        'Furniture Aesthetic',
        'Mountain View',
    ];
@endphp

<div
    class="grid grid-cols-12 gap-4 md:gap-6"
    x-data="villaForm({{ \Illuminate\Support\Js::from([
        'isResort' => old('is_resort', $villa->is_resort),
        'isEditMode' => isset($httpMethod),
        'name' => old('name', $villa->name),
        'slug' => old('slug', $villa->slug),
        'location' => old('location', $villa->location),
        'status' => old('status', $villa->status),
        'description' => old('description', $villa->description),
        'rules' => old('rules', $villa->rules),
        'pros' => old('pros', $villa->pros),
        'cons' => old('cons', $villa->cons),
        'youtubeUrl' => old('youtube_url', $villa->youtube_url),
        'brandIds' => array_map('intval', $initialBrandIds),
        'initialData' => [
            'name' => $villa->name,
            'slug' => $villa->slug,
            'location' => $villa->location,
            'status' => $villa->status,
            'isResort' => (bool) $villa->is_resort,
            'brandIds' => array_map('intval', $selectedBrands ?? []),
            'facilities' => array_values(isset($villa) && $villa->relationLoaded('primaryFacilities') ? $villa->primaryFacilities->pluck('name')->all() : []),
            'additionalFacilities' => array_values(isset($villa) && $villa->relationLoaded('additionalFacilities') ? $villa->additionalFacilities->pluck('name')->all() : []),
            'description' => $villa->description,
        ],
        'brandOptions' => collect($brands ?? [])->map(fn ($brand) => ['id' => $brand->id, 'name' => $brand->name])->values()->all(),
        'facilityOptions' => $facilityOptions,
        'facilities' => !empty($initialFacilities) ? array_values($initialFacilities) : [''],
        'additionalFacilities' => !empty($initialAdditionalFacilities) ? array_values($initialAdditionalFacilities) : [''],
    ]) }})"
>
    <div class="col-span-12">
        <x-common.component-card :title="$pageTitle" :desc="$pageDescription">
            <form method="POST" action="{{ $formAction }}" class="space-y-6"
                data-toast-loading="Data villa sedang disimpan."
                data-toast-loading-title="{{ isset($httpMethod) ? 'Memperbarui Villa' : 'Menyimpan Villa' }}">
                @csrf
                @isset($httpMethod)
                    @method($httpMethod)
                @endisset

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Villa</label>
                        <input type="text" name="name" x-model="name" value="{{ old('name', $villa->name) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                        @error('name')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Slug</label>
                        <input type="text" name="slug" x-model="slug" value="{{ old('slug', $villa->slug) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                        @error('slug')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Lokasi</label>
                        <input type="text" name="location" x-model="location" value="{{ old('location', $villa->location) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                        @error('location')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                        <select name="status" x-model="status" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">
                            @foreach (['draft' => 'Draft', 'active' => 'Aktif', 'inactive' => 'Nonaktif'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $villa->status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Akses Brand</label>
                    <div class="flex flex-wrap gap-3">
                        @foreach($brands ?? [] as $brand)
                            <label class="inline-flex items-center cursor-pointer gap-2 rounded-lg border border-gray-200 px-4 py-2 transition hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-white/[0.03]">
                                <input type="checkbox" name="brand_ids[]" value="{{ $brand->id }}" x-model="brandIds" @checked(in_array($brand->id, old('brand_ids', $selectedBrands ?? []))) class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/20" />
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $brand->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('brand_ids')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="cursor-pointer inline-flex items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-800">
                        <input type="hidden" name="is_resort" value="0" />
                        <input type="checkbox" name="is_resort" value="1" x-model="isResort" @checked(old('is_resort', $villa->is_resort)) class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/20" />
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Tandai sebagai resort (memiliki beberapa unit)</span>
                    </label>
                </div>

                {{-- Section unit: hanya tampil untuk villa biasa (non-resort) --}}
                <div x-show="!isResort" x-transition class="space-y-4 rounded-xl border border-brand-200 bg-brand-50/30 p-5 dark:border-brand-800/50 dark:bg-brand-900/10">
                    <h4 class="text-sm font-semibold text-gray-800 dark:text-white/90">Data Unit Villa</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Villa biasa hanya memiliki 1 unit. Isi kapasitas dan harga di bawah ini.</p>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Kapasitas (orang)</label>
                            <input type="number" min="1" name="unit_capacity" value="{{ old('unit_capacity', $villaUnit->capacity ?? '') }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                            @error('unit_capacity')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Harga Weekday</label>
                            <input type="number" step="1" min="0" name="price_weekday" value="{{ old('price_weekday', $villaUnit->price_weekday ?? 0) }}" data-money class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                            @error('price_weekday')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Harga Semi Weekend</label>
                            <input type="number" step="1" min="0" name="price_semi_weekend" value="{{ old('price_semi_weekend', $villaUnit->price_semi_weekend ?? 0) }}" data-money class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                            @error('price_semi_weekend')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Harga Weekend</label>
                            <input type="number" step="1" min="0" name="price_weekend" value="{{ old('price_weekend', $villaUnit->price_weekend ?? 0) }}" data-money class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                            @error('price_weekend')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <div x-show="isResort" x-transition class="rounded-xl border border-yellow-200 bg-yellow-50/30 px-5 py-4 dark:border-yellow-800/50 dark:bg-yellow-900/10">
                    <p class="text-sm text-yellow-700 dark:text-yellow-300">Resort memiliki beberapa unit. Setelah membuat villa, tambahkan unit di menu <strong>Villa Units</strong>.</p>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Deskripsi</label>
                    <textarea name="description" x-model="description" rows="4" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">{{ old('description', $villa->description) }}</textarea>
                    @error('description')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Fasilitas Villa</label>
                        <div class="space-y-3">
                            <div class="flex flex-wrap gap-2 rounded-xl border border-gray-200 bg-gray-50/40 p-3 dark:border-gray-800 dark:bg-white/[0.02]">
                                <template x-for="option in facilityOptions" :key="option">
                                    <button
                                        type="button"
                                        @click="toggleFacility(option)"
                                        class="rounded-full border px-3 py-1.5 text-xs font-medium transition"
                                        :class="hasFacility(option)
                                            ? 'border-brand-500 bg-brand-500 text-white'
                                            : 'border-gray-300 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.04]'"
                                        x-text="option"
                                    ></button>
                                </template>
                            </div>

                            <div class="flex items-center gap-3">
                                <input type="text" x-model="customFacility" @keydown.enter.prevent="addCustomFacility()" placeholder="Tambah fasilitas custom" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                                <button type="button" @click="addCustomFacility()" class="inline-flex h-11 shrink-0 items-center justify-center rounded-lg border border-brand-200 px-4 text-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:border-brand-800/60 dark:text-brand-300 dark:hover:bg-brand-500/10">Tambah</button>
                            </div>

                            <div class="space-y-2">
                                <template x-for="(facility, index) in facilities.filter(item => item !== '')" :key="`primary-${facility}-${index}`">
                                    <div
                                        class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-3 py-3 dark:border-gray-800 dark:bg-white/[0.02]"
                                        draggable="true"
                                        @dragstart="dragStartList('primary', index)"
                                        @dragover.prevent
                                        @drop="dropList('primary', index)"
                                    >
                                        <input type="hidden" :name="'facilities[' + index + ']'" :value="facility" />
                                        <span class="cursor-move text-gray-400">⋮⋮</span>
                                        <span class="flex-1 text-sm font-medium text-gray-800 dark:text-white/90" x-text="facility"></span>
                                        <button type="button" @click="removeFacility('primary', index)" class="inline-flex shrink-0 items-center justify-center rounded-lg border border-error-200 px-3 py-2 text-sm font-medium text-error-600 transition hover:bg-error-50 dark:border-error-800 dark:text-error-400 dark:hover:bg-error-500/10">Hapus</button>
                                    </div>
                                </template>
                                <p x-show="facilities.filter(item => item !== '').length === 0" class="text-xs text-gray-500 dark:text-gray-400">Belum ada fasilitas dipilih.</p>
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Pilih dari daftar utama, tambah custom jika perlu, lalu atur urutannya dengan drag and drop.</p>
                        @error('facilities')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        @error('facilities.*')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Fasilitas Tambahan</label>
                        <div class="space-y-3">
                            <template x-for="(facility, index) in additionalFacilities" :key="`additional-${index}`">
                                <div
                                    class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-3 py-3 dark:border-gray-800 dark:bg-white/[0.02]"
                                    draggable="true"
                                    @dragstart="dragStartList('additional', index)"
                                    @dragover.prevent
                                    @drop="dropList('additional', index)"
                                >
                                    <span class="cursor-move text-gray-400">⋮⋮</span>
                                    <input type="text" :name="'additional_facilities[' + index + ']'" x-model="additionalFacilities[index]" placeholder="Contoh: Alat BBQ" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                                    <button type="button" @click="removeFacility('additional', index)" class="inline-flex h-11 shrink-0 items-center justify-center rounded-lg border border-error-200 px-3 text-sm font-medium text-error-600 transition hover:bg-error-50 dark:border-error-800 dark:text-error-400 dark:hover:bg-error-500/10">Hapus</button>
                                </div>
                            </template>
                            <button type="button" @click="addFacility('additional')" class="inline-flex items-center rounded-lg border border-brand-200 px-4 py-2.5 text-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:border-brand-800/60 dark:text-brand-300 dark:hover:bg-brand-500/10">Tambah Fasilitas Tambahan</button>
                        </div>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Gunakan untuk bonus atau fasilitas ekstra yang tetap melekat pada villa.</p>
                        @error('additional_facilities')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        @error('additional_facilities.*')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Aturan Villa</label>
                        <textarea name="rules" x-model="rules" rows="4" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">{{ old('rules', $villa->rules) }}</textarea>
                        @error('rules')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">URL YouTube</label>
                        <input type="url" name="youtube_url" x-model="youtubeUrl" value="{{ old('youtube_url', $villa->youtube_url) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                        @error('youtube_url')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Kelebihan</label>
                        <textarea name="pros" x-model="pros" rows="4" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">{{ old('pros', $villa->pros) }}</textarea>
                        @error('pros')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Kekurangan</label>
                        <textarea name="cons" x-model="cons" rows="4" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">{{ old('cons', $villa->cons) }}</textarea>
                        @error('cons')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('villas.index') }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Batal</a>
                    @if (isset($httpMethod))
                        <button type="button" @click="openUpdateConfirmation()" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">{{ $submitLabel }}</button>
                    @else
                        <button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">{{ $submitLabel }}</button>
                    @endif
                </div>
            </form>
        </x-common.component-card>
    </div>

@if (isset($httpMethod))
    <div
        x-cloak
        x-show="showUpdateConfirmation"
        x-transition.opacity
        @keydown.escape.window="closeUpdateConfirmation()"
        class="fixed inset-0 z-99999 flex items-center justify-center bg-gray-900/60 px-4 py-6"
    >
        <div @click.away="closeUpdateConfirmation()" class="max-h-[90vh] w-full max-w-5xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">
            <div class="flex items-start justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Konfirmasi Perbarui Villa</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Periksa perubahan data sebelum villa diperbarui di sistem.</p>
                </div>
                <button type="button" @click="closeUpdateConfirmation()" class="rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-500 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Tutup</button>
            </div>

            <div class="max-h-[calc(90vh-150px)] overflow-y-auto px-6 py-5">
                <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                    <div class="rounded-xl border border-gray-100 dark:border-gray-800">
                        <div class="border-b border-gray-100 px-4 py-3 dark:border-gray-800">
                            <h4 class="font-medium text-gray-800 dark:text-white/90">Sebelum</h4>
                        </div>
                        <div class="space-y-3 px-4 py-4 text-sm text-gray-600 dark:text-gray-300">
                            <div class="flex justify-between gap-3"><span>Nama</span><span class="text-right font-medium text-gray-800 dark:text-white/90" x-text="initialData.name || '-'"></span></div>
                            <div class="flex justify-between gap-3"><span>Slug</span><span class="text-right font-medium text-gray-800 dark:text-white/90" x-text="initialData.slug || '-'"></span></div>
                            <div class="flex justify-between gap-3"><span>Lokasi</span><span class="text-right font-medium text-gray-800 dark:text-white/90" x-text="initialData.location || '-'"></span></div>
                            <div class="flex justify-between gap-3"><span>Status</span><span class="text-right font-medium text-gray-800 dark:text-white/90" x-text="formatStatus(initialData.status)"></span></div>
                            <div class="flex justify-between gap-3"><span>Tipe</span><span class="text-right font-medium text-gray-800 dark:text-white/90" x-text="formatResort(initialData.isResort)"></span></div>
                            <div class="flex justify-between gap-3"><span>Brand</span><span class="text-right font-medium text-gray-800 dark:text-white/90" x-text="formatBrands(initialData.brandIds)"></span></div>
                            <div class="space-y-1"><span>Fasilitas Villa</span><p class="font-medium text-gray-800 dark:text-white/90" x-text="formatList(initialData.facilities)"></p></div>
                            <div class="space-y-1"><span>Fasilitas Tambahan</span><p class="font-medium text-gray-800 dark:text-white/90" x-text="formatList(initialData.additionalFacilities)"></p></div>
                            <div class="space-y-1"><span>Deskripsi</span><p class="font-medium text-gray-800 dark:text-white/90" x-text="initialData.description || '-'"></p></div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-brand-200 bg-brand-50/30 dark:border-brand-800/40 dark:bg-brand-500/10">
                        <div class="border-b border-brand-100 px-4 py-3 dark:border-brand-800/40">
                            <h4 class="font-medium text-gray-800 dark:text-white/90">Sesudah</h4>
                        </div>
                        <div class="space-y-3 px-4 py-4 text-sm text-gray-600 dark:text-gray-300">
                            <div class="flex justify-between gap-3"><span>Nama</span><span class="text-right font-medium text-gray-800 dark:text-white/90" x-text="name || '-'"></span></div>
                            <div class="flex justify-between gap-3"><span>Slug</span><span class="text-right font-medium text-gray-800 dark:text-white/90" x-text="slug || '-'"></span></div>
                            <div class="flex justify-between gap-3"><span>Lokasi</span><span class="text-right font-medium text-gray-800 dark:text-white/90" x-text="location || '-'"></span></div>
                            <div class="flex justify-between gap-3"><span>Status</span><span class="text-right font-medium text-gray-800 dark:text-white/90" x-text="formatStatus(status)"></span></div>
                            <div class="flex justify-between gap-3"><span>Tipe</span><span class="text-right font-medium text-gray-800 dark:text-white/90" x-text="formatResort(isResort)"></span></div>
                            <div class="flex justify-between gap-3"><span>Brand</span><span class="text-right font-medium text-gray-800 dark:text-white/90" x-text="formatBrands(brandIds)"></span></div>
                            <div class="space-y-1"><span>Fasilitas Villa</span><p class="font-medium text-gray-800 dark:text-white/90" x-text="formatList(facilities)"></p></div>
                            <div class="space-y-1"><span>Fasilitas Tambahan</span><p class="font-medium text-gray-800 dark:text-white/90" x-text="formatList(additionalFacilities)"></p></div>
                            <div class="space-y-1"><span>Deskripsi</span><p class="font-medium text-gray-800 dark:text-white/90" x-text="description || '-'"></p></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-gray-800">
                <button type="button" @click="closeUpdateConfirmation()" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Kembali Edit</button>
                <button type="button" @click="submitConfirmedUpdate()" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Konfirmasi & Perbarui</button>
            </div>
        </div>
    </div>
@endif
</div>
