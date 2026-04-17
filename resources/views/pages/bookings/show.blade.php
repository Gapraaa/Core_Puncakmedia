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
                    <a href="{{ route('documents.invoices.show', $booking->invoices->first()) }}" target="_blank" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">Lihat Invoice</a>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-12 gap-4 md:gap-6">
            <div class="col-span-12 xl:col-span-4">
                <x-common.component-card title="Ringkasan Booking" desc="Informasi inti booking dan status transaksi saat ini.">
                    <div class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Brand:</span> {{ $booking->brand?->name }}</div>
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Villa:</span> {{ $booking->villa?->name }}</div>
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Villa Unit:</span> {{ $booking->villaUnit?->unit_name }}</div>
                        <div><span class="font-medium text-gray-800 dark:text-white/90">Periode:</span> {{ $booking->check_in->format('d M Y') }} - {{ $booking->check_out->format('d M Y') }}</div>
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
                <x-common.component-card title="Ringkasan Total" desc="Total booking dihitung dari item booking dan pembayaran yang sudah tercatat.">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="rounded-xl border border-gray-100 px-4 py-4 dark:border-gray-800"><p class="text-sm text-gray-500 dark:text-gray-400">Subtotal</p><p class="mt-2 text-xl font-semibold text-gray-800 dark:text-white/90">{{ number_format($booking->total_before_discount, 0, ',', '.') }}</p></div>
                        <div class="rounded-xl border border-gray-100 px-4 py-4 dark:border-gray-800"><p class="text-sm text-gray-500 dark:text-gray-400">Total Pembayaran</p><p class="mt-2 text-xl font-semibold text-gray-800 dark:text-white/90">{{ number_format($booking->total_paid, 0, ',', '.') }}</p></div>
                        <div class="rounded-xl border border-gray-100 px-4 py-4 dark:border-gray-800"><p class="text-sm text-gray-500 dark:text-gray-400">Sisa Tagihan</p><p class="mt-2 text-xl font-semibold {{ $booking->remaining_balance > 0 ? 'text-error-600 dark:text-error-400' : 'text-success-600 dark:text-success-400' }}">{{ number_format($booking->remaining_balance, 0, ',', '.') }}</p></div>
                    </div>
                    <div class="rounded-xl border border-gray-100 px-4 py-4 dark:border-gray-800"><p class="text-sm text-gray-500 dark:text-gray-400">Grand Total</p><p class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ number_format($booking->grand_total, 0, ',', '.') }}</p></div>
                </x-common.component-card>
            </div>
        </div>

        <x-common.component-card title="Booking Items" desc="Daftar nightly pricing, add-on, dan penyesuaian yang terhubung ke booking ini.">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full">
                    <thead><tr class="border-b border-gray-100 dark:border-gray-800"><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Jenis</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Nama</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Invoice</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tanggal</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Qty</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total</th></tr></thead>
                    <tbody>
                        @foreach ($booking->items as $item)
                            <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800"><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $item->item_type }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $item->item_name }}<div class="text-xs text-gray-500 dark:text-gray-400">{{ $item->notes }}</div></td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $item->invoice?->label ?? 'Belum dipetakan' }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $item->reference_date?->format('d M Y') ?: '-' }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $item->quantity }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ number_format($item->total_price, 0, ',', '.') }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-common.component-card>

        <div class="grid grid-cols-12 gap-4 md:gap-6">
            <div class="col-span-12 xl:col-span-7">
                <x-common.component-card title="Daftar Invoice" desc="Satu booking bisa memiliki invoice gabungan atau invoice terpisah untuk item tertentu.">
                    <div class="space-y-4">
                        @foreach ($booking->invoices as $invoice)
                            <div class="rounded-xl border border-gray-100 px-4 py-4 dark:border-gray-800">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="font-medium text-gray-800 dark:text-white/90">{{ $invoice->label }}</p>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $invoice->invoice_number }} - {{ $invoice->invoice_type === 'combined' ? 'Gabungan' : 'Terpisah' }}</p>
                                    </div>
                                    <x-ui.badge color="{{ $invoice->payment_status === 'lunas' ? 'success' : ($invoice->payment_status === 'cicil' ? 'warning' : 'info') }}">{{ strtoupper($invoice->payment_status) }}</x-ui.badge>
                                </div>
                                <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-3 text-sm text-gray-600 dark:text-gray-300">
                                    <div><span class="block text-xs text-gray-500 dark:text-gray-400">Subtotal</span><span class="font-medium text-gray-800 dark:text-white/90">{{ number_format($invoice->subtotal, 0, ',', '.') }}</span></div>
                                    <div><span class="block text-xs text-gray-500 dark:text-gray-400">Total Pembayaran</span><span class="font-medium text-gray-800 dark:text-white/90">{{ number_format($invoice->total_paid, 0, ',', '.') }}</span></div>
                                    <div><span class="block text-xs text-gray-500 dark:text-gray-400">Sisa</span><span class="font-medium {{ $invoice->remaining_balance > 0 ? 'text-error-600 dark:text-error-400' : 'text-success-600 dark:text-success-400' }}">{{ number_format($invoice->remaining_balance, 0, ',', '.') }}</span></div>
                                </div>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <a href="{{ route('documents.invoices.show', $invoice) }}" target="_blank" class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Lihat Invoice</a>
                                    <a href="{{ route('documents.invoices.show', ['invoice' => $invoice, 'download' => 1]) }}" class="rounded-lg border border-brand-200 px-3 py-2 text-xs font-medium text-brand-600 transition hover:bg-brand-50 dark:border-brand-800/50 dark:text-brand-300 dark:hover:bg-brand-500/10">Unduh Invoice</a>
                                </div>
                                <div class="mt-4 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                                    @forelse ($invoice->items as $item)
                                        <div class="flex items-center justify-between gap-3 rounded-lg bg-gray-50 px-3 py-2 dark:bg-white/[0.03]">
                                            <span>{{ $item->item_name }}</span>
                                            <span>{{ number_format($item->total_price, 0, ',', '.') }}</span>
                                        </div>
                                    @empty
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Belum ada item di invoice ini.</p>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-common.component-card>
            </div>

            <div class="col-span-12 xl:col-span-5">
                <x-common.component-card title="Pisahkan Invoice" desc="Pilih item booking yang ingin dipindahkan ke invoice baru.">
                    <form method="POST" action="{{ route('bookings.invoices.split', $booking) }}" class="space-y-4">
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

        <x-common.component-card title="Riwayat Pembayaran" desc="Semua pembayaran tercatat terpisah dari total booking dan mempengaruhi saldo tersisa.">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full">
                    <thead><tr class="border-b border-gray-100 dark:border-gray-800"><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tanggal</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Invoice</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Metode</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Penerima</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Jumlah</th></tr></thead>
                    <tbody>
                        @forelse ($booking->payments as $payment)
                            <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800"><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $payment->paid_at?->format('d M Y H:i') }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $payment->invoice?->label ?? 'Invoice utama' }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ ucfirst($payment->payment_method) }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ ucfirst(str_replace('_', ' ', $payment->received_by)) }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ number_format($payment->amount, 0, ',', '.') }}<div class="text-xs text-gray-500 dark:text-gray-400">{{ $payment->note }}</div><div class="mt-2"><a href="{{ route('documents.payments.receipt', $payment) }}" target="_blank" class="text-xs font-medium text-brand-600 dark:text-brand-300">Lihat Bukti Pembayaran</a></div></td></tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada pembayaran untuk booking ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-common.component-card>

        {{-- Tambah Pembayaran (hanya jika belum lunas dan belum cancelled) --}}
        @if ($booking->payment_status !== 'lunas' && $booking->booking_status !== 'cancelled')
            <x-common.component-card title="Tambah Pembayaran" desc="Cicilan atau pelunasan untuk booking ini.">
                <form method="POST" action="{{ route('bookings.payments.store', $booking) }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Invoice</label>
                            <select name="invoice_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">
                                @foreach ($booking->invoices as $invoice)
                                    <option value="{{ $invoice->id }}">{{ $invoice->label }} - {{ number_format($invoice->remaining_balance, 0, ',', '.') }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nominal (Rupiah)</label>
                            <input type="number" step="1" min="1" name="amount" value="{{ old('amount', 0) }}" data-money class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                            @error('amount')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Metode</label>
                            <select name="payment_method" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">
                                <option value="cash" @selected(old('payment_method') === 'cash')>Cash</option>
                                <option value="transfer" @selected(old('payment_method') === 'transfer')>Transfer</option>
                            </select>
                            @error('payment_method')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Penerima</label>
                            <select name="received_by" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">
                                <option value="finance" @selected(old('received_by') === 'finance')>Finance</option>
                                <option value="office" @selected(old('received_by') === 'office')>Office</option>
                                <option value="field_staff" @selected(old('received_by') === 'field_staff')>Field Staff</option>
                            </select>
                            @error('received_by')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Bayar</label>
                            <input onclick="this.showPicker()" type="date" name="paid_at" value="{{ old('paid_at', now()->format('Y-m-d')) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                        </div>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan</label>
                        <textarea name="note" rows="2" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">{{ old('note') }}</textarea>
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
