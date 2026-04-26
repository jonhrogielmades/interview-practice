@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Admin Dashboard" />

    <div class="space-y-6">
        @if (session('status'))
            <div class="rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-300">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/20 dark:bg-error-500/10 dark:text-error-300">
                {{ session('error') }}
            </div>
        @endif

        <section class="overflow-hidden rounded-[28px] border border-gray-200/50 bg-white/80 shadow-theme-lg backdrop-blur-2xl dark:border-white/5 dark:bg-gray-900/80">
            <div class="grid gap-6 p-6 lg:grid-cols-[1.1fr_0.9fr] lg:p-8">
                <div class="flex flex-col justify-center">
                    <span class="mb-4 inline-flex w-fit rounded-full bg-warning-50 px-3 py-1 text-xs font-medium uppercase tracking-[0.2em] text-warning-700 dark:bg-warning-500/15 dark:text-warning-300">
                        Admin Control Center
                    </span>

                    <h1 class="bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-title-sm mb-4 font-bold text-transparent dark:from-white dark:to-gray-400">
                        Monitor the system and route admins to the manuscript-aligned control pages.
                    </h1>

                    <p class="mb-6 max-w-2xl text-sm leading-7 text-gray-600 dark:text-gray-400">
                        This dashboard is the admin overview only. Use the dedicated sidebar pages for user access,
                        API routing, question-bank planning, announcements, and monitoring records so system operations
                        stay separate from the user workspace.
                    </p>

                    <div class="flex flex-wrap gap-3">
                        <span class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                            Fixed admin email: {{ $primaryAdminEmail }}
                        </span>
                        <span class="inline-flex items-center rounded-full border border-brand-200 bg-brand-50 px-4 py-2 text-sm font-medium text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                            Admin area only
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach ($summaryCards as $card)
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/5">
                            <p class="mb-2 text-theme-xs text-gray-500 dark:text-gray-400">{{ $card['label'] }}</p>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $card['value'] }}</h3>
                            <p @class([
                                'mt-2 text-theme-xs font-medium',
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
                <section class="rounded-2xl border border-gray-200/50 bg-white/80 p-5 shadow-theme-sm backdrop-blur-xl dark:border-white/5 dark:bg-gray-900/80 lg:p-6">
                    <div class="mb-5">
                        <h2 class="bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-lg font-bold text-transparent dark:from-white dark:to-gray-400">Admin Workflows</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Use dedicated admin pages instead of mixing manuscript-level management controls into the user workspace.
                        </p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach ($quickLinks as $link)
                            <a href="{{ $link['href'] }}"
                                class="rounded-2xl border border-gray-200/50 bg-white/50 p-5 transition-all duration-300 hover:-translate-y-1.5 hover:bg-white/80 hover:shadow-theme-md dark:border-white/5 dark:bg-white/5 dark:hover:bg-white/10">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ $link['title'] }}</h3>
                                <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $link['body'] }}</p>
                                <span @class([
                                    'mt-4 inline-flex rounded-full px-3 py-1 text-xs font-medium',
                                    'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-300' => $link['tone'] === 'warning',
                                    'bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300' => $link['tone'] === 'brand',
                                ])>
                                    Open page
                                </span>
                            </a>
                        @endforeach
                    </div>
                </section>

                <section class="rounded-2xl border border-gray-200/50 bg-white/80 p-5 shadow-theme-sm backdrop-blur-xl dark:border-white/5 dark:bg-gray-900/80 lg:p-6">
                    <h2 class="bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-lg font-bold text-transparent dark:from-white dark:to-gray-400">Top Practice Categories</h2>
                    <div class="mt-4 space-y-3">
                        @forelse ($topCategories as $category)
                            <div class="rounded-2xl border border-gray-200 px-4 py-3 dark:border-gray-800">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $category['name'] }}</p>
                                    <span class="rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-500/15 dark:text-brand-300">
                                        {{ $category['total'] }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <p class="rounded-2xl border border-dashed border-gray-300 px-4 py-5 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                No saved sessions yet. Once users begin practice, category totals will appear here.
                            </p>
                        @endforelse
                    </div>
                </section>
            </div>

            <div class="col-span-12 space-y-6 xl:col-span-4">
                <section class="rounded-2xl border border-gray-200/50 bg-white/80 p-5 shadow-theme-sm backdrop-blur-xl dark:border-white/5 dark:bg-gray-900/80 lg:p-6">
                    <h2 class="bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-lg font-bold text-transparent dark:from-white dark:to-gray-400">System Signals</h2>
                    <div class="mt-4 space-y-3">
                        @foreach ($systemSignals as $signal)
                            <div class="flex items-center justify-between rounded-2xl border border-gray-200 px-4 py-3 dark:border-gray-800">
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $signal['label'] }}</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $signal['value'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="rounded-2xl border border-gray-200/50 bg-white/80 p-5 shadow-theme-sm backdrop-blur-xl dark:border-white/5 dark:bg-gray-900/80 lg:p-6">
                    <h2 class="bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-lg font-bold text-transparent dark:from-white dark:to-gray-400">Recent Signups</h2>
                    <div class="mt-4 space-y-4">
                        @foreach ($recentUsers as $user)
                            <div class="rounded-2xl border border-gray-200 px-4 py-3 dark:border-gray-800">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $user['name'] }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user['email'] }}</p>
                                    </div>
                                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                        {{ $user['role'] }}
                                    </span>
                                </div>
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ $user['joinedAt'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection
