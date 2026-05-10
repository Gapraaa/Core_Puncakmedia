@php
    $galleryImages = $villa->images->map(fn ($image) => [
        'id' => $image->id,
        'uuid' => $image->uuid,
        'preview_url' => $image->preview_url,
        'display_url' => $image->display_url,
        'original_name' => $image->original_name,
        'is_cover' => $image->is_cover,
        'status' => $image->status,
        'sort_order' => $image->sort_order,
    ])->values()->all();
@endphp

<div class="space-y-6">
    <x-common.component-card title="Upload Gambar Villa" desc="Upload banyak gambar sekaligus. File asli disimpan rapi per villa, lalu diproses ke WebP lewat queue agar siap dipakai untuk gallery dan cover.">
        <form method="POST" action="{{ route('villas.images.store', $villa) }}" enctype="multipart/form-data" class="space-y-4" data-toast-loading="Gambar sedang diunggah dan akan masuk antrean proses WebP." data-toast-loading-title="Mengunggah Gambar">
            @csrf

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pilih Gambar</label>
                <input
                    type="file"
                    name="images[]"
                    accept=".jpg,.jpeg,.png,.webp"
                    multiple
                    class="block w-full rounded-lg border border-dashed border-gray-300 bg-transparent px-4 py-4 text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-500 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-white hover:file:bg-brand-600 dark:border-gray-700 dark:text-gray-300 dark:file:bg-brand-600"
                />
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Format yang diterima: JPG, JPEG, PNG, WEBP. Maksimal 20 gambar per upload, 5 MB per file.</p>
                @error('images')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                @error('images.*')<p class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
            </div>

            <div class="flex justify-end">
                <button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                    Upload Gambar
                </button>
            </div>
        </form>
    </x-common.component-card>

    <x-common.component-card title="Gallery Villa" desc="Atur cover utama, hapus gambar yang tidak dipakai, dan simpan urutan gallery dengan drag and drop.">
        <div x-data="villaGalleryManager(@js(['images' => $galleryImages, 'statusUrl' => route('villas.images.index', $villa)]))" class="space-y-5">
            <template x-if="!hasImages()">
                <div class="rounded-xl border border-dashed border-gray-300 px-5 py-10 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    Belum ada gambar untuk villa ini. Upload gambar dulu agar gallery bisa diatur.
                </div>
            </template>

            <template x-if="hasImages()">
                <div class="space-y-5">
                    <div class="flex flex-col gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-4 dark:border-gray-800 dark:bg-white/[0.02] sm:flex-row sm:items-center sm:justify-between">
                        <div class="space-y-1">
                            <p class="text-sm font-medium text-gray-800 dark:text-white/90">Aksi Massal Gallery</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Pilih beberapa gambar lalu hapus sekaligus agar lebih cepat.</p>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <button
                                type="button"
                                @click="toggleSelectAll()"
                                class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5"
                                x-text="allSelected ? 'Batalkan Pilih Semua' : 'Pilih Semua'"
                            ></button>

                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                Terpilih <span x-text="selectedImageIds.length"></span> gambar
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        <template x-for="(image, index) in images" :key="image.id">
                            <div
                                class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.02]"
                                :class="isSelected(image.id) ? 'ring-2 ring-brand-500 ring-offset-2 ring-offset-white dark:ring-offset-gray-900' : ''"
                                draggable="true"
                                @dragstart="dragStart(index)"
                                @dragover.prevent
                                @drop="drop(index)"
                            >
                                <div class="relative aspect-[4/3] bg-gray-100 dark:bg-gray-800">
                                    <label class="absolute right-3 top-3 z-10 inline-flex cursor-pointer items-center rounded-full bg-white/90 px-2 py-1 shadow-sm backdrop-blur dark:bg-gray-900/80">
                                        <input
                                            type="checkbox"
                                            class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                                            :checked="isSelected(image.id)"
                                            @change="toggleSelected(image.id)"
                                        />
                                    </label>

                                    <template x-if="image.preview_url">
                                        <img
                                            :src="image.preview_url"
                                            :alt="image.original_name"
                                            class="h-full w-full object-cover transition duration-500"
                                            :class="image.status === 'ready' ? 'opacity-100' : 'opacity-45 scale-[1.02]'"
                                        />
                                    </template>
                                    <template x-if="!image.preview_url">
                                        <div class="absolute inset-0 animate-pulse bg-gradient-to-br from-gray-200 via-gray-100 to-gray-200 dark:from-gray-800 dark:via-gray-700 dark:to-gray-800"></div>
                                    </template>

                                    <div
                                        x-show="image.status === 'pending' || image.status === 'processing'"
                                        class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-black/35 backdrop-blur-[1px]"
                                    >
                                        <div class="h-12 w-12 animate-spin rounded-full border-4 border-white/25 border-t-white"></div>
                                        <div class="space-y-1 text-center text-white">
                                            <p class="text-sm font-semibold" x-text="image.status === 'pending' ? 'Masuk antrean proses' : 'Sedang memproses gambar'"></p>
                                            <p class="text-xs text-white/80">Mohon tunggu, versi preview WebP sedang disiapkan.</p>
                                        </div>
                                        <div class="h-1.5 w-36 overflow-hidden rounded-full bg-white/20">
                                            <div class="h-full w-1/2 animate-pulse rounded-full bg-white"></div>
                                        </div>
                                    </div>

                                    <div
                                        x-show="image.status === 'failed'"
                                        class="absolute inset-x-4 bottom-4 rounded-xl border border-error-300 bg-error-500/90 px-3 py-2 text-xs font-medium text-white shadow-lg"
                                    >
                                        Proses gambar gagal. Coba upload ulang atau proses ulang dari server queue.
                                    </div>

                                    <div class="absolute left-3 top-3 flex flex-wrap gap-2">
                                        <span class="inline-flex items-center rounded-full bg-black/70 px-2.5 py-1 text-[11px] font-medium text-white">Urutan <span class="ml-1" x-text="index + 1"></span></span>
                                        <span x-show="image.is_cover" class="inline-flex items-center rounded-full bg-brand-500 px-2.5 py-1 text-[11px] font-medium text-white">Cover</span>
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-medium"
                                            :class="{
                                                'bg-success-500 text-white': image.status === 'ready',
                                                'bg-warning-500 text-white': image.status === 'processing' || image.status === 'pending',
                                                'bg-error-500 text-white': image.status === 'failed'
                                            }"
                                            x-text="image.status === 'ready' ? 'Ready' : (image.status === 'failed' ? 'Failed' : 'Processing')"
                                        ></span>
                                    </div>
                                </div>

                                <div class="space-y-4 p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-medium text-gray-800 dark:text-white/90" x-text="image.original_name"></p>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Drag untuk ubah urutan gallery.</p>
                                        </div>
                                        <span class="cursor-move text-lg text-gray-400">⋮⋮</span>
                                    </div>

                                    <div class="flex flex-wrap gap-2">
                                        <form
                                            x-show="image.status === 'failed'"
                                            method="POST"
                                            :action="`{{ url('master-data/villas/'.$villa->id.'/images') }}/${image.id}/retry`"
                                            data-toast-loading="Gambar sedang dikirim ulang ke antrean proses."
                                            data-toast-loading-title="Retry Proses Gambar"
                                        >
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded-lg border border-warning-200 px-3 py-2 text-xs font-medium text-warning-700 transition hover:bg-warning-50 dark:border-warning-800 dark:text-warning-300 dark:hover:bg-warning-500/10">
                                                Retry Proses
                                            </button>
                                        </form>

                                        <form method="POST" :action="`{{ url('master-data/villas/'.$villa->id.'/images') }}/${image.id}/cover`" data-toast-loading="Cover villa sedang diperbarui." data-toast-loading-title="Memperbarui Cover">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded-lg border border-brand-200 px-3 py-2 text-xs font-medium text-brand-600 transition hover:bg-brand-50 dark:border-brand-800/50 dark:text-brand-300 dark:hover:bg-brand-500/10">
                                                Jadikan Cover
                                            </button>
                                        </form>

                                        <form method="POST" :action="`{{ url('master-data/villas/'.$villa->id.'/images') }}/${image.id}`" data-confirm="Hapus gambar ini dari gallery villa?" data-confirm-title="Hapus Gambar Gallery" data-confirm-label="Ya, hapus gambar" data-toast-loading="Gambar sedang dihapus dari gallery villa." data-toast-loading-title="Menghapus Gambar">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-error-200 px-3 py-2 text-xs font-medium text-error-600 transition hover:bg-error-50 dark:border-error-800 dark:text-error-400 dark:hover:bg-error-500/10">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('villas.images.bulk-destroy', $villa) }}"
                        class="space-y-4"
                        data-confirm="Hapus semua gambar yang sedang dipilih dari gallery villa?"
                        data-confirm-title="Hapus Beberapa Gambar"
                        data-confirm-label="Ya, hapus gambar terpilih"
                        data-toast-loading="Gambar terpilih sedang dihapus dari gallery villa."
                        data-toast-loading-title="Menghapus Gambar Terpilih"
                    >
                        @csrf
                        @method('DELETE')

                        <template x-for="imageId in selectedImageIds" :key="`selected-${imageId}`">
                            <input type="hidden" name="selected_image_ids[]" :value="imageId">
                        </template>

                        <div class="flex flex-wrap justify-end gap-3">
                            <button
                                type="button"
                                @click="clearSelection()"
                                x-show="hasSelection"
                                class="rounded-lg border border-gray-300 px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5"
                            >
                                Bersihkan Pilihan
                            </button>

                            <button
                                type="submit"
                                :disabled="!hasSelection"
                                class="rounded-lg bg-error-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-error-600 disabled:cursor-not-allowed disabled:opacity-40"
                            >
                                Hapus Gambar Terpilih
                            </button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('villas.images.reorder', $villa) }}" class="space-y-4" data-toast-loading="Urutan gallery sedang disimpan." data-toast-loading-title="Menyimpan Urutan Gallery">
                        @csrf
                        @method('PATCH')
                        <template x-for="image in images" :key="`sort-${image.id}`">
                            <input type="hidden" name="ordered_image_ids[]" :value="image.id">
                        </template>

                        <div class="flex justify-end">
                            <button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                                Simpan Urutan Gallery
                            </button>
                        </div>
                    </form>
                </div>
            </template>
        </div>
    </x-common.component-card>
</div>
