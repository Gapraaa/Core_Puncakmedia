<div
  x-cloak
  x-show="isLoading"
  x-transition.opacity.duration.150ms
  class="pointer-events-none fixed right-4 top-4 z-999999 sm:right-6 sm:top-6"
>
  <div
    class="flex min-w-[180px] items-center gap-3 rounded-2xl border border-gray-200 bg-white/96 px-4 py-3 shadow-theme-md dark:border-gray-800 dark:bg-gray-900/96"
  >
    <div class="h-9 w-9 animate-spin rounded-full border-[3px] border-solid border-brand-500 border-t-transparent"></div>
    <div>
      <p class="text-sm font-semibold text-gray-800 dark:text-white/90">Memuat</p>
      <p class="text-xs text-gray-500 dark:text-gray-400">Sedang membuka halaman...</p>
    </div>
  </div>
</div>
