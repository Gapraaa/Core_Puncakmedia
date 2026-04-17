@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Audit Log" />

    @php
        $formatAuditValue = function ($value) {
            if (is_bool($value)) {
                return $value ? 'Ya' : 'Tidak';
            }

            if ($value === null || $value === '') {
                return '-';
            }

            if (is_array($value)) {
                return implode(', ', array_map(function ($item) {
                    if (is_bool($item)) {
                        return $item ? 'Ya' : 'Tidak';
                    }

                    return $item === null || $item === '' ? '-' : (string) $item;
                }, $value));
            }

            return (string) $value;
        };
    @endphp

    <div class="space-y-6">
        <div>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">Audit Log Aktivitas Sistem</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Semua aksi penting user seperti login, tambah, ubah, hapus, booking, invoice, dan pembayaran akan direkam di sini.</p>
            <div class="mt-4">
                <a
                    href="{{ route('audit-logs.export', request()->query()) }}"
                    class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
                >
                    Export CSV
                </a>
            </div>
        </div>

        <x-common.component-card title="Filter Audit Log" desc="Cari berdasarkan user, modul, aksi, tanggal, atau kata kunci.">
            <form method="GET" action="{{ route('audit-logs.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">
                <div class="xl:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pencarian</label>
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Aktivitas, target, user" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Modul</label>
                    <select name="module" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">
                        <option value="">Semua modul</option>
                        @foreach ($modules as $module)
                            <option value="{{ $module }}" @selected(($filters['module'] ?? '') === $module)>{{ ucfirst(str_replace('-', ' ', $module)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Aksi</label>
                    <select name="action" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">
                        <option value="">Semua aksi</option>
                        @foreach ($actions as $action)
                            <option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>{{ ucfirst(str_replace('_', ' ', $action)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">User</label>
                    <select name="user_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">
                        <option value="">Semua user</option>
                        @foreach ($users as $managedUser)
                            <option value="{{ $managedUser->id }}" @selected((string) ($filters['user_id'] ?? '') === (string) $managedUser->id)>{{ $managedUser->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Dari Tanggal</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Sampai Tanggal</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                </div>
                <div class="flex items-end gap-3 xl:col-span-6">
                    <button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Terapkan</button>
                    <a href="{{ route('audit-logs.index') }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Reset</a>
                </div>
            </form>
        </x-common.component-card>

        <x-common.component-card title="Riwayat Aktivitas" desc="Log terbaru dari seluruh aksi penting user di Core PMS.">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Waktu</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">User</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Modul</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aksi</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aktivitas</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Target</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($auditLogs as $auditLog)
                            <tr class="border-b border-gray-100 align-top dark:border-gray-800">
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    <div class="font-medium text-gray-800 dark:text-white/90">{{ $auditLog->created_at?->format('d M Y') }}</div>
                                    <div>{{ $auditLog->created_at?->format('H:i:s') }}</div>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    <div class="font-medium text-gray-800 dark:text-white/90">{{ $auditLog->user?->name ?? 'Sistem / Guest' }}</div>
                                    <div>{{ $auditLog->user?->username ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    <span class="rounded bg-gray-100 px-2 py-1 text-xs font-medium uppercase tracking-wide text-gray-700 dark:bg-white/[0.06] dark:text-gray-200">{{ str_replace('-', ' ', $auditLog->module) }}</span>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    <x-ui.badge :color="match($auditLog->action) { 'create', 'login' => 'success', 'delete', 'logout' => 'error', default => 'warning' }">
                                        {{ ucfirst(str_replace('_', ' ', $auditLog->action)) }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    <div class="font-medium text-gray-800 dark:text-white/90">{{ $auditLog->description }}</div>
                                    @if (! empty($auditLog->properties))
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            @foreach ($auditLog->properties as $key => $value)
                                                @if (!is_array($value))
                                                    <span class="rounded bg-brand-50 px-2 py-1 text-xs text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">{{ str_replace('_', ' ', $key) }}: {{ $value === null || $value === '' ? '-' : $value }}</span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                    <div class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                                        IP: {{ $auditLog->ip_address ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    <div class="font-medium text-gray-800 dark:text-white/90">{{ $auditLog->subject_label ?? '-' }}</div>
                                    @if ($auditLog->subject_type)
                                        <div>{{ class_basename($auditLog->subject_type) }}{{ $auditLog->subject_id ? ' #' . $auditLog->subject_id : '' }}</div>
                                    @endif
                                </td>
                            </tr>
                            @if (! empty($auditLog->before) || ! empty($auditLog->after))
                                <tr class="border-b border-gray-100 bg-gray-50/60 dark:border-gray-800 dark:bg-white/[0.02]">
                                    <td colspan="6" class="px-5 pb-5 pt-0">
                                        <details class="group mt-3 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900/[0.35]">
                                            <summary class="cursor-pointer list-none text-sm font-medium text-gray-700 dark:text-gray-200">
                                                Detail perubahan
                                                <span class="ml-2 text-xs text-gray-400 group-open:hidden">Klik untuk lihat before / after</span>
                                                <span class="ml-2 hidden text-xs text-gray-400 group-open:inline">Klik untuk tutup</span>
                                            </summary>

                                            <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-2">
                                                <div class="rounded-xl border border-error-100 bg-error-50/60 p-4 dark:border-error-900/40 dark:bg-error-500/5">
                                                    <h4 class="text-sm font-semibold text-error-700 dark:text-error-300">Sebelum</h4>
                                                    @if (! empty($auditLog->before))
                                                        <div class="mt-3 space-y-2">
                                                            @foreach ($auditLog->before as $key => $value)
                                                                <div class="flex flex-col gap-1 rounded-lg bg-white/80 px-3 py-2 text-sm dark:bg-white/[0.04]">
                                                                    <span class="text-xs font-medium uppercase tracking-wide text-error-600 dark:text-error-300">{{ str_replace('_', ' ', $key) }}</span>
                                                                    <span class="text-gray-700 dark:text-gray-200">{{ $formatAuditValue($value) }}</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Tidak ada data sebelumnya.</p>
                                                    @endif
                                                </div>

                                                <div class="rounded-xl border border-success-100 bg-success-50/60 p-4 dark:border-success-900/40 dark:bg-success-500/5">
                                                    <h4 class="text-sm font-semibold text-success-700 dark:text-success-300">Sesudah</h4>
                                                    @if (! empty($auditLog->after))
                                                        <div class="mt-3 space-y-2">
                                                            @foreach ($auditLog->after as $key => $value)
                                                                <div class="flex flex-col gap-1 rounded-lg bg-white/80 px-3 py-2 text-sm dark:bg-white/[0.04]">
                                                                    <span class="text-xs font-medium uppercase tracking-wide text-success-600 dark:text-success-300">{{ str_replace('_', ' ', $key) }}</span>
                                                                    <span class="text-gray-700 dark:text-gray-200">{{ $formatAuditValue($value) }}</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Tidak ada data sesudahnya.</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </details>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada audit log yang terekam.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>{{ $auditLogs->links() }}</div>
        </x-common.component-card>
    </div>
@endsection
