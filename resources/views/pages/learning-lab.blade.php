@extends('layouts.app')

@section('content')
    @php
        $toneClasses = [
            'brand' => 'border-brand-100 bg-brand-50/70 dark:border-brand-500/20 dark:bg-brand-500/10',
            'blue' => 'border-blue-light-100 bg-blue-light-50/80 dark:border-blue-light-500/20 dark:bg-blue-light-500/10',
            'success' => 'border-success-100 bg-success-50/80 dark:border-success-500/20 dark:bg-success-500/10',
            'warning' => 'border-warning-100 bg-warning-50/80 dark:border-warning-500/20 dark:bg-warning-500/10',
            'neutral' => 'border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-900/70',
        ];

        $actionClasses = [
            'primary' => 'bg-brand-500 text-white hover:bg-brand-600',
            'secondary' => 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.03]',
            'ghost' => 'border border-brand-300 bg-transparent text-brand-600 hover:bg-brand-50 dark:border-brand-500/40 dark:text-brand-300 dark:hover:bg-brand-500/10',
        ];
    @endphp

    <x-common.page-breadcrumb :pageTitle="$featurePage['title']" />

    <section class="mb-6 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div @class([
            'border-b border-gray-200 p-6 dark:border-gray-800',
            $featurePage['gradient'] ?? 'bg-gradient-to-r from-brand-500/10 via-white to-blue-light-500/10 dark:from-brand-500/5 dark:via-gray-900 dark:to-blue-light-500/5',
        ])>
            <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
                <div class="max-w-3xl">
                    <span class="mb-3 inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                        {{ $featurePage['eyebrow'] }}
                    </span>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white/90 md:text-3xl">
                        {{ $featurePage['title'] }}
                    </h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-gray-600 dark:text-gray-400">
                        {{ $featurePage['description'] }}
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($featurePage['summaryCards'] as $card)
                        <div class="rounded-2xl border border-gray-200 bg-white/80 px-4 py-3 backdrop-blur dark:border-gray-800 dark:bg-gray-900/80">
                            <p class="text-xs uppercase tracking-wide text-gray-500">{{ $card['label'] }}</p>
                            <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">{{ $card['value'] }}</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $card['detail'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-12">
        <section class="space-y-6 xl:col-span-8">
            <article class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-5">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Learning Modules</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Each module now launches a real Practice flow or adjacent tool, so Learning Lab decisions carry straight into the interview workspace.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    @foreach ($featurePage['learningModules'] as $module)
                        <div @class([
                            'rounded-2xl border p-4',
                            $toneClasses[$module['tone'] ?? 'neutral'] ?? $toneClasses['neutral'],
                        ])>
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <h4 class="text-base font-semibold text-gray-900 dark:text-white/90">{{ $module['title'] }}</h4>
                                <span class="inline-flex items-center rounded-full bg-white/80 px-3 py-1 text-xs font-semibold text-gray-700 shadow-theme-xs dark:bg-gray-900/80 dark:text-gray-200">
                                    {{ $module['tag'] }}
                                </span>
                            </div>

                            <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-300">{{ $module['summary'] }}</p>
                            <p class="mt-3 text-xs leading-5 text-gray-500 dark:text-gray-400">{{ $module['meta'] }}</p>

                            <div class="mt-4 flex flex-wrap gap-3">
                                <a
                                    href="{{ $module['primaryActionHref'] }}"
                                    class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                                    {{ $module['primaryActionLabel'] }}
                                </a>

                                <a
                                    href="{{ $module['secondaryActionHref'] }}"
                                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                                    {{ $module['secondaryActionLabel'] }}
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-5">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Learning Activities</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        These activities are connected to Practice through launch links that carry category and drill context into the workspace.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    @foreach ($featurePage['learningActivities'] as $activity)
                        <div @class([
                            'rounded-2xl border p-4',
                            $toneClasses[$activity['tone'] ?? 'neutral'] ?? $toneClasses['neutral'],
                        ])>
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <h4 class="text-base font-semibold text-gray-900 dark:text-white/90">{{ $activity['title'] }}</h4>
                                <span class="inline-flex items-center rounded-full bg-white/80 px-3 py-1 text-xs font-semibold text-gray-700 shadow-theme-xs dark:bg-gray-900/80 dark:text-gray-200">
                                    {{ $activity['tag'] }}
                                </span>
                            </div>

                            <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-300">{{ $activity['summary'] }}</p>

                            <div class="mt-4">
                                <a
                                    href="{{ $activity['actionHref'] }}"
                                    class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                                    {{ $activity['actionLabel'] }}
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-5">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Practice Track Connections</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Launch any interview track from Learning Lab and continue straight into the Practice workspace with a connected drill context.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    @foreach ($featurePage['practiceTracks'] as $track)
                        <div @class([
                            'rounded-2xl border p-4',
                            $toneClasses[$track['tone'] ?? 'neutral'] ?? $toneClasses['neutral'],
                        ])>
                            @if (!empty($track['eyebrow']))
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">
                                    {{ $track['eyebrow'] }}
                                </p>
                            @endif

                            <div class="mt-2 flex flex-wrap items-start justify-between gap-3">
                                <h4 class="text-base font-semibold text-gray-900 dark:text-white/90">{{ $track['title'] }}</h4>
                                <span class="inline-flex items-center rounded-full bg-white/80 px-3 py-1 text-xs font-semibold text-gray-700 shadow-theme-xs dark:bg-gray-900/80 dark:text-gray-200">
                                    {{ $track['value'] }}
                                </span>
                            </div>

                            <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-300">{{ $track['body'] }}</p>

                            <ul class="mt-4 space-y-2">
                                @foreach ($track['list'] as $line)
                                    <li class="flex gap-3 text-sm leading-6 text-gray-700 dark:text-gray-300">
                                        <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-gray-900 dark:bg-white"></span>
                                        <span>{{ $line }}</span>
                                    </li>
                                @endforeach
                            </ul>

                            <p class="mt-4 text-xs leading-5 text-gray-500 dark:text-gray-400">{{ $track['meta'] }}</p>

                            <div class="mt-4">
                                <a
                                    href="{{ $track['actionHref'] }}"
                                    class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                                    {{ $track['actionLabel'] }}
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>
        </section>

        <section class="space-y-6 xl:col-span-4">
            @if (!empty($featurePage['actions']))
                <article class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="mb-5">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Quick Actions</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ $featurePage['actionsDescription'] }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-3">
                        @foreach ($featurePage['actions'] as $action)
                            <a
                                href="{{ $action['href'] }}"
                                @class([
                                    'inline-flex items-center justify-center rounded-lg px-4 py-3 text-sm font-medium transition',
                                    $actionClasses[$action['style'] ?? 'secondary'] ?? $actionClasses['secondary'],
                                ])>
                                {{ $action['label'] }}
                            </a>
                        @endforeach
                    </div>
                </article>
            @endif

            @foreach ($featurePage['secondarySections'] as $section)
                <article class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="mb-5">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">{{ $section['title'] }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $section['description'] }}</p>
                    </div>

                    <div class="space-y-3">
                        @foreach ($section['items'] as $item)
                            <div @class([
                                'rounded-xl border p-4',
                                $toneClasses[$item['tone'] ?? 'neutral'] ?? $toneClasses['neutral'],
                            ])>
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white/90">{{ $item['title'] }}</h4>
                                    <span class="inline-flex items-center rounded-full bg-white/80 px-3 py-1 text-xs font-semibold text-gray-700 shadow-theme-xs dark:bg-gray-900/80 dark:text-gray-200">
                                        {{ $item['value'] }}
                                    </span>
                                </div>

                                <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-300">{{ $item['body'] }}</p>

                                @if (!empty($item['list']))
                                    <ul class="mt-3 space-y-2">
                                        @foreach ($item['list'] as $line)
                                            <li class="flex gap-3 text-sm leading-6 text-gray-700 dark:text-gray-300">
                                                <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-gray-900 dark:bg-white"></span>
                                                <span>{{ $line }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </article>
            @endforeach
        </section>
    </div>
@endsection
