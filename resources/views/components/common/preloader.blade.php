<div
  x-cloak
  x-show="loaded"
  x-transition:enter="transition-opacity duration-150 ease-out"
  x-transition:enter-start="opacity-0"
  x-transition:enter-end="opacity-100"
  x-transition:leave="transition-opacity duration-200 ease-in"
  x-transition:leave-start="opacity-100"
  x-transition:leave-end="opacity-0"
  class="fixed left-0 top-0 z-999999 flex h-screen w-screen items-center justify-center bg-white dark:bg-black"
>
  <div
    class="h-16 w-16 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent"
  ></div>
</div>
