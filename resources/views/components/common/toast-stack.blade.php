<div
    x-data
    x-cloak
    class="pointer-events-none fixed bottom-4 right-4 z-[99998] flex w-full max-w-sm flex-col gap-3 sm:bottom-6 sm:right-6"
>
    <template x-for="toast in $store.toastCenter.toasts" :key="toast.id">
        <div
            x-show="true"
            x-transition:enter="transform transition ease-out duration-300"
            x-transition:enter-start="translate-y-3 opacity-0 sm:translate-x-8 sm:translate-y-0"
            x-transition:enter-end="translate-x-0 translate-y-0 opacity-100"
            x-transition:leave="transform transition ease-in duration-200"
            x-transition:leave-start="translate-x-0 opacity-100"
            x-transition:leave-end="translate-y-2 opacity-0 sm:translate-x-6 sm:translate-y-0"
            class="pointer-events-auto overflow-hidden rounded-2xl border bg-white shadow-2xl ring-1 ring-black/5 dark:bg-gray-900"
            :class="{
                'border-success-200 dark:border-success-500/30': toast.variant === 'success',
                'border-error-200 dark:border-error-500/30': toast.variant === 'error',
                'border-warning-200 dark:border-warning-500/30': toast.variant === 'warning',
                'border-brand-200 dark:border-brand-500/30': toast.variant === 'info'
            }"
        >
            <div class="flex items-start gap-3 p-4">
                <div
                    class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl text-sm font-semibold"
                    :class="{
                        'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-300': toast.variant === 'success',
                        'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-300': toast.variant === 'error',
                        'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-300': toast.variant === 'warning',
                        'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-300': toast.variant === 'info'
                    }"
                    x-text="toast.variant === 'success' ? 'OK' : (toast.variant === 'error' ? 'ERR' : (toast.variant === 'warning' ? '!' : 'i'))"
                ></div>

                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-gray-800 dark:text-white/90" x-text="toast.title"></p>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300" x-text="toast.message"></p>
                </div>

                <button
                    type="button"
                    @click="$store.toastCenter.remove(toast.id)"
                    class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.05] dark:hover:text-gray-200"
                >
                    <span class="sr-only">Tutup notifikasi</span>
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M5 5L15 15M15 5L5 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>

            <div class="h-1 w-full bg-gray-100 dark:bg-white/[0.06]">
                <div
                    class="h-full origin-left animate-[toast-shrink_var(--toast-duration,4.5s)_linear_forwards]"
                    :style="`--toast-duration:${Math.max((toast.duration || 4500) / 1000, 0.2)}s;`"
                    :class="{
                        'bg-success-500': toast.variant === 'success',
                        'bg-error-500': toast.variant === 'error',
                        'bg-warning-500': toast.variant === 'warning',
                        'bg-brand-500': toast.variant === 'info'
                    }"
                ></div>
            </div>
        </div>
    </template>
</div>

<style>
    @keyframes toast-shrink {
        from { transform: scaleX(1); }
        to { transform: scaleX(0); }
    }
</style>
