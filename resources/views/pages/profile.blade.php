@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Profil Saya" />

    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-18 w-18 items-center justify-center rounded-full bg-brand-500 text-xl font-semibold text-white">
                        {{ collect(explode(' ', trim((string) $user?->name)))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('') ?: 'PM' }}
                    </div>
                    <div>
                        <h2 class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $user?->name }}</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $user?->email }}</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($user?->roles ?? [] as $role)
                                <span class="rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">{{ $role->name }}</span>
                            @endforeach
                            <span class="rounded-full {{ $user?->is_active ? 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-300' : 'bg-error-50 text-error-700 dark:bg-error-500/10 dark:text-error-300' }} px-3 py-1 text-xs font-medium">
                                {{ $user?->is_active ? 'AKTIF' : 'NONAKTIF' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 text-sm text-gray-600 dark:border-gray-800 dark:bg-gray-900/40 dark:text-gray-300">
                    Halaman ini menampilkan data akun internal yang sedang login.
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <x-common.component-card title="Informasi Akun" desc="Data inti akun yang dipakai untuk login dan akses dashboard.">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Nama Lengkap</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $user?->name }}</p>
                    </div>
                    <div>
                        <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Username</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $user?->username }}</p>
                    </div>
                    <div>
                        <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Email</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $user?->email }}</p>
                    </div>
                    <div>
                        <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Status Akun</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $user?->is_active ? 'Aktif' : 'Nonaktif' }}</p>
                    </div>
                </div>
            </x-common.component-card>

            <x-common.component-card title="Role & Akses" desc="Ringkasan role yang menentukan menu dan hak akses kamu di sistem.">
                <div class="space-y-3">
                    @forelse ($user?->roles ?? [] as $role)
                        <div class="rounded-xl border border-gray-100 px-4 py-3 dark:border-gray-800">
                            <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $role->name }}</p>
                            <p class="mt-1 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $role->slug }}</p>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                            Akun ini belum memiliki role.
                        </div>
                    @endforelse
                </div>
            </x-common.component-card>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <x-common.component-card title="Edit Profil" desc="Perbarui nama, username, dan email akun yang sedang login.">
                <form method="POST" action="{{ route('profile.update') }}" class="space-y-4"
                    data-toast-loading="Profil akun sedang diperbarui."
                    data-toast-loading-title="Menyimpan Profil">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name', $user?->name) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                        @error('name')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Username</label>
                        <input type="text" name="username" value="{{ old('username', $user?->username) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                        @error('username')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user?->email) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                        @error('email')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Simpan Profil</button>
                    </div>
                </form>
            </x-common.component-card>

            <x-common.component-card title="Ganti Kata Sandi" desc="Gunakan kata sandi saat ini untuk mengatur kata sandi baru.">
                <form method="POST" action="{{ route('profile.password.update') }}" class="space-y-4"
                    data-toast-loading="Kata sandi baru sedang disimpan."
                    data-toast-loading-title="Memperbarui Kata Sandi">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Kata Sandi Saat Ini</label>
                        <input type="password" name="current_password" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                        @error('current_password')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Kata Sandi Baru</label>
                        <input type="password" name="password" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                        @error('password')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Konfirmasi Kata Sandi Baru</label>
                        <input type="password" name="password_confirmation" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Simpan Kata Sandi</button>
                    </div>
                </form>
            </x-common.component-card>
        </div>
    </div>
@endsection
