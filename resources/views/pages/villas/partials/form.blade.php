<div class="grid grid-cols-12 gap-4 md:gap-6">
    <div class="col-span-12 xl:col-span-8">
        <x-common.component-card :title="$pageTitle" :desc="$pageDescription">
            <form method="POST" action="{{ $formAction }}" class="space-y-6" x-data="{ isResort: {{ old('is_resort', $villa->is_resort) ? 'true' : 'false' }} }">
                @csrf
                @isset($httpMethod)
                    @method($httpMethod)
                @endisset

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Villa</label>
                        <input type="text" name="name" value="{{ old('name', $villa->name) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                        @error('name')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Slug</label>
                        <input type="text" name="slug" value="{{ old('slug', $villa->slug) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                        @error('slug')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Lokasi</label>
                        <input type="text" name="location" value="{{ old('location', $villa->location) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                        @error('location')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                        <select name="status" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">
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
                                <input type="checkbox" name="brand_ids[]" value="{{ $brand->id }}" @checked(in_array($brand->id, old('brand_ids', $selectedBrands ?? []))) class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/20" />
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
                    <textarea name="description" rows="4" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">{{ old('description', $villa->description) }}</textarea>
                    @error('description')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Aturan Villa</label>
                        <textarea name="rules" rows="4" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">{{ old('rules', $villa->rules) }}</textarea>
                        @error('rules')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">URL YouTube</label>
                        <input type="url" name="youtube_url" value="{{ old('youtube_url', $villa->youtube_url) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                        @error('youtube_url')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Kelebihan</label>
                        <textarea name="pros" rows="4" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">{{ old('pros', $villa->pros) }}</textarea>
                        @error('pros')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Kekurangan</label>
                        <textarea name="cons" rows="4" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">{{ old('cons', $villa->cons) }}</textarea>
                        @error('cons')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('villas.index') }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Batal</a>
                    <button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">{{ $submitLabel }}</button>
                </div>
            </form>
        </x-common.component-card>
    </div>

    <div class="col-span-12 xl:col-span-4">
        <x-common.component-card title="Catatan Villa" desc="Jaga kebersihan data master karena modul booking, unit, dan pricing bergantung pada data ini.">
            <div class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
                <div class="rounded-xl border border-gray-100 px-4 py-3 dark:border-gray-800">Villa biasa langsung memiliki 1 unit dengan kapasitas dan harga yang diisi di form ini.</div>
                <div class="rounded-xl border border-gray-100 px-4 py-3 dark:border-gray-800">Resort memiliki beberapa unit yang dikelola terpisah lewat halaman Villa Units.</div>
                <div class="rounded-xl border border-gray-100 px-4 py-3 dark:border-gray-800">Kelebihan, kekurangan, dan aturan disimpan agar bisa dipakai ulang untuk tampilan internal maupun publik.</div>
            </div>
        </x-common.component-card>
    </div>

</div>
