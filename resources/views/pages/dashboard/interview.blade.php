@extends('layouts.app')

@php
    $featureCards = [
        [
            'title' => 'Authentication',
            'body' => 'Sign in, create accounts, and keep access flow clear for every user of the system.',
            'href' => route('signin'),
            'tone' => 'brand',
            'icon' => 'lock',
        ],
        [
            'title' => 'Voice Practice',
            'body' => 'Answer interview prompts through voice or typed responses inside a structured practice flow.',
            'href' => route('practice'),
            'tone' => 'blue',
            'icon' => 'mic',
        ],
        [
            'title' => 'Progress Tracking',
            'body' => 'Review session counts, scoring patterns, and improvement signals after each practice round.',
            'href' => route('progress'),
            'tone' => 'success',
            'icon' => 'pulse',
        ],
        [
            'title' => 'User Profile',
            'body' => 'Manage profile details and keep a visible record of completed interview practice sessions.',
            'href' => route('profile'),
            'tone' => 'warning',
            'icon' => 'user',
        ],
    ];

    $workflow = [
        [
            'step' => '01',
            'title' => 'Choose an interview category',
            'body' => 'Select a scenario that matches the user goal, from job hiring to technical panels.',
        ],
        [
            'step' => '02',
            'title' => 'Answer by voice or text',
            'body' => 'Simulate the interview naturally instead of practicing from static notes alone.',
        ],
        [
            'step' => '03',
            'title' => 'Review AI automated feedback',
            'body' => 'Receive AI-assisted scoring and criterion-based coaching for clarity, relevance, grammar, and professionalism.',
        ],
        [
            'step' => '04',
            'title' => 'Track improvement',
            'body' => 'Use the dashboard to compare saved AI evaluations and identify what still needs work.',
        ],
    ];

    $onboardingSteps = [
        [
            'label' => 'Session Setup',
            'title' => 'Prepare each interview round before you begin',
            'body' => 'Use Session Setup to define the scenario, pacing, and timing so every practice session starts with the right structure.',
            'target' => 'sidebar-session-setup',
            'area' => 'sidebar',
            'cards' => [
                [
                    'eyebrow' => 'Core Defaults',
                    'title' => 'Question count, coach focus, and pacing',
                    'body' => 'Set the main defaults that preload every practice session with the structure you want.',
                ],
                [
                    'eyebrow' => 'Preferences',
                    'title' => 'Category, response mode, and notes',
                    'body' => 'Save the interview category, preferred answer style, and a quick reminder for your next round.',
                ],
                [
                    'eyebrow' => 'Saved Summary',
                    'title' => 'Preview your setup before saving',
                    'body' => 'See the selected defaults, estimated session time, and category summary in one place.',
                ],
                [
                    'eyebrow' => 'Actions',
                    'title' => 'Save, reset, or jump into practice',
                    'body' => 'Finish by saving your defaults, clearing them, or opening Practice with setup-ready values.',
                ],
            ],
        ],
        [
            'label' => 'Practice',
            'title' => 'Move into the live mock interview flow',
            'body' => 'Practice opens the full interview workspace where you can set up the session, define your target field, generate AI questions, and answer each prompt by voice or text.',
            'target' => 'sidebar-practice',
            'area' => 'sidebar',
            'cards' => [
                [
                    'eyebrow' => 'Session Setup',
                    'title' => 'Tune question count, coaching, and pacing',
                    'body' => 'Start by setting the interview length and guidance style so the next mock run matches your goal.',
                ],
                [
                    'eyebrow' => 'Target Field',
                    'title' => 'Choose the track and target field',
                    'body' => 'Shape the field plan inside Practice and prepare the workspace for a focused interview scenario.',
                ],
                [
                    'eyebrow' => 'AI Prompts',
                    'title' => 'Create a fresh set of prompts',
                    'body' => 'Use the AI question flow to generate track-based interview questions and keep practice sessions varied.',
                ],
                [
                    'eyebrow' => 'Interview Modal',
                    'title' => 'Answer by voice or text with live guidance',
                    'body' => 'Open the interview modal to move through active questions, record answers, and review the running practice state.',
                ],
            ],
        ],
        [
            'label' => 'Learning Lab',
            'title' => 'Study before another attempt',
            'body' => 'Learning Lab gives you extra review material so you can sharpen weak areas before the next round.',
            'target' => 'sidebar-learning-lab',
            'area' => 'sidebar',
            'cards' => [
                [
                    'eyebrow' => 'Modules',
                    'title' => 'Choose guided drills before practice',
                    'body' => 'Open focused modules like answer blueprint, delivery rehearsal, visual presence, and reflection review.',
                ],
                [
                    'eyebrow' => 'Activities',
                    'title' => 'Launch quick study exercises',
                    'body' => 'Start short activities such as quick drills, STAR practice, voice rehearsal, and follow-up sprints.',
                ],
                [
                    'eyebrow' => 'Track Connections',
                    'title' => 'Jump straight into a practice track',
                    'body' => 'Each learning card can send you into the matching interview category with connected drill context.',
                ],
                [
                    'eyebrow' => 'Signals',
                    'title' => 'Use saved history to pick the next move',
                    'body' => 'Recent scores, completed sessions, and recommendations help you decide what to practice next.',
                ],
            ],
        ],
        [
            'label' => 'Interview Chatbot',
            'title' => 'Ask for fast coaching between sessions',
            'body' => 'The Interview Chatbot is your quick coaching lane for feedback, rewrites, follow-up prompts, and answer ideas.',
            'target' => 'sidebar-chatbot',
            'area' => 'sidebar',
            'cards' => [
                [
                    'eyebrow' => 'Provider Status',
                    'title' => 'Check which AI routes are available',
                    'body' => 'Run a live API check and review which supported providers are configured before sending requests.',
                ],
                [
                    'eyebrow' => 'Workspace',
                    'title' => 'Switch between coach chat, question builder, and review',
                    'body' => 'Use one workspace for coaching questions, generated interview sets, and answer-draft feedback.',
                ],
                [
                    'eyebrow' => 'Scope & Prompts',
                    'title' => 'Choose interview scope and fast starters',
                    'body' => 'Set the interview scope, provider route, and quick prompts so requests stay focused and relevant.',
                ],
                [
                    'eyebrow' => 'Results',
                    'title' => 'Review output with built-in guardrails',
                    'body' => 'See generated questions or review feedback while staying inside the interview-only PH coaching limits.',
                ],
            ],
        ],
        [
            'label' => 'Progress',
            'title' => 'Track growth across saved sessions',
            'body' => 'Use Progress to review score trends, streaks, category breakdowns, recent performance cards, and export-ready summaries in one place.',
            'target' => 'sidebar-progress',
            'area' => 'sidebar',
            'cards' => [
                [
                    'eyebrow' => 'Trends',
                    'title' => 'Follow average score movement over time',
                    'body' => 'The progress dashboard highlights overall sessions, average score, weekly goals, and streak momentum together.',
                ],
                [
                    'eyebrow' => 'History',
                    'title' => 'Open saved sessions from the dashboard view',
                    'body' => 'Review session history, detailed session cards, and recent performance snapshots without leaving the analytics workflow.',
                ],
                [
                    'eyebrow' => 'Exports',
                    'title' => 'Download capstone-ready reports',
                    'body' => 'Export saved history as JSON or CSV when you need documentation, reporting, or offline review.',
                ],
            ],
        ],
        [
            'label' => 'Session Review',
            'title' => 'Inspect every saved interview in detail',
            'body' => 'Session Review is the fastest way to search saved interviews, filter by category, and reopen question-by-question answer data.',
            'target' => 'sidebar-session-review',
            'area' => 'sidebar',
            'cards' => [
                [
                    'eyebrow' => 'Search',
                    'title' => 'Find a past session by prompt, answer, or category',
                    'body' => 'Use the saved-session search, category filter, and sort controls to narrow the list before opening a result.',
                ],
                [
                    'eyebrow' => 'Saved List',
                    'title' => 'Jump between all reviewed interviews',
                    'body' => 'The session list keeps totals, best score, and current focus visible while you compare multiple interview runs.',
                ],
                [
                    'eyebrow' => 'Detail View',
                    'title' => 'Read every question, answer, and score signal',
                    'body' => 'Open the full review panel to inspect what you answered, where the score dipped, and what the AI feedback recommended next.',
                ],
            ],
        ],
        [
            'label' => 'Feedback Center',
            'title' => 'Spot repeated coaching themes faster',
            'body' => 'Feedback Center gathers saved evaluations so you can compare strengths, improvement areas, and session-level summaries across practice runs.',
            'target' => 'sidebar-feedback-center',
            'area' => 'sidebar',
            'cards' => [
                [
                    'eyebrow' => 'Filters',
                    'title' => 'Search feedback by category or review type',
                    'body' => 'Narrow the workspace by search term, interview category, or evaluation view before scanning the saved results.',
                ],
                [
                    'eyebrow' => 'Digest',
                    'title' => 'Surface common strengths and improvement areas',
                    'body' => 'The coaching digest summarizes repeated praise and repeated weaknesses so the next practice goal is easier to choose.',
                ],
                [
                    'eyebrow' => 'Saved Reviews',
                    'title' => 'Compare answer evaluations with full-session summaries',
                    'body' => 'Keep both individual answer reviews and session history together when you want to verify whether a pattern keeps repeating.',
                ],
            ],
        ],
        [
            'label' => 'Category Insights',
            'title' => 'Compare readiness by interview category',
            'body' => 'Category Insights shows which interview tracks are strongest, which still need attention, and where another practice round will help most.',
            'target' => 'sidebar-category-insights',
            'area' => 'sidebar',
            'cards' => [
                [
                    'eyebrow' => 'Compare',
                    'title' => 'Sort categories by score, recency, or volume',
                    'body' => 'Search a category name and reorder the list to see which interview scenarios are most practiced or most successful.',
                ],
                [
                    'eyebrow' => 'Readiness',
                    'title' => 'See strongest categories and next-focus areas',
                    'body' => 'The summary cards call out strongest category, ready categories, and the best next area for improvement.',
                ],
                [
                    'eyebrow' => 'Detail',
                    'title' => 'Open a category-level breakdown before the next session',
                    'body' => 'Use the detail panel to decide which scenario should guide your next saved interview run.',
                ],
            ],
        ],
    ];
@endphp

@section('content')
    <div id="dashboardOnboarding" class="flex flex-col gap-10">
        <div x-show="$store.dashboardOnboarding.active" x-cloak x-transition.opacity
            class="fixed inset-0 z-[100000] bg-gray-900/35 backdrop-blur-sm"></div>

        <div x-show="$store.dashboardOnboarding.active" x-cloak x-transition.opacity
            class="fixed inset-x-4 bottom-4 z-[100002] sm:left-auto sm:right-4"
            :class="$store.dashboardOnboarding.currentStep()?.form || ($store.dashboardOnboarding.currentStep()?.cards || []).length > 0
                ? 'w-[min(480px,calc(100vw-2rem))]'
                : 'w-[min(360px,calc(100vw-2rem))]'">
            <div
                class="shadow-theme-xl max-h-[calc(100vh-2rem)] w-full overflow-y-auto rounded-[24px] border border-white/70 bg-white/95 p-4 backdrop-blur-xl dark:border-gray-700/70 dark:bg-gray-900/95 sm:p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <span
                            class="bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-300 inline-flex items-center gap-2 rounded-full px-3 py-1 text-[11px] font-semibold tracking-[0.18em] uppercase">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7"
                                stroke="currentColor" class="h-3.5 w-3.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m4.5 19.5 15-15m0 0H8.25m11.25 0v11.25" />
                            </svg>
                            <span x-text="$store.dashboardOnboarding.currentStep()?.label"></span>
                        </span>

                        <h2 class="mt-3 text-lg leading-7 font-semibold text-gray-900 dark:text-white"
                            x-text="$store.dashboardOnboarding.currentStep()?.title"></h2>
                    </div>

                    <button type="button" @click="$store.dashboardOnboarding.finish()"
                        class="rounded-full border border-gray-200 px-3 py-1.5 text-[12px] font-medium text-gray-500 transition hover:border-gray-300 hover:text-gray-700 dark:border-gray-700 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:text-white">
                        Skip
                    </button>
                </div>

                <p class="mt-3 text-[13px] leading-6 text-gray-600 dark:text-gray-300"
                    x-text="$store.dashboardOnboarding.currentStep()?.body"></p>

                <div x-show="$store.dashboardOnboarding.currentStep()?.form" x-cloak class="mt-4">
                    <div
                        class="rounded-[22px] border border-brand-100 bg-gradient-to-br from-brand-50 via-white to-blue-light-50/60 p-3.5 dark:border-brand-500/20 dark:from-brand-500/10 dark:via-gray-900 dark:to-blue-light-500/10">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-brand-500"
                                    x-text="$store.dashboardOnboarding.currentStep()?.form?.eyebrow"></p>
                                <p class="mt-2 text-[13px] font-semibold leading-5 text-gray-900 dark:text-white"
                                    x-text="$store.dashboardOnboarding.currentStep()?.form?.title"></p>
                            </div>

                            <span
                                class="rounded-full border border-brand-200 bg-white/80 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-brand-500 dark:border-brand-500/30 dark:bg-gray-900/80 dark:text-brand-300">
                                Form
                            </span>
                        </div>

                        <div class="mt-4 space-y-3">
                            <template
                                x-for="section in ($store.dashboardOnboarding.currentStep()?.form?.sections || [])"
                                :key="section.title">
                                <div
                                    class="rounded-2xl border border-white/70 bg-white/85 p-3 shadow-theme-xs dark:border-white/10 dark:bg-gray-900/80">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500"
                                        x-text="section.title"></p>

                                    <div x-show="(section.fields || []).length > 0" class="mt-3 space-y-2.5">
                                        <template x-for="field in (section.fields || [])"
                                            :key="`${section.title}-${field.label}`">
                                            <div>
                                                <p class="text-[10px] font-medium uppercase tracking-[0.14em] text-gray-400 dark:text-gray-500"
                                                    x-text="field.label"></p>

                                                <div
                                                    class="mt-1.5 rounded-xl border border-gray-200 bg-gray-50/90 px-3 py-2.5 dark:border-gray-700 dark:bg-white/5">
                                                    <div x-show="field.kind !== 'textarea'"
                                                        class="flex items-center justify-between gap-3">
                                                        <span class="text-[12px] font-medium text-gray-800 dark:text-gray-100"
                                                            x-text="field.value"></span>
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24" stroke-width="1.7"
                                                            stroke="currentColor"
                                                            class="h-3.5 w-3.5 text-gray-400 dark:text-gray-500">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                                        </svg>
                                                    </div>

                                                    <p x-show="field.kind === 'textarea'"
                                                        class="text-[11px] leading-5 text-gray-600 dark:text-gray-300"
                                                        x-text="field.value"></p>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <div x-show="(section.actions || []).length > 0"
                                        class="mt-3 grid grid-cols-2 gap-2">
                                        <template x-for="action in (section.actions || [])"
                                            :key="`${section.title}-${action.label}`">
                                            <span
                                                class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-[11px] font-semibold"
                                                :class="action.tone === 'primary'
                                                    ? 'bg-brand-500 text-white'
                                                    : 'border border-brand-200 bg-brand-50 text-brand-600 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-300'">
                                                <span x-text="action.label"></span>
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div x-show="($store.dashboardOnboarding.currentStep()?.cards || []).length > 0" x-cloak
                    class="mt-4">
                    <p
                        class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-400 dark:text-gray-500">
                        What's inside
                    </p>

                    <div class="mt-3 grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                        <template x-for="card in ($store.dashboardOnboarding.currentStep()?.cards || [])"
                            :key="card.eyebrow">
                            <div
                                class="flex h-full flex-col rounded-2xl border border-gray-200 bg-gray-50/80 p-3 dark:border-gray-700 dark:bg-white/5">
                                <p class="text-[10px] font-semibold tracking-[0.18em] text-brand-500 uppercase"
                                    x-text="card.eyebrow"></p>
                                <p class="mt-2 text-[12px] font-semibold leading-5 text-gray-900 dark:text-white"
                                    x-text="card.title"></p>
                                <p class="mt-1 text-[11px] leading-5 text-gray-500 dark:text-gray-400"
                                    x-text="card.body"></p>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="mt-5 flex items-center gap-1.5">
                    <template x-for="(step, index) in $store.dashboardOnboarding.steps" :key="step.target">
                        <button type="button" @click="$store.dashboardOnboarding.goTo(index)"
                            class="h-2 flex-1 rounded-full transition"
                            :class="index === $store.dashboardOnboarding.currentStepIndex
                                ? 'bg-brand-500'
                                : (index < $store.dashboardOnboarding.currentStepIndex
                                    ? 'bg-brand-200 dark:bg-brand-700'
                                    : 'bg-gray-200 dark:bg-gray-700')"
                            :aria-label="`Go to step ${index + 1}`"></button>
                    </template>
                </div>

                <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-400"
                        x-text="$store.dashboardOnboarding.stepLabel()"></p>

                    <div class="grid grid-cols-2 gap-3 sm:min-w-[220px]">
                        <button type="button" @click="$store.dashboardOnboarding.previous()"
                            :disabled="$store.dashboardOnboarding.currentStepIndex === 0"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 px-4 py-3 text-[13px] font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:text-gray-200 dark:hover:border-gray-600 dark:hover:bg-white/5">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7"
                                stroke="currentColor" class="h-4 w-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                            </svg>
                            Previous
                        </button>

                        <button type="button" @click="$store.dashboardOnboarding.next()"
                            class="bg-brand-500 inline-flex items-center justify-center gap-2 rounded-xl px-4 py-3 text-[13px] font-medium text-white transition hover:bg-brand-600">
                            <span
                                x-text="$store.dashboardOnboarding.currentStepIndex === $store.dashboardOnboarding.steps.length - 1 ? 'Finish' : 'Next'"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7"
                                stroke="currentColor" class="h-4 w-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <section data-dashboard-tour-target="dashboard-home"
            class="shadow-theme-lg overflow-hidden rounded-[28px] border border-gray-200/50 bg-white/80 backdrop-blur-2xl dark:border-white/5 dark:bg-gray-900/80"
            :class="$store.dashboardOnboarding.targetClass('dashboard-home')">
            <div class="grid gap-6 p-6 lg:grid-cols-[1.1fr_0.9fr] lg:p-8">
                <div class="flex flex-col justify-center">
                    <span class="bg-brand-50 text-brand-500 dark:bg-brand-500/15 dark:text-brand-400 mb-4 inline-flex w-fit rounded-full px-3 py-1 text-xs font-medium tracking-[0.2em] uppercase">
                        Capstone Project Dashboard
                    </span>

                    <h1 class="bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-title-sm mb-4 font-bold text-transparent dark:from-white dark:to-gray-400">
                        AI-Based Interview Practice System
                    </h1>

                    <p class="text-theme-sm mb-6 max-w-2xl leading-7 text-gray-600 dark:text-gray-400">
                        A web-based interview simulation platform designed for students and job seekers. Users can answer
                        interview questions through text or voice, receive AI-based automated feedback for clarity,
                        relevance, grammar, and professionalism, and track performance across saved practice sessions.
                    </p>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('practice') }}"
                            class="bg-brand-500 text-theme-sm hover:scale-105 hover:bg-brand-600 inline-flex items-center justify-center rounded-xl px-5 py-3 font-medium text-white shadow-[0_0_15px_rgba(70,95,255,0.3)] transition-all duration-300">
                            Start Practice
                        </a>

                        <a href="{{ route('progress') }}"
                            class="text-theme-sm inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-3 font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            View Progress
                        </a>

                        <a href="{{ route('profile') }}"
                            class="text-theme-sm inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-3 font-medium text-gray-700 transition hover:bg-gray-50 hover:-translate-y-0.5 hover:shadow-theme-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            Profile
                        </a>

                        <button type="button" @click="$store.dashboardOnboarding.restart()"
                            class="text-theme-sm inline-flex items-center justify-center rounded-xl border border-dashed border-brand-300 bg-brand-50 px-5 py-3 font-medium text-brand-600 transition hover:border-brand-400 hover:bg-brand-100 dark:border-brand-700 dark:bg-brand-500/10 dark:text-brand-300 dark:hover:bg-brand-500/15">
                            {{ count($onboardingSteps) }}-Step Tour
                        </button>
                    </div>
                </div>

                <div data-dashboard-tour-target="dashboard-metrics"
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2"
                    :class="$store.dashboardOnboarding.targetClass('dashboard-metrics')">
                    @foreach ($summaryCards as $card)
                        <div class="shadow-theme-xs rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/5">
                            <p class="text-theme-xs mb-2 text-gray-500 dark:text-gray-400">{{ $card['label'] }}</p>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $card['value'] }}</h3>
                            <p @class([
                                'text-theme-xs mt-2 font-medium',
                                'text-success-600' => $card['tone'] === 'success',
                                'text-blue-light-600' => $card['tone'] === 'blue',
                                'text-brand-500' => $card['tone'] === 'brand',
                                'text-warning-600' => $card['tone'] === 'warning',
                            ])>
                                {{ $card['detail'] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <div class="grid grid-cols-12 gap-4 md:gap-6">
            <div class="col-span-12 space-y-6 xl:col-span-8">
                <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($featureCards as $card)
                        <a href="{{ $card['href'] }}"
                            class="shadow-theme-sm hover:shadow-theme-xl hover:-translate-y-1.5 rounded-2xl border border-gray-200/50 bg-white/80 p-5 backdrop-blur-xl transition-all duration-300 dark:border-white/5 dark:bg-gray-900/80">
                            <h3 class="mb-2 flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-white">
                                @if ($card['icon'] === 'lock')
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor" class="h-5 w-5 text-brand-500">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M16.5 10.5V6.75A2.25 2.25 0 0 0 14.25 4.5h-4.5A2.25 2.25 0 0 0 7.5 6.75v3.75m9 0h.75A2.25 2.25 0 0 1 19.5 12.75v4.5A2.25 2.25 0 0 1 17.25 19.5h-10.5A2.25 2.25 0 0 1 4.5 17.25v-4.5A2.25 2.25 0 0 1 6.75 10.5h9.75Z" />
                                    </svg>
                                @elseif ($card['icon'] === 'mic')
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor" class="h-5 w-5 text-blue-light-500">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 18.75a6 6 0 0 0 6-6v-1.5m-12 0v1.5a6 6 0 0 0 6 6m0 0v3m-3-3h6m-3-15a3 3 0 0 1 3 3v6a3 3 0 1 1-6 0v-6a3 3 0 0 1 3-3Z" />
                                    </svg>
                                @elseif ($card['icon'] === 'pulse')
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor" class="h-5 w-5 text-success-500">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125h3.75L9 7.5l3.75 9 2.25-4.5H21" />
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor" class="h-5 w-5 text-warning-500">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.25a8.25 8.25 0 0 1 14.998 0" />
                                    </svg>
                                @endif
                                {{ $card['title'] }}
                            </h3>
                            <p class="text-theme-sm text-gray-600 dark:text-gray-400">
                                {{ $card['body'] }}
                            </p>
                        </a>
                    @endforeach
                </section>

                <section id="practice-categories"
                    class="shadow-theme-sm rounded-2xl border border-gray-200/50 bg-white/80 p-6 backdrop-blur-xl dark:border-white/5 dark:bg-gray-900/80">
                    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-xl font-bold text-transparent dark:from-white dark:to-gray-400">
                                Interview Categories
                            </h2>
                            <p class="text-theme-sm text-gray-500 dark:text-gray-400">
                                Practice different interview scenarios based on user goals
                            </p>
                        </div>

                        <a href="#workflow"
                            class="text-theme-sm text-brand-500 hover:text-brand-600 font-medium">
                            Review workflow
                        </a>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($categories as $category)
                            <div class="rounded-xl border border-gray-200 p-4 transition-all duration-300 hover:-translate-y-1 hover:border-brand-300 hover:shadow-theme-md dark:border-gray-800 dark:hover:border-brand-700">
                                <h3 class="mb-2 font-semibold text-gray-900 dark:text-white">
                                    {{ $category['title'] }}
                                </h3>
                                <p class="text-theme-sm text-gray-600 dark:text-gray-400">
                                    {{ $category['body'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section id="workflow"
                    class="shadow-theme-sm rounded-2xl border border-gray-200/50 bg-white/80 p-6 backdrop-blur-xl dark:border-white/5 dark:bg-gray-900/80">
                    <div class="mb-6">
                        <h2 class="bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-xl font-bold text-transparent dark:from-white dark:to-gray-400">
                            Practice Workflow
                        </h2>
                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">
                            Simple steps that connect dashboard, interview rehearsal, and result review
                        </p>
                    </div>

                    <div class="grid gap-4 lg:grid-cols-2">
                        @foreach ($workflow as $item)
                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/5">
                                <div class="bg-brand-50 text-brand-500 dark:bg-brand-500/15 dark:text-brand-300 inline-flex rounded-full px-3 py-1 text-xs font-semibold tracking-[0.2em] uppercase">
                                    Step {{ $item['step'] }}
                                </div>
                                <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ $item['title'] }}
                                </h3>
                                <p class="mt-2 text-sm leading-7 text-gray-600 dark:text-gray-400">
                                    {{ $item['body'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>

            <div class="col-span-12 space-y-6 xl:col-span-4">
                <section id="progress-overview"
                    class="shadow-theme-sm rounded-2xl border border-gray-200/50 bg-white/80 p-6 backdrop-blur-xl dark:border-white/5 dark:bg-gray-900/80">
                    <div class="mb-5">
                        <h2 class="bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-xl font-bold text-transparent dark:from-white dark:to-gray-400">
                            Sample Evaluation
                        </h2>
                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">
                            Latest AI-generated evaluation from your saved interview practice
                        </p>
                    </div>

                    <div class="mb-5 rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-white/5">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-gray-700 shadow-theme-xs dark:bg-gray-800 dark:text-gray-300">
                                {{ $sampleEvaluation['category'] }}
                            </span>
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-gray-700 shadow-theme-xs dark:bg-gray-800 dark:text-gray-300">
                                {{ $sampleEvaluation['questionLabel'] }}
                            </span>
                        </div>

                        <h3 class="mt-4 text-sm font-semibold leading-7 text-gray-900 dark:text-white">
                            {{ $sampleEvaluation['question'] }}
                        </h3>

                        <div class="mt-4 space-y-2 text-sm text-gray-600 dark:text-gray-400">
                            <p><strong class="text-gray-900 dark:text-white">AI Provider:</strong> {{ $sampleEvaluation['provider'] }}</p>
                            @if ($sampleEvaluation['savedAt'])
                                <p><strong class="text-gray-900 dark:text-white">Saved:</strong> {{ $sampleEvaluation['savedAt'] }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-4">
                        @foreach ($sampleEvaluation['items'] as $item)
                            <div>
                                <div class="mb-1 flex items-center justify-between">
                                    <span class="text-theme-sm text-gray-700 dark:text-gray-300">
                                        {{ $item['label'] }}
                                    </span>
                                    <span @class([
                                        'text-theme-sm font-medium',
                                        'text-brand-500' => $item['tone'] === 'brand',
                                        'text-success-600' => $item['tone'] === 'success',
                                        'text-blue-light-600' => $item['tone'] === 'blue',
                                        'text-warning-600' => $item['tone'] === 'warning',
                                    ])>
                                        {{ $item['score'] }}
                                    </span>
                                </div>
                                <div class="h-2 rounded-full bg-gray-200 dark:bg-gray-800">
                                    <div @class([
                                        'h-2 rounded-full',
                                        'bg-brand-500' => $item['tone'] === 'brand',
                                        'bg-success-500' => $item['tone'] === 'success',
                                        'bg-blue-light-500' => $item['tone'] === 'blue',
                                        'bg-warning-500' => $item['tone'] === 'warning',
                                    ]) style="width: {{ $item['width'] }}"></div>
                                </div>
                                <p class="text-theme-xs mt-2 leading-6 text-gray-500 dark:text-gray-400">
                                    {{ $item['note'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>

                    <div class="bg-brand-50 text-theme-sm dark:bg-brand-500/15 mt-6 rounded-xl p-4 leading-7 text-gray-700 dark:text-gray-300">
                        <strong>Feedback Summary:</strong><br>
                        {{ $sampleEvaluation['summary'] }}
                        <div class="mt-3">
                            <strong>Next Step:</strong><br>
                            {{ $sampleEvaluation['nextStep'] }}
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (!window.Alpine) {
                return;
            }

            window.Alpine.store('dashboardOnboarding').bootDashboard(@json($onboardingSteps));
        });
    </script>
@endpush
