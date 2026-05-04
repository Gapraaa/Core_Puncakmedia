<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} | TailAdmin - Laravel Tailwind CSS Admin Dashboard Template</title>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    {{-- <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script> --}}

    <!-- Theme Store -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                init() {
                    const savedTheme = localStorage.getItem('theme');
                    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' :
                        'light';
                    this.theme = savedTheme || systemTheme;
                    this.updateTheme();
                },
                theme: 'light',
                toggle() {
                    this.theme = this.theme === 'light' ? 'dark' : 'light';
                    localStorage.setItem('theme', this.theme);
                    this.updateTheme();
                },
                updateTheme() {
                    const html = document.documentElement;
                    const body = document.body;
                    if (this.theme === 'dark') {
                        html.classList.add('dark');
                        body.classList.add('dark', 'bg-gray-900');
                    } else {
                        html.classList.remove('dark');
                        body.classList.remove('dark', 'bg-gray-900');
                    }
                }
            });

            Alpine.store('sidebar', {
                // Initialize based on screen size
                isExpanded: window.innerWidth >= 1280, // true for desktop, false for mobile
                isMobileOpen: false,
                isHovered: false,

                toggleExpanded() {
                    this.isExpanded = !this.isExpanded;
                    // When toggling desktop sidebar, ensure mobile menu is closed
                    this.isMobileOpen = false;
                },

                toggleMobileOpen() {
                    this.isMobileOpen = !this.isMobileOpen;
                    // Don't modify isExpanded when toggling mobile menu
                },

                setMobileOpen(val) {
                    this.isMobileOpen = val;
                },

                setHovered(val) {
                    // Only allow hover effects on desktop when sidebar is collapsed
                    if (window.innerWidth >= 1280 && !this.isExpanded) {
                        this.isHovered = val;
                    }
                }
            });
        });
    </script>

    <!-- Apply dark mode immediately to prevent flash -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            const theme = savedTheme || systemTheme;
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
                document.body.classList.add('dark', 'bg-gray-900');
            } else {
                document.documentElement.classList.remove('dark');
                document.body.classList.remove('dark', 'bg-gray-900');
            }
        })();
    </script>
</head>

<body
    x-data="{
        loaded: true,
        loaderTimer: null,
        loaderInitialized: false,
        hideLoader() {
            if (this.loaderTimer) {
                clearTimeout(this.loaderTimer);
                this.loaderTimer = null;
            }
            this.loaded = false;
        },
        showLoader() {
            if (this.loaderTimer) {
                clearTimeout(this.loaderTimer);
                this.loaderTimer = null;
            }

            this.loaded = true;
        },
        scheduleLoader() {
            if (this.loaderTimer) {
                clearTimeout(this.loaderTimer);
            }

            this.loaderTimer = window.setTimeout(() => {
                this.showLoader();
            }, 120);
        },
        shouldHandleNavigation(link) {
            if (!link) {
                return false;
            }

            if (link.target === '_blank' || link.hasAttribute('download') || link.dataset.skipLoading !== undefined) {
                return false;
            }

            const href = link.getAttribute('href');

            if (!href || href.startsWith('#') || href.startsWith('javascript:')) {
                return false;
            }

            try {
                const url = new URL(link.href, window.location.origin);

                return url.origin === window.location.origin;
            } catch (error) {
                return false;
            }
        },
        initializeLoader() {
            if (this.loaderInitialized) {
                return;
            }

            this.loaderInitialized = true;
            requestAnimationFrame(() => {
                this.hideLoader();
            });
        }
    }"
    x-init="$store.sidebar.isExpanded = window.innerWidth >= 1280;
    const checkMobile = () => {
        if (window.innerWidth < 1280) {
            $store.sidebar.setMobileOpen(false);
            $store.sidebar.isExpanded = false;
        } else {
            $store.sidebar.isMobileOpen = false;
            $store.sidebar.isExpanded = true;
        }
    };
    checkMobile();
    window.addEventListener('resize', checkMobile);
    window.addEventListener('pageshow', () => hideLoader());
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        initializeLoader();
    } else {
        document.addEventListener('DOMContentLoaded', () => initializeLoader(), { once: true });
    }
    document.addEventListener('click', (event) => {
        const link = event.target.closest('a');

        if (shouldHandleNavigation(link)) {
            scheduleLoader();
        }
    }, true);
    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement) || form.dataset.skipLoading !== undefined) {
            return;
        }

        scheduleLoader();
    }, true);">

    {{-- preloader --}}
    <x-common.preloader/>
    {{-- preloader end --}}

    @yield('content')

</body>

@stack('scripts')

</html>
