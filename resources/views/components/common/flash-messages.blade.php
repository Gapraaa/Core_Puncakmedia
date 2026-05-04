@php
    $flashMessages = collect([
        session('success') ? ['variant' => 'success', 'message' => session('success')] : null,
        session('error') ? ['variant' => 'error', 'message' => session('error')] : null,
        session('warning') ? ['variant' => 'warning', 'message' => session('warning')] : null,
        session('info') ? ['variant' => 'info', 'message' => session('info')] : null,
    ])->filter()->values();
@endphp

@if ($flashMessages->isNotEmpty())
    <div
        x-data
        x-init='
            const flashes = @json($flashMessages);
            flashes.forEach((flash) => Alpine.store("toastCenter").push(flash));
        '
        class="hidden"
        aria-hidden="true"
    ></div>
@endif
