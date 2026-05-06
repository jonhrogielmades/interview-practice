@props(['page'])

@php
    $toneClasses = [
        'brand' => 'border-brand-100 bg-brand-50/70 dark:border-brand-500/20 dark:bg-brand-500/10',
        'blue' => 'border-blue-light-100 bg-blue-light-50/80 dark:border-blue-light-500/20 dark:bg-blue-light-500/10',
        'success' => 'border-success-100 bg-success-50/80 dark:border-success-500/20 dark:bg-success-500/10',
        'warning' => 'border-warning-100 bg-warning-50/80 dark:border-warning-500/20 dark:bg-warning-500/10',
        'neutral' => 'border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-900/70',
    ];

    $actionClasses = [
        'primary' => 'bg-brand-500 text-white shadow-theme-xs hover:bg-brand-600',
        'secondary' => 'border border-gray-300 bg-white/80 text-gray-700 shadow-theme-xs hover:bg-white dark:border-gray-700 dark:bg-gray-900/80 dark:text-gray-300 dark:hover:bg-white/[0.05]',
        'ghost' => 'border border-brand-300 bg-brand-50/60 text-brand-700 hover:bg-brand-100 dark:border-brand-500/40 dark:bg-brand-500/10 dark:text-brand-300 dark:hover:bg-brand-500/15',
    ];
@endphp

<section class="app-surface-strong mb-6 overflow-hidden rounded-lg border">
    <div @class([
        'border-b p-6 dark:border-white/10',
        $page['gradient'] ?? 'bg-[linear-gradient(135deg,rgba(20,120,100,0.1),rgba(255,255,255,0.72),rgba(249,115,22,0.09))] dark:bg-[linear-gradient(135deg,rgba(93,187,158,0.1),rgba(15,23,42,0.8),rgba(249,115,22,0.06))]',
    ])>
        <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-3xl">
                <span class="mb-3 inline-flex items-center rounded-full border border-brand-200 bg-brand-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                    {{ $page['eyebrow'] ?? 'Feature Overview' }}
                </span>
                <h1 class="text-2xl font-semibold text-gray-950 dark:text-white md:text-3xl">
                    {{ $page['title'] ?? 'Feature Page' }}
                </h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-gray-600 dark:text-gray-400">
                    {{ $page['description'] ?? '' }}
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($page['summaryCards'] ?? [] as $card)
                    <div class="rounded-lg border border-white/70 bg-white/80 px-4 py-3 shadow-theme-xs backdrop-blur dark:border-white/10 dark:bg-gray-900/80">
                        <p class="text-xs uppercase tracking-wide text-gray-500">{{ $card['label'] ?? 'Summary' }}</p>
                        <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">{{ $card['value'] ?? 'N/A' }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $card['detail'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

<div class="grid gap-6 xl:grid-cols-12">
    <section class="space-y-6 xl:col-span-8">
        @foreach ($page['primarySections'] ?? [] as $section)
            <article class="app-surface rounded-lg border p-5">
                <div class="mb-5">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">{{ $section['title'] ?? 'Section' }}</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $section['description'] ?? '' }}</p>
                </div>

                <div @class([
                    'grid gap-4',
                    $section['columns'] ?? 'md:grid-cols-2',
                ])>
                    @foreach ($section['items'] ?? [] as $item)
                        <div @class([
                            'rounded-lg border p-4 shadow-theme-xs',
                            $toneClasses[$item['tone'] ?? 'neutral'] ?? $toneClasses['neutral'],
                        ])>
                            @if (!empty($item['eyebrow']))
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">
                                    {{ $item['eyebrow'] }}
                                </p>
                            @endif

                            <div class="mt-2 flex flex-wrap items-start justify-between gap-3">
                                <h4 class="text-base font-semibold text-gray-900 dark:text-white/90">{{ $item['title'] ?? 'Item' }}</h4>
                                @if (!empty($item['value']))
                                    <span class="inline-flex items-center rounded-full bg-white/80 px-3 py-1 text-xs font-semibold text-gray-700 shadow-theme-xs dark:bg-gray-900/80 dark:text-gray-200">
                                        {{ $item['value'] }}
                                    </span>
                                @endif
                            </div>

                            @if (!empty($item['body']))
                                <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-300">{{ $item['body'] }}</p>
                            @endif

                            @if (!empty($item['list']))
                                <ul class="mt-4 space-y-2">
                                    @foreach ($item['list'] as $line)
                                        <li class="flex gap-3 text-sm leading-6 text-gray-700 dark:text-gray-300">
                                            <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-gray-900 dark:bg-white"></span>
                                            <span>{{ $line }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            @if (!empty($item['meta']))
                                <p class="mt-4 text-xs leading-5 text-gray-500 dark:text-gray-400">{{ $item['meta'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </article>
        @endforeach
    </section>

    <section class="space-y-6 xl:col-span-4">
        @if (!empty($page['actions']))
            <article class="app-surface rounded-lg border p-5">
                <div class="mb-5">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Quick Actions</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ $page['actionsDescription'] ?? 'Open the related parts of the app from here.' }}
                    </p>
                </div>

                <div class="flex flex-col gap-3">
                    @foreach ($page['actions'] as $action)
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

        @foreach ($page['secondarySections'] ?? [] as $section)
            <article class="app-surface rounded-lg border p-5">
                <div class="mb-5">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">{{ $section['title'] ?? 'Details' }}</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $section['description'] ?? '' }}</p>
                </div>

                <div class="space-y-3">
                    @foreach ($section['items'] ?? [] as $item)
                        <div @class([
                            'rounded-lg border p-4 shadow-theme-xs',
                            $toneClasses[$item['tone'] ?? 'neutral'] ?? $toneClasses['neutral'],
                        ])>
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white/90">{{ $item['title'] ?? 'Item' }}</h4>
                                @if (!empty($item['value']))
                                    <span class="inline-flex items-center rounded-full bg-white/80 px-3 py-1 text-xs font-semibold text-gray-700 shadow-theme-xs dark:bg-gray-900/80 dark:text-gray-200">
                                        {{ $item['value'] }}
                                    </span>
                                @endif
                            </div>

                            @if (!empty($item['body']))
                                <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-300">{{ $item['body'] }}</p>
                            @endif

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
