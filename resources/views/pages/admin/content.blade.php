@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Question Bank & Announcements" />

    <div class="space-y-6">
        <section class="overflow-hidden rounded-[28px] border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="grid gap-6 p-6 lg:grid-cols-[1.1fr_0.9fr] lg:p-8">
                <div class="flex flex-col justify-center">
                    <span class="mb-4 inline-flex w-fit rounded-full bg-brand-50 px-3 py-1 text-xs font-medium uppercase tracking-[0.2em] text-brand-700 dark:bg-brand-500/15 dark:text-brand-300">
                        Admin Content Studio
                    </span>

                    <h1 class="mb-4 text-title-sm font-bold text-gray-900 dark:text-white">
                        Review the manuscript-aligned question banks and announcement templates.
                    </h1>

                    <p class="max-w-2xl text-sm leading-7 text-gray-600 dark:text-gray-400">
                        This page brings the category catalog, starter questions, quick prompts, and announcement
                        planning into one admin-facing overview so the capstone scope is easier to manage.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach ($summaryCards as $card)
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/5">
                            <p class="mb-2 text-theme-xs text-gray-500 dark:text-gray-400">{{ $card['label'] }}</p>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $card['value'] }}</h3>
                            <p class="mt-2 text-theme-xs font-medium text-gray-600 dark:text-gray-300">{{ $card['detail'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <div class="grid grid-cols-12 gap-4 md:gap-6">
            <div class="col-span-12 space-y-6 xl:col-span-8">
                <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 lg:p-6">
                    <div class="mb-5">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Category Question Banks</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            These practice categories and starter prompts drive the public landing page and the workspace.
                        </p>
                    </div>

                    <div class="grid gap-4 xl:grid-cols-2">
                        @foreach ($questionBanks as $bank)
                            <article class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/5">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-600 dark:text-brand-300">{{ strtoupper($bank['id']) }}</p>
                                        <h3 class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $bank['name'] }}</h3>
                                    </div>
                                    <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-gray-700 shadow-theme-xs dark:bg-gray-800 dark:text-gray-300">
                                        {{ $bank['questionCount'] }} prompts
                                    </span>
                                </div>

                                <p class="mt-4 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $bank['description'] }}</p>

                                <div class="mt-5">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Starter questions</p>
                                    <div class="mt-3 space-y-3">
                                        @foreach ($bank['questions'] as $question)
                                            <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm leading-6 text-gray-700 dark:border-gray-800 dark:bg-gray-950/40 dark:text-gray-300">
                                                {{ $question }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="mt-5">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Quick prompts</p>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @foreach ($bank['quickPrompts'] as $prompt)
                                            <span class="rounded-full border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                                {{ $prompt }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            </div>

            <div class="col-span-12 space-y-6 xl:col-span-4">
                <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 lg:p-6">
                    <div class="mb-5">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Announcement Templates</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Default message directions that fit the manuscript's reminder and feedback-notice scope.
                        </p>
                    </div>

                    <div class="space-y-4">
                        @foreach ($announcements as $announcement)
                            <article class="rounded-2xl border border-gray-200 px-4 py-4 dark:border-gray-800">
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $announcement['title'] }}</h3>
                                    <span class="rounded-full bg-brand-50 px-2.5 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/15 dark:text-brand-300">
                                        {{ $announcement['audience'] }}
                                    </span>
                                </div>
                                <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $announcement['body'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 lg:p-6">
                    <div class="mb-5">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Admin Coverage</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Manuscript-backed admin responsibilities reflected in the current prototype.
                        </p>
                    </div>

                    <div class="space-y-3">
                        @foreach ($adminAreas as $area)
                            <article class="rounded-2xl border border-gray-200 px-4 py-4 dark:border-gray-800">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $area['title'] }}</h3>
                                <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $area['body'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection
