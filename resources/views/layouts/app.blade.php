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

            Alpine.store('dashboardOnboarding', {
                active: false,
                currentStepIndex: 0,
                steps: [],
                storageKey: 'interviewpilot.dashboard-onboarding-complete',
                initialized: false,

                init() {
                    if (this.initialized) {
                        return;
                    }

                    window.addEventListener('keydown', (event) => {
                        if (! this.active) {
                            return;
                        }

                        if (event.key === 'ArrowRight') {
                            event.preventDefault();
                            this.next();
                        }

                        if (event.key === 'ArrowLeft') {
                            event.preventDefault();
                            this.previous();
                        }

                        if (event.key === 'Escape') {
                            event.preventDefault();
                            this.finish();
                        }
                    });

                    this.initialized = true;
                },

                configure(steps = []) {
                    this.steps = Array.isArray(steps) ? steps : [];
                    this.currentStepIndex = 0;
                },

                bootDashboard(steps = [], options = {}) {
                    this.init();
                    this.configure(steps);

                    if (this.steps.length === 0) {
                        return;
                    }

                    const query = new URLSearchParams(window.location.search);
                    const force = options.force === true || query.get('tour') === '1';
                    const completed = window.localStorage.getItem(this.storageKey) === '1';

                    if (force || ! completed) {
                        this.start();
                    }
                },

                start(index = 0) {
                    if (this.steps.length === 0) {
                        return;
                    }

                    this.currentStepIndex = Math.max(0, Math.min(index, this.steps.length - 1));
                    this.active = true;
                    document.body.classList.add('overflow-hidden');
                    this.syncStep();
                },

                restart() {
                    window.localStorage.removeItem(this.storageKey);
                    this.start(0);
                },

                finish() {
                    window.localStorage.setItem(this.storageKey, '1');
                    this.active = false;
                    document.body.classList.remove('overflow-hidden');

                    const sidebarStore = Alpine.store('sidebar');

                    if (sidebarStore && window.innerWidth < 1280) {
                        sidebarStore.setMobileOpen(false);
                    }
                },

                next() {
                    if (this.currentStepIndex >= this.steps.length - 1) {
                        this.finish();
                        return;
                    }

                    this.currentStepIndex += 1;
                    this.syncStep();
                },

                previous() {
                    if (this.currentStepIndex <= 0) {
                        return;
                    }

                    this.currentStepIndex -= 1;
                    this.syncStep();
                },

                goTo(index) {
                    const nextIndex = Number(index);

                    if (Number.isNaN(nextIndex) || nextIndex < 0 || nextIndex >= this.steps.length) {
                        return;
                    }

                    this.currentStepIndex = nextIndex;
                    this.syncStep();
                },

                currentStep() {
                    return this.steps[this.currentStepIndex] ?? null;
                },

                currentTarget() {
                    return this.currentStep()?.target ?? null;
                },

                currentArea() {
                    return this.currentStep()?.area ?? 'content';
                },

                isTarget(target) {
                    return this.active && this.currentTarget() === target;
                },

                targetClass(target, area = 'content') {
                    const classes = ['dashboard-tour-target'];

                    if (! this.active || ! target) {
                        return classes.join(' ');
                    }

                    if (this.isTarget(target)) {
                        classes.push('dashboard-tour-target-active');
                    } else if (area === 'sidebar' && this.currentArea() === 'sidebar') {
                        classes.push('dashboard-tour-target-muted');
                    }

                    return classes.join(' ');
                },

                stepLabel() {
                    return `Step ${this.currentStepIndex + 1} of ${this.steps.length}`;
                },

                syncStep() {
                    if (! this.active) {
                        return;
                    }

                    const step = this.currentStep();
                    const sidebarStore = Alpine.store('sidebar');

                    if (sidebarStore && step?.area === 'sidebar') {
                        if (window.innerWidth < 1280) {
                            sidebarStore.setMobileOpen(true);
                        } else {
                            sidebarStore.isExpanded = true;
                        }
                    }

                    requestAnimationFrame(() => {
                        requestAnimationFrame(() => {
                            this.scrollCurrentTargetIntoView();
                        });
                    });
                },

                scrollCurrentTargetIntoView() {
                    const target = this.currentTarget();

                    if (! target) {
                        return;
                    }

                    const element = document.querySelector(`[data-dashboard-tour-target="${target}"]`);

                    if (! element) {
                        return;
                    }

                    element.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center',
                        inline: 'nearest',
                    });
                },
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

    <script>
        window.__INTERVIEW_WORKSPACE__ = @json($interviewWorkspaceBootstrap ?? ['setup' => null, 'sessions' => []]);
        window.__INTERVIEW_WORKSPACE_ROUTES__ = @json($interviewWorkspaceRoutes ?? []);
        window.__INTERVIEW_CHATBOT__ = @json($interviewChatbotBootstrap ?? ['defaultProviderId' => 'auto', 'providers' => []]);
        window.__INTERVIEW_AUDIO__ = @json($interviewAudioBootstrap ?? ['configured' => false, 'provider' => 'browser']);
    </script>
    
</head>

@php($appShellClass = auth()->user()?->isAdmin() ? 'app-shell-admin' : 'app-shell-user')

<body
    class="overflow-x-hidden {{ $appShellClass }}"
    x-data="{ 'loaded': true}"
    x-init="$store.sidebar.isExpanded = window.innerWidth >= 1280;
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

    <div class="min-h-screen overflow-x-hidden xl:flex">
        @include('layouts.backdrop')
        @include('layouts.sidebar')

        <div class="min-w-0 flex-1 transition-all duration-300 ease-in-out"
            :class="{
                'xl:ml-[290px]': $store.sidebar.isExpanded || $store.sidebar.isHovered,
                'xl:ml-[90px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
                'ml-0': $store.sidebar.isMobileOpen
            }">
            <!-- app header start -->
            @include('layouts.app-header')
            <!-- app header end -->
            <div class="mx-auto max-w-(--breakpoint-2xl) px-3 py-3 sm:px-5 sm:py-4 md:p-6">
                @yield('content')
            </div>
        </div>

    </div>

</body>

@stack('scripts')

</html>
