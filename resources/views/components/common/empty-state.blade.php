@props([
    'title' => 'Belum Ada Data',
    'description' => 'Belum ada data yang bisa ditampilkan.',
    'actionLabel' => null,
    'actionHref' => null,
    'secondaryActionLabel' => null,
    'secondaryActionHref' => null,
    'compact' => false,
])

<div @class([
    'rounded-2xl border border-dashed border-gray-300 bg-gray-50/70 text-center dark:border-gray-700 dark:bg-white/[0.02]',
    'px-6 py-8' => $compact,
    'px-6 py-14' => ! $compact,
])>
    <div class="mx-auto max-w-2xl">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-brand-50 text-brand-500 dark:bg-brand-500/10 dark:text-brand-400">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M12 7V12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                <path d="M12 16H12.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                <circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.5"/>
            </svg>
        </div>
        <h3 class="mt-4 text-base font-semibold text-gray-800 dark:text-white/90">{{ $title }}</h3>
        <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $description }}</p>

        @if ($actionLabel && $actionHref)
            <div class="mt-5 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ $actionHref }}" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                    {{ $actionLabel }}
                </a>

                @if ($secondaryActionLabel && $secondaryActionHref)
                    <a href="{{ $secondaryActionHref }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                        {{ $secondaryActionLabel }}
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
