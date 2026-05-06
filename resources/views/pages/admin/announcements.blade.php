@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Announcements" />

    <div class="space-y-6">
        <section class="overflow-hidden rounded-[28px] border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="grid gap-6 p-6 lg:grid-cols-[1.1fr_0.9fr] lg:p-8">
                <div class="flex flex-col justify-center">
                    <span class="mb-4 inline-flex w-fit rounded-full bg-warning-50 px-3 py-1 text-xs font-medium uppercase tracking-[0.2em] text-warning-700 dark:bg-warning-500/15 dark:text-warning-300">
                        Announcements
                    </span>
                    <h1 class="mb-4 text-title-sm font-bold text-gray-900 dark:text-white">Review announcement templates separately from question-bank operations.</h1>
                    <p class="max-w-2xl text-sm leading-7 text-gray-600 dark:text-gray-400">
                        Question Bank & Announcements are now split. Use this page for reminder, feedback-ready,
                        and category-focus message planning.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('admin.question-bank') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">Open Question Bank</a>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach ($summaryCards as $card)
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/5">
                            <p class="mb-2 text-theme-xs text-gray-500 dark:text-gray-400">{{ $card['label'] }}</p>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $card['value'] }}</h3>
                            <p @class([
                                'mt-2 text-theme-xs font-medium',
                                'text-brand-500' => $card['tone'] === 'brand',
                                'text-blue-light-600' => $card['tone'] === 'blue',
                                'text-warning-600' => $card['tone'] === 'warning',
                                'text-success-600' => $card['tone'] === 'success',
                            ])>{{ $card['detail'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <div class="grid grid-cols-12 gap-4 md:gap-6">
            <section class="col-span-12 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 lg:p-6 xl:col-span-8">
                <div class="mb-5">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Announcement Templates</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Default message directions that fit reminder and feedback-notice workflows.</p>
                </div>

                <div class="grid gap-4 lg:grid-cols-3">
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

            <section class="col-span-12 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 lg:p-6 xl:col-span-4">
                <div class="mb-5">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Admin Coverage</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Announcement responsibilities reflected in the current prototype.</p>
                </div>

                <div class="space-y-3">
                    @forelse ($adminAreas as $area)
                        <article class="rounded-2xl border border-gray-200 px-4 py-4 dark:border-gray-800">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $area['title'] }}</h3>
                            <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $area['body'] }}</p>
                        </article>
                    @empty
                        <article class="rounded-2xl border border-gray-200 px-4 py-4 dark:border-gray-800">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Announcements and Notifications</h3>
                            <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">Prepare reminders, feedback notices, and practice nudges that keep users engaged.</p>
                        </article>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
@endsection
