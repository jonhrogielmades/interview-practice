<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} | InterviewPilot</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo/interviewpilot-icon.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo/interviewpilot-icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo/interviewpilot-icon.png') }}">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    {{-- <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script> --}}

    <!-- Theme Store -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                colors: [
                    { id: 'ocean', label: 'Ocean', swatch: '#465fff', accent: '#0ba5ec' },
                    { id: 'emerald', label: 'Emerald', swatch: '#12b76a', accent: '#06b6d4' },
                ],
                init() {
                    const savedTheme = localStorage.getItem('theme');
                    const savedColor = localStorage.getItem('theme-color');
                    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' :
                        'light';
                    this.theme = savedTheme || systemTheme;
                    this.color = this.isValidColor(savedColor) ? savedColor : 'ocean';
                    this.updateTheme();
                },
                theme: 'light',
                color: 'ocean',
                isValidColor(color) {
                    return this.colors.some((option) => option.id === color);
                },
                setColor(color) {
                    if (! this.isValidColor(color)) {
                        return;
                    }

                    this.color = color;
                    localStorage.setItem('theme-color', color);
                    this.updateTheme();
                },
                toggle() {
                    this.theme = this.theme === 'light' ? 'dark' : 'light';
                    localStorage.setItem('theme', this.theme);
                    this.updateTheme();
                },
                updateTheme() {
                    const html = document.documentElement;
                    const body = document.body;
                    html.dataset.themeColor = this.color;

                    if (this.theme === 'dark') {
                        html.classList.add('dark');
                        body?.classList.add('dark', 'bg-gray-900');
                    } else {
                        html.classList.remove('dark');
                        body?.classList.remove('dark', 'bg-gray-900');
                    }
                }
            });

            Alpine.store('sidebar', {
                // Initialize based on screen size
                isExpanded: window.innerWidth >= 1280, // true for desktop, false for mobile
                isMobileOpen: false,
                isHovered: false,

                syncDocumentState() {
                    const shouldLock = window.innerWidth < 1280 && this.isMobileOpen;
                    document.documentElement.classList.toggle('sidebar-mobile-open', shouldLock);
                    document.body.classList.toggle('sidebar-mobile-open', shouldLock);
                },

                toggleExpanded() {
                    this.isExpanded = !this.isExpanded;
                    // When toggling desktop sidebar, ensure mobile menu is closed
                    this.isMobileOpen = false;
                    this.syncDocumentState();
                },

                toggleMobileOpen() {
                    this.isMobileOpen = !this.isMobileOpen;
                    // Don't modify isExpanded when toggling mobile menu
                    this.syncDocumentState();
                },

                setMobileOpen(val) {
                    this.isMobileOpen = val;
                    this.syncDocumentState();
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
            const savedColor = localStorage.getItem('theme-color');
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            const theme = savedTheme || systemTheme;
            const color = ['ocean', 'emerald'].includes(savedColor) ? savedColor : 'ocean';
            document.documentElement.dataset.themeColor = color;

            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
                document.body?.classList.add('dark', 'bg-gray-900');
            } else {
                document.documentElement.classList.remove('dark');
                document.body?.classList.remove('dark', 'bg-gray-900');
            }
        })();
    </script>
</head>

<body x-data="{ 'loaded': true}" x-init="$store.sidebar.isExpanded = window.innerWidth >= 1280;
const checkMobile = () => {
    if (window.innerWidth < 1280) {
        $store.sidebar.isExpanded = false;
    } else {
        $store.sidebar.isExpanded = true;
        $store.sidebar.isHovered = false;
    }
    $store.sidebar.setMobileOpen(false);
    $store.sidebar.syncDocumentState();
};
checkMobile();
window.addEventListener('resize', checkMobile);">

    {{-- preloader --}}
    <x-common.preloader/>
    {{-- preloader end --}}

    @yield('content')

</body>

@stack('scripts')
<script src="{{ asset('js/ai-translator.js') }}"></script>

</html>
