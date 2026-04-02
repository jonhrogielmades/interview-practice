@extends('layouts.fullscreen-layout')

@php
    $firstTrackId = $practiceTracks[0]['id'] ?? null;
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
    $workflow = [
        [
            'step' => '01',
            'title' => 'Start with the right interview track',
            'description' => 'Browse job, scholarship, college admission, and IT interview flows before you even open the workspace.',
        ],
        [
            'step' => '02',
            'title' => 'Tune the session to match your pace',
            'description' => 'Question count, coaching style, pacing, and response mode are already reflected from the live practice setup options.',
        ],
        [
            'step' => '03',
            'title' => 'Practice, save, and review',
            'description' => 'Returning users can jump back into dashboard insights, AI-reviewed answers, and saved feedback without extra clicks.',
        ],
    ];
@endphp

@section('content')
    <div x-data="{
        mobileMenu: false,
        activeTrack: @js($firstTrackId),
        authModalOpen: @js($authModalOpen),
        authModalView: @js($initialAuthView),
        init() {
            this.$watch('authModalOpen', value => document.body.classList.toggle('overflow-hidden', value));
            document.body.classList.toggle('overflow-hidden', this.authModalOpen);

            if (this.authModalOpen) {
                this.clearAuthQuery();
            }
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
            this.authModalView = view;
            this.authModalOpen = true;
            this.mobileMenu = false;
            this.clearAuthQuery();
        },
        closeAuthModal() {
            this.authModalOpen = false;
            this.clearAuthQuery();
        }
    }" @keydown.escape.window="closeAuthModal()"
        class="relative min-h-screen overflow-hidden bg-white text-gray-900 transition-colors dark:bg-gray-950 dark:text-white">
        <div class="home-orb left-[-8rem] top-[-6rem] h-72 w-72 bg-blue-light-200"></div>
        <div class="home-orb right-[-5rem] top-24 h-80 w-80 bg-blue-light-300 [animation-delay:1.5s]"></div>
        <div class="home-orb bottom-[-8rem] left-1/3 h-80 w-80 bg-blue-light-100 dark:bg-blue-light-500/20 [animation-delay:3s]"></div>
        <div class="home-grid absolute inset-0 opacity-50 dark:opacity-30"></div>

        <header class="sticky top-0 z-40 border-b border-gray-200 bg-white/95 backdrop-blur-xl dark:border-gray-800 dark:bg-gray-950/90">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:grid lg:grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] lg:px-8 lg:py-4">
                <a href="{{ route('home') }}" class="flex min-w-0 flex-1 items-center gap-3 lg:flex-none">
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                        <img src="{{ asset('images/logo/interviewpilot-icon.png') }}" alt="InterviewPilot" class="h-full w-full object-cover" />
                    </span>
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-semibold uppercase tracking-[0.22em] text-gray-900 dark:text-white">InterviewPilot</span>
                        <span class="block truncate text-sm text-gray-500 dark:text-gray-400">Guided interview practice with AI feedback</span>
                    </span>
                </a>

                <nav class="hidden items-center justify-center gap-1 lg:flex lg:justify-self-center">
                    <a href="#workflow"
                        class="inline-flex h-11 items-center rounded-full px-4 text-sm font-medium text-gray-600 transition hover:bg-blue-light-50 hover:text-blue-light-700 dark:text-gray-300 dark:hover:bg-blue-light-500/10 dark:hover:text-blue-light-200">Workflow</a>
                    <a href="#features"
                        class="inline-flex h-11 items-center rounded-full px-4 text-sm font-medium text-gray-600 transition hover:bg-blue-light-50 hover:text-blue-light-700 dark:text-gray-300 dark:hover:bg-blue-light-500/10 dark:hover:text-blue-light-200">Features</a>
                    <a href="#tracks"
                        class="inline-flex h-11 items-center rounded-full px-4 text-sm font-medium text-gray-600 transition hover:bg-blue-light-50 hover:text-blue-light-700 dark:text-gray-300 dark:hover:bg-blue-light-500/10 dark:hover:text-blue-light-200">Tracks</a>
                    <a href="#cta"
                        class="inline-flex h-11 items-center rounded-full px-4 text-sm font-medium text-gray-600 transition hover:bg-blue-light-50 hover:text-blue-light-700 dark:text-gray-300 dark:hover:bg-blue-light-500/10 dark:hover:text-blue-light-200">Launch</a>
                </nav>

                <div class="hidden items-center justify-end gap-3 lg:flex lg:justify-self-end">
                    <button type="button"
                        class="relative flex h-11 w-11 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
                        @click.prevent="$store.theme.toggle()">
                        <span class="sr-only">Toggle theme</span>
                        <svg class="hidden h-5 w-5 fill-current dark:block" viewBox="0 0 20 20" aria-hidden="true">
                            <path d="M9.99998 1.5415C10.4142 1.5415 10.75 1.87729 10.75 2.2915V3.5415C10.75 3.95572 10.4142 4.2915 9.99998 4.2915C9.58577 4.2915 9.24998 3.95572 9.24998 3.5415V2.2915C9.24998 1.87729 9.58577 1.5415 9.99998 1.5415ZM10.0009 6.79327C8.22978 6.79327 6.79402 8.22904 6.79402 10.0001C6.79402 11.7712 8.22978 13.207 10.0009 13.207C11.772 13.207 13.2078 11.7712 13.2078 10.0001C13.2078 8.22904 11.772 6.79327 10.0009 6.79327ZM5.29402 10.0001C5.29402 7.40061 7.40135 5.29327 10.0009 5.29327C12.6004 5.29327 14.7078 7.40061 14.7078 10.0001C14.7078 12.5997 12.6004 14.707 10.0009 14.707C7.40135 14.707 5.29402 12.5997 5.29402 10.0001ZM15.9813 5.08035C16.2742 4.78746 16.2742 4.31258 15.9813 4.01969C15.6884 3.7268 15.2135 3.7268 14.9207 4.01969L14.0368 4.90357C13.7439 5.19647 13.7439 5.67134 14.0368 5.96423C14.3297 6.25713 14.8045 6.25713 15.0974 5.96423L15.9813 5.08035ZM18.4577 10.0001C18.4577 10.4143 18.1219 10.7501 17.7077 10.7501H16.4577C16.0435 10.7501 15.7077 10.4143 15.7077 10.0001C15.7077 9.58592 16.0435 9.25013 16.4577 9.25013H17.7077C18.1219 9.25013 18.4577 9.58592 18.4577 10.0001ZM14.9207 15.9806C15.2135 16.2735 15.6884 16.2735 15.9813 15.9806C16.2742 15.6877 16.2742 15.2128 15.9813 14.9199L15.0974 14.036C14.8045 13.7431 14.3297 13.7431 14.0368 14.036C13.7439 14.3289 13.7439 14.8038 14.0368 15.0967L14.9207 15.9806ZM9.99998 15.7088C10.4142 15.7088 10.75 16.0445 10.75 16.4588V17.7088C10.75 18.123 10.4142 18.4588 9.99998 18.4588C9.58577 18.4588 9.24998 18.123 9.24998 17.7088V16.4588C9.24998 16.0445 9.58577 15.7088 9.99998 15.7088ZM5.96356 15.0972C6.25646 14.8043 6.25646 14.3295 5.96356 14.0366C5.67067 13.7437 5.1958 13.7437 4.9029 14.0366L4.01902 14.9204C3.72613 15.2133 3.72613 15.6882 4.01902 15.9811C4.31191 16.274 4.78679 16.274 5.07968 15.9811L5.96356 15.0972ZM4.29224 10.0001C4.29224 10.4143 3.95645 10.7501 3.54224 10.7501H2.29224C1.87802 10.7501 1.54224 10.4143 1.54224 10.0001C1.54224 9.58592 1.87802 9.25013 2.29224 9.25013H3.54224C3.95645 9.25013 4.29224 9.58592 4.29224 10.0001ZM4.9029 5.9637C5.1958 6.25659 5.67067 6.25659 5.96356 5.9637C6.25646 5.6708 6.25646 5.19593 5.96356 4.90303L5.07968 4.01915C4.78679 3.72626 4.31191 3.72626 4.01902 4.01915C3.72613 4.31204 3.72613 4.78692 4.01902 5.07981L4.9029 5.9637Z" />
                        </svg>
                        <svg class="h-5 w-5 fill-current dark:hidden" viewBox="0 0 20 20" aria-hidden="true">
                            <path d="M17.4547 11.97L18.1799 12.1611C18.265 11.8383 18.1265 11.4982 17.8401 11.3266C17.5538 11.1551 17.1885 11.1934 16.944 11.4207L17.4547 11.97ZM8.0306 2.5459L8.57989 3.05657C8.80718 2.81209 8.84554 2.44682 8.67398 2.16046C8.50243 1.8741 8.16227 1.73559 7.83948 1.82066L8.0306 2.5459ZM12.9154 13.0035C9.64678 13.0035 6.99707 10.3538 6.99707 7.08524H5.49707C5.49707 11.1823 8.81835 14.5035 12.9154 14.5035V13.0035ZM16.944 11.4207C15.8869 12.4035 14.4721 13.0035 12.9154 13.0035V14.5035C14.8657 14.5035 16.6418 13.7499 17.9654 12.5193L16.944 11.4207ZM16.7295 11.7789C15.9437 14.7607 13.2277 16.9586 10.0003 16.9586V18.4586C13.9257 18.4586 17.2249 15.7853 18.1799 12.1611L16.7295 11.7789ZM10.0003 16.9586C6.15734 16.9586 3.04199 13.8433 3.04199 10.0003H1.54199C1.54199 14.6717 5.32892 18.4586 10.0003 18.4586V16.9586ZM3.04199 10.0003C3.04199 6.77289 5.23988 4.05695 8.22173 3.27114L7.83948 1.82066C4.21532 2.77574 1.54199 6.07486 1.54199 10.0003H3.04199ZM6.99707 7.08524C6.99707 5.52854 7.5971 4.11366 8.57989 3.05657L7.48132 2.03522C6.25073 3.35885 5.49707 5.13487 5.49707 7.08524H6.99707Z" />
                        </svg>
                    </button>

                    @auth
                        <a href="{{ route('dashboard') }}"
                            class="inline-flex h-11 items-center justify-center rounded-full border border-gray-300 bg-white px-5 text-sm font-semibold text-gray-700 shadow-theme-xs transition hover:-translate-y-0.5 hover:border-blue-light-300 hover:bg-blue-light-50 hover:text-blue-light-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-blue-light-500/40 dark:hover:bg-blue-light-500/10 dark:hover:text-blue-light-200">
                            View Dashboard
                        </a>
                        <a href="{{ route('practice') }}"
                            class="inline-flex h-11 items-center justify-center rounded-full bg-blue-light-500 px-5 text-sm font-semibold text-white shadow-theme-xs transition hover:-translate-y-0.5 hover:bg-blue-light-600">
                            Continue Practice
                        </a>
                    @else
                        <a href="{{ route('signin') }}" @click.prevent="openAuthModal('signin')"
                            class="inline-flex h-11 items-center justify-center rounded-full border border-gray-300 bg-white px-5 text-sm font-semibold text-gray-700 shadow-theme-xs transition hover:-translate-y-0.5 hover:border-blue-light-300 hover:bg-blue-light-50 hover:text-blue-light-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-blue-light-500/40 dark:hover:bg-blue-light-500/10 dark:hover:text-blue-light-200">
                            Sign In
                        </a>
                        <a href="{{ route('signup') }}" @click.prevent="openAuthModal('signup')"
                            class="inline-flex h-11 items-center justify-center rounded-full bg-blue-light-500 px-5 text-sm font-semibold text-white shadow-theme-xs transition hover:-translate-y-0.5 hover:bg-blue-light-600">
                            Create Free Account
                        </a>
                    @endauth
                </div>

                <div class="flex shrink-0 items-center gap-2 lg:hidden">
                    <button type="button"
                        class="relative flex h-11 w-11 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
                        @click.prevent="$store.theme.toggle()">
                        <span class="sr-only">Toggle theme</span>
                        <svg class="hidden h-5 w-5 fill-current dark:block" viewBox="0 0 20 20" aria-hidden="true">
                            <path d="M9.99998 1.5415C10.4142 1.5415 10.75 1.87729 10.75 2.2915V3.5415C10.75 3.95572 10.4142 4.2915 9.99998 4.2915C9.58577 4.2915 9.24998 3.95572 9.24998 3.5415V2.2915C9.24998 1.87729 9.58577 1.5415 9.99998 1.5415ZM10.0009 6.79327C8.22978 6.79327 6.79402 8.22904 6.79402 10.0001C6.79402 11.7712 8.22978 13.207 10.0009 13.207C11.772 13.207 13.2078 11.7712 13.2078 10.0001C13.2078 8.22904 11.772 6.79327 10.0009 6.79327ZM5.29402 10.0001C5.29402 7.40061 7.40135 5.29327 10.0009 5.29327C12.6004 5.29327 14.7078 7.40061 14.7078 10.0001C14.7078 12.5997 12.6004 14.707 10.0009 14.707C7.40135 14.707 5.29402 12.5997 5.29402 10.0001ZM15.9813 5.08035C16.2742 4.78746 16.2742 4.31258 15.9813 4.01969C15.6884 3.7268 15.2135 3.7268 14.9207 4.01969L14.0368 4.90357C13.7439 5.19647 13.7439 5.67134 14.0368 5.96423C14.3297 6.25713 14.8045 6.25713 15.0974 5.96423L15.9813 5.08035ZM18.4577 10.0001C18.4577 10.4143 18.1219 10.7501 17.7077 10.7501H16.4577C16.0435 10.7501 15.7077 10.4143 15.7077 10.0001C15.7077 9.58592 16.0435 9.25013 16.4577 9.25013H17.7077C18.1219 9.25013 18.4577 9.58592 18.4577 10.0001ZM14.9207 15.9806C15.2135 16.2735 15.6884 16.2735 15.9813 15.9806C16.2742 15.6877 16.2742 15.2128 15.9813 14.9199L15.0974 14.036C14.8045 13.7431 14.3297 13.7431 14.0368 14.036C13.7439 14.3289 13.7439 14.8038 14.0368 15.0967L14.9207 15.9806ZM9.99998 15.7088C10.4142 15.7088 10.75 16.0445 10.75 16.4588V17.7088C10.75 18.123 10.4142 18.4588 9.99998 18.4588C9.58577 18.4588 9.24998 18.123 9.24998 17.7088V16.4588C9.24998 16.0445 9.58577 15.7088 9.99998 15.7088ZM5.96356 15.0972C6.25646 14.8043 6.25646 14.3295 5.96356 14.0366C5.67067 13.7437 5.1958 13.7437 4.9029 14.0366L4.01902 14.9204C3.72613 15.2133 3.72613 15.6882 4.01902 15.9811C4.31191 16.274 4.78679 16.274 5.07968 15.9811L5.96356 15.0972ZM4.29224 10.0001C4.29224 10.4143 3.95645 10.7501 3.54224 10.7501H2.29224C1.87802 10.7501 1.54224 10.4143 1.54224 10.0001C1.54224 9.58592 1.87802 9.25013 2.29224 9.25013H3.54224C3.95645 9.25013 4.29224 9.58592 4.29224 10.0001ZM4.9029 5.9637C5.1958 6.25659 5.67067 6.25659 5.96356 5.9637C6.25646 5.6708 6.25646 5.19593 5.96356 4.90303L5.07968 4.01915C4.78679 3.72626 4.31191 3.72626 4.01902 4.01915C3.72613 4.31204 3.72613 4.78692 4.01902 5.07981L4.9029 5.9637Z" />
                        </svg>
                        <svg class="h-5 w-5 fill-current dark:hidden" viewBox="0 0 20 20" aria-hidden="true">
                            <path d="M17.4547 11.97L18.1799 12.1611C18.265 11.8383 18.1265 11.4982 17.8401 11.3266C17.5538 11.1551 17.1885 11.1934 16.944 11.4207L17.4547 11.97ZM8.0306 2.5459L8.57989 3.05657C8.80718 2.81209 8.84554 2.44682 8.67398 2.16046C8.50243 1.8741 8.16227 1.73559 7.83948 1.82066L8.0306 2.5459ZM12.9154 13.0035C9.64678 13.0035 6.99707 10.3538 6.99707 7.08524H5.49707C5.49707 11.1823 8.81835 14.5035 12.9154 14.5035V13.0035ZM16.944 11.4207C15.8869 12.4035 14.4721 13.0035 12.9154 13.0035V14.5035C14.8657 14.5035 16.6418 13.7499 17.9654 12.5193L16.944 11.4207ZM16.7295 11.7789C15.9437 14.7607 13.2277 16.9586 10.0003 16.9586V18.4586C13.9257 18.4586 17.2249 15.7853 18.1799 12.1611L16.7295 11.7789ZM10.0003 16.9586C6.15734 16.9586 3.04199 13.8433 3.04199 10.0003H1.54199C1.54199 14.6717 5.32892 18.4586 10.0003 18.4586V16.9586ZM3.04199 10.0003C3.04199 6.77289 5.23988 4.05695 8.22173 3.27114L7.83948 1.82066C4.21532 2.77574 1.54199 6.07486 1.54199 10.0003H3.04199ZM6.99707 7.08524C6.99707 5.52854 7.5971 4.11366 8.57989 3.05657L7.48132 2.03522C6.25073 3.35885 5.49707 5.13487 5.49707 7.08524H6.99707Z" />
                        </svg>
                    </button>
                    <button type="button"
                        class="relative flex h-11 w-11 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
                        @click="mobileMenu = ! mobileMenu">
                        <span class="sr-only">Toggle navigation</span>
                        <svg class="h-5 w-5 stroke-current" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M3.75 6.25H16.25M3.75 10H16.25M3.75 13.75H16.25" stroke-width="1.5" stroke-linecap="round" />
                        </svg>
                    </button>
                </div>
            </div>

            <div x-show="mobileMenu" x-cloak x-transition.opacity
                class="border-t border-gray-200 bg-white/95 px-4 py-4 lg:hidden dark:border-gray-800 dark:bg-gray-950/95">
                <div class="mx-auto flex max-w-7xl flex-col gap-3">
                    <a href="#workflow" class="rounded-2xl px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-blue-light-50 hover:text-blue-light-700 dark:text-gray-200 dark:hover:bg-blue-light-500/10 dark:hover:text-blue-light-200" @click="mobileMenu = false">Workflow</a>
                    <a href="#features" class="rounded-2xl px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-blue-light-50 hover:text-blue-light-700 dark:text-gray-200 dark:hover:bg-blue-light-500/10 dark:hover:text-blue-light-200" @click="mobileMenu = false">Features</a>
                    <a href="#tracks" class="rounded-2xl px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-blue-light-50 hover:text-blue-light-700 dark:text-gray-200 dark:hover:bg-blue-light-500/10 dark:hover:text-blue-light-200" @click="mobileMenu = false">Tracks</a>
                    <a href="#cta" class="rounded-2xl px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-blue-light-50 hover:text-blue-light-700 dark:text-gray-200 dark:hover:bg-blue-light-500/10 dark:hover:text-blue-light-200" @click="mobileMenu = false">Launch</a>

                    @auth
                        <a href="{{ route('dashboard') }}"
                            class="rounded-2xl border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-gray-700 shadow-theme-xs transition hover:border-blue-light-300 hover:bg-blue-light-50 hover:text-blue-light-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-blue-light-500/40 dark:hover:bg-blue-light-500/10 dark:hover:text-blue-light-200">
                            View Dashboard
                        </a>
                        <a href="{{ route('practice') }}"
                            class="rounded-2xl bg-blue-light-500 px-4 py-3 text-sm font-semibold text-white shadow-theme-xs transition hover:bg-blue-light-600">
                            Continue Practice
                        </a>
                    @else
                        <a href="{{ route('signin') }}" @click.prevent="openAuthModal('signin')"
                            class="rounded-2xl border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-gray-700 shadow-theme-xs transition hover:border-blue-light-300 hover:bg-blue-light-50 hover:text-blue-light-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-blue-light-500/40 dark:hover:bg-blue-light-500/10 dark:hover:text-blue-light-200">
                            Sign In
                        </a>
                        <a href="{{ route('signup') }}" @click.prevent="openAuthModal('signup')"
                            class="rounded-2xl bg-blue-light-500 px-4 py-3 text-sm font-semibold text-white shadow-theme-xs transition hover:bg-blue-light-600">
                            Create Free Account
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        <main class="relative">
            <section class="mx-auto max-w-7xl px-4 pb-14 pt-10 sm:px-6 sm:pt-16 lg:px-8 lg:pb-20">
                <div class="grid gap-10 lg:grid-cols-[1.06fr_0.94fr] lg:items-start">
                    <div class="space-y-8">
                        <span class="home-chip">Philippine-ready interview practice</span>

                        <div class="max-w-2xl">
                            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-blue-light-700 dark:text-blue-light-300">TailAdmin-inspired landing experience</p>
                            <h1 class="mt-4 text-4xl font-semibold leading-tight text-gray-900 sm:text-5xl lg:text-[4.25rem] lg:leading-[1.02] dark:text-white">
                                Practice the interview that matters next, not someday.
                            </h1>
                            <p class="mt-5 max-w-xl text-lg leading-8 text-gray-600 dark:text-gray-300">
                                InterviewPilot now opens with a cleaner homepage that points visitors into the real sign-in,
                                session setup, practice, progress, and dashboard flows already built in this project.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            @auth
                                <a href="{{ route('practice') }}"
                                    class="inline-flex items-center justify-center rounded-full bg-blue-light-500 px-6 py-3 text-sm font-semibold text-white shadow-theme-xs transition hover:-translate-y-0.5 hover:bg-blue-light-600">
                                    Continue Practice
                                </a>
                                <a href="{{ route('session-setup') }}"
                                    class="inline-flex items-center justify-center rounded-full border border-gray-300 bg-white px-6 py-3 text-sm font-semibold text-gray-700 shadow-theme-xs transition hover:-translate-y-0.5 hover:border-blue-light-300 hover:bg-blue-light-50 hover:text-blue-light-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-blue-light-500/40 dark:hover:bg-blue-light-500/10 dark:hover:text-blue-light-200">
                                    Session Setup
                                </a>
                            @else
                                <a href="{{ route('signup') }}" @click.prevent="openAuthModal('signup')"
                                    class="inline-flex items-center justify-center rounded-full bg-blue-light-500 px-6 py-3 text-sm font-semibold text-white shadow-theme-xs transition hover:-translate-y-0.5 hover:bg-blue-light-600">
                                    Create Free Account
                                </a>
                                <a href="{{ route('signin') }}" @click.prevent="openAuthModal('signin')"
                                    class="inline-flex items-center justify-center rounded-full border border-gray-300 bg-white px-6 py-3 text-sm font-semibold text-gray-700 shadow-theme-xs transition hover:-translate-y-0.5 hover:border-blue-light-300 hover:bg-blue-light-50 hover:text-blue-light-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-blue-light-500/40 dark:hover:bg-blue-light-500/10 dark:hover:text-blue-light-200">
                                    Sign In
                                </a>
                            @endauth

                            <a href="#tracks"
                                class="inline-flex items-center justify-center rounded-full border border-blue-light-200 bg-blue-light-50 px-6 py-3 text-sm font-semibold text-blue-light-700 transition hover:-translate-y-0.5 hover:border-blue-light-300 hover:bg-blue-light-100 dark:border-blue-light-500/20 dark:bg-blue-light-500/10 dark:text-blue-light-200 dark:hover:bg-blue-light-500/15">
                                Explore Practice Tracks
                            </a>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                            @foreach ($stats as $stat)
                                <div class="home-panel p-5">
                                    <p class="text-3xl font-semibold text-gray-900 dark:text-white">{{ $stat['value'] }}</p>
                                    <p class="mt-2 text-sm font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">{{ $stat['label'] }}</p>
                                    <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-300">{{ $stat['detail'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="home-panel relative overflow-hidden p-6 sm:p-8">
                        <div class="absolute inset-x-0 top-0 h-40 bg-[radial-gradient(circle_at_top,_rgba(11,165,236,0.18),_transparent_70%)] dark:bg-[radial-gradient(circle_at_top,_rgba(54,191,250,0.16),_transparent_70%)]"></div>

                        <div class="relative">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold uppercase tracking-[0.26em] text-blue-light-700 dark:text-blue-light-300">Interactive preview</p>
                                    <h2 class="mt-3 text-2xl font-semibold text-gray-900 dark:text-white">Switch tracks and inspect real practice content.</h2>
                                </div>
                                <span class="rounded-full border border-gray-900/10 bg-gray-900 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-white dark:border-white/10 dark:bg-white/10">
                                    Responsive
                                </span>
                            </div>

                            <div class="mt-6 flex flex-wrap gap-2">
                                @foreach ($practiceTracks as $track)
                                    <button type="button"
                                        class="rounded-full px-4 py-2 text-sm font-medium transition"
                                        @click="activeTrack = '{{ $track['id'] }}'"
                                        :class="activeTrack === '{{ $track['id'] }}'
                                            ? 'bg-blue-light-500 text-white shadow-theme-xs'
                                            : 'border border-gray-200 bg-white text-gray-600 hover:border-blue-light-200 hover:text-blue-light-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-blue-light-500/40 dark:hover:bg-blue-light-500/10 dark:hover:text-blue-light-200'">
                                        {{ $track['name'] }}
                                    </button>
                                @endforeach
                            </div>

                            <div class="mt-6 space-y-4">
                                @foreach ($practiceTracks as $track)
                                    <section x-show="activeTrack === '{{ $track['id'] }}'" x-cloak x-transition.opacity.duration.300ms>
                                        <div class="rounded-[28px] border border-gray-900/5 bg-gray-900 p-5 text-white shadow-theme-lg dark:border-white/10">
                                            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-blue-light-200">{{ $track['name'] }}</p>
                                            <p class="mt-3 text-base leading-7 text-white/80">{{ $track['description'] }}</p>

                                            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                                                <div>
                                                    <h3 class="text-sm font-semibold uppercase tracking-[0.22em] text-blue-light-200/90">Sample questions</h3>
                                                    <ul class="mt-3 space-y-3 text-sm leading-6 text-white/80">
                                                        @foreach ($track['questions'] as $question)
                                                            <li class="flex gap-3">
                                                                <span class="mt-1 h-2 w-2 rounded-full bg-blue-light-400"></span>
                                                                <span>{{ $question }}</span>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>

                                                <div class="space-y-4">
                                                    <div>
                                                        <h3 class="text-sm font-semibold uppercase tracking-[0.22em] text-blue-light-200/90">Local focus</h3>
                                                        <ul class="mt-3 space-y-3 text-sm leading-6 text-white/80">
                                                            @foreach ($track['localFocus'] as $focus)
                                                                <li class="flex gap-3">
                                                                    <span class="mt-1 h-2 w-2 rounded-full bg-blue-light-300"></span>
                                                                    <span>{{ $focus }}</span>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>

                                                    <div>
                                                        <h3 class="text-sm font-semibold uppercase tracking-[0.22em] text-blue-light-200/90">Quick prompts</h3>
                                                        <div class="mt-3 flex flex-wrap gap-2">
                                                            @foreach ($track['quickPrompts'] as $prompt)
                                                                <span class="rounded-full border border-white/10 bg-white/10 px-3 py-2 text-xs font-medium text-white/85">
                                                                    {{ $prompt }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </section>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="workflow" class="mx-auto max-w-7xl px-4 pb-14 sm:px-6 lg:px-8 lg:pb-20">
                <div class="grid gap-6 lg:grid-cols-[0.95fr_1.05fr]">
                    <div class="home-panel p-6 sm:p-8">
                        <span class="home-chip">How it works</span>
                        <h2 class="mt-6 text-3xl font-semibold text-gray-900 dark:text-white">Move from landing page to practice flow without dead ends.</h2>
                        <p class="mt-4 max-w-xl text-base leading-7 text-gray-600 dark:text-gray-300">
                            The homepage now acts like a real product entry point, not a blank route. Every CTA takes the
                            user into an existing part of the application.
                        </p>

                        <div class="mt-8 space-y-4">
                            @foreach ($workflow as $item)
                                <div class="rounded-[28px] border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/70">
                                    <div class="flex items-start gap-4">
                                        <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gray-900 text-base font-semibold text-white ring-4 ring-blue-light-50 dark:ring-blue-light-500/10">
                                            {{ $item['step'] }}
                                        </span>
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $item['title'] }}</h3>
                                            <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">{{ $item['description'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="home-panel p-6 sm:p-8">
                        <span class="home-chip">Session controls</span>

                        <div class="mt-6 grid gap-6 sm:grid-cols-2">
                            <div class="rounded-[24px] border border-blue-light-100 bg-blue-light-50 p-5 dark:border-blue-light-500/20 dark:bg-blue-light-500/10">
                                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-blue-light-700 dark:text-blue-light-200">Question counts</p>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @foreach ($questionCountOptions as $count)
                                        <span class="rounded-full border border-blue-light-100 bg-white px-3 py-2 text-sm font-medium text-gray-900 shadow-theme-xs dark:border-blue-light-500/20 dark:bg-gray-900 dark:text-white">
                                            {{ $count }} questions
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <div class="rounded-[24px] border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900/70">
                                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-gray-900 dark:text-white">Response modes</p>
                                <ul class="mt-4 space-y-3 text-sm leading-6 text-gray-700 dark:text-gray-300">
                                    @foreach ($responseModes as $mode)
                                        <li class="flex items-center gap-3">
                                            <span class="h-2.5 w-2.5 rounded-full bg-blue-light-500"></span>
                                            <span>{{ $mode }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            <div class="rounded-[24px] border border-gray-200 bg-gray-50 p-5 dark:border-gray-800 dark:bg-gray-900/70">
                                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-gray-900 dark:text-white">Focus styles</p>
                                <ul class="mt-4 space-y-3 text-sm leading-6 text-gray-700 dark:text-gray-300">
                                    @foreach ($focusModes as $mode)
                                        <li class="flex items-center gap-3">
                                            <span class="h-2.5 w-2.5 rounded-full bg-gray-900 dark:bg-blue-light-300"></span>
                                            <span>{{ $mode }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            <div class="rounded-[24px] border border-blue-light-100 bg-blue-light-50 p-5 dark:border-blue-light-500/20 dark:bg-blue-light-500/10">
                                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-blue-light-700 dark:text-blue-light-200">Answer pacing</p>
                                <ul class="mt-4 space-y-3 text-sm leading-6 text-gray-700 dark:text-gray-300">
                                    @foreach ($pacingModes as $mode)
                                        <li>
                                            <span class="font-semibold text-gray-900 dark:text-white">{{ $mode['label'] }}</span>
                                            <span class="block text-gray-600 dark:text-gray-400">{{ $mode['detail'] }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="features" class="mx-auto max-w-7xl px-4 pb-14 sm:px-6 lg:px-8 lg:pb-20">
                <div class="home-panel overflow-hidden p-6 sm:p-8">
                    <div class="flex flex-col gap-6 border-b border-gray-200 pb-8 dark:border-gray-800 lg:flex-row lg:items-end lg:justify-between">
                        <div class="max-w-3xl">
                            <span class="home-chip">Platform features</span>
                            <h2 class="mt-6 text-3xl font-semibold text-gray-900 dark:text-white">InterviewPilot ships with more than 25 visible product features.</h2>
                            <p class="mt-4 text-base leading-7 text-gray-600 dark:text-gray-300">
                                The project already covers guided practice, AI coaching, saved history, profile tools, and
                                provider-aware chatbot workflows. This section makes that scope easier to scan at a glance.
                            </p>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="rounded-[28px] border border-blue-light-100 bg-blue-light-50 px-5 py-4 dark:border-blue-light-500/20 dark:bg-blue-light-500/10">
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-light-700 dark:text-blue-light-200">Feature count</p>
                                <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ count($platformFeatures) }}</p>
                            </div>
                            <div class="rounded-[28px] border border-gray-200 bg-white px-5 py-4 dark:border-gray-800 dark:bg-gray-900/70">
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Coverage</p>
                                <p class="mt-2 text-sm leading-6 text-gray-700 dark:text-gray-300">Practice flow, chatbot routing, saved results, profile updates, and mobile-ready UI.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($platformFeatures as $feature)
                            <article @class([
                                'rounded-[28px] border p-5 shadow-theme-xs transition duration-300 hover:-translate-y-1 hover:shadow-theme-lg',
                                'border-brand-100 bg-brand-50/70 dark:border-brand-500/20 dark:bg-brand-500/10' => $feature['tone'] === 'brand',
                                'border-blue-light-100 bg-blue-light-50/80 dark:border-blue-light-500/20 dark:bg-blue-light-500/10' => $feature['tone'] === 'blue',
                                'border-success-100 bg-success-50/80 dark:border-success-500/20 dark:bg-success-500/10' => $feature['tone'] === 'success',
                                'border-warning-100 bg-warning-50/80 dark:border-warning-500/20 dark:bg-warning-500/10' => $feature['tone'] === 'warning',
                            ])>
                                <div class="flex items-start gap-4">
                                    <span @class([
                                        'inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl text-sm font-semibold',
                                        'bg-white text-brand-600 dark:bg-gray-900 dark:text-brand-300' => $feature['tone'] === 'brand',
                                        'bg-white text-blue-light-700 dark:bg-gray-900 dark:text-blue-light-200' => $feature['tone'] === 'blue',
                                        'bg-white text-success-700 dark:bg-gray-900 dark:text-success-300' => $feature['tone'] === 'success',
                                        'bg-white text-warning-700 dark:bg-gray-900 dark:text-warning-300' => $feature['tone'] === 'warning',
                                    ])>
                                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                    </span>

                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $feature['title'] }}</h3>
                                        <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">{{ $feature['body'] }}</p>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="tracks" class="mx-auto max-w-7xl px-4 pb-14 sm:px-6 lg:px-8 lg:pb-20">
                <div class="home-panel p-6 sm:p-8">
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                        <div class="max-w-2xl">
                            <span class="home-chip">Practice tracks</span>
                            <h2 class="mt-6 text-3xl font-semibold text-gray-900 dark:text-white">The homepage content stays synced with the actual interview catalog.</h2>
                            <p class="mt-4 text-base leading-7 text-gray-600 dark:text-gray-300">
                                These cards come from the same categories used by the workspace, which keeps the public-facing
                                experience aligned with the real product behavior.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            @auth
                                <a href="{{ route('progress') }}"
                                    class="inline-flex items-center justify-center rounded-full border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 shadow-theme-xs transition hover:border-blue-light-300 hover:bg-blue-light-50 hover:text-blue-light-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-blue-light-500/40 dark:hover:bg-blue-light-500/10 dark:hover:text-blue-light-200">
                                    See Progress
                                </a>
                                <a href="{{ route('feedback-center') }}"
                                    class="inline-flex items-center justify-center rounded-full border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 shadow-theme-xs transition hover:border-blue-light-300 hover:bg-blue-light-50 hover:text-blue-light-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-blue-light-500/40 dark:hover:bg-blue-light-500/10 dark:hover:text-blue-light-200">
                                    Open Feedback Center
                                </a>
                            @else
                                <a href="{{ route('signup') }}" @click.prevent="openAuthModal('signup')"
                                    class="inline-flex items-center justify-center rounded-full border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 shadow-theme-xs transition hover:border-blue-light-300 hover:bg-blue-light-50 hover:text-blue-light-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-blue-light-500/40 dark:hover:bg-blue-light-500/10 dark:hover:text-blue-light-200">
                                    Create account to save sessions
                                </a>
                            @endauth
                        </div>
                    </div>

                    <div class="mt-8 grid gap-5 md:grid-cols-2">
                        @foreach ($practiceTracks as $track)
                            <article class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-theme-xs transition duration-300 hover:-translate-y-1 hover:border-blue-light-200 hover:shadow-theme-lg dark:border-gray-800 dark:bg-gray-900/70 dark:hover:border-blue-light-500/20">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-light-700 dark:text-blue-light-300">Track {{ $loop->iteration }}</p>
                                        <h3 class="mt-3 text-2xl font-semibold text-gray-900 dark:text-white">{{ $track['name'] }}</h3>
                                    </div>
                                    <span class="rounded-full bg-blue-light-500 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-white">
                                        {{ count($track['questions']) }} questions
                                    </span>
                                </div>

                                <p class="mt-4 text-sm leading-7 text-gray-600 dark:text-gray-300">{{ $track['description'] }}</p>

                                <div class="mt-6 space-y-3">
                                    @foreach ($track['questions'] as $question)
                                        <div class="rounded-2xl border border-gray-100 bg-gray-50 px-4 py-3 text-sm leading-6 text-gray-700 transition hover:border-blue-light-100 hover:bg-blue-light-50 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-200 dark:hover:border-blue-light-500/20 dark:hover:bg-blue-light-500/5">
                                            {{ $question }}
                                        </div>
                                    @endforeach
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="cta" class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8 lg:pb-24">
                <div class="relative overflow-hidden rounded-[36px] border border-gray-200 bg-gray-900 px-6 py-10 text-white shadow-theme-xl sm:px-8 lg:px-12 dark:border-gray-800">
                    <div class="absolute right-0 top-0 h-48 w-48 rounded-full bg-blue-light-500/20 blur-3xl"></div>
                    <div class="absolute bottom-[-4rem] left-12 h-40 w-40 rounded-full bg-blue-light-300/10 blur-3xl"></div>

                    <div class="relative grid gap-8 lg:grid-cols-[1fr_auto] lg:items-end">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-blue-light-200">Ready to launch</p>
                            <h2 class="mt-4 max-w-2xl text-3xl font-semibold sm:text-4xl">
                                The homepage now works as the front door to the full InterviewPilot experience.
                            </h2>
                            <p class="mt-4 max-w-2xl text-base leading-7 text-white/75">
                                It gives guests a clear path into account creation and gives signed-in users a faster return
                                path to the tools they actually use.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            @auth
                                <a href="{{ route('dashboard') }}"
                                    class="inline-flex items-center justify-center rounded-full bg-blue-light-500 px-6 py-3 text-sm font-semibold text-white shadow-theme-xs transition hover:bg-blue-light-400">
                                    View Dashboard
                                </a>
                                <a href="{{ route('practice') }}"
                                    class="inline-flex items-center justify-center rounded-full border border-white/15 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/10">
                                    Continue Practice
                                </a>
                            @else
                                <a href="{{ route('signup') }}" @click.prevent="openAuthModal('signup')"
                                    class="inline-flex items-center justify-center rounded-full bg-blue-light-500 px-6 py-3 text-sm font-semibold text-white shadow-theme-xs transition hover:bg-blue-light-400">
                                    Create Free Account
                                </a>
                                <a href="{{ route('signin') }}" @click.prevent="openAuthModal('signin')"
                                    class="inline-flex items-center justify-center rounded-full border border-white/15 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/10">
                                    Sign In
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </section>
        </main>

        @guest
            <div x-show="authModalOpen" x-cloak x-transition.opacity class="fixed inset-0 z-[999] flex items-center justify-center p-4 sm:p-6">
                <div class="absolute inset-0 bg-slate-950/65 backdrop-blur-sm" @click="closeAuthModal()"></div>

                <div class="relative z-10 w-full max-w-5xl overflow-hidden rounded-[32px] border border-gray-200 bg-white shadow-[0_32px_100px_-42px_rgba(15,23,42,0.65)] dark:border-gray-800 dark:bg-gray-950">
                    <button
                        type="button"
                        class="absolute right-4 top-4 z-20 inline-flex h-11 w-11 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 transition hover:border-blue-light-300 hover:text-blue-light-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:hover:border-blue-light-500/30 dark:hover:text-blue-light-200"
                        @click="closeAuthModal()">
                        <span class="sr-only">Close authentication modal</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M5 5L15 15M15 5L5 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                        </svg>
                    </button>

                    <div class="grid lg:grid-cols-[0.92fr_1.08fr] lg:items-stretch">
                        <aside class="relative hidden border-r border-gray-200 bg-gray-900 px-8 py-10 text-white dark:border-gray-800 lg:flex lg:flex-col lg:justify-center lg:gap-12">
                            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(56,189,248,0.25),_transparent_35%)]"></div>

                            <div class="relative mx-auto w-full max-w-md">
                                <span class="inline-flex rounded-full bg-blue-light-400/15 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-blue-light-200">
                                    Home Auth Modal
                                </span>
                                <h2 class="mt-5 max-w-sm text-4xl font-semibold leading-tight text-white">
                                    Sign in or create your account without leaving the homepage.
                                </h2>
                                <p class="mt-4 max-w-md text-sm leading-7 text-white/75">
                                    Use only the two supported access methods: Google or your email account. Once you're
                                    in, the existing dashboard and practice flows stay exactly the same.
                                </p>
                            </div>

                            <div class="relative mx-auto w-full max-w-md space-y-4">
                                <div class="rounded-[28px] border border-white/10 bg-white/5 p-5">
                                    <p class="text-sm font-semibold text-white">Supported methods</p>
                                    <p class="mt-2 text-sm leading-6 text-white/70">
                                        Google for fast sign-in, or email and password for your regular account flow.
                                    </p>
                                </div>

                                <div class="rounded-[28px] border border-blue-light-400/20 bg-blue-light-400/10 p-5">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-light-200">InterviewPilot</p>
                                    <p class="mt-2 text-sm leading-6 text-blue-light-50">
                                        Open the modal, choose your method, and jump straight into interview practice.
                                    </p>
                                </div>
                            </div>
                        </aside>

                        <div class="flex px-5 py-6 sm:px-8 sm:py-8 lg:px-10 lg:py-10">
                            <div class="w-full lg:mx-auto lg:max-w-[37rem]">
                                <div class="flex rounded-2xl border border-blue-light-100 bg-blue-light-50 p-1 dark:border-blue-light-500/20 dark:bg-blue-light-500/10">
                                    <button
                                        type="button"
                                        class="flex-1 rounded-xl px-4 py-3 text-sm font-semibold transition"
                                        @click="authModalView = 'signin'"
                                        :class="authModalView === 'signin'
                                            ? 'bg-gray-900 text-white shadow-theme-xs dark:bg-white dark:text-gray-900'
                                            : 'text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white'">
                                        Sign In
                                    </button>
                                    <button
                                        type="button"
                                        class="flex-1 rounded-xl px-4 py-3 text-sm font-semibold transition"
                                        @click="authModalView = 'signup'"
                                        :class="authModalView === 'signup'
                                            ? 'bg-blue-light-500 text-white shadow-theme-xs'
                                            : 'text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white'">
                                        Sign Up
                                    </button>
                                </div>

                                <section x-show="authModalView === 'signin'" x-transition.opacity.duration.200ms class="mt-8">
                                <div class="max-w-xl">
                                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-blue-light-700 dark:text-blue-light-300">Welcome back</p>
                                    <h3 class="mt-3 text-3xl font-semibold text-gray-900 dark:text-white">Sign in with Google or email</h3>
                                    <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-300">
                                        Use the sign-in method connected to your account. Google-only users should continue with Google.
                                    </p>
                                </div>

                                @if ($showSigninErrors)
                                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-200">
                                        <p class="font-semibold">Sign-in could not be completed.</p>
                                        <ul class="mt-2 space-y-1">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="mt-6">
                                    <a href="{{ route('google.redirect') }}"
                                        class="inline-flex w-full items-center justify-center gap-3 rounded-2xl border border-gray-200 bg-white px-4 py-3.5 text-sm font-semibold text-gray-800 shadow-theme-xs transition hover:border-blue-light-200 hover:bg-blue-light-50 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:hover:border-blue-light-500/30 dark:hover:bg-blue-light-500/10">
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
                                            value="{{ old('auth_form') === 'signin' ? old('email') : '' }}"
                                            required
                                            autocomplete="email"
                                            class="h-12 w-full rounded-2xl border border-gray-200 bg-white px-4 text-sm text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-blue-light-400 focus:ring-4 focus:ring-blue-light-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-blue-light-500"
                                            placeholder="you@example.com">
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
                                                class="h-12 w-full rounded-2xl border border-gray-200 bg-white px-4 pr-14 text-sm text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-blue-light-400 focus:ring-4 focus:ring-blue-light-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-blue-light-500"
                                                placeholder="Enter your password">
                                            <button
                                                type="button"
                                                class="absolute right-3 top-1/2 inline-flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full text-gray-400 transition hover:text-blue-light-600 focus:outline-none focus:text-blue-light-600 dark:text-gray-500 dark:hover:text-blue-light-300 dark:focus:text-blue-light-300"
                                                @click="showPassword = ! showPassword"
                                                x-bind:aria-label="showPassword ? 'Hide password' : 'Show password'">
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
                                                class="h-4 w-4 rounded border-gray-300 text-blue-light-600 focus:ring-blue-light-500 dark:border-gray-600">
                                            Keep me signed in
                                        </label>
                                        <button type="button" class="text-sm font-medium text-blue-light-700 transition hover:text-blue-light-800 dark:text-blue-light-300 dark:hover:text-blue-light-200" @click="authModalView = 'signup'">
                                            Need an account?
                                        </button>
                                    </div>

                                    <button
                                        type="submit"
                                        class="inline-flex w-full items-center justify-center rounded-2xl bg-gray-900 px-4 py-3.5 text-sm font-semibold text-white shadow-theme-xs transition hover:bg-blue-light-600 dark:bg-blue-light-500 dark:hover:bg-blue-light-400">
                                        Sign In With Email
                                    </button>
                                </form>
                                </section>

                                <section x-show="authModalView === 'signup'" x-transition.opacity.duration.200ms class="mt-8">
                                <div class="max-w-xl">
                                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-blue-light-700 dark:text-blue-light-300">Create account</p>
                                    <h3 class="mt-3 text-3xl font-semibold text-gray-900 dark:text-white">Sign up with Google or email</h3>
                                    <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-300">
                                        Register here on the homepage, then move straight into the app without leaving this landing screen.
                                    </p>
                                </div>

                                @if ($showSignupErrors)
                                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-200">
                                        <p class="font-semibold">Sign-up could not be completed.</p>
                                        <ul class="mt-2 space-y-1">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="mt-6">
                                    <a href="{{ route('google.redirect') }}"
                                        class="inline-flex w-full items-center justify-center gap-3 rounded-2xl border border-gray-200 bg-white px-4 py-3.5 text-sm font-semibold text-gray-800 shadow-theme-xs transition hover:border-blue-light-200 hover:bg-blue-light-50 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:hover:border-blue-light-500/30 dark:hover:bg-blue-light-500/10">
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
                                            value="{{ old('auth_form') === 'signup' ? old('email') : '' }}"
                                            required
                                            autocomplete="email"
                                            class="h-12 w-full rounded-2xl border border-gray-200 bg-white px-4 text-sm text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-blue-light-400 focus:ring-4 focus:ring-blue-light-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-blue-light-500"
                                            placeholder="you@example.com">
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
                                                    class="h-12 w-full rounded-2xl border border-gray-200 bg-white px-4 pr-14 text-sm text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-blue-light-400 focus:ring-4 focus:ring-blue-light-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-blue-light-500"
                                                    placeholder="At least 8 characters">
                                                <button
                                                    type="button"
                                                    class="absolute right-3 top-1/2 inline-flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full text-gray-400 transition hover:text-blue-light-600 focus:outline-none focus:text-blue-light-600 dark:text-gray-500 dark:hover:text-blue-light-300 dark:focus:text-blue-light-300"
                                                    @click="showPassword = ! showPassword"
                                                    x-bind:aria-label="showPassword ? 'Hide password' : 'Show password'">
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
                                                    class="h-12 w-full rounded-2xl border border-gray-200 bg-white px-4 pr-14 text-sm text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-blue-light-400 focus:ring-4 focus:ring-blue-light-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-blue-light-500"
                                                    placeholder="Repeat your password">
                                                <button
                                                    type="button"
                                                    class="absolute right-3 top-1/2 inline-flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full text-gray-400 transition hover:text-blue-light-600 focus:outline-none focus:text-blue-light-600 dark:text-gray-500 dark:hover:text-blue-light-300 dark:focus:text-blue-light-300"
                                                    @click="showPassword = ! showPassword"
                                                    x-bind:aria-label="showPassword ? 'Hide password' : 'Show password'">
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
                                        <button type="button" class="text-sm font-medium text-blue-light-700 transition hover:text-blue-light-800 dark:text-blue-light-300 dark:hover:text-blue-light-200" @click="authModalView = 'signin'">
                                            Already have an account?
                                        </button>
                                    </div>

                                    <button
                                        type="submit"
                                        class="inline-flex w-full items-center justify-center rounded-2xl bg-blue-light-500 px-4 py-3.5 text-sm font-semibold text-white shadow-theme-xs transition hover:bg-blue-light-600">
                                        Create Account With Email
                                    </button>
                                </form>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endguest
    </div>
@endsection
