@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Monitoring Records" />

    <div class="space-y-6">
        <section class="overflow-hidden rounded-[28px] border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="grid gap-6 p-6 lg:grid-cols-[1.1fr_0.9fr] lg:p-8">
                <div class="flex flex-col justify-center">
                    <span class="mb-4 inline-flex w-fit rounded-full bg-warning-50 px-3 py-1 text-xs font-medium uppercase tracking-[0.2em] text-warning-700 dark:bg-warning-500/15 dark:text-warning-300">
                        Admin Monitoring
                    </span>

                    <h1 class="mb-4 text-title-sm font-bold text-gray-900 dark:text-white">
                        Review saved practice records, notification totals, and report-oriented signals.
                    </h1>

                    <p class="max-w-2xl text-sm leading-7 text-gray-600 dark:text-gray-400">
                        The manuscript expects administrative monitoring and reporting support. This page surfaces the
                        saved session totals, recent practice records, and report notes already available in the prototype.
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
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Practice Records</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Latest session entries that an administrator can monitor without opening the user workspace.
                        </p>
                    </div>

                    <div class="space-y-4">
                        @forelse ($recentSessions as $session)
                            <article class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-white/5">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $session['category'] }}</h3>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $session['savedAt'] }}</p>
                                    </div>
                                    <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-gray-700 shadow-theme-xs dark:bg-gray-800 dark:text-gray-300">
                                        {{ $session['status'] }}
                                    </span>
                                </div>

                                <div class="mt-4 grid gap-3 sm:grid-cols-4">
                                    <div class="rounded-xl border border-gray-200 bg-white px-3 py-3 dark:border-gray-800 dark:bg-gray-950/40">
                                        <p class="text-xs uppercase tracking-wide text-gray-500">Runtime</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $session['averageScore'] }} / 10</p>
                                    </div>
                                    <div class="rounded-xl border border-gray-200 bg-white px-3 py-3 dark:border-gray-800 dark:bg-gray-950/40">
                                        <p class="text-xs uppercase tracking-wide text-gray-500">Capstone</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $session['capstoneOverall'] }} / 5</p>
                                    </div>
                                    <div class="rounded-xl border border-gray-200 bg-white px-3 py-3 dark:border-gray-800 dark:bg-gray-950/40">
                                        <p class="text-xs uppercase tracking-wide text-gray-500">Answered</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $session['answeredCount'] }}</p>
                                    </div>
                                    <div class="rounded-xl border border-gray-200 bg-white px-3 py-3 dark:border-gray-800 dark:bg-gray-950/40">
                                        <p class="text-xs uppercase tracking-wide text-gray-500">Questions</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $session['questionCount'] }}</p>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <p class="rounded-2xl border border-dashed border-gray-300 px-4 py-5 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                No saved sessions yet. Once users finish practice rounds, monitoring entries will appear here.
                            </p>
                        @endforelse
                    </div>
                </section>
            </div>

            <div class="col-span-12 space-y-6 xl:col-span-4">
                <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 lg:p-6">
                    <div class="mb-5">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">System Signals</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            High-level metrics for monitoring practice activity and generated records.
                        </p>
                    </div>

                    <div class="space-y-3">
                        @foreach ($signals as $signal)
                            <div class="flex items-center justify-between rounded-2xl border border-gray-200 px-4 py-3 dark:border-gray-800">
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $signal['label'] }}</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $signal['value'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 lg:p-6">
                    <div class="mb-5">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Report Notes</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Existing report and review surfaces that already support documentation and monitoring.
                        </p>
                    </div>

                    <div class="space-y-3">
                        @foreach ($reportNotes as $note)
                            <article class="rounded-2xl border border-gray-200 px-4 py-4 dark:border-gray-800">
                                <p class="text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $note }}</p>
                            </article>
                        @endforeach
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection
