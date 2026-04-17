@php
    $flashMessages = collect([
        session('success') ? ['variant' => 'success', 'message' => session('success')] : null,
        session('error') ? ['variant' => 'error', 'message' => session('error')] : null,
        session('warning') ? ['variant' => 'warning', 'message' => session('warning')] : null,
    ])->filter()->values();
@endphp

@if ($flashMessages->isNotEmpty())
    <div class="space-y-3">
        @foreach ($flashMessages as $flash)
            <div
                x-data="{ visible: true }"
                x-init="setTimeout(() => visible = false, 4500)"
                x-show="visible"
                x-transition.opacity.duration.300ms
            >
                <x-ui.alert :variant="$flash['variant']" :message="$flash['message']" />
            </div>
        @endforeach
    </div>
@endif
