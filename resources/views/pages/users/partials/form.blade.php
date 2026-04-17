@php
    $selectedRoles = collect(old('role_ids', $user->roles?->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id)->all();
@endphp

<div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama</label>
        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
        @error('name')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Username</label>
        <input type="text" name="username" value="{{ old('username', $user->username) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
        @error('username')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
        @error('email')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Kata Sandi {{ $user->exists ? '(Kosongkan jika tidak diubah)' : '' }}</label>
        <input type="password" name="password" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
        @error('password')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
    </div>
</div>

<div class="mt-5">
    <label class="mb-3 block text-sm font-medium text-gray-700 dark:text-gray-300">Status User</label>
    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
        <label class="flex items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 dark:border-gray-800">
            <input type="radio" name="is_active" value="1" @checked((string) old('is_active', $user->exists ? (int) $user->is_active : 1) === '1') class="h-4 w-4 border-gray-300 text-brand-500 focus:ring-brand-500/20" />
            <span class="text-sm text-gray-700 dark:text-gray-300">Aktif</span>
        </label>
        <label class="flex items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 dark:border-gray-800">
            <input type="radio" name="is_active" value="0" @checked((string) old('is_active', $user->exists ? (int) $user->is_active : 1) === '0') class="h-4 w-4 border-gray-300 text-brand-500 focus:ring-brand-500/20" />
            <span class="text-sm text-gray-700 dark:text-gray-300">Nonaktif</span>
        </label>
    </div>
    @error('is_active')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
</div>

<div class="mt-5">
    <label class="mb-3 block text-sm font-medium text-gray-700 dark:text-gray-300">Role User</label>
    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($roles as $role)
            <label class="flex items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 dark:border-gray-800">
                <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" @checked(in_array($role->id, $selectedRoles, true)) class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/20" />
                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $role->name }}</span>
            </label>
        @endforeach
    </div>
    @error('role_ids')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
    @error('role_ids.*')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
</div>

<div class="mt-6 flex justify-end gap-3">
    <a href="{{ route('users.index') }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Batal</a>
    <button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">{{ $submitLabel }}</button>
</div>
