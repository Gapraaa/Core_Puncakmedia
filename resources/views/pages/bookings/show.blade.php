@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Detail Booking" />

    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $booking->booking_code }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $booking->guest_name }} - {{ $booking->guest_phone }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('bookings.adjustments.create', $booking) }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Penyesuaian Booking</a>
                @if ($booking->invoices->isNotEmpty())
                    <a href="{{ route('documents.invoices.show', $booking->invoices->first()) }}" target="_blank" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">Unduh Invoice</a>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="ops-kpi-card">
                <div class="ops-kpi-label">Grand Total</div>
                <div class="ops-kpi-value">Rp {{ number_format($booking->grand_total, 0, ',', '.') }}</div>
                <div class="ops-kpi-note">Nilai booking keseluruhan.</div>
            </div>
            <div class="ops-kpi-card">
                <div class="ops-kpi-label">Total Pembayaran</div>
                <div class="ops-kpi-value">Rp {{ number_format($booking->total_paid, 0, ',', '.') }}</div>
                <div class="ops-kpi-note">Akumulasi pembayaran yang sudah masuk.</div>
            </div>
            <div class="ops-kpi-card">
                <div class="ops-kpi-label">Sisa Tagihan</div>
                <div class="ops-kpi-value {{ $booking->remaining_balance > 0 ? 'text-error-600 dark:text-error-400' : 'text-success-600 dark:text-success-400' }}">Rp {{ number_format($booking->remaining_balance, 0, ',', '.') }}</div>
                <div class="ops-kpi-note">Acuan utama untuk follow-up finance.</div>
            </div>
            <div class="ops-kpi-card">
                <div class="ops-kpi-label">Status Pembayaran</div>
                <div class="ops-kpi-value text-xl">{{ strtoupper($booking->payment_status) }}</div>
                <div class="ops-kpi-note">Meringkas posisi transaksi internal booking ini.</div>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-4 md:gap-6">
            <div class="col-span-12 xl:col-span-4">
                <x-common.component-card title="Ringkasan Booking" desc="Informasi inti booking dan status transaksi saat ini.">
                    <div class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Brand:</span> {{ $booking->brand?->name }}</div>
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Villa:</span> {{ $booking->villa?->name }}</div>
                        @if ($booking->villa?->is_resort)
                            <div><span class="font-medium text-gray-800 dark:text-white/90">Unit:</span> {{ $booking->villaUnit?->unit_name }}</div>
                        @endif
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Periode:</span> {{ $booking->check_in->format('j M Y') }} - {{ $booking->check_out->format('j M Y') }}</div>
                        <div>
                            <span class="font-medium text-gray-800 dark:text-white/90">Target Pelunasan:</span>
                            @if ($booking->final_payment_due_date?->isSameDay($booking->check_in))
                                Pelunasan saat check-in ({{ $booking->final_payment_due_date?->format('j M Y') }})
                            @else
                                {{ $booking->final_payment_due_date?->format('j M Y') ?? '-' }} (H-3)
                            @endif
                        </div>
                        <div>
                            <span class="font-medium text-gray-800 dark:text-white/90">Status Booking:</span>
                            <x-ui.badge color="{{ $booking->booking_status === 'confirmed' ? 'success' : 'error' }}">{{ ucfirst($booking->booking_status) }}</x-ui.badge>
                        </div>
                        <div>
                            <span class="font-medium text-gray-800 dark:text-white/90">Status Pembayaran:</span>
                            @php
                                $paymentBadgeColor = match($booking->payment_status) {
                                    'lunas' => 'success',
                                    'cicil' => 'warning',
                                    default => 'info',
                                };
                            @endphp
                            <x-ui.badge color="{{ $paymentBadgeColor }}">{{ strtoupper($booking->payment_status) }}</x-ui.badge>
                        </div>
                    </div>
                </x-common.component-card>
            </div>
            <div class="col-span-12 xl:col-span-8">
                <x-common.component-card title="Ringkasan Keuangan Booking" desc="Finance utama membaca total, pembayaran, dan sisa tagihan langsung dari booking ini.">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="ops-panel-soft"><p class="text-sm text-gray-500 dark:text-gray-400">Subtotal Sebelum Diskon</p><p class="mt-2 text-xl font-semibold text-gray-800 dark:text-white/90">Rp {{ number_format($booking->total_before_discount, 0, ',', '.') }}</p></div>
                        <div class="ops-panel-soft"><p class="text-sm text-gray-500 dark:text-gray-400">Periode Menginap</p><p class="mt-2 text-base font-semibold text-gray-800 dark:text-white/90">{{ $booking->check_in->format('j M Y') }} - {{ $booking->check_out->format('j M Y') }}</p></div>
                        <div class="ops-panel-soft md:col-span-2"><p class="text-sm text-gray-500 dark:text-gray-400">Jadwal Pelunasan</p><p class="mt-2 text-base font-semibold text-gray-800 dark:text-white/90">@if ($booking->final_payment_due_date?->isSameDay($booking->check_in)) Pelunasan saat check-in ({{ $booking->final_payment_due_date?->format('j M Y') }}) @else {{ $booking->final_payment_due_date?->format('j M Y') ?? '-' }} (H-3 sebelum check-in) @endif</p></div>
                    </div>
                </x-common.component-card>
            </div>
        </div>

        <x-common.component-card title="Komponen Biaya Booking" desc="Semua biaya operasional booking terkumpul di sini dan menjadi sumber utama perhitungan keuangan internal.">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="ops-compact-table">
                    <thead><tr><th>Jenis</th><th>Nama</th><th>Tanggal</th><th>Qty</th><th>Total</th></tr></thead>
                    <tbody>
                        @foreach ($booking->items as $item)
                            <tr><td>{{ $item->item_type }}</td><td>{{ $item->item_name }}<div class="text-xs text-gray-500 dark:text-gray-400">{{ $item->notes }}</div></td><td>{{ $item->reference_date?->format('j M Y') ?: '-' }}</td><td>{{ $item->quantity }}</td><td>Rp {{ number_format($item->total_price, 0, ',', '.') }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-common.component-card>

        <div class="grid grid-cols-12 gap-4 md:gap-6">
            <div class="col-span-12 xl:col-span-7">
                <x-common.component-card title="Invoice" desc="Invoice tetap tersedia untuk dikirim ke tamu, tapi tidak ditampilkan sebagai nomor utama di halaman booking.">
                    <div class="space-y-4">
                        @foreach ($booking->invoices as $invoice)
                            <div class="rounded-xl border border-gray-100 px-4 py-4 dark:border-gray-800">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="font-medium text-gray-800 dark:text-white/90">{{ $invoice->label }}</p>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $invoice->invoice_type === 'combined' ? 'Invoice gabungan' : 'Invoice terpisah' }}</p>
                                    </div>
                                    <x-ui.badge color="{{ $invoice->payment_status === 'lunas' ? 'success' : ($invoice->payment_status === 'cicil' ? 'warning' : 'info') }}">{{ strtoupper($invoice->payment_status) }}</x-ui.badge>
                                </div>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <a href="{{ route('documents.invoices.show', $invoice) }}" target="_blank" class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Lihat Invoice</a>
                                    <a href="{{ route('documents.invoices.show', ['invoice' => $invoice, 'download' => 1]) }}" class="rounded-lg border border-brand-200 px-3 py-2 text-xs font-medium text-brand-600 transition hover:bg-brand-50 dark:border-brand-800/50 dark:text-brand-300 dark:hover:bg-brand-500/10">Unduh Invoice</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-common.component-card>
            </div>

            <div class="col-span-12 xl:col-span-5">
                <x-common.component-card title="Pisahkan Invoice" desc="Gunakan hanya jika tamu meminta invoice terpisah. Booking internal tetap satu sumber utama.">
                    <form method="POST" action="{{ route('bookings.invoices.split', $booking) }}" class="space-y-4"
                        data-toast-loading="Invoice terpisah sedang dibuat dari item booking terpilih."
                        data-toast-loading-title="Membuat Invoice Terpisah">
                        @csrf
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Label Invoice Baru</label>
                            <input type="text" name="label" value="{{ old('label') }}" placeholder="Contoh: Invoice Add-on" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                            @error('label')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        </div>
                        <div class="space-y-3">
                            @foreach ($booking->items as $item)
                                <label class="flex items-center justify-between gap-3 rounded-xl border border-gray-200 px-4 py-3 dark:border-gray-800">
                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" name="item_ids[]" value="{{ $item->id }}" @checked(collect(old('item_ids', []))->contains($item->id)) class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/20" />
                                        <div>
                                            <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $item->item_name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Invoice saat ini: {{ $item->invoice?->label ?? 'Belum dipetakan' }}</p>
                                        </div>
                                    </div>
                                    <span class="text-sm text-gray-600 dark:text-gray-300">{{ number_format($item->total_price, 0, ',', '.') }}</span>
                                </label>
                            @endforeach
                            @error('item_ids')<p class="text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                            @error('item_ids.*')<p class="text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Buat Invoice Terpisah</button>
                        </div>
                    </form>
                </x-common.component-card>
            </div>
        </div>

        <x-common.component-card title="Riwayat Pembayaran Booking" desc="Semua pembayaran tercatat ke booking ini. Finance utama memantau cashflow dari bagian ini, sedangkan invoice hanya referensi dokumen tamu.">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="ops-compact-table">
                    <thead><tr><th>Tanggal</th><th>Metode</th><th>Penerima</th><th>Jumlah</th></tr></thead>
                    <tbody>
                        @forelse ($booking->payments as $payment)
                                            <tr><td>{{ $payment->paid_at?->format('j M Y H:i') }}</td><td>{{ ucfirst($payment->payment_method) }}</td><td>{{ ucfirst(str_replace('_', ' ', $payment->received_by)) }}</td><td>Rp {{ number_format($payment->amount, 0, ',', '.') }}<div class="text-xs text-gray-500 dark:text-gray-400">{{ $payment->note }}</div><div class="mt-2"><a href="{{ route('documents.payments.receipt', $payment) }}" target="_blank" class="text-xs font-medium text-brand-600 dark:text-brand-300">Lihat Bukti Pembayaran</a></div></td></tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada pembayaran untuk booking ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-common.component-card>

        {{-- Tambah Pembayaran (hanya jika belum lunas dan belum cancelled) --}}
        @if ($booking->payment_status !== 'lunas' && $booking->booking_status !== 'cancelled')
            <x-common.component-card title="Tambah Pembayaran" desc="Cicilan atau pelunasan untuk booking ini.">
                <form method="POST" action="{{ route('bookings.payments.store', $booking) }}" class="space-y-4"
                    data-toast-loading="Pembayaran sedang dicatat dan status booking akan diperbarui."
                    data-toast-loading-title="Mencatat Pembayaran">
                    @csrf
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Invoice</label>
                            <select name="invoice_id" class="ops-input">
                                @foreach ($booking->invoices as $invoice)
                                    <option value="{{ $invoice->id }}">{{ $invoice->label }} - {{ number_format($invoice->remaining_balance, 0, ',', '.') }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nominal (Rupiah)</label>
                            <input type="number" step="1" min="1" name="amount" value="{{ old('amount', 0) }}" data-money class="ops-input" />
                            @error('amount')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Metode</label>
                            <select name="payment_method" class="ops-input">
                                <option value="cash" @selected(old('payment_method') === 'cash')>Cash</option>
                                <option value="transfer" @selected(old('payment_method') === 'transfer')>Transfer</option>
                            </select>
                            @error('payment_method')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Penerima</label>
                            <select name="received_by" class="ops-input">
                                <option value="finance" @selected(old('received_by') === 'finance')>Finance</option>
                                <option value="office" @selected(old('received_by') === 'office')>Office</option>
                                <option value="field_staff" @selected(old('received_by') === 'field_staff')>Field Staff</option>
                            </select>
                            @error('received_by')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Bayar</label>
                            <input onclick="this.showPicker()" type="date" name="paid_at" value="{{ old('paid_at', now()->format('Y-m-d')) }}" class="ops-input" />
                        </div>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan</label>
                        <textarea name="note" rows="2" class="ops-textarea">{{ old('note') }}</textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Catat Pembayaran</button>
                    </div>
                </form>
            </x-common.component-card>
        @endif
    </div>
@endsection

@push('scripts')
    @if (session('auto_download_document_url'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const documentUrl = @json(session('auto_download_document_url'));

                if (documentUrl) {
                    const iframe = document.createElement('iframe');
                    iframe.style.display = 'none';
                    iframe.src = documentUrl;
                    document.body.appendChild(iframe);
                }
            });
        </script>
    @endif
@endpush
