@extends('layouts.fullscreen-layout')

@php
    $authRequestView = request()->query('auth');
    $authOldForm = old('auth_form');
    $initialAuthView = in_array($authOldForm, ['signin', 'signup'], true)
        ? $authOldForm
        : (in_array($authRequestView, ['signin', 'signup'], true) ? $authRequestView : 'signin');
    $authModalOpen = ! auth()->check() && (
        $errors->any()
        || in_array($authRequestView, ['signin', 'signup'], true)
        || in_array($authOldForm, ['signin', 'signup'], true)
    );
    $showSigninErrors = $errors->any() && $initialAuthView === 'signin';
    $showSignupErrors = $errors->any() && $initialAuthView === 'signup';
    $questionSampleCount = collect($practiceTracks)->sum(fn (array $track) => count($track['questions']));
    $authPrimaryUrl = auth()->check()
        ? (auth()->user()?->isAdmin() ? route('dashboard') : route('practice'))
        : null;
    $authPrimaryLabel = auth()->check()
        ? (auth()->user()?->isAdmin() ? 'Open Admin' : 'Continue Practice')
        : 'Get Started';
    $heroMetrics = [
        ['value' => count($practiceTracks), 'label' => 'Interview tracks', 'icon' => 'tracks'],
        ['value' => count($platformFeatures), 'label' => 'Feedback features', 'icon' => 'feedback'],
        ['value' => count($workflow), 'label' => 'Coaching steps', 'icon' => 'coaching'],
    ];
    $courseMeta = [
        'job' => [
            'badge' => 'Career',
            'badgeClass' => 'border-warning-200 bg-warning-50 text-warning-700 dark:border-warning-500/20 dark:bg-warning-500/10 dark:text-warning-300',
            'image' => asset('images/cards/card-01.png'),
        ],
        'scholarship' => [
            'badge' => 'Funding',
            'badgeClass' => 'border-blue-light-200 bg-blue-light-50 text-blue-light-700 dark:border-blue-light-500/20 dark:bg-blue-light-500/10 dark:text-blue-light-300',
            'image' => asset('images/cards/card-02.png'),
        ],
        'admission' => [
            'badge' => 'Campus',
            'badgeClass' => 'border-brand-200 bg-brand-50 text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300',
            'image' => asset('images/cards/card-03.png'),
        ],
        'it' => [
            'badge' => 'Tech',
            'badgeClass' => 'border-gray-200 bg-gray-50 text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200',
            'image' => asset('images/cards/card-04.png'),
        ],
    ];
    $mentorCards = [
        [
            'name' => 'Rica Santos',
            'role' => 'HR Interview Coach',
            'rating' => '4.9',
            'learners' => '2.8k',
            'sessions' => '180+',
            'bio' => 'Former recruitment lead helping fresh graduates answer with stronger structure and confidence.',
            'avatar' => asset('images/user/user-01.jpg'),
        ],
        [
            'name' => 'Marco Villanueva',
            'role' => 'Career Mentor',
            'rating' => '4.8',
            'learners' => '3.1k',
            'sessions' => '220+',
            'bio' => 'Guides entry-level applicants through follow-up questions, delivery, and local interview expectations.',
            'avatar' => asset('images/user/user-01.jpg'),
        ],
        [
            'name' => 'Alyssa Cruz',
            'role' => 'Admissions Coach',
            'rating' => '4.9',
            'learners' => '1.9k',
            'sessions' => '140+',
            'bio' => 'Works on scholarship and college interview answers that sound clear, grounded, and sincere.',
            'avatar' => asset('images/user/user-01.jpg'),
        ],
        [
            'name' => 'Neil Ramos',
            'role' => 'Tech Interview Reviewer',
            'rating' => '4.8',
            'learners' => '2.4k',
            'sessions' => '160+',
            'bio' => 'Focuses on capstone storytelling, debugging answers, and technical communication for junior IT roles.',
            'avatar' => asset('images/user/user-01.jpg'),
        ],
    ];
    $developers = [
    [
        'quote' => 'The IT track pushed me to explain my capstone clearly instead of listing tools without context.',
        'name' => 'Jonh Rogiel M. Tumanda',
        'role' => 'Lead Programmer',
        'avatar' => asset('images/user/user-01.jpg'),   // You can change the filename
    ],
    [
        'quote' => 'Voice rehearsal plus the pacing modes gave me a cleaner answer style after only a few sessions.',
        'name' => 'Karyl G. Gesto',
        'role' => 'Manuscript Editor',
        'avatar' => asset('images/user/user-02.png'),
    ],
    [
        'quote' => 'It feels closer to a real interview than reading questions from a document or random notes.',
        'name' => 'Eva Mae C. Cabilic',
        'role' => 'QA Tester',
        'avatar' => asset('images/user/user-03.png'),
    ],
];
    $pricingPlans = [
        [
            'name' => 'Starter',
            'subtitle' => 'Perfect for beginners',
            'price' => 'Free',
            'period' => '',
            'featured' => false,
            'cta' => 'Get Started',
            'features' => [
                'Access the core interview tracks',
                'Basic AI-guided practice flow',
                'Sample feedback and progress history',
                'Mobile-ready session access',
            ],
        ],
        [
            'name' => 'Pro',
            'subtitle' => 'Most popular choice',
            'price' => '$29',
            'period' => '/month',
            'featured' => true,
            'cta' => 'Start Pro Trial',
            'features' => [
                'Unlimited interview sessions',
                'Detailed feedback center access',
                'Voice, text, and hybrid practice',
                'Priority support and coaching tools',
            ],
        ],
        [
            'name' => 'Enterprise',
            'subtitle' => 'For teams and institutions',
            'price' => '$99',
            'period' => '/month',
            'featured' => false,
            'cta' => 'Talk to Team',
            'features' => [
                'Everything in Pro',
                'Shared dashboards and analytics',
                'Group onboarding and monitoring',
                'Team-level support coordination',
            ],
        ],
    ];
@endphp

@section('content')
    <div
        x-data="{
            mobileMenu: false,
            authModalOpen: @js($authModalOpen),
            authModalView: @js($initialAuthView),
            authPanelHeight: null,
            authFocusTimer: null,
            init() {
                this.$watch('authModalOpen', value => {
                    document.body.classList.toggle('overflow-hidden', value);

                    if (!value) {
                        window.clearTimeout(this.authFocusTimer);
                        return;
                    }

                    this.clearAuthQuery();
                    this.$nextTick(() => {
                        this.syncAuthPanelHeight();
                        this.queueAuthFocus();
                    });
                });

                document.body.classList.toggle('overflow-hidden', this.authModalOpen);

                this.$nextTick(() => {
                    if (!this.authModalOpen) {
                        return;
                    }

                    this.clearAuthQuery();
                    this.syncAuthPanelHeight();
                    this.queueAuthFocus();
                });
            },
            getActiveAuthPanel() {
                return this.authModalView === 'signin' ? this.$refs.signinPanel : this.$refs.signupPanel;
            },
            syncAuthPanelHeight() {
                const activePanel = this.getActiveAuthPanel();

                if (!activePanel) {
                    return;
                }

                this.authPanelHeight = `${activePanel.scrollHeight}px`;
            },
            focusActiveAuthField() {
                const activePanel = this.getActiveAuthPanel();
                const target = activePanel?.querySelector('[data-auth-autofocus]');

                target?.focus({ preventScroll: true });
            },
            queueAuthFocus() {
                window.clearTimeout(this.authFocusTimer);
                this.authFocusTimer = window.setTimeout(() => this.focusActiveAuthField(), 260);
            },
            clearAuthQuery() {
                const url = new URL(window.location.href);

                if (!url.searchParams.has('auth')) {
                    return;
                }

                url.searchParams.delete('auth');
                window.history.replaceState({}, '', url.toString());
            },
            openAuthModal(view = 'signin') {
                this.switchAuthModal(view);
                this.authModalOpen = true;
                this.mobileMenu = false;
                this.clearAuthQuery();
            },
            switchAuthModal(view) {
                if (this.authModalView === view) {
                    return;
                }

                this.authModalView = view;
                this.$nextTick(() => {
                    this.syncAuthPanelHeight();
                    this.queueAuthFocus();
                });
            },
            closeAuthModal() {
                window.clearTimeout(this.authFocusTimer);
                this.authModalOpen = false;
                this.clearAuthQuery();
            }
        }"
        @keydown.escape.window="authModalOpen ? closeAuthModal() : (mobileMenu = false)"
        @resize.window="authModalOpen && syncAuthPanelHeight()"
        class="relative min-h-screen overflow-hidden bg-white text-gray-900 dark:bg-gray-950 dark:text-white"
    >
        <div class="home-orb left-[-8rem] top-[-6rem] h-72 w-72 bg-gradient-to-tr from-brand-200 to-warning-200"></div>
        <div class="home-orb right-[-6rem] top-20 h-80 w-80 bg-gradient-to-bl from-blue-light-100 to-brand-300 [animation-delay:1.5s]"></div>
        <div class="home-orb bottom-[-8rem] left-1/3 h-80 w-80 bg-gradient-to-tr from-brand-100 to-blue-light-100 [animation-delay:3s]"></div>
        <div class="home-grid absolute inset-0 opacity-25 dark:opacity-20"></div>

        <header class="sticky top-0 z-40 border-b border-brand-100/70 bg-white/90 backdrop-blur-xl dark:border-gray-800 dark:bg-gray-950/85">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <span class="inline-flex h-11 w-11 items-center justify-center overflow-hidden rounded-2xl border border-brand-100 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                        <img src="{{ asset('images/logo/interviewpilot-icon.png') }}" alt="InterviewPilot" class="h-full w-full object-cover" />
                    </span>
                    <span>
                        <span class="block text-base font-semibold text-gray-900 dark:text-white">InterviewPilot</span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400">AI mock interviews for practice and feedback</span>
                    </span>
                </a>

                <nav class="hidden items-center gap-7 text-sm font-medium text-gray-600 lg:flex dark:text-gray-300">
                    <a href="#home" class="transition hover:text-brand-600 dark:hover:text-brand-300">Home</a>
                    <a href="#courses" class="transition hover:text-brand-600 dark:hover:text-brand-300">Courses</a>
                    <a href="#mentors" class="transition hover:text-brand-600 dark:hover:text-brand-300">Mentors</a>
                    <a href="#features" class="transition hover:text-brand-600 dark:hover:text-brand-300">Features</a>
                    <a href="#developers" class="transition hover:text-brand-600 dark:hover:text-brand-300">Developers</a>
                    <a href="#pricing" class="transition hover:text-brand-600 dark:hover:text-brand-300">Pricing</a>
                </nav>

                <div class="hidden items-center gap-3 lg:flex">
                    <button
                        type="button"
                        @click="$store.theme.toggle()"
                        class="inline-flex h-11 items-center gap-3 rounded-full border border-brand-100 bg-white px-4 text-sm font-semibold text-gray-700 shadow-theme-xs transition hover:border-brand-200 hover:text-brand-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-brand-500/30 dark:hover:text-brand-300"
                        :aria-label="$store.theme.theme === 'dark' ? 'Switch to day theme' : 'Switch to night theme'"
                    >
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-300">
                            <svg x-show="$store.theme.theme !== 'dark'" x-cloak class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                <path d="M10 2.5v1.75M10 15.75v1.75M4.697 4.697l1.237 1.237M14.066 14.066l1.237 1.237M2.5 10h1.75M15.75 10h1.75M4.697 15.303l1.237-1.237M14.066 5.934l1.237-1.237M13 10a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <svg x-show="$store.theme.theme === 'dark'" x-cloak class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                <path d="M15.25 11.79A6.75 6.75 0 0 1 8.21 4.75 6.76 6.76 0 1 0 15.25 11.79Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                        <span x-text="$store.theme.theme === 'dark' ? 'Night' : 'Day'"></span>
                    </button>
                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="inline-flex h-11 items-center justify-center rounded-full px-4 text-sm font-semibold text-gray-700 transition hover:text-brand-600 dark:text-gray-200 dark:hover:text-brand-300"
                        >
                            Dashboard
                        </a>
                        <a
                            href="{{ $authPrimaryUrl }}"
                            class="inline-flex h-11 items-center justify-center rounded-full bg-brand-500 px-5 text-sm font-semibold text-white shadow-theme-xs transition hover:-translate-y-0.5 hover:bg-brand-600"
                        >
                            {{ $authPrimaryLabel }}
                        </a>
                    @else
                        <a
                            href="{{ route('signin') }}"
                            @click.prevent="openAuthModal('signin')"
                            class="inline-flex h-11 items-center justify-center rounded-full px-4 text-sm font-semibold text-gray-700 transition hover:text-brand-600 dark:text-gray-200 dark:hover:text-brand-300"
                        >
                            Sign In
                        </a>
                        <a
                            href="{{ route('signup') }}"
                            @click.prevent="openAuthModal('signup')"
                            class="inline-flex h-11 items-center justify-center rounded-full bg-brand-500 px-5 text-sm font-semibold text-white shadow-theme-xs transition hover:-translate-y-0.5 hover:bg-brand-600"
                        >
                            Create Free Account
                        </a>
                    @endauth
                </div>

                <button
                    type="button"
                    class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-brand-100 bg-white text-gray-700 shadow-theme-xs transition hover:border-brand-200 hover:text-brand-600 lg:hidden dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:text-brand-300"
                    @click="mobileMenu = ! mobileMenu"
                >
                    <span class="sr-only">Toggle menu</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                        <path d="M3.333 5.833h13.334M3.333 10h13.334M3.333 14.167h13.334" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                    </svg>
                </button>
            </div>

            <div x-show="mobileMenu" x-cloak x-transition.origin.top class="border-t border-brand-100/70 bg-white/95 px-4 py-4 lg:hidden dark:border-gray-800 dark:bg-gray-950/95">
                <div class="mx-auto flex max-w-7xl flex-col gap-2">
                    <a href="#home" @click="mobileMenu = false" class="rounded-2xl px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-brand-50 hover:text-brand-700 dark:text-gray-200 dark:hover:bg-gray-900 dark:hover:text-brand-300">Home</a>
                    <a href="#courses" @click="mobileMenu = false" class="rounded-2xl px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-brand-50 hover:text-brand-700 dark:text-gray-200 dark:hover:bg-gray-900 dark:hover:text-brand-300">Courses</a>
                    <a href="#mentors" @click="mobileMenu = false" class="rounded-2xl px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-brand-50 hover:text-brand-700 dark:text-gray-200 dark:hover:bg-gray-900 dark:hover:text-brand-300">Mentors</a>
                    <a href="#features" @click="mobileMenu = false" class="rounded-2xl px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-brand-50 hover:text-brand-700 dark:text-gray-200 dark:hover:bg-gray-900 dark:hover:text-brand-300">Features</a>
                    <a href="#developers" @click="mobileMenu = false" class="rounded-2xl px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-brand-50 hover:text-brand-700 dark:text-gray-200 dark:hover:bg-gray-900 dark:hover:text-brand-300">Developers</a>
                    <a href="#pricing" @click="mobileMenu = false" class="rounded-2xl px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-brand-50 hover:text-brand-700 dark:text-gray-200 dark:hover:bg-gray-900 dark:hover:text-brand-300">Pricing</a>

                    <button
                        type="button"
                        @click="$store.theme.toggle()"
                        class="mt-3 inline-flex h-11 items-center gap-3 rounded-2xl border border-brand-100 bg-white px-4 text-sm font-semibold text-gray-700 shadow-theme-xs transition hover:border-brand-200 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-brand-500/30 dark:hover:text-brand-300"
                        :aria-label="$store.theme.theme === 'dark' ? 'Switch to day theme' : 'Switch to night theme'"
                    >
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-300">
                            <svg x-show="$store.theme.theme !== 'dark'" x-cloak class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                <path d="M10 2.5v1.75M10 15.75v1.75M4.697 4.697l1.237 1.237M14.066 14.066l1.237 1.237M2.5 10h1.75M15.75 10h1.75M4.697 15.303l1.237-1.237M14.066 5.934l1.237-1.237M13 10a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <svg x-show="$store.theme.theme === 'dark'" x-cloak class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                <path d="M15.25 11.79A6.75 6.75 0 0 1 8.21 4.75 6.76 6.76 0 1 0 15.25 11.79Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                        <span x-text="$store.theme.theme === 'dark' ? 'Night theme' : 'Day theme'"></span>
                    </button>

                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        @auth
                            <a href="{{ route('dashboard') }}" class="inline-flex h-11 items-center justify-center rounded-full border border-brand-200 bg-white px-4 text-sm font-semibold text-gray-700 shadow-theme-xs dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                                Dashboard
                            </a>
                            <a href="{{ $authPrimaryUrl }}" class="inline-flex h-11 items-center justify-center rounded-full bg-brand-500 px-4 text-sm font-semibold text-white shadow-theme-xs">
                                {{ $authPrimaryLabel }}
                            </a>
                        @else
                            <a href="{{ route('signin') }}" @click.prevent="openAuthModal('signin')" class="inline-flex h-11 items-center justify-center rounded-full border border-brand-200 bg-white px-4 text-sm font-semibold text-gray-700 shadow-theme-xs dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                                Sign In
                            </a>
                            <a href="{{ route('signup') }}" @click.prevent="openAuthModal('signup')" class="inline-flex h-11 items-center justify-center rounded-full bg-brand-500 px-4 text-sm font-semibold text-white shadow-theme-xs">
                                Create Free Account
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </header>

        <main class="relative z-10">
            <section id="home" class="scroll-mt-28">
                <div class="mx-auto max-w-7xl px-4 pb-24 pt-12 sm:px-6 lg:px-8 lg:pb-32 lg:pt-20">
                    <div class="flex flex-col gap-10 lg:flex-row lg:items-center lg:justify-between xl:gap-12">
                        <div class="w-full max-w-xl lg:w-5/12">
                            <span class="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-brand-50 px-4 py-2 text-xs font-semibold text-brand-700 shadow-theme-xs dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                                <span class="h-2 w-2 rounded-full bg-brand-500"></span>
                                New AI Interview Labs Available
                            </span>

                            <h1 class="mt-6 text-4xl font-semibold leading-tight text-gray-900 sm:text-4xl xl:text-5xl dark:text-white">
                                Master Interview Skills
                                <span class="block text-brand-500">Anytime, Anywhere</span>
                            </h1>

                            <p class="mt-6 max-w-xl text-base leading-7 text-gray-600 sm:text-lg sm:leading-8 lg:text-xl lg:leading-9 dark:text-gray-300">
                                Practice online with interview simulations, automated feedback, guided learning, and progress tracking.
                            </p>

                            <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                                @auth
                                    <a
                                        href="{{ $authPrimaryUrl }}"
                                        class="inline-flex h-12 items-center justify-center rounded-full bg-brand-500 px-6 text-sm font-semibold text-white shadow-theme-xs transition hover:-translate-y-0.5 hover:bg-brand-600"
                                    >
                                        {{ $authPrimaryLabel }}
                                    </a>
                                    <a
                                        href="{{ route('dashboard') }}"
                                        class="inline-flex h-12 items-center justify-center rounded-full border border-gray-200 bg-white px-6 text-sm font-semibold text-gray-700 shadow-theme-xs transition hover:-translate-y-0.5 hover:border-brand-200 hover:text-brand-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-brand-500/30 dark:hover:text-brand-300"
                                    >
                                        View Dashboard
                                    </a>
                                @else
                                    <a
                                        href="{{ route('signup') }}"
                                        @click.prevent="openAuthModal('signup')"
                                        class="inline-flex h-12 items-center justify-center rounded-full bg-brand-500 px-6 text-sm font-semibold text-white shadow-theme-xs transition hover:-translate-y-0.5 hover:bg-brand-600"
                                    >
                                        Create Free Account
                                    </a>
                                    <a
                                        href="{{ route('signin') }}"
                                        @click.prevent="openAuthModal('signin')"
                                        class="inline-flex h-12 items-center justify-center rounded-full border border-gray-200 bg-white px-6 text-sm font-semibold text-gray-700 shadow-theme-xs transition hover:-translate-y-0.5 hover:border-brand-200 hover:text-brand-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-brand-500/30 dark:hover:text-brand-300"
                                    >
                                        Sign In
                                    </a>
                                @endauth
                            </div>

                            <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-3 text-sm text-gray-600 dark:text-gray-300">
                                @foreach ($heroMetrics as $metric)
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex h-5 w-5 items-center justify-center text-brand-500 dark:text-brand-300">
                                            @switch($metric['icon'])
                                                @case('tracks')
                                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                        <path d="M3 19a9 9 0 0 1 9 0a9 9 0 0 1 9 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M3 6a9 9 0 0 1 9 0a9 9 0 0 1 9 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M3 6v13M12 6v13M21 6v13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                @break

                                                @case('feedback')
                                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                        <path d="M8 9h8M8 13h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M7 18.5A8.38 8.38 0 0 1 3 19l1.3-3.9A8 8 0 1 1 7 18.5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                @break

                                                @case('coaching')
                                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                        <path d="M12 9a6 6 0 1 0 0 12a6 6 0 1 0 0-12Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M12 15l3.4 5.89l1.598-3.233l3.598.232l-3.4-5.889" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M6.802 12l-3.4 5.89l3.598-.233l1.598 3.232l3.4-5.889" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                @break
                                            @endswitch
                                        </span>
                                        <small class="text-sm leading-none text-gray-600 dark:text-gray-300">
                                            <span class="font-bold text-gray-900 dark:text-white">{{ $metric['value'] }}</span>
                                            {{ $metric['label'] }}
                                        </small>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="w-full lg:flex lg:w-7/12 lg:justify-end">
                            <div class="home-panel relative mx-auto w-full max-w-3xl overflow-hidden border-white/80 bg-white/85 p-4 shadow-[0_35px_80px_-45px_rgba(15,23,42,0.45)] lg:mx-0 dark:border-gray-800 dark:bg-gray-900/80 sm:p-5">
                                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(70,95,255,0.18),_transparent_42%),radial-gradient(circle_at_bottom_right,_rgba(11,165,236,0.14),_transparent_35%)]"></div>
                                <div class="relative overflow-hidden rounded-[28px] border border-brand-100 bg-gray-950 dark:border-gray-800 dark:bg-gray-900">
                                    <div class="relative w-full overflow-hidden bg-gray-950">
                                        <img
                                            src="{{ asset('images/ai/video-thumb.png') }}"
                                            alt="Interview practice preview"
                                            class="block h-auto w-full"
                                        />
                                        <div class="absolute inset-0 bg-gradient-to-t from-gray-950/60 via-gray-950/10 to-transparent"></div>
                                        <div class="absolute left-5 top-5 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.22em] text-white/90 backdrop-blur-md">
                                            <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                                            Interview preview
                                        </div>
                                    </div>
                                </div>

                                <div class="absolute -left-4 bottom-6 hidden rounded-[24px] border border-white/80 bg-white/95 px-4 py-3 shadow-theme-lg sm:block dark:border-gray-800 dark:bg-gray-900/95">
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $questionSampleCount }}+</p>
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Sample questions ready now</p>
                                </div>

                                <div class="absolute -right-4 top-6 hidden rounded-[24px] border border-white/80 bg-white/95 px-4 py-3 shadow-theme-lg sm:block dark:border-gray-800 dark:bg-gray-900/95">
                                    <div class="flex items-center">
                                        <img src="{{ asset('images/user/user-01.jpg') }}" alt="" class="h-9 w-9 rounded-full border-2 border-white object-cover dark:border-gray-900" />
                                        <img src="{{ asset('images/user/user-02.png') }}" alt="" class="-ml-3 h-9 w-9 rounded-full border-2 border-white object-cover dark:border-gray-900" />
                                        <img src="{{ asset('images/user/user-03.png') }}" alt="" class="-ml-3 h-9 w-9 rounded-full border-2 border-white object-cover dark:border-gray-900" />
                                    </div>
                                    <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ count($focusModes) }} coach modes</p>
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Built for repeated practice</p>
                                </div>

                                <div class="absolute bottom-8 right-6 hidden rounded-[24px] border border-white/80 bg-white/95 px-4 py-3 shadow-theme-lg lg:block dark:border-gray-800 dark:bg-gray-900/95">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ count($responseModes) }} response styles</p>
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Text, voice, and hybrid</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="courses" class="scroll-mt-28 py-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-2xl text-center">
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-brand-600 dark:text-brand-300">Courses</p>
                        <h2 class="mt-5 text-4xl font-semibold text-gray-900 dark:text-white">
                            Explore Our Popular <span class="text-brand-500">Courses</span>
                        </h2>
                        <p class="mt-4 text-base leading-7 text-gray-600 dark:text-gray-300">
                            Choose from the interview tracks designed for job seekers, scholarship applicants, admissions, and technical roles.
                        </p>
                    </div>

                    <div class="mt-14 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                        @foreach ($practiceTracks as $track)
                            @php
                                $course = $courseMeta[$track['id']] ?? [
                                    'badge' => 'Track',
                                    'badgeClass' => 'border-brand-200 bg-brand-50 text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300',
                                    'image' => asset('images/grid-image/image-02.png'),
                                ];
                            @endphp
                            <article class="group rounded-[30px] border border-gray-200 bg-white p-4 shadow-[0_18px_44px_-32px_rgba(15,23,42,0.35)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_30px_60px_-34px_rgba(15,23,42,0.4)] dark:border-gray-800 dark:bg-gray-900">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="inline-flex rounded-full border px-3 py-1 text-[11px] font-semibold {{ $course['badgeClass'] }}">
                                        {{ $course['badge'] }}
                                    </span>
                                    <span class="text-xs font-medium text-gray-400 dark:text-gray-500">Track {{ $loop->iteration }}</span>
                                </div>

                                <div class="mt-4 rounded-[24px] bg-gradient-to-br from-brand-50 via-white to-blue-light-50 p-4 dark:from-gray-800 dark:via-gray-900 dark:to-gray-800">
                                    <img src="{{ $course['image'] }}" alt="{{ $track['name'] }}" class="mx-auto h-40 object-contain" />
                                </div>

                                <div class="mt-6">
                                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $track['name'] }}</h3>
                                    <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-300">
                                        {{ \Illuminate\Support\Str::limit($track['description'], 96) }}
                                    </p>
                                </div>

                                <div class="mt-5 flex flex-wrap gap-3 text-xs font-medium text-gray-500 dark:text-gray-400">
                                    <span class="inline-flex items-center gap-2">
                                        <span class="h-2 w-2 rounded-full bg-brand-500"></span>
                                        {{ count($track['questions']) }} sample questions
                                    </span>
                                    <span class="inline-flex items-center gap-2">
                                        <span class="h-2 w-2 rounded-full bg-blue-light-500"></span>
                                        {{ count($track['localFocus']) }} local tips
                                    </span>
                                    <span class="inline-flex items-center gap-2">
                                        <span class="h-2 w-2 rounded-full bg-warning-500"></span>
                                        {{ count($track['quickPrompts']) }} AI prompts
                                    </span>
                                </div>

                                <div class="mt-6 flex items-center justify-between border-t border-dashed border-gray-200 pt-4 dark:border-gray-800">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Interview-ready track</p>
                                    @auth
                                        <a href="{{ $authPrimaryUrl }}" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-600 transition hover:text-brand-700 dark:text-brand-300 dark:hover:text-brand-200">
                                            Explore now
                                            <span aria-hidden="true">&rarr;</span>
                                        </a>
                                    @else
                                        <a href="{{ route('signup') }}" @click.prevent="openAuthModal('signup')" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-600 transition hover:text-brand-700 dark:text-brand-300 dark:hover:text-brand-200">
                                            Enroll now
                                            <span aria-hidden="true">&rarr;</span>
                                        </a>
                                    @endauth
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="mt-10 text-center">
                        @auth
                            <a href="{{ $authPrimaryUrl }}" class="inline-flex h-12 items-center justify-center rounded-full border border-gray-200 bg-white px-6 text-sm font-semibold text-gray-700 shadow-theme-xs transition hover:-translate-y-0.5 hover:border-brand-200 hover:text-brand-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-brand-500/30 dark:hover:text-brand-300">
                                View All Courses
                            </a>
                        @else
                            <a href="{{ route('signup') }}" @click.prevent="openAuthModal('signup')" class="inline-flex h-12 items-center justify-center rounded-full border border-gray-200 bg-white px-6 text-sm font-semibold text-gray-700 shadow-theme-xs transition hover:-translate-y-0.5 hover:border-brand-200 hover:text-brand-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-brand-500/30 dark:hover:text-brand-300">
                                View All Courses
                            </a>
                        @endauth
                    </div>
                </div>
            </section>

            <section id="mentors" class="scroll-mt-28 py-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-2xl text-center">
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-brand-600 dark:text-brand-300">Mentors</p>
                        <h2 class="mt-5 text-4xl font-semibold text-gray-900 dark:text-white">
                            Learn From Industry <span class="text-brand-500">Experts</span>
                        </h2>
                        <p class="mt-4 text-base leading-7 text-gray-600 dark:text-gray-300">
                            The practice experience is backed by coaching patterns for hiring, admissions, scholarship, and technical interviews.
                        </p>
                    </div>

                    <div class="mt-14 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                        @foreach ($mentorCards as $mentor)
                            <article class="rounded-[30px] border border-gray-200 bg-white px-6 py-8 text-center shadow-[0_18px_44px_-32px_rgba(15,23,42,0.35)] dark:border-gray-800 dark:bg-gray-900">
                                <div class="relative mx-auto w-fit">
                                    <img src="{{ $mentor['avatar'] }}" alt="{{ $mentor['name'] }}" class="h-20 w-20 rounded-full border-4 border-warning-300 object-cover" />
                                    <span class="absolute -bottom-2 left-1/2 inline-flex -translate-x-1/2 rounded-full bg-warning-500 px-3 py-1 text-[11px] font-semibold text-white shadow-theme-xs">
                                        {{ $mentor['rating'] }}
                                    </span>
                                </div>

                                <h3 class="mt-6 text-lg font-semibold text-gray-900 dark:text-white">{{ $mentor['name'] }}</h3>
                                <p class="mt-1 text-sm font-medium text-gray-500 dark:text-gray-400">{{ $mentor['role'] }}</p>
                                <p class="mt-4 text-sm leading-6 text-gray-600 dark:text-gray-300">{{ $mentor['bio'] }}</p>

                                <div class="mt-5 flex items-center justify-center gap-5 text-xs font-medium text-gray-500 dark:text-gray-400">
                                    <span>{{ $mentor['learners'] }} learners</span>
                                    <span>{{ $mentor['sessions'] }} sessions</span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="features" class="scroll-mt-28 py-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-6xl">
                        <div class="mx-auto max-w-3xl text-center">
                            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-brand-600 dark:text-brand-300">Features</p>
                            <h2 class="mt-5 text-4xl font-semibold text-gray-900 dark:text-white">
                                Platform <span class="text-brand-500">features</span>
                            </h2>
                            <p class="mt-4 text-base leading-7 text-gray-600 dark:text-gray-300">
                                Discover the tools that power InterviewPilot, from guided interview sessions and automated feedback to progress tracking and focused practice support.
                            </p>
                        </div>

                        <div class="mt-10 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            @foreach (collect($platformFeatures)->take(6) as $feature)
                                <div class="rounded-[24px] border border-gray-200 bg-white/85 p-5 shadow-theme-xs backdrop-blur dark:border-gray-800 dark:bg-gray-900/75">
                                    <div class="flex items-start gap-3">
                                        <span class="mt-1 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-100 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.25 7.313a1 1 0 0 1-1.42-.002L3.29 9.25a1 1 0 0 1 1.42-1.406l4.04 4.08 6.543-6.628a1 1 0 0 1 1.411-.006Z" />
                                            </svg>
                                        </span>
                                        <div>
                                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $feature['title'] }}</h3>
                                            <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">
                                                {{ \Illuminate\Support\Str::limit($feature['body'], 92) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-8 text-center">
                            @auth
                                <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
                                    <a href="{{ $authPrimaryUrl }}" class="inline-flex h-12 items-center justify-center rounded-full bg-brand-500 px-6 text-sm font-semibold text-white shadow-theme-xs transition hover:-translate-y-0.5 hover:bg-brand-600">
                                        Explore Features
                                    </a>
                                    <a href="{{ route('session-setup') }}" class="inline-flex h-12 items-center justify-center rounded-full border border-gray-200 bg-white px-6 text-sm font-semibold text-gray-700 shadow-theme-xs transition hover:-translate-y-0.5 hover:border-brand-200 hover:text-brand-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-brand-500/30 dark:hover:text-brand-300">
                                        Session Setup
                                    </a>
                                </div>
                            @else
                                <a href="{{ route('signup') }}" @click.prevent="openAuthModal('signup')" class="inline-flex h-12 items-center justify-center rounded-full bg-brand-500 px-6 text-sm font-semibold text-white shadow-theme-xs transition hover:-translate-y-0.5 hover:bg-brand-600">
                                    Explore Features
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </section>

            <section id="developers" class="scroll-mt-28 py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-brand-600 dark:text-brand-300">Developers</p>
            <h2 class="mt-5 text-4xl font-semibold text-gray-900 dark:text-white">
                What Developers <span class="text-brand-500">Are Saying</span>
            </h2>
            <p class="mt-4 text-base leading-7 text-gray-600 dark:text-gray-300">
                Real stories from developers and tech job seekers who improved their interview performance with InterviewPilot.
            </p>
        </div>

        <div class="mt-14 grid gap-6 lg:grid-cols-3">
            @foreach ($developers as $developer)
                <article class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center gap-1 text-warning-400">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="m10 1.5 2.63 5.33 5.88.85-4.25 4.14 1 5.85L10 14.9l-5.26 2.77 1-5.85L1.5 7.68l5.88-.85L10 1.5Z" />
                            </svg>
                        @endfor
                    </div>

                    <p class="mt-5 text-base leading-7 text-gray-600 dark:text-gray-300">"{{ $developer['quote'] }}"</p>

                    <div class="mt-6 flex items-center gap-4">
                        <img 
                            src="{{ $developer['avatar'] }}" 
                            alt="{{ $developer['name'] }}" 
                            class="h-12 w-12 rounded-full border-2 border-white object-cover shadow-sm dark:border-gray-700"
                        >
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $developer['name'] }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $developer['role'] }}</p>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

            <section id="pricing" class="scroll-mt-28 py-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-2xl text-center">
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-brand-600 dark:text-brand-300">Pricing</p>
                        <h2 class="mt-5 text-4xl font-semibold text-gray-900 dark:text-white">
                            Simple, Transparent <span class="text-brand-500">Pricing</span>
                        </h2>
                        <p class="mt-4 text-base leading-7 text-gray-600 dark:text-gray-300">
                            Choose the plan that fits your interview practice goals, from free self-study to team access.
                        </p>
                    </div>

                    <div class="mx-auto mt-14 grid max-w-6xl gap-6 lg:grid-cols-3">
                        @foreach ($pricingPlans as $plan)
                            <article class="relative rounded-[30px] border {{ $plan['featured'] ? 'border-brand-300 bg-brand-50/60 shadow-[0_30px_70px_-38px_rgba(70,95,255,0.35)] dark:border-brand-500/40 dark:bg-brand-500/10' : 'border-gray-200 bg-white shadow-[0_18px_44px_-32px_rgba(15,23,42,0.35)] dark:border-gray-800 dark:bg-gray-900' }} p-8">
                                @if ($plan['featured'])
                                    <span class="absolute left-1/2 top-0 inline-flex -translate-x-1/2 -translate-y-1/2 rounded-full bg-brand-500 px-4 py-1.5 text-xs font-semibold text-white shadow-theme-xs">
                                        Most Popular
                                    </span>
                                @endif

                                <div class="text-center">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $plan['name'] }}</p>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $plan['subtitle'] }}</p>
                                    <div class="mt-6">
                                        <span class="text-4xl font-semibold text-gray-900 dark:text-white">{{ $plan['price'] }}</span>
                                        @if ($plan['period'] !== '')
                                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $plan['period'] }}</span>
                                        @endif
                                    </div>
                                </div>

                                <ul class="mt-8 space-y-4">
                                    @foreach ($plan['features'] as $feature)
                                        <li class="flex items-start gap-3 text-sm leading-6 text-gray-600 dark:text-gray-300">
                                            <span class="mt-1 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-brand-100 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                                                <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.25 7.313a1 1 0 0 1-1.42-.002L3.29 9.25a1 1 0 1 1 1.42-1.406l4.04 4.08 6.543-6.628a1 1 0 0 1 1.411-.006Z" />
                                                </svg>
                                            </span>
                                            <span>{{ $feature }}</span>
                                        </li>
                                    @endforeach
                                </ul>

                                <div class="mt-8">
                                    @auth
                                        <a href="{{ $authPrimaryUrl }}" class="inline-flex h-12 w-full items-center justify-center rounded-full {{ $plan['featured'] ? 'bg-brand-500 text-white hover:bg-brand-600' : 'border border-gray-200 bg-white text-gray-700 hover:border-brand-200 hover:text-brand-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-brand-500/30 dark:hover:text-brand-300' }} text-sm font-semibold shadow-theme-xs transition">
                                            {{ $plan['cta'] }}
                                        </a>
                                    @else
                                        <a href="{{ route('signup') }}" @click.prevent="openAuthModal('signup')" class="inline-flex h-12 w-full items-center justify-center rounded-full {{ $plan['featured'] ? 'bg-brand-500 text-white hover:bg-brand-600' : 'border border-gray-200 bg-white text-gray-700 hover:border-brand-200 hover:text-brand-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-brand-500/30 dark:hover:text-brand-300' }} text-sm font-semibold shadow-theme-xs transition">
                                            {{ $plan['cta'] }}
                                        </a>
                                    @endauth
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        </main>

        <footer id="footer" class="relative z-10 border-t border-brand-100/70 bg-white/90 py-14 dark:border-gray-800 dark:bg-gray-950/85">
            <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[minmax(0,1.3fr)_repeat(4,minmax(0,1fr))] lg:px-8">
                <div>
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-11 w-11 items-center justify-center overflow-hidden rounded-2xl border border-brand-100 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                            <img src="{{ asset('images/logo/interviewpilot-icon.png') }}" alt="InterviewPilot" class="h-full w-full object-cover" />
                        </span>
                        <div>
                            <p class="text-base font-semibold text-gray-900 dark:text-white">InterviewPilot</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Practice better answers before the real interview.</p>
                        </div>
                    </div>
                    <p class="mt-5 max-w-sm text-sm leading-7 text-gray-600 dark:text-gray-300">
                        Built for students, applicants, and job seekers who want repeatable interview practice with guided AI feedback.
                    </p>
                </div>

                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Product</p>
                    <div class="mt-4 space-y-3 text-sm text-gray-600 dark:text-gray-300">
                        <a href="#courses" class="block transition hover:text-brand-600 dark:hover:text-brand-300">Courses</a>
                        <a href="#pricing" class="block transition hover:text-brand-600 dark:hover:text-brand-300">Pricing</a>
                        <a href="#features" class="block transition hover:text-brand-600 dark:hover:text-brand-300">Features</a>
                    </div>
                </div>

                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Company</p>
                    <div class="mt-4 space-y-3 text-sm text-gray-600 dark:text-gray-300">
                        <a href="#mentors" class="block transition hover:text-brand-600 dark:hover:text-brand-300">Mentors</a>
                        <a href="#developers" class="block transition hover:text-brand-600 dark:hover:text-brand-300">Developers</a>
                        <a href="#home" class="block transition hover:text-brand-600 dark:hover:text-brand-300">About</a>
                    </div>
                </div>

                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Support</p>
                    <div class="mt-4 space-y-3 text-sm text-gray-600 dark:text-gray-300">
                        @auth
                            <a href="{{ route('dashboard') }}" class="block transition hover:text-brand-600 dark:hover:text-brand-300">Dashboard</a>
                            <a href="{{ $authPrimaryUrl }}" class="block transition hover:text-brand-600 dark:hover:text-brand-300">{{ $authPrimaryLabel }}</a>
                        @else
                            <a href="{{ route('signin') }}" @click.prevent="openAuthModal('signin')" class="block transition hover:text-brand-600 dark:hover:text-brand-300">Sign In</a>
                            <a href="{{ route('signup') }}" @click.prevent="openAuthModal('signup')" class="block transition hover:text-brand-600 dark:hover:text-brand-300">Create Free Account</a>
                        @endauth
                    </div>
                </div>

                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Highlights</p>
                    <div class="mt-4 space-y-3 text-sm text-gray-600 dark:text-gray-300">
                        <p>{{ count($practiceTracks) }} interview tracks</p>
                        <p>{{ count($platformFeatures) }} feedback tools</p>
                        <p>{{ count($workflow) }}-step coaching flow</p>
                    </div>
                </div>
            </div>

            <div class="mx-auto mt-10 max-w-7xl border-t border-brand-100/70 px-4 pt-6 text-sm text-gray-500 sm:px-6 lg:px-8 dark:border-gray-800 dark:text-gray-400">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <p>&copy; {{ now()->year }} InterviewPilot. All rights reserved.</p>
                    <a href="#home" class="font-medium text-gray-600 transition hover:text-brand-600 dark:text-gray-300 dark:hover:text-brand-300">Back to top</a>
                </div>
            </div>
        </footer>

        @guest
            <div x-show="authModalOpen" x-cloak x-transition.opacity class="fixed inset-0 z-[999] flex items-center justify-center p-4 sm:p-6">
                <div class="absolute inset-0 bg-slate-950/65 backdrop-blur-sm" @click="closeAuthModal()"></div>

                <div class="relative z-10 w-full max-w-5xl overflow-hidden rounded-[32px] border border-gray-200 bg-white shadow-[0_32px_100px_-42px_rgba(15,23,42,0.65)] dark:border-gray-800 dark:bg-gray-950">
                    <button
                        type="button"
                        class="absolute right-4 top-4 z-20 inline-flex h-11 w-11 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 transition hover:border-brand-300 hover:text-brand-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:hover:border-brand-500/30 dark:hover:text-brand-200"
                        @click="closeAuthModal()"
                    >
                        <span class="sr-only">Close authentication modal</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M5 5L15 15M15 5L5 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                        </svg>
                    </button>

                    <div class="grid lg:grid-cols-[0.92fr_1.08fr] lg:items-stretch">
                        <aside class="relative hidden border-r border-gray-200 bg-gray-900 px-8 py-10 text-white dark:border-gray-800 lg:flex lg:flex-col lg:justify-center lg:gap-12">
                            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(70,95,255,0.25),_transparent_35%)]"></div>

                            <div class="relative mx-auto w-full max-w-md">
                                <span class="inline-flex rounded-full bg-brand-400/15 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-brand-200">
                                    Landing Page Access
                                </span>
                                <h2 class="mt-5 max-w-sm text-4xl font-semibold leading-tight text-white">
                                    Sign in or create your account without leaving the homepage.
                                </h2>
                                <p class="mt-4 max-w-md text-sm leading-7 text-white/75">
                                    Use Google or email access, then continue straight into the interview practice workspace from this landing page.
                                </p>
                            </div>

                            <div class="relative mx-auto w-full max-w-md space-y-4">
                                <div class="rounded-[28px] border border-white/10 bg-white/5 p-5">
                                    <p class="text-sm font-semibold text-white">Supported methods</p>
                                    <p class="mt-2 text-sm leading-6 text-white/70">
                                        Google for fast sign-in, or email and password for your regular account flow.
                                    </p>
                                </div>

                                <div class="rounded-[28px] border border-brand-400/20 bg-brand-400/10 p-5">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-200">InterviewPilot</p>
                                    <p class="mt-2 text-sm leading-6 text-brand-50">
                                        Open the modal, choose your method, and jump directly into guided interview practice.
                                    </p>
                                </div>
                            </div>
                        </aside>

                        <div class="flex px-5 py-6 sm:px-8 sm:py-8 lg:px-10 lg:py-10">
                            <div class="w-full lg:mx-auto lg:max-w-[37rem]">
                                <div class="relative flex rounded-2xl border border-brand-100 bg-brand-50 p-1 dark:border-brand-500/20 dark:bg-brand-500/10">
                                    <div
                                        aria-hidden="true"
                                        class="pointer-events-none absolute inset-y-1 left-1 w-[calc(50%-0.25rem)] rounded-xl transition-all duration-300 ease-out"
                                        :class="authModalView === 'signin'
                                            ? 'translate-x-0 bg-gray-900 shadow-theme-xs dark:bg-white'
                                            : 'translate-x-full bg-brand-500 shadow-theme-xs'"
                                    >
                                    </div>
                                    <button
                                        type="button"
                                        class="relative z-10 flex-1 rounded-xl px-4 py-3 text-sm font-semibold transition"
                                        @click="switchAuthModal('signin')"
                                        :class="authModalView === 'signin'
                                            ? 'text-white dark:text-gray-900'
                                            : 'text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white'"
                                    >
                                        Sign In
                                    </button>
                                    <button
                                        type="button"
                                        class="relative z-10 flex-1 rounded-xl px-4 py-3 text-sm font-semibold transition"
                                        @click="switchAuthModal('signup')"
                                        :class="authModalView === 'signup'
                                            ? 'text-white'
                                            : 'text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white'"
                                    >
                                        Sign Up
                                    </button>
                                </div>

                                <div
                                    class="relative mt-8 overflow-hidden transition-[height] duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]"
                                    :style="authPanelHeight ? `height: ${authPanelHeight}` : null"
                                >
                                    <div
                                        class="flex w-[200%] transition-transform duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]"
                                        :style="authModalView === 'signin'
                                            ? 'transform: translate3d(0%, 0, 0);'
                                            : 'transform: translate3d(-50%, 0, 0);'"
                                    >
                                        <section
                                            x-ref="signinPanel"
                                            class="w-1/2 flex-none pr-2 sm:pr-4"
                                            x-bind:aria-hidden="authModalView !== 'signin'"
                                            x-bind:inert="authModalView !== 'signin'"
                                        >
                                            <div
                                                class="max-w-xl transition duration-500"
                                                :class="authModalView === 'signin'
                                                    ? 'translate-x-0 opacity-100'
                                                    : '-translate-x-6 opacity-50'"
                                            >
                                                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-brand-700 dark:text-brand-300">Welcome back</p>
                                                <h3 class="mt-3 text-3xl font-semibold text-gray-900 dark:text-white">Sign in with Google or email</h3>
                                                <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-300">
                                                    Use the sign-in method connected to your account. Google-only users should continue with Google.
                                                </p>
                                            </div>

                                            @if ($showSigninErrors)
                                                <div x-data="{ visible: true }" x-show="visible" class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-200">
                                                    <div class="flex items-start justify-between gap-4">
                                                        <p class="font-semibold">Sign-in could not be completed.</p>
                                                        <button
                                                            type="button"
                                                            class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-red-500 transition hover:bg-red-100 hover:text-red-700 dark:text-red-300 dark:hover:bg-red-500/10 dark:hover:text-red-200"
                                                            @click="visible = false"
                                                            aria-label="Dismiss sign-in error"
                                                        >
                                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                                                <path d="M5 5L15 15M15 5L5 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    <ul class="mt-2 space-y-1 pr-8">
                                                        @foreach ($errors->all() as $error)
                                                            <li>{{ $error }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif

                                            <div class="mt-6">
                                                <a href="{{ route('google.redirect') }}" class="inline-flex w-full items-center justify-center gap-3 rounded-2xl border border-gray-200 bg-white px-4 py-3.5 text-sm font-semibold text-gray-800 shadow-theme-xs transition hover:border-brand-200 hover:bg-brand-50 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:hover:border-brand-500/30 dark:hover:bg-brand-500/10">
                                                    <svg class="h-5 w-5" viewBox="0 0 48 48" aria-hidden="true">
                                                        <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303C33.654 32.657 29.201 36 24 36c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.844 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.277 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917Z"/>
                                                        <path fill="#FF3D00" d="M6.306 14.691 12.88 19.51C14.655 15.108 18.961 12 24 12c3.059 0 5.844 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.277 4 24 4c-7.682 0-14.318 4.337-17.694 10.691Z"/>
                                                        <path fill="#4CAF50" d="M24 44c5.176 0 9.86-1.977 13.409-5.192l-6.191-5.238C29.144 35.091 26.7 36 24 36c-5.18 0-9.625-3.331-11.287-7.946l-6.525 5.025C9.529 39.556 16.227 44 24 44Z"/>
                                                        <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303a12.05 12.05 0 0 1-4.085 5.571l.003-.002 6.191 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917Z"/>
                                                    </svg>
                                                    Continue With Google
                                                </a>
                                            </div>

                                            <div class="my-6 flex items-center gap-4">
                                                <div class="h-px flex-1 bg-gray-200 dark:bg-gray-800"></div>
                                                <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-400 dark:text-gray-500">Or Use Email</span>
                                                <div class="h-px flex-1 bg-gray-200 dark:bg-gray-800"></div>
                                            </div>

                                            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                                                @csrf
                                                <input type="hidden" name="auth_form" value="signin">

                                                <div>
                                                    <label for="home_signin_email" class="mb-2 block text-sm font-semibold text-gray-800 dark:text-gray-200">Email Address</label>
                                                    <input
                                                        id="home_signin_email"
                                                        name="email"
                                                        type="email"
                                                        data-auth-autofocus
                                                        value="{{ old('auth_form') === 'signin' ? old('email') : '' }}"
                                                        required
                                                        autocomplete="email"
                                                        class="h-12 w-full rounded-2xl border border-gray-200 bg-white px-4 text-sm text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-brand-500"
                                                        placeholder="you@example.com"
                                                    >
                                                </div>

                                                <div x-data="{ showPassword: false }">
                                                    <label for="home_signin_password" class="mb-2 block text-sm font-semibold text-gray-800 dark:text-gray-200">Password</label>
                                                    <div class="relative">
                                                        <input
                                                            id="home_signin_password"
                                                            name="password"
                                                            x-bind:type="showPassword ? 'text' : 'password'"
                                                            required
                                                            autocomplete="current-password"
                                                            class="h-12 w-full rounded-2xl border border-gray-200 bg-white px-4 pr-14 text-sm text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-brand-500"
                                                            placeholder="Enter your password"
                                                        >
                                                        <button
                                                            type="button"
                                                            class="absolute right-3 top-1/2 inline-flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full text-gray-400 transition hover:text-brand-600 focus:outline-none focus:text-brand-600 dark:text-gray-500 dark:hover:text-brand-300 dark:focus:text-brand-300"
                                                            @click="showPassword = ! showPassword"
                                                            x-bind:aria-label="showPassword ? 'Hide password' : 'Show password'"
                                                        >
                                                            <svg x-show="! showPassword" x-cloak class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                                                <path d="M1.667 10S4.583 4.167 10 4.167 18.333 10 18.333 10 15.417 15.833 10 15.833 1.667 10 1.667 10Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                <path d="M10 12.188a2.188 2.188 0 1 0 0-4.376 2.188 2.188 0 0 0 0 4.376Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                            </svg>
                                                            <svg x-show="showPassword" x-cloak class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                                                <path d="M2.5 2.5 17.5 17.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                <path d="M8.939 4.246A8.85 8.85 0 0 1 10 4.167c5.417 0 8.333 5.833 8.333 5.833a13.16 13.16 0 0 1-2.169 2.953M11.767 11.768A2.188 2.188 0 0 1 8.232 8.233M5.192 5.192A13.599 13.599 0 0 0 1.667 10S4.583 15.833 10 15.833a8.815 8.815 0 0 0 4.808-1.416" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                                    <label class="inline-flex items-center gap-3 text-sm text-gray-600 dark:text-gray-300">
                                                        <input
                                                            type="checkbox"
                                                            name="remember"
                                                            value="1"
                                                            @checked(old('auth_form') === 'signin' && old('remember'))
                                                            class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-600"
                                                        >
                                                        Keep me signed in
                                                    </label>
                                                    <button type="button" class="text-sm font-medium text-brand-700 transition hover:text-brand-800 dark:text-brand-300 dark:hover:text-brand-200" @click="switchAuthModal('signup')">
                                                        Need an account?
                                                    </button>
                                                </div>

                                                <button
                                                    type="submit"
                                                    class="inline-flex w-full items-center justify-center rounded-2xl bg-gray-900 px-4 py-3.5 text-sm font-semibold text-white shadow-theme-xs transition hover:bg-brand-600 dark:bg-brand-500 dark:hover:bg-brand-400"
                                                >
                                                    Sign In With Email
                                                </button>
                                            </form>
                                        </section>

                                        <section
                                            x-ref="signupPanel"
                                            class="w-1/2 flex-none pl-2 sm:pl-4"
                                            x-bind:aria-hidden="authModalView !== 'signup'"
                                            x-bind:inert="authModalView !== 'signup'"
                                        >
                                            <div
                                                class="max-w-xl transition duration-500"
                                                :class="authModalView === 'signup'
                                                    ? 'translate-x-0 opacity-100'
                                                    : 'translate-x-6 opacity-50'"
                                            >
                                                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-brand-700 dark:text-brand-300">Create account</p>
                                                <h3 class="mt-3 text-3xl font-semibold text-gray-900 dark:text-white">Sign up with Google or email</h3>
                                                <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-300">
                                                    Register here on the homepage, then move straight into the app without leaving this landing screen.
                                                </p>
                                            </div>

                                            @if ($showSignupErrors)
                                                <div x-data="{ visible: true }" x-show="visible" class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-200">
                                                    <div class="flex items-start justify-between gap-4">
                                                        <p class="font-semibold">Sign-up could not be completed.</p>
                                                        <button
                                                            type="button"
                                                            class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-red-500 transition hover:bg-red-100 hover:text-red-700 dark:text-red-300 dark:hover:bg-red-500/10 dark:hover:text-red-200"
                                                            @click="visible = false"
                                                            aria-label="Dismiss sign-up error"
                                                        >
                                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                                                <path d="M5 5L15 15M15 5L5 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    <ul class="mt-2 space-y-1 pr-8">
                                                        @foreach ($errors->all() as $error)
                                                            <li>{{ $error }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif

                                            <div class="mt-6">
                                                <a href="{{ route('google.redirect') }}" class="inline-flex w-full items-center justify-center gap-3 rounded-2xl border border-gray-200 bg-white px-4 py-3.5 text-sm font-semibold text-gray-800 shadow-theme-xs transition hover:border-brand-200 hover:bg-brand-50 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:hover:border-brand-500/30 dark:hover:bg-brand-500/10">
                                                    <svg class="h-5 w-5" viewBox="0 0 48 48" aria-hidden="true">
                                                        <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303C33.654 32.657 29.201 36 24 36c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.844 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.277 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917Z"/>
                                                        <path fill="#FF3D00" d="M6.306 14.691 12.88 19.51C14.655 15.108 18.961 12 24 12c3.059 0 5.844 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.277 4 24 4c-7.682 0-14.318 4.337-17.694 10.691Z"/>
                                                        <path fill="#4CAF50" d="M24 44c5.176 0 9.86-1.977 13.409-5.192l-6.191-5.238C29.144 35.091 26.7 36 24 36c-5.18 0-9.625-3.331-11.287-7.946l-6.525 5.025C9.529 39.556 16.227 44 24 44Z"/>
                                                        <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303a12.05 12.05 0 0 1-4.085 5.571l.003-.002 6.191 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917Z"/>
                                                    </svg>
                                                    Continue With Google
                                                </a>
                                            </div>

                                            <div class="my-6 flex items-center gap-4">
                                                <div class="h-px flex-1 bg-gray-200 dark:bg-gray-800"></div>
                                                <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-400 dark:text-gray-500">Or Use Email</span>
                                                <div class="h-px flex-1 bg-gray-200 dark:bg-gray-800"></div>
                                            </div>

                                            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                                                @csrf
                                                <input type="hidden" name="auth_form" value="signup">

                                                <div>
                                                    <label for="home_signup_email" class="mb-2 block text-sm font-semibold text-gray-800 dark:text-gray-200">Email Address</label>
                                                    <input
                                                        id="home_signup_email"
                                                        name="email"
                                                        type="email"
                                                        data-auth-autofocus
                                                        value="{{ old('auth_form') === 'signup' ? old('email') : '' }}"
                                                        required
                                                        autocomplete="email"
                                                        class="h-12 w-full rounded-2xl border border-gray-200 bg-white px-4 text-sm text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-brand-500"
                                                        placeholder="you@example.com"
                                                    >
                                                </div>

                                                <div class="grid gap-5 sm:grid-cols-2">
                                                    <div x-data="{ showPassword: false }">
                                                        <label for="home_signup_password" class="mb-2 block text-sm font-semibold text-gray-800 dark:text-gray-200">Password</label>
                                                        <div class="relative">
                                                            <input
                                                                id="home_signup_password"
                                                                name="password"
                                                                x-bind:type="showPassword ? 'text' : 'password'"
                                                                required
                                                                autocomplete="new-password"
                                                                class="h-12 w-full rounded-2xl border border-gray-200 bg-white px-4 pr-14 text-sm text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-brand-500"
                                                                placeholder="At least 8 characters"
                                                            >
                                                            <button
                                                                type="button"
                                                                class="absolute right-3 top-1/2 inline-flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full text-gray-400 transition hover:text-brand-600 focus:outline-none focus:text-brand-600 dark:text-gray-500 dark:hover:text-brand-300 dark:focus:text-brand-300"
                                                                @click="showPassword = ! showPassword"
                                                                x-bind:aria-label="showPassword ? 'Hide password' : 'Show password'"
                                                            >
                                                                <svg x-show="! showPassword" x-cloak class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                                                    <path d="M1.667 10S4.583 4.167 10 4.167 18.333 10 18.333 10 15.417 15.833 10 15.833 1.667 10 1.667 10Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                    <path d="M10 12.188a2.188 2.188 0 1 0 0-4.376 2.188 2.188 0 0 0 0 4.376Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                </svg>
                                                                <svg x-show="showPassword" x-cloak class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                                                    <path d="M2.5 2.5 17.5 17.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                    <path d="M8.939 4.246A8.85 8.85 0 0 1 10 4.167c5.417 0 8.333 5.833 8.333 5.833a13.16 13.16 0 0 1-2.169 2.953M11.767 11.768A2.188 2.188 0 0 1 8.232 8.233M5.192 5.192A13.599 13.599 0 0 0 1.667 10S4.583 15.833 10 15.833a8.815 8.815 0 0 0 4.808-1.416" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <div x-data="{ showPassword: false }">
                                                        <label for="home_signup_password_confirmation" class="mb-2 block text-sm font-semibold text-gray-800 dark:text-gray-200">Confirm Password</label>
                                                        <div class="relative">
                                                            <input
                                                                id="home_signup_password_confirmation"
                                                                name="password_confirmation"
                                                                x-bind:type="showPassword ? 'text' : 'password'"
                                                                required
                                                                autocomplete="new-password"
                                                                class="h-12 w-full rounded-2xl border border-gray-200 bg-white px-4 pr-14 text-sm text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-brand-500"
                                                                placeholder="Repeat your password"
                                                            >
                                                            <button
                                                                type="button"
                                                                class="absolute right-3 top-1/2 inline-flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full text-gray-400 transition hover:text-brand-600 focus:outline-none focus:text-brand-600 dark:text-gray-500 dark:hover:text-brand-300 dark:focus:text-brand-300"
                                                                @click="showPassword = ! showPassword"
                                                                x-bind:aria-label="showPassword ? 'Hide password' : 'Show password'"
                                                            >
                                                                <svg x-show="! showPassword" x-cloak class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                                                    <path d="M1.667 10S4.583 4.167 10 4.167 18.333 10 18.333 10 15.417 15.833 10 15.833 1.667 10 1.667 10Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                    <path d="M10 12.188a2.188 2.188 0 1 0 0-4.376 2.188 2.188 0 0 0 0 4.376Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                </svg>
                                                                <svg x-show="showPassword" x-cloak class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                                                    <path d="M2.5 2.5 17.5 17.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                    <path d="M8.939 4.246A8.85 8.85 0 0 1 10 4.167c5.417 0 8.333 5.833 8.333 5.833a13.16 13.16 0 0 1-2.169 2.953M11.767 11.768A2.188 2.188 0 0 1 8.232 8.233M5.192 5.192A13.599 13.599 0 0 0 1.667 10S4.583 15.833 10 15.833a8.815 8.815 0 0 0 4.808-1.416" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="pt-1">
                                                    <button type="button" class="text-sm font-medium text-brand-700 transition hover:text-brand-800 dark:text-brand-300 dark:hover:text-brand-200" @click="switchAuthModal('signin')">
                                                        Already have an account?
                                                    </button>
                                                </div>

                                                <button
                                                    type="submit"
                                                    class="inline-flex w-full items-center justify-center rounded-2xl bg-brand-500 px-4 py-3.5 text-sm font-semibold text-white shadow-theme-xs transition hover:bg-brand-600"
                                                >
                                                    Create Account With Email
                                                </button>
                                            </form>
                                        </section>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endguest
    </div>
@endsection
