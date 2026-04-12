@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="API Management" />

    <div class="space-y-6">
        <section class="overflow-hidden rounded-[28px] border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="grid gap-6 p-6 lg:grid-cols-[1.1fr_0.9fr] lg:p-8">
                <div class="flex flex-col justify-center">
                    <span class="mb-4 inline-flex w-fit rounded-full bg-brand-50 px-3 py-1 text-xs font-medium uppercase tracking-[0.2em] text-brand-700 dark:bg-brand-500/15 dark:text-brand-300">
                        System Integrations
                    </span>

                    <h1 class="mb-4 text-title-sm font-bold text-gray-900 dark:text-white">
                        Manage AI provider visibility and protected API integrations.
                    </h1>

                    <p class="mb-6 max-w-2xl text-sm leading-7 text-gray-600 dark:text-gray-400">
                        This page is admin-only. It centralizes provider configuration, environment-key coverage,
                        routing order, and live health checks so those concerns stay outside the normal user workspace.
                    </p>

                    <div class="flex flex-wrap gap-3">
                        <span class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                            Protected admin page
                        </span>
                        <span class="inline-flex items-center rounded-full border border-warning-200 bg-warning-50 px-4 py-2 text-sm font-medium text-warning-700 dark:border-warning-500/20 dark:bg-warning-500/10 dark:text-warning-300">
                            Runtime use stays separate
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
                <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 lg:p-6">
                    <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Provider Directory</h2>
                            <p id="adminApiHealthNote" class="text-sm text-gray-500 dark:text-gray-400">
                                Use the live check to verify which configured remote APIs are responding right now.
                            </p>
                        </div>

                        <button
                            id="adminApiHealthButton"
                            type="button"
                            class="inline-flex w-full items-center justify-center rounded-full bg-gray-900 px-4 py-2 text-sm font-medium text-white shadow-theme-xs transition hover:bg-gray-800 sm:w-auto dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                            Run Live API Check
                        </button>
                    </div>

                    <div id="adminApiStatus" class="hidden rounded-xl border px-4 py-3 text-sm"></div>

                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        @foreach ($providers as $provider)
                            <article data-admin-provider-card="{{ $provider['id'] }}" class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/5">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $provider['typeLabel'] }}</p>
                                        <h3 class="mt-1 text-base font-semibold text-gray-900 dark:text-white">{{ $provider['label'] }}</h3>
                                    </div>
                                    <span data-admin-provider-indicator @class([
                                        'inline-flex h-3 w-3 rounded-full',
                                        'bg-brand-500' => $provider['stateTone'] === 'brand',
                                        'bg-success-500' => $provider['stateTone'] === 'success',
                                        'bg-warning-500' => $provider['stateTone'] === 'warning',
                                        'bg-blue-light-500' => $provider['stateTone'] === 'blue',
                                    ])></span>
                                </div>

                                <p data-admin-provider-state class="mt-2 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $provider['stateLabel'] }}</p>
                                <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $provider['description'] }}</p>

                                <div class="mt-4 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                                    <p><span class="font-medium text-gray-900 dark:text-white">Model:</span> {{ $provider['model'] }}</p>
                                    <p><span class="font-medium text-gray-900 dark:text-white">Key:</span> {{ $provider['envKey'] }}</p>
                                </div>

                                <p data-admin-provider-note class="mt-4 rounded-xl bg-white px-3 py-3 text-sm leading-6 text-gray-600 dark:bg-gray-900/70 dark:text-gray-300">
                                    {{ $provider['note'] }}
                                </p>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 lg:p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Protected System Areas</h2>
                    <div class="mt-4 grid gap-4 md:grid-cols-3">
                        @foreach ($systemAreas as $area)
                            <article class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-white/5">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ $area['title'] }}</h3>
                                <p class="mt-2 inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ $area['value'] }}</p>
                                <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $area['body'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </section>
            </div>

            <div class="col-span-12 space-y-6 xl:col-span-4">
                <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 lg:p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Environment Keys</h2>
                    <div class="mt-4 space-y-3">
                        @foreach ($keyStatuses as $key)
                            <div class="rounded-2xl border border-gray-200 px-4 py-3 dark:border-gray-800">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $key['name'] }}</p>
                                    <span @class([
                                        'rounded-full px-2.5 py-1 text-xs font-medium',
                                        'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-300' => $key['configured'],
                                        'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-300' => ! $key['configured'],
                                    ])>
                                        {{ $key['configured'] ? 'Configured' : 'Missing' }}
                                    </span>
                                </div>
                                <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $key['note'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const button = document.getElementById('adminApiHealthButton');
            const note = document.getElementById('adminApiHealthNote');
            const status = document.getElementById('adminApiStatus');
            const providerIds = @json($liveCheckProviderIds);
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const stateMeta = {
                configured: { label: 'Configured', indicator: 'bg-brand-500' },
                needs_key: { label: 'Needs key', indicator: 'bg-warning-500' },
                working: { label: 'Live check passed', indicator: 'bg-success-500' },
                unavailable: { label: 'Unavailable right now', indicator: 'bg-error-500' },
            };

            function setStatus(type, message) {
                status.className = 'rounded-xl border px-4 py-3 text-sm';
                if (type === 'success') {
                    status.classList.add('border-success-200', 'bg-success-50', 'text-success-700', 'dark:border-success-500/20', 'dark:bg-success-500/10', 'dark:text-success-300');
                } else if (type === 'warning') {
                    status.classList.add('border-warning-200', 'bg-warning-50', 'text-warning-700', 'dark:border-warning-500/20', 'dark:bg-warning-500/10', 'dark:text-warning-300');
                } else {
                    status.classList.add('border-gray-200', 'bg-gray-50', 'text-gray-700', 'dark:border-gray-700', 'dark:bg-gray-900/70', 'dark:text-gray-300');
                }
                status.textContent = message;
                status.classList.remove('hidden');
            }

            function updateCard(item) {
                const card = document.querySelector(`[data-admin-provider-card="${item.id}"]`);
                if (!card) return;
                const stateEl = card.querySelector('[data-admin-provider-state]');
                const noteEl = card.querySelector('[data-admin-provider-note]');
                const indicatorEl = card.querySelector('[data-admin-provider-indicator]');
                const nextState = stateMeta[item.state] || stateMeta.configured;
                if (stateEl) stateEl.textContent = nextState.label;
                if (noteEl) noteEl.textContent = item.provider ? `${item.message} Provider reply: ${item.provider}.` : item.message;
                if (indicatorEl) indicatorEl.className = `inline-flex h-3 w-3 rounded-full ${nextState.indicator}`;
            }

            button?.addEventListener('click', async () => {
                if (!providerIds.length) return;
                button.disabled = true;
                button.classList.add('cursor-not-allowed', 'opacity-60');
                button.textContent = 'Checking APIs...';
                note.textContent = 'Running a quick backend check against each configured provider.';

                try {
                    const response = await fetch(@json(route('admin.apis.providers.status')), {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({ providers: providerIds }),
                    });
                    const payload = await response.json();
                    if (!response.ok) throw new Error(payload.message || 'The live provider check could not be completed.');

                    const statuses = Array.isArray(payload.providers) ? payload.providers : [];
                    const configuredCount = statuses.filter((item) => Boolean(item.configured)).length;
                    const workingCount = statuses.filter((item) => String(item.state || '') === 'working').length;
                    statuses.forEach(updateCard);

                    note.textContent = configuredCount === 0
                        ? 'No provider API keys are configured yet.'
                        : `${workingCount} of ${configuredCount} configured APIs responded during the latest live check.`;

                    if (configuredCount === 0) {
                        setStatus('info', 'No provider API keys are configured yet. Add the matching keys in the environment file first.');
                    } else if (workingCount > 0) {
                        setStatus('success', `${workingCount} configured API${workingCount === 1 ? '' : 's'} passed the live check.`);
                    } else {
                        setStatus('warning', 'The configured APIs did not return a usable live response right now.');
                    }
                } catch (error) {
                    note.textContent = 'The live provider check could not be completed right now.';
                    setStatus('warning', error instanceof Error ? error.message : 'The live provider check could not be completed.');
                } finally {
                    button.disabled = false;
                    button.classList.remove('cursor-not-allowed', 'opacity-60');
                    button.textContent = 'Run Live API Check';
                }
            });
        });
    </script>
@endpush
