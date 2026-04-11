<div class="grid grid-cols-12 gap-4 md:gap-6">
    <div class="col-span-12 xl:col-span-8">
        <x-common.component-card :title="$pageTitle" :desc="$pageDescription">
            <form method="POST" action="{{ $formAction }}" class="space-y-6">
                @csrf
                @isset($httpMethod) @method($httpMethod) @endisset
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Villa</label>
                    <select name="villa_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">
                        <option value="">Pilih villa</option>
                        @foreach ($villas as $villa)
                            <option value="{{ $villa->id }}" @selected((string) old('villa_id', $villaUnit->villa_id) === (string) $villa->id)>{{ $villa->name }}</option>
                        @endforeach
                    </select>
                    @error('villa_id')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Unit</label>
                        <input type="text" name="unit_name" value="{{ old('unit_name', $villaUnit->unit_name) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                        @error('unit_name')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tipe Unit</label>
                        <input type="text" name="unit_type" value="{{ old('unit_type', $villaUnit->unit_type) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Kapasitas</label>
                        <input type="number" min="0" name="capacity" value="{{ old('capacity', $villaUnit->capacity ?? 0) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                        <select name="status" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">
                            <option value="active" @selected(old('status', $villaUnit->status) === 'active')>Aktif</option>
                            <option value="inactive" @selected(old('status', $villaUnit->status) === 'inactive')>Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Harga Weekday (Rupiah)</label><input type="number" step="1" min="0" name="price_weekday" value="{{ old('price_weekday', $villaUnit->price_weekday ?? 0) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" /></div>
                    <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Harga Semi Weekend (Rupiah)</label><input type="number" step="1" min="0" name="price_semi_weekend" value="{{ old('price_semi_weekend', $villaUnit->price_semi_weekend ?? 0) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" /></div>
                    <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Harga Weekend (Rupiah)</label><input type="number" step="1" min="0" name="price_weekend" value="{{ old('price_weekend', $villaUnit->price_weekend ?? 0) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" /></div>
                </div>
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('villa-units.index') }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Batal</a>
                    <button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">{{ $submitLabel }}</button>
                </div>
            </form>
        </x-common.component-card>
    </div>
</div>

