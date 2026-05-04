<div
    x-data
    x-cloak
    x-show="$store.confirmDialog.open"
    @keydown.escape.window="$store.confirmDialog.close()"
    class="fixed inset-0 z-[99999] flex items-center justify-center p-5"
>
    <div
        @click="$store.confirmDialog.close()"
        class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm"
        x-transition.opacity.duration.200ms
    ></div>

    <div
        @click.stop
        x-transition:enter="transform transition ease-out duration-200"
        x-transition:enter-start="translate-y-3 scale-95 opacity-0"
        x-transition:enter-end="translate-y-0 scale-100 opacity-100"
        x-transition:leave="transform transition ease-in duration-150"
        x-transition:leave-start="translate-y-0 scale-100 opacity-100"
        x-transition:leave-end="translate-y-2 scale-95 opacity-0"
        class="relative w-full max-w-lg overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-2xl dark:border-gray-800 dark:bg-gray-900"
    >
        <div class="flex items-start gap-4 p-6">
            <div
                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl"
                :class="$store.confirmDialog.tone === 'danger'
                    ? 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-300'
                    : 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-300'"
            >
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 8V13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    <path d="M12 16.5V16.55" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                    <path d="M10.29 3.86L2.82 17.36C2.03 18.79 3.06 20.5 4.69 20.5H19.31C20.94 20.5 21.97 18.79 21.18 17.36L13.71 3.86C12.9 2.41 11.1 2.41 10.29 3.86Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                </svg>
            </div>

            <div class="min-w-0 flex-1">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90" x-text="$store.confirmDialog.title"></h3>
                <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300" x-text="$store.confirmDialog.message"></p>
            </div>
        </div>

        <div class="flex flex-col-reverse gap-3 border-t border-gray-100 px-6 py-5 sm:flex-row sm:justify-end dark:border-gray-800">
            <button
                type="button"
                @click="$store.confirmDialog.close()"
                class="rounded-xl border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                x-text="$store.confirmDialog.cancelLabel"
            ></button>
            <button
                type="button"
                @click="$store.confirmDialog.confirm()"
                class="rounded-xl px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition"
                :class="$store.confirmDialog.tone === 'danger'
                    ? 'bg-error-500 hover:bg-error-600'
                    : 'bg-brand-500 hover:bg-brand-600'"
                x-text="$store.confirmDialog.confirmLabel"
            ></button>
        </div>
    </div>
</div>

<style>
    [x-cloak] {
        display: none !important;
    }
</style>
