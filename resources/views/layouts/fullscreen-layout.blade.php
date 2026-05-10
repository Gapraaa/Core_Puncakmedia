<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} | {{ config('app.name', 'Core PMS') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo/logo-icon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">

    <script>
        (() => {
            const html = document.documentElement;
            const savedTheme = localStorage.getItem('theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            const theme = savedTheme || systemTheme;

            html.classList.toggle('dark', theme === 'dark');
        })();
    </script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

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
                    html.classList.toggle('dark', this.theme === 'dark');
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

</head>

<body
    class="min-h-screen bg-gray-50 text-gray-800 dark:bg-gray-900 dark:text-gray-100"
    x-data="{
        isLoading: false,
        loaderTimer: null,
        scrollStateKey() {
            return `scroll-state:${window.location.pathname}${window.location.search}`;
        },
        saveScrollState() {
            sessionStorage.setItem(this.scrollStateKey(), JSON.stringify({
                windowY: window.scrollY || window.pageYOffset || 0,
            }));
        },
        restoreScrollState() {
            const rawState = sessionStorage.getItem(this.scrollStateKey());

            if (!rawState) {
                return;
            }

            sessionStorage.removeItem(this.scrollStateKey());

            try {
                const state = JSON.parse(rawState);

                requestAnimationFrame(() => {
                    window.scrollTo({
                        top: Number(state.windowY ?? 0),
                        behavior: 'auto',
                    });
                });
            } catch (error) {
                // Ignore malformed session state and let the page use default scroll.
            }
        },
        hideLoader() {
            if (this.loaderTimer) {
                clearTimeout(this.loaderTimer);
                this.loaderTimer = null;
            }
            this.isLoading = false;
        },
        showLoader() {
            if (this.loaderTimer) {
                clearTimeout(this.loaderTimer);
                this.loaderTimer = null;
            }

            this.isLoading = true;
        },
        scheduleLoader() {
            if (this.loaderTimer) {
                clearTimeout(this.loaderTimer);
            }

            this.loaderTimer = window.setTimeout(() => {
                this.showLoader();
            }, 350);
        },
        shouldHandleNavigation(link) {
            if (!link) {
                return false;
            }

            if (link.target === '_blank' || link.hasAttribute('download') || link.dataset.skipLoading !== undefined) {
                return false;
            }

            if (link.dataset.showLoading === undefined) {
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
    window.addEventListener('pageshow', () => {
        restoreScrollState();
        hideLoader();
    });
    requestAnimationFrame(() => {
        restoreScrollState();
        hideLoader();
    });
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

        if (form.dataset.confirm && form.dataset.confirmBypassed !== 'true') {
            return;
        }

        if (form.method.toUpperCase() !== 'GET') {
            saveScrollState();
            scheduleLoader();
        }
    }, true);">

    {{-- preloader --}}
    <x-common.preloader/>
    {{-- preloader end --}}

    @yield('content')

</body>

@stack('scripts')

</html>
