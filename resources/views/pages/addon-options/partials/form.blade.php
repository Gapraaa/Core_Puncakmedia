<div class="grid grid-cols-12 gap-4 md:gap-6">
    <div class="col-span-12 xl:col-span-8">
        <x-common.component-card :title="$pageTitle" :desc="$pageDescription">
            <form method="POST" action="{{ $formAction }}" class="space-y-6"
                data-toast-loading="Opsi add-on sedang disimpan."
                data-toast-loading-title="{{ isset($httpMethod) ? 'Memperbarui Opsi Add-on' : 'Menyimpan Opsi Add-on' }}">
                @csrf
                @isset($httpMethod) @method($httpMethod) @endisset

                <div class="rounded-xl border border-gray-200 bg-gray-50/50 px-4 py-3 text-sm text-gray-700 dark:border-gray-800 dark:bg-white/[0.02] dark:text-gray-300">
                    Kategori add-on: <span class="font-medium text-gray-800 dark:text-white/90">{{ $addon->name }}</span>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Opsi</label><input type="text" name="name" value="{{ old('name', $addonOption->name) }}" placeholder="Contoh: Grill Paket A" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />@error('name')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror</div>
                    <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Harga</label><input type="number" step="1" min="0" name="price" value="{{ old('price', $addonOption->price ?? 0) }}" data-money class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />@error('price')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror</div>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Basis Charge</label><select name="charge_basis" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90"><option value="per_item" @selected(old('charge_basis', $addonOption->charge_basis) === 'per_item')>Per item</option><option value="per_stay" @selected(old('charge_basis', $addonOption->charge_basis) === 'per_stay')>Per stay</option><option value="per_night" @selected(old('charge_basis', $addonOption->charge_basis) === 'per_night')>Per malam</option><option value="per_item_per_night" @selected(old('charge_basis', $addonOption->charge_basis) === 'per_item_per_night')>Per item per malam</option><option value="per_person_per_night" @selected(old('charge_basis', $addonOption->charge_basis) === 'per_person_per_night')>Per orang per malam</option><option value="per_person_per_stay" @selected(old('charge_basis', $addonOption->charge_basis) === 'per_person_per_stay')>Per orang per stay</option></select>@error('charge_basis')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror</div>
                    <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Satuan</label><input type="text" name="unit_label" value="{{ old('unit_label', $addonOption->unit_label ?? 'pcs') }}" placeholder="pcs / pax / orang / paket" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />@error('unit_label')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror</div>
                    <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Urutan</label><input type="number" min="0" name="sort_order" value="{{ old('sort_order', $addonOption->sort_order ?? 0) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />@error('sort_order')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror</div>
                </div>

                <div class="flex items-end"><label class="inline-flex items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-800"><input type="hidden" name="is_active" value="0" /><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $addonOption->is_active)) class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/20" /><span class="text-sm font-medium text-gray-700 dark:text-gray-300">Aktif</span></label></div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('addons.show', $addon) }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Batal</a>
                    <button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">{{ $submitLabel }}</button>
                </div>
            </form>
        </x-common.component-card>
    </div>
</div>
