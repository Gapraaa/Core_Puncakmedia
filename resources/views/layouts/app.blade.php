<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} | TailAdmin - Laravel Tailwind CSS Admin Dashboard Template</title>

    <script>
        (() => {
            const html = document.documentElement;
            const savedTheme = localStorage.getItem('theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            const theme = savedTheme || systemTheme;
            const savedSidebarExpanded = localStorage.getItem('sidebar_expanded');
            const isDesktop = window.innerWidth >= 1280;
            const isSidebarExpanded = isDesktop ? (savedSidebarExpanded !== null ? savedSidebarExpanded === 'true' : true) : false;

            html.classList.toggle('dark', theme === 'dark');
            html.classList.toggle('sidebar-expanded', isSidebarExpanded);
            html.classList.toggle('sidebar-collapsed', !isSidebarExpanded);
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
                isExpanded: window.innerWidth >= 1280,
                isMobileOpen: false,
                isHovered: false,
                init() {
                    this.syncWithViewport();
                },
                persistExpanded() {
                    localStorage.setItem('sidebar_expanded', this.isExpanded ? 'true' : 'false');
                },
                syncDocumentState() {
                    const html = document.documentElement;
                    html.classList.toggle('sidebar-expanded', this.isExpanded);
                    html.classList.toggle('sidebar-collapsed', !this.isExpanded);
                },
                syncWithViewport() {
                    const isDesktop = window.innerWidth >= 1280;

                    if (!isDesktop) {
                        this.isExpanded = false;
                        this.isHovered = false;
                        this.isMobileOpen = false;
                        this.syncDocumentState();
                        return;
                    }

                    const savedSidebarExpanded = localStorage.getItem('sidebar_expanded');
                    this.isExpanded = savedSidebarExpanded !== null ? savedSidebarExpanded === 'true' : true;
                    this.isHovered = false;
                    this.isMobileOpen = false;
                    this.syncDocumentState();
                },

                toggleExpanded() {
                    this.isExpanded = !this.isExpanded;
                    this.persistExpanded();
                    this.syncDocumentState();
                    this.isMobileOpen = false;
                },

                toggleMobileOpen() {
                    this.isMobileOpen = !this.isMobileOpen;
                },

                setMobileOpen(val) {
                    this.isMobileOpen = val;
                },

                setHovered(val) {
                    if (window.innerWidth >= 1280 && !this.isExpanded) {
                        this.isHovered = val;
                    }
                }
            });
        });
    </script>
</head>

<body
    x-data="{
        loaded: true,
        loaderTimer: null,
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
    x-init="$store.sidebar.init();
    const syncSidebarViewport = () => $store.sidebar.syncWithViewport();
    window.addEventListener('resize', syncSidebarViewport);
    window.addEventListener('pageshow', () => {
        hideLoader();
    });
    requestAnimationFrame(() => hideLoader());
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

        if (form.method.toUpperCase() !== 'GET') {
            scheduleLoader();
        }
    }, true);">

    {{-- preloader --}}
    <x-common.preloader />
    {{-- preloader end --}}

    <div class="min-h-screen xl:flex">
        @include('layouts.backdrop')
        @include('layouts.sidebar')

        <div id="app-shell-main" class="flex-1 xl:ml-[290px]"
            :class="{
                'xl:ml-[290px]': $store.sidebar.isExpanded || $store.sidebar.isHovered,
                'xl:ml-[90px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
                'ml-0': $store.sidebar.isMobileOpen
            }">
            <!-- app header start -->
            @include('layouts.app-header')
            <!-- app header end -->
            <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
                <x-common.flash-messages />
                @yield('content')
            </div>
        </div>

    </div>

    <x-common.toast-stack />
    <x-common.confirm-dialog />

</body>

@stack('scripts')

</html>
