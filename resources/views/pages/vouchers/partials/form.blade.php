<div class="grid grid-cols-12 gap-4 md:gap-6">
    <div class="col-span-12 xl:col-span-8">
        <x-common.component-card :title="$pageTitle" :desc="$pageDescription">
            <form method="POST" action="{{ $formAction }}" class="space-y-6">
                @csrf
                @isset($httpMethod) @method($httpMethod) @endisset
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Kode Voucher</label><input type="text" name="code" value="{{ old('code', $voucher->code) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" /></div>
                    <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tipe Diskon</label><select name="discount_type" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90"><option value="fixed" @selected(old('discount_type', $voucher->discount_type) === 'fixed')>Nominal tetap</option><option value="percentage" @selected(old('discount_type', $voucher->discount_type) === 'percentage')>Persentase</option></select></div>
                </div>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nilai Diskon</label><input type="number" step="1" min="0" name="amount" value="{{ old('amount', $voucher->amount ?? 0) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" /></div>
                    <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Minimum Transaksi</label><input type="number" step="1" min="0" name="minimum_transaction" value="{{ old('minimum_transaction', $voucher->minimum_transaction ?? 0) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" /></div>
                    <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Berlaku Sampai</label><input onclick="this.showPicker()" type="date" name="valid_until" value="{{ old('valid_until', optional($voucher->valid_until)->format('Y-m-d')) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" /></div>
                </div>
                <div class="flex items-end"><label class="inline-flex items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-800"><input type="hidden" name="is_active" value="0" /><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $voucher->is_active)) class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/20" /><span class="text-sm font-medium text-gray-700 dark:text-gray-300">Aktif</span></label></div>
                <div class="flex items-center justify-end gap-3"><a href="{{ route('vouchers.index') }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Batal</a><button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">{{ $submitLabel }}</button></div>
            </form>
        </x-common.component-card>
    </div>
</div>
