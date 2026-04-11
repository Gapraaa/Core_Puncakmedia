<div class="grid grid-cols-12 gap-4 md:gap-6">
    <div class="col-span-12 xl:col-span-8">
        <x-common.component-card title="Form Booking" desc="Input booking awal dengan nightly pricing, voucher, add-on, dan diskon manual.">
            <form method="POST" action="{{ route('bookings.store') }}" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Brand</label>
                        <select name="brand_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">
                            <option value="">Pilih brand</option>
                            @foreach ($brands as $brand)<option value="{{ $brand->id }}" @selected(old('brand_id') == $brand->id)>{{ $brand->name }}</option>@endforeach
                        </select>
                        @error('brand_id')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Villa</label>
                        <select name="villa_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">
                            <option value="">Pilih villa</option>
                            @foreach ($villas as $villa)<option value="{{ $villa->id }}" @selected(old('villa_id') == $villa->id)>{{ $villa->name }}</option>@endforeach
                        </select>
                        @error('villa_id')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Villa Unit</label>
                        <select name="villa_unit_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">
                            <option value="">Pilih villa unit</option>
                            @foreach ($villaUnits as $villaUnit)<option value="{{ $villaUnit->id }}" @selected(old('villa_unit_id') == $villaUnit->id)>{{ $villaUnit->villa?->name }} - {{ $villaUnit->unit_name }}</option>@endforeach
                        </select>
                        @error('villa_unit_id')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Tamu</label><input type="text" name="guest_name" value="{{ old('guest_name') }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />@error('guest_name')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror</div>
                    <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">No. Telepon</label><input type="text" name="guest_phone" value="{{ old('guest_phone') }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />@error('guest_phone')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror</div>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Check-in</label><input type="date" name="check_in" value="{{ old('check_in') }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />@error('check_in')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror</div>
                    <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Check-out</label><input type="date" name="check_out" value="{{ old('check_out') }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />@error('check_out')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror</div>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Voucher</label>
                        <select name="voucher_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">
                            <option value="">Tanpa voucher</option>
                            @foreach ($vouchers as $voucher)<option value="{{ $voucher->id }}" @selected(old('voucher_id') == $voucher->id)>{{ $voucher->code }}</option>@endforeach
                        </select>
                    </div>
                    <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Diskon Manual (Rupiah)</label><input type="number" step="1" min="0" name="manual_discount_amount" value="{{ old('manual_discount_amount', 0) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" /></div>
                </div>

                <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Alasan Diskon Manual</label><textarea name="manual_discount_reason" rows="3" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">{{ old('manual_discount_reason') }}</textarea>@error('manual_discount_reason')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror</div>

                <div>
                    <label class="mb-3 block text-sm font-medium text-gray-700 dark:text-gray-300">Pilih Add-ons</label>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        @foreach ($addons as $addon)
                            <label class="flex items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 dark:border-gray-800">
                                <input type="checkbox" name="selected_addons[]" value="{{ $addon->id }}" @checked(collect(old('selected_addons', []))->contains($addon->id)) class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/20" />
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $addon->name }} <span class="text-gray-500">({{ $addon->charge_type === 'per_night' ? 'per malam' : 'per stay' }})</span></span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3"><a href="{{ route('bookings.index') }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Batal</a><button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Simpan Booking</button></div>
            </form>
        </x-common.component-card>
    </div>

    <div class="col-span-12 xl:col-span-4">
        <x-common.component-card title="Catatan Booking" desc="Versi awal flow booking ini langsung membuat booking_items dan total awal.">
            <div class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
                <div class="rounded-xl border border-gray-100 px-4 py-3 dark:border-gray-800">Nightly pricing dihitung dari weekday, semi weekend, weekend, lalu dioverride oleh harga musiman jika cocok.</div>
                <div class="rounded-xl border border-gray-100 px-4 py-3 dark:border-gray-800">Add-on per malam akan dikalikan jumlah malam, sedangkan per stay hanya satu kali charge.</div>
                <div class="rounded-xl border border-gray-100 px-4 py-3 dark:border-gray-800">Booking baru akan mulai sebagai draft atau pending sampai flow payment dibangun lebih lanjut.</div>
            </div>
        </x-common.component-card>
    </div>
</div>

