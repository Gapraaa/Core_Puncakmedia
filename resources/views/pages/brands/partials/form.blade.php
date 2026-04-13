<div class="grid grid-cols-12 gap-4 md:gap-6">
    <div class="col-span-12 xl:col-span-8">
        <x-common.component-card :title="$pageTitle" :desc="$pageDescription">
            <form method="POST" action="{{ $formAction }}" class="space-y-6">
                @csrf
                @isset($httpMethod)
                    @method($httpMethod)
                @endisset

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Brand</label>
                        <input type="text" name="name" value="{{ old('name', $brand->name) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                        @error('name')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Slug</label>
                        <input type="text" name="slug" value="{{ old('slug', $brand->slug) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Kosongkan jika ingin dibuat otomatis dari nama brand.</p>
                        @error('slug')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Path Logo</label>
                    <input type="text" name="logo" value="{{ old('logo', $brand->logo) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90" />
                    @error('logo')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('brands.index') }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Batal</a>
                    <button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">{{ $submitLabel }}</button>
                </div>
            </form>
        </x-common.component-card>
    </div>

    <div class="col-span-12 xl:col-span-4">
        <x-common.component-card title="Catatan Brand" desc="Jaga konsistensi data master agar booking dan referensi tetap sinkron.">
            <div class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
                <div class="rounded-xl border border-gray-100 px-4 py-3 dark:border-gray-800">Gunakan penamaan brand yang sama seperti di dokumentasi bisnis.</div>
                <div class="rounded-xl border border-gray-100 px-4 py-3 dark:border-gray-800">Brand digunakan untuk mengelompokkan villa dan booking berdasarkan channel pemasaran.</div>
                <div class="rounded-xl border border-gray-100 px-4 py-3 dark:border-gray-800">Form ini sengaja tetap berada dalam sistem card dan input TailAdmin.</div>
            </div>
        </x-common.component-card>
    </div>

</div>

