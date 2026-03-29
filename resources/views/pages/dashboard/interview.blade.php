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
@endphp

@section('content')
    <div class="space-y-6">
        <section class="shadow-theme-sm overflow-hidden rounded-[28px] border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="grid gap-6 p-6 lg:grid-cols-[1.1fr_0.9fr] lg:p-8">
                <div class="flex flex-col justify-center">
                    <span class="bg-brand-50 text-brand-500 dark:bg-brand-500/15 dark:text-brand-400 mb-4 inline-flex w-fit rounded-full px-3 py-1 text-xs font-medium tracking-[0.2em] uppercase">
                        Capstone Project Dashboard
                    </span>

                    <h1 class="text-title-sm mb-4 font-bold text-gray-900 dark:text-white">
                        AI-Based Interview Practice System
                    </h1>

                    <p class="text-theme-sm mb-6 max-w-2xl leading-7 text-gray-600 dark:text-gray-400">
                        A web-based interview simulation platform designed for students and job seekers. Users can answer
                        interview questions through text or voice, receive AI-based automated feedback for clarity,
                        relevance, grammar, and professionalism, and track performance across saved practice sessions.
                    </p>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('practice') }}"
                            class="bg-brand-500 text-theme-sm shadow-theme-sm hover:bg-brand-600 inline-flex items-center justify-center rounded-xl px-5 py-3 font-medium text-white transition">
                            Start Practice
                        </a>

                        <a href="{{ route('progress') }}"
                            class="text-theme-sm inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-3 font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            View Progress
                        </a>

                        <a href="{{ route('profile') }}"
                            class="text-theme-sm inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-3 font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            Profile
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
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
                            class="shadow-theme-sm hover:shadow-theme-md rounded-2xl border border-gray-200 bg-white p-5 transition hover:-translate-y-1 dark:border-gray-800 dark:bg-gray-900">
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
                    class="shadow-theme-sm rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
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
                            <div class="rounded-xl border border-gray-200 p-4 transition hover:border-brand-300 dark:border-gray-800 dark:hover:border-brand-700">
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
                    class="shadow-theme-sm rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
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
                    class="shadow-theme-sm rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-5">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
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

                <section class="shadow-theme-sm rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-5">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                            Current Focus
                        </h2>
                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">
                            Suggested priorities before the next session
                        </p>
                    </div>

                    <div class="space-y-3">
                        @foreach ($focusItems as $item)
                            <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                                <div class="flex items-start gap-3">
                                    <span class="bg-success-500 mt-1 inline-flex size-2.5 rounded-full"></span>
                                    <p class="text-sm leading-7 text-gray-700 dark:text-gray-300">
                                        {{ $item }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="shadow-theme-sm rounded-2xl border border-gray-200 bg-gray-900 p-6 text-white dark:border-gray-800">
                    <div class="mb-5">
                        <p class="text-brand-300 text-xs font-semibold tracking-[0.2em] uppercase">
                            Weekly Signals
                        </p>
                        <h2 class="mt-2 text-xl font-semibold">
                            Keep progress visible
                        </h2>
                    </div>

                    <div class="space-y-3">
                        @foreach ($weeklySignals as $signal)
                            <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                                <p class="text-xs tracking-[0.2em] text-gray-400 uppercase">{{ $signal['label'] }}</p>
                                <p class="mt-2 text-base font-semibold text-white">{{ $signal['value'] }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('home') }}"
                            class="inline-flex items-center justify-center rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-medium transition hover:bg-white/10">
                            Back Home
                        </a>
                        <a href="{{ route('signup') }}"
                            class="bg-brand-500 hover:bg-brand-600 inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-medium text-white transition">
                            Register User
                        </a>
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection
