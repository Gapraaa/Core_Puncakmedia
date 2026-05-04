<div x-data="bookingForm(@js($bookingPreviewConfig))" class="space-y-6">
    <div>
        <x-common.component-card title="Form Booking" desc="Isi data booking dalam satu halaman, cek ringkasan harga singkat, lalu pastikan lagi di modal konfirmasi sebelum booking disimpan.">
            <form method="POST" action="{{ route('bookings.store', $villa) }}" class="space-y-6" data-toast-loading="Booking baru sedang disimpan dan invoice sedang disiapkan." data-toast-loading-title="Menyimpan Booking">
                @csrf

                {{-- Villa & Brand --}}
                <div class="ops-panel-soft space-y-4">
                    <div>
                        <h3 class="ops-section-title text-base">Identitas Properti</h3>
                        <p class="ops-section-desc mt-1">Pilih brand dan pastikan unit yang dipakai sudah benar sebelum lanjut ke data tamu.</p>
                    </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Brand</label>
                        <select name="brand_id" class="ops-input">
                            <option value="">Pilih brand</option>
                            @foreach ($brands as $brand)<option value="{{ $brand->id }}" @selected(old('brand_id', request('brand_id')) == $brand->id)>{{ $brand->name }}</option>@endforeach
                        </select>
                        @error('brand_id')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Villa</label>
                        <input type="hidden" name="villa_id" value="{{ $villa->id }}">
                        <input type="text" value="{{ $villa->name }}" readonly disabled class="ops-input !bg-gray-50 !text-gray-500 dark:!bg-gray-800 dark:!text-gray-400" />
                        @error('villa_id')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Unit</label>
                        @if ($villa->is_resort)
                            <select name="villa_unit_id" x-model="selectedUnitId" class="ops-input">
                                <option value="">Pilih unit</option>
                                @foreach ($villaUnits as $villaUnit)<option value="{{ $villaUnit->id }}" @selected(old('villa_unit_id', request('villa_unit_id')) == $villaUnit->id)>{{ $villaUnit->unit_name }}</option>@endforeach
                            </select>
                        @else
                            <input type="hidden" name="villa_unit_id" x-model="selectedUnitId">
                            <input type="text" :value="selectedUnit ? selectedUnit.name : 'Unit belum tersedia'" readonly disabled class="ops-input !bg-gray-50 !text-gray-500 dark:!bg-gray-800 dark:!text-gray-400" />
                        @endif
                        @error('villa_unit_id')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                </div>
                </div>

                {{-- Data Tamu --}}
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Tamu</label><input type="text" name="guest_name" value="{{ old('guest_name') }}" class="ops-input" />@error('guest_name')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror</div>
                    <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">No. Telepon</label><input type="text" name="guest_phone" inputmode="numeric" oninput="this.value=this.value.replace(/\D/g,'')" value="{{ old('guest_phone') }}" class="ops-input" />@error('guest_phone')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror</div>
                </div>

                {{-- Tanggal --}}
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Check-in</label><input x-model="checkIn" onclick="this.showPicker()" type="date" name="check_in" value="{{ old('check_in', request('check_in')) }}" class="ops-input" />@error('check_in')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror</div>
                    <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Check-out</label><input x-model="checkOut" :min="checkIn" onclick="this.showPicker()" type="date" name="check_out" value="{{ old('check_out', request('check_out')) }}" class="ops-input" />@error('check_out')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror</div>
                </div>

                {{-- Add-ons --}}
                <div class="space-y-4 rounded-xl border border-gray-200 bg-gray-50/40 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Add-ons</label>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Bagian ini opsional. Tampilkan hanya jika tamu memang menambahkan layanan atau item tambahan.</p>
                        </div>
                        <button
                            type="button"
                            @click="showAddons = !showAddons"
                            class="inline-flex items-center justify-center rounded-lg border border-brand-200 px-4 py-2.5 text-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:border-brand-800/60 dark:text-brand-300 dark:hover:bg-brand-500/10"
                            x-text="showAddons ? 'Sembunyikan Add-ons' : 'Pakai Add-ons'"
                        ></button>
                    </div>

                    <div x-show="showAddons" class="space-y-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Setiap kategori bisa punya beberapa opsi harga. Isi jumlah sesuai pcs, orang, pax, atau paket yang dipesan tamu.</p>

                        @foreach ($addonChoiceGroups as $group)
                            <div x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }" class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800">
                                <button type="button" x-on:click="open = !open" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left transition hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h4 class="text-sm font-semibold text-gray-800 dark:text-white/90">{{ $group['name'] }}</h4>
                                            <span class="rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ count($group['choices']) }} opsi</span>
                                            <span x-show="selectedAddonIds.filter((id) => {{ \Illuminate\Support\Js::from(collect($group['choices'])->pluck('id')->map(fn ($id) => (string) $id)->all()) }}.includes(String(id))).length > 0" class="rounded-full bg-brand-50 px-2.5 py-1 text-[11px] font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-300" x-text="`${selectedAddonIds.filter((id) => {{ \Illuminate\Support\Js::from(collect($group['choices'])->pluck('id')->map(fn ($id) => (string) $id)->all()) }}.includes(String(id))).length} dipilih`"></span>
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Klik untuk melihat opsi di kategori ini.</p>
                                    </div>
                                    <svg class="h-4 w-4 shrink-0 text-gray-500 transition" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.51a.75.75 0 0 1-1.08 0l-4.25-4.51a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                                    </svg>
                                </button>

                                <div x-show="open" class="space-y-3 border-t border-gray-200 px-4 py-4 dark:border-gray-800">
                                    @foreach ($group['choices'] as $choice)
                                        @php
                                            $selectedChoiceValues = collect(old('selected_addon_choices', old('selected_addons', [])))->map(fn ($value) => (string) $value);
                                            $isSelected = $selectedChoiceValues->contains((string) $choice['id']);
                                            $chargeLabel = match ($choice['charge_basis']) {
                                                'per_night' => 'per malam',
                                                'per_item_per_night' => 'per ' . $choice['unit_label'] . ' per malam',
                                                'per_person_per_night' => 'per ' . $choice['unit_label'] . ' per malam',
                                                'per_person_per_stay' => 'per ' . $choice['unit_label'] . ' per stay',
                                                'per_item' => 'per ' . $choice['unit_label'],
                                                default => 'per stay',
                                            };
                                        @endphp
                                        <label class="flex flex-col gap-3 rounded-xl border border-gray-200 px-3 py-3 transition hover:border-brand-300 dark:border-gray-800 dark:hover:border-brand-700/60 md:flex-row md:items-center md:justify-between">
                                            <div class="flex min-w-0 items-start gap-3">
                                                <input type="checkbox" name="selected_addon_choices[]" value="{{ $choice['id'] }}" x-model="selectedAddonIds" x-on:change="ensureAddonQuantity('{{ $choice['id'] }}', $event.target.checked)" @checked($isSelected) class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/20" />
                                                <div class="min-w-0">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ $choice['name'] }}</span>
                                                        <span class="rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ $chargeLabel }}</span>
                                                    </div>
                                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Satuan: {{ $choice['unit_label'] }}. Contoh: 2 {{ $choice['unit_label'] }} jika tamu pesan dua item.</p>
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-[72px_minmax(0,1fr)] items-center gap-3 md:w-[210px]">
                                                <div>
                                                    <label class="mb-1 block text-[11px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Jumlah</label>
                                                    <input type="number" min="1" name="addon_choice_quantities[{{ $choice['id'] }}]" x-model="addonQuantities['{{ $choice['id'] }}']" :disabled="!selectedAddonIds.includes('{{ $choice['id'] }}')" value="{{ old('addon_choice_quantities.' . $choice['id'], 1) }}" class="h-8 w-full rounded-lg border border-gray-300 bg-transparent px-2 py-1.5 text-xs text-gray-800 shadow-theme-xs disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-400 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90 dark:disabled:bg-gray-800" />
                                                    @error('addon_choice_quantities.' . $choice['id'])<p class="mt-1 text-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                                                </div>
                                                <div class="text-right">
                                                    <label class="mb-1 block text-[11px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Harga</label>
                                                    <span class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Rp {{ number_format($choice['price'], 0, ',', '.') }}</span>
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-4 rounded-xl border border-gray-200 bg-gray-50/50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-800 dark:text-white/90">Voucher, Diskon, dan Markup</h4>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Bagian ini opsional. Buka hanya jika kamu memang perlu menambahkan penyesuaian harga.</p>
                        </div>
                        <button
                            type="button"
                            @click="showPricingAdjustments = !showPricingAdjustments"
                            class="inline-flex items-center justify-center rounded-lg border border-brand-200 px-4 py-2.5 text-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:border-brand-800/60 dark:text-brand-300 dark:hover:bg-brand-500/10"
                            x-text="showPricingAdjustments ? 'Sembunyikan Pengaturan Harga' : (hasPricingAdjustments ? 'Lihat Pengaturan Harga Aktif' : 'Pakai Voucher / Markup')"
                        ></button>
                    </div>

                    <div x-show="showPricingAdjustments" class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Voucher</label>
                                <select name="voucher_id" x-model="voucherId" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">
                                    <option value="">Tanpa voucher</option>
                                    @foreach ($vouchers as $voucher)<option value="{{ $voucher->id }}" @selected(old('voucher_id') == $voucher->id)>{{ $voucher->code }}</option>@endforeach
                                </select>
                            </div>
                            <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Diskon Manual (Rupiah)</label><input type="number" step="1" min="0" name="manual_discount_amount" value="{{ old('manual_discount_amount', 0) }}" data-money x-on:input="syncMoney($event, 'manualDiscountAmount')" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" /></div>
                        </div>

                        <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Alasan Diskon Manual</label><textarea name="manual_discount_reason" rows="2" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">{{ old('manual_discount_reason') }}</textarea>@error('manual_discount_reason')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror</div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Markup Harga (Rupiah)</label><input type="number" step="1" min="0" name="markup_amount" value="{{ old('markup_amount', 0) }}" data-money x-on:input="syncMoney($event, 'markupAmount')" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" /></div>
                            <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan Markup</label><input type="text" name="markup_reason" value="{{ old('markup_reason') }}" placeholder="Contoh: surcharge libur khusus" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" /></div>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-brand-200 bg-brand-50/40 p-5 dark:border-brand-800/50 dark:bg-brand-500/10">
                    <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-800 dark:text-white/90">Preview Harga Booking</h4>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Ringkasan cepat sebelum lanjut ke konfirmasi akhir.</p>
                        </div>
                        <div class="text-left md:text-right">
                            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Unit</p>
                            <p class="text-sm font-semibold text-gray-800 dark:text-white/90" x-text="selectedUnit ? selectedUnit.name : 'Belum dipilih'"></p>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-4 xl:grid-cols-8">
                        <div class="rounded-xl bg-white px-4 py-3 shadow-theme-xs dark:bg-gray-900/50">
                            <p class="text-[11px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Malam</p>
                            <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90" x-text="`${nightsCount} malam`"></p>
                        </div>
                        <div class="rounded-xl bg-white px-4 py-3 shadow-theme-xs dark:bg-gray-900/50">
                            <p class="text-[11px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Harga Villa</p>
                            <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90" x-text="`Rp ${formatMoney(subtotalNight)}`"></p>
                        </div>
                        <div class="rounded-xl bg-white px-4 py-3 shadow-theme-xs dark:bg-gray-900/50">
                            <p class="text-[11px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Add-ons</p>
                            <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90" x-text="`Rp ${formatMoney(subtotalAddon)}`"></p>
                        </div>
                        <div class="rounded-xl bg-white px-4 py-3 shadow-theme-xs dark:bg-gray-900/50">
                            <p class="text-[11px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Markup</p>
                            <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90" x-text="`Rp ${formatMoney(markupValue)}`"></p>
                        </div>
                        <div class="rounded-xl bg-white px-4 py-3 shadow-theme-xs dark:bg-gray-900/50">
                            <p class="text-[11px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Diskon</p>
                            <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90" x-text="`Rp ${formatMoney(voucherDiscountValue + manualDiscountValue)}`"></p>
                        </div>
                        <div class="rounded-xl bg-white px-4 py-3 shadow-theme-xs dark:bg-gray-900/50">
                            <p class="text-[11px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Grand Total</p>
                            <p class="mt-1 text-sm font-semibold text-brand-600 dark:text-brand-300" x-text="`Rp ${formatMoney(grandTotal)}`"></p>
                        </div>
                        <div class="rounded-xl bg-white px-4 py-3 shadow-theme-xs dark:bg-gray-900/50">
                            <p class="text-[11px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Sisa</p>
                            <p class="mt-1 text-sm font-semibold text-error-600 dark:text-error-400" x-text="`Rp ${formatMoney(remainingBalance)}`"></p>
                        </div>
                        <div class="rounded-xl bg-white px-4 py-3 shadow-theme-xs dark:bg-gray-900/50 col-span-2 md:col-span-2 xl:col-span-1">
                            <p class="text-[11px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Pelunasan</p>
                            <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90" x-text="finalPaymentDueLabel"></p>
                        </div>
                    </div>
                </div>

                {{-- DP / Pembayaran Awal --}}
                <div class="space-y-4 rounded-xl border border-green-200 bg-green-50/30 p-5 dark:border-green-800/50 dark:bg-green-900/10">
                    <h4 class="text-sm font-semibold text-gray-800 dark:text-white/90">Pembayaran Awal (DP)</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Setiap booking wajib memiliki DP. Pembayaran tambahan bisa dilakukan dari halaman detail booking.</p>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nominal DP (Rupiah)</label>
                            <input type="number" step="1" min="1" name="dp_amount" value="{{ old('dp_amount', 0) }}" data-money x-on:input="syncMoney($event, 'dpAmount')" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                            @error('dp_amount')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Metode Pembayaran</label>
                            <select name="payment_method" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">
                                <option value="">Pilih metode</option>
                                <option value="cash" @selected(old('payment_method') === 'cash')>Cash</option>
                                <option value="transfer" @selected(old('payment_method') === 'transfer')>Transfer</option>
                            </select>
                            @error('payment_method')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Diterima Oleh</label>
                            <select name="received_by" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">
                                <option value="">Pilih penerima</option>
                                <option value="finance" @selected(old('received_by') === 'finance')>Finance</option>
                                <option value="office" @selected(old('received_by') === 'office')>Office</option>
                                <option value="field_staff" @selected(old('received_by') === 'field_staff')>Field Staff</option>
                            </select>
                            @error('received_by')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan Pembayaran</label>
                        <textarea name="payment_note" rows="2" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">{{ old('payment_note') }}</textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('bookings.index') }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Batal</a>
                    <button type="button" @click="openConfirmationModal()" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Lanjutkan Konfirmasi</button>
                </div>
            </form>
        </x-common.component-card>
    </div>

    <div
        x-cloak
        x-show="showConfirmationModal"
        x-transition.opacity
        @keydown.escape.window="closeConfirmationModal()"
        class="fixed inset-0 z-99999 flex items-center justify-center bg-gray-900/60 px-4 py-6"
    >
        <div @click.away="closeConfirmationModal()" class="max-h-[90vh] w-full max-w-4xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">
            <div class="flex items-start justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Konfirmasi Booking</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Periksa ringkasan booking terlebih dahulu sebelum data disimpan ke sistem.</p>
                </div>
                <button type="button" @click="closeConfirmationModal()" class="rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-500 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Tutup</button>
            </div>

            <div class="max-h-[calc(90vh-150px)] overflow-y-auto px-6 py-5">
                <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                    <div class="space-y-4 text-sm text-gray-600 dark:text-gray-300">
                        <div class="rounded-xl border border-gray-100 px-4 py-3 dark:border-gray-800">
                            <div class="flex items-center justify-between">
                                <span>Unit Terpilih</span>
                                <span class="font-medium text-gray-800 dark:text-white/90" x-text="selectedUnit ? selectedUnit.name : 'Belum dipilih'"></span>
                            </div>
                            <div class="mt-2 flex items-center justify-between">
                                <span>Jumlah Malam</span>
                                <span class="font-medium text-gray-800 dark:text-white/90" x-text="`${nightsCount} malam`"></span>
                            </div>
                        </div>

                        <div class="rounded-xl border border-gray-100 px-4 py-3 dark:border-gray-800">
                            <h4 class="font-medium text-gray-800 dark:text-white/90">Harga per malam</h4>
                            <div class="mt-3 space-y-3" x-show="previewNights.length > 0">
                                <template x-for="night in previewNights" :key="night.date">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="font-medium text-gray-800 dark:text-white/90" x-text="night.label"></p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400" x-text="night.note"></p>
                                        </div>
                                        <p class="font-medium text-gray-800 dark:text-white/90" x-text="`Rp ${formatMoney(night.amount)}`"></p>
                                    </div>
                                </template>
                            </div>
                            <p x-show="previewNights.length === 0" class="mt-3 text-xs text-gray-500 dark:text-gray-400">Pilih unit dan tanggal untuk melihat harga per malam.</p>
                        </div>
                    </div>

                    <div class="space-y-4 text-sm text-gray-600 dark:text-gray-300">
                        <div class="rounded-xl border border-gray-100 px-4 py-3 dark:border-gray-800">
                            <h4 class="font-medium text-gray-800 dark:text-white/90">Preview Add-ons</h4>
                            <div class="mt-3 space-y-3" x-show="previewAddons.length > 0">
                                <template x-for="addon in previewAddons" :key="addon.id">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="font-medium text-gray-800 dark:text-white/90" x-text="addon.name"></p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400" x-text="addon.summary"></p>
                                        </div>
                                        <p class="font-medium text-gray-800 dark:text-white/90" x-text="`Rp ${formatMoney(addon.total)}`"></p>
                                    </div>
                                </template>
                            </div>
                            <p x-show="previewAddons.length === 0" class="mt-3 text-xs text-gray-500 dark:text-gray-400">Belum ada add-on yang dipilih.</p>
                        </div>

                        <div class="rounded-xl border border-gray-100 px-4 py-3 dark:border-gray-800 space-y-3">
                            <div class="flex items-center justify-between"><span>Subtotal harga per malam</span><span class="font-medium text-gray-800 dark:text-white/90" x-text="`Rp ${formatMoney(subtotalNight)}`"></span></div>
                            <div class="flex items-center justify-between"><span>Subtotal add-ons</span><span class="font-medium text-gray-800 dark:text-white/90" x-text="`Rp ${formatMoney(subtotalAddon)}`"></span></div>
                            <div class="flex items-center justify-between"><span>Markup harga</span><span class="font-medium text-gray-800 dark:text-white/90" x-text="`Rp ${formatMoney(markupValue)}`"></span></div>
                            <div class="flex items-center justify-between"><span>Diskon voucher</span><span class="font-medium text-gray-800 dark:text-white/90" x-text="`Rp ${formatMoney(voucherDiscountValue)}`"></span></div>
                            <div class="flex items-center justify-between"><span>Diskon manual</span><span class="font-medium text-gray-800 dark:text-white/90" x-text="`Rp ${formatMoney(manualDiscountValue)}`"></span></div>
                            <div class="border-t border-dashed border-gray-200 pt-3 dark:border-gray-700 flex items-center justify-between"><span class="font-semibold text-gray-800 dark:text-white/90">Grand Total</span><span class="text-base font-semibold text-gray-800 dark:text-white/90" x-text="`Rp ${formatMoney(grandTotal)}`"></span></div>
                            <div class="flex items-center justify-between"><span>DP Masuk</span><span class="font-medium text-gray-800 dark:text-white/90" x-text="`Rp ${formatMoney(dpValue)}`"></span></div>
                            <div class="flex items-center justify-between"><span>Sisa Tagihan</span><span class="font-medium text-error-600 dark:text-error-400" x-text="`Rp ${formatMoney(remainingBalance)}`"></span></div>
                            <div class="flex items-center justify-between"><span>Target Pelunasan</span><span class="font-medium text-gray-800 dark:text-white/90" x-text="finalPaymentDueLabel"></span></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-gray-800">
                <button type="button" @click="closeConfirmationModal()" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Kembali Edit</button>
                <button type="button" @click="submitConfirmedBooking()" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Konfirmasi & Simpan Booking</button>
            </div>
        </div>
    </div>
</div>
