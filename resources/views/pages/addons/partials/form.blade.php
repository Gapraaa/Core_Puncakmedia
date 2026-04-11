<div class="grid grid-cols-12 gap-4 md:gap-6">
    <div class="col-span-12 xl:col-span-8">
        <x-common.component-card :title="$pageTitle" :desc="$pageDescription">
            <form method="POST" action="{{ $formAction }}" class="space-y-6">
                @csrf
                @isset($httpMethod) @method($httpMethod) @endisset
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Add-on</label><input type="text" name="name" value="{{ old('name', $addon->name) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" /></div>
                    <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Harga (Rupiah)</label><input type="number" step="1" min="0" name="price" value="{{ old('price', $addon->price ?? 0) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" /></div>
                </div>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tipe Charge</label><select name="charge_type" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90"><option value="per_stay" @selected(old('charge_type', $addon->charge_type) === 'per_stay')>Per stay</option><option value="per_night" @selected(old('charge_type', $addon->charge_type) === 'per_night')>Per malam</option></select></div>
                    <div class="flex items-end"><label class="inline-flex items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-800"><input type="hidden" name="is_active" value="0" /><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $addon->is_active)) class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/20" /><span class="text-sm font-medium text-gray-700 dark:text-gray-300">Aktif</span></label></div>
                </div>
                <div class="flex items-center justify-end gap-3"><a href="{{ route('addons.index') }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Batal</a><button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">{{ $submitLabel }}</button></div>
            </form>
        </x-common.component-card>
    </div>
</div>
