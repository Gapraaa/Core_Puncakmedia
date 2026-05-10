@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Dashboard Core PMS" />

    @if ($brandCount === 0 && $villaCount === 0 && $villaUnitCount === 0 && $bookingCount === 0)
        <div class="mb-6">
            <x-common.empty-state
                title="Sistem Masih Fresh"
                description="Starter saat ini hanya berisi roles dan user awal. Mulai dari tambah brand jika dipakai, lalu tambah villa atau resort agar modul booking, kalender, pembayaran, dan invoice bisa mulai berjalan."
                actionLabel="Tambah Villa"
                :actionHref="auth()->user()?->hasAnyRole(['master', 'superadmin', 'head-office', 'admin-sales']) ? route('villas.create') : route('profile')"
                :secondaryActionLabel="auth()->user()?->hasAnyRole(['master', 'superadmin', 'head-office']) ? 'Tambah Brand' : null"
                :secondaryActionHref="auth()->user()?->hasAnyRole(['master', 'superadmin', 'head-office']) ? route('brands.create') : null"
            />
        </div>
    @endif

    <div class="grid grid-cols-12 gap-4 md:gap-6">
        <div class="col-span-12 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:col-span-8 xl:grid-cols-4">
            @foreach ([
                ['label' => 'Total Brand', 'value' => $brandCount, 'hint' => 'Brand aktif yang sudah terdaftar di sistem'],
                ['label' => 'Total Villa', 'value' => $villaCount, 'hint' => 'Villa induk yang siap dikelola'],
                ['label' => 'Total Unit', 'value' => $villaUnitCount, 'hint' => 'Unit yang bisa dipakai untuk booking'],
                ['label' => 'Total Booking', 'value' => $bookingCount, 'hint' => 'Seluruh booking yang sudah tercatat'],
            ] as $metric)
                <div class="ops-kpi-card">
                    <span class="ops-kpi-label">{{ $metric['label'] }}</span>
                    <h3 class="ops-kpi-value text-3xl">{{ number_format($metric['value'], 0, ',', '.') }}</h3>
                    <p class="ops-kpi-note">{{ $metric['hint'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="col-span-12 xl:col-span-4">
            <x-common.component-card title="Sorotan Hari Ini" desc="Ringkasan operasional real-time untuk tim booking dan finance.">
                <div class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
                    <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/40">
                        Check-in mendatang: <span class="font-semibold text-gray-800 dark:text-white/90">{{ number_format($upcomingCheckInsCount, 0, ',', '.') }}</span>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/40">
                        Sisa saldo seluruh booking: <span class="font-semibold text-gray-800 dark:text-white/90">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</span>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/40">
                        Booking DP / Cicil / Lunas: <span class="font-semibold text-gray-800 dark:text-white/90">{{ $paymentStatusCounts['dp'] }} / {{ $paymentStatusCounts['cicil'] }} / {{ $paymentStatusCounts['lunas'] }}</span>
                    </div>
                </div>
            </x-common.component-card>
        </div>

        <div class="col-span-12 grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="ops-kpi-card">
                <div class="ops-kpi-label">Outstanding</div>
                <div class="ops-kpi-value">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</div>
                <div class="ops-kpi-note">Akumulasi sisa tagihan seluruh booking aktif.</div>
            </div>
            <div class="ops-kpi-card">
                <div class="ops-kpi-label">Check-in Terdekat</div>
                <div class="ops-kpi-value">{{ number_format($upcomingCheckInsCount, 0, ',', '.') }}</div>
                <div class="ops-kpi-note">Jumlah booking yang perlu disiapkan oleh tim operasional.</div>
            </div>
            <div class="ops-kpi-card">
                <div class="ops-kpi-label">Komposisi Status</div>
                <div class="ops-kpi-value">{{ $paymentStatusCounts['dp'] }} / {{ $paymentStatusCounts['cicil'] }} / {{ $paymentStatusCounts['lunas'] }}</div>
                <div class="ops-kpi-note">DP, cicil, dan lunas dalam satu pandangan cepat.</div>
            </div>
        </div>

        <div class="col-span-12 xl:col-span-5">
            <x-common.component-card title="Status Pembayaran Booking" desc="Pantau sebaran DP, Cicil, dan Lunas dari seluruh booking.">
                <div class="space-y-4">
                    @foreach ([
                        ['label' => 'DP', 'value' => $paymentStatusCounts['dp'], 'color' => 'bg-blue-400 dark:bg-blue-600'],
                        ['label' => 'Cicil', 'value' => $paymentStatusCounts['cicil'], 'color' => 'bg-warning-500'],
                        ['label' => 'Lunas', 'value' => $paymentStatusCounts['lunas'], 'color' => 'bg-success-500'],
                    ] as $status)
                        @php
                            $totalStatuses = max(1, array_sum($paymentStatusCounts));
                            $percentage = (int) round(($status['value'] / $totalStatuses) * 100);
                        @endphp
                        <div>
                            <div class="mb-2 flex items-center justify-between text-sm">
                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $status['label'] }}</span>
                                <span class="text-gray-500 dark:text-gray-400">{{ $status['value'] }} booking</span>
                            </div>
                            <div class="h-2 rounded-full bg-gray-100 dark:bg-gray-800">
                                <div class="h-2 rounded-full {{ $status['color'] }}" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-common.component-card>
        </div>

        <div class="col-span-12 xl:col-span-7">
            <x-common.component-card title="Check-in Mendatang" desc="Booking terdekat yang perlu dipantau oleh tim operasional.">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="ops-compact-table">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <th>Tanggal</th>
                                <th>Booking</th>
                                <th>Tamu</th>
                                <th>Sisa Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($upcomingCheckIns as $booking)
                                <tr>
                                    <td>{{ $booking->check_in->format('j M Y') }}</td>
                                    <td class="font-medium text-gray-800 dark:text-white/90">{{ $booking->booking_code }}<div class="text-xs text-gray-500 dark:text-gray-400">{{ $booking->villa?->name }} - {{ $booking->villaUnit?->unit_name }}</div></td>
                                    <td>{{ $booking->guest_name }}</td>
                                    <td>Rp {{ number_format($booking->remaining_balance, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada check-in mendatang.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-common.component-card>
        </div>

        <div class="col-span-12 xl:col-span-6">
            <x-common.component-card title="Pembayaran Terbaru" desc="Aktivitas pembayaran terakhir yang masuk ke sistem.">
                <div class="space-y-3">
                    @forelse ($recentPayments as $payment)
                        <div class="rounded-xl border border-gray-100 px-4 py-3 dark:border-gray-800">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $payment->booking?->booking_code ?? 'Tanpa booking' }}</p>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $payment->paid_at?->format('j M Y H:i') }} &middot; {{ ucfirst($payment->payment_method) }} &middot; {{ str_replace('_', ' ', $payment->received_by) }}</p>
                                </div>
                                <p class="text-sm font-semibold text-gray-800 dark:text-white/90">Rp {{ number_format($payment->amount, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">Belum ada pembayaran yang dicatat.</div>
                    @endforelse
                </div>
            </x-common.component-card>
        </div>

        <div class="col-span-12 xl:col-span-6">
            <x-common.component-card title="Booking Terbaru" desc="Booking terbaru yang baru masuk ke sistem.">
                <div class="space-y-3">
                    @forelse ($recentBookings as $booking)
                        <div class="rounded-xl border border-gray-100 px-4 py-3 dark:border-gray-800">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $booking->booking_code }}</p>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $booking->guest_name }} &middot; {{ $booking->check_in->format('j M Y') }} - {{ $booking->check_out->format('j M Y') }}</p>
                                </div>
                                @php $paymentBadge = match($booking->payment_status) { 'lunas' => 'success', 'cicil' => 'warning', default => 'info', }; @endphp
                                <x-ui.badge :color="$paymentBadge">{{ strtoupper($booking->payment_status) }}</x-ui.badge>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">Belum ada booking yang dibuat.</div>
                    @endforelse
                </div>
            </x-common.component-card>
        </div>
    </div>
@endsection
