<div class="grid grid-cols-12 gap-4 md:gap-6">
    <div class="col-span-12 xl:col-span-8">
        <x-common.component-card :title="$pageTitle" :desc="$pageDescription">
            <form method="POST" action="{{ $formAction }}" class="space-y-6">
                @csrf
                @isset($httpMethod) @method($httpMethod) @endisset
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Villa Unit</label>
                    <select name="villa_unit_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">
                        <option value="">Pilih villa unit</option>
                        @foreach ($villaUnits as $villaUnit)
                            <option value="{{ $villaUnit->id }}" @selected((string) old('villa_unit_id', $seasonalPrice->villa_unit_id) === (string) $villaUnit->id)>{{ $villaUnit->villa?->name }} - {{ $villaUnit->unit_name }}</option>
                        @endforeach
                    </select>
                    @error('villa_unit_id')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2" x-data="{ start: '{{ old('start_date', optional($seasonalPrice->start_date)->format('Y-m-d')) }}' }">
                    <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Mulai</label><input x-model="start" onclick="this.showPicker()" type="date" name="start_date" value="{{ old('start_date', optional($seasonalPrice->start_date)->format('Y-m-d')) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" /></div>
                    <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Selesai</label><input :min="start" onclick="this.showPicker()" type="date" name="end_date" value="{{ old('end_date', optional($seasonalPrice->end_date)->format('Y-m-d')) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" /></div>
                </div>
                <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Harga Override (Rupiah)</label><input type="number" step="1" min="0" name="price" value="{{ old('price', $seasonalPrice->price ?? 0) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" /></div>
                <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan</label><textarea name="note" rows="4" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">{{ old('note', $seasonalPrice->note) }}</textarea></div>
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('seasonal-prices.index') }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Batal</a>
                    <button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">{{ $submitLabel }}</button>
                </div>
            </form>
        </x-common.component-card>
    </div>
</div>

