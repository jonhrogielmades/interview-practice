@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Question Bank" />

    <div
        class="space-y-6"
        x-data="{
            questionModal: @js(old('question_modal')),
            questionSearch: @js($questionSearchTerm),
            questionListSize: @js((string) $selectedQuestionListSize),
            normalize(value) {
                return String(value || '').toLowerCase().trim();
            },
            applyLiveQuestionFilter() {
                const term = this.normalize(this.questionSearch);
                const limit = Number(this.questionListSize) || 5;

                document.querySelectorAll('[data-question-bank]').forEach((bank) => {
                    const rows = Array.from(bank.querySelectorAll('[data-question-row]'));
                    let matchingCount = 0;
                    let visibleCount = 0;

                    rows.forEach((row) => {
                        const matches = term === '' || this.normalize(row.dataset.search).includes(term);

                        if (matches) {
                            matchingCount += 1;
                        }

                        const shouldShow = matches && visibleCount < limit;

                        if (shouldShow) {
                            visibleCount += 1;
                        }

                        row.classList.toggle('hidden', !shouldShow);
                    });

                    const label = bank.querySelector('[data-question-count-label]');

                    if (label) {
                        label.textContent = `Showing ${visibleCount} of ${matchingCount} ${term !== '' ? 'matching ' : ''}${matchingCount === 1 ? 'question' : 'questions'}`;
                    }

                    const emptyRow = bank.querySelector('[data-question-empty-row]');
                    const emptyText = bank.querySelector('[data-question-empty-text]');

                    if (emptyRow) {
                        emptyRow.classList.toggle('hidden', visibleCount > 0);
                    }

                    if (emptyText) {
                        emptyText.textContent = term !== '' ? 'No questions match your search.' : 'No managed questions in this category yet.';
                    }
                });
            }
        }"
        x-effect="
            document.body.classList.toggle('overflow-hidden', questionModal !== null);
            document.documentElement.classList.toggle('overflow-hidden', questionModal !== null);
            questionSearch;
            questionListSize;
            applyLiveQuestionFilter();
        "
        @keydown.escape.window="questionModal = null"
    >
        @if (session('status'))
            <div class="rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-300">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/20 dark:bg-error-500/10 dark:text-error-300">
                <p class="font-medium">Please review the question form and try again.</p>
                <ul class="mt-2 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="overflow-hidden rounded-[28px] border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="grid gap-6 p-6 lg:grid-cols-[1.1fr_0.9fr] lg:p-8">
                <div class="flex flex-col justify-center">
                    <span class="mb-4 inline-flex w-fit rounded-full bg-brand-50 px-3 py-1 text-xs font-medium uppercase tracking-[0.2em] text-brand-700 dark:bg-brand-500/15 dark:text-brand-300">
                        Question Bank
                    </span>
                    <h1 class="mb-4 text-title-sm font-bold text-gray-900 dark:text-white">Manage the interview prompts used by the local PH coach and AI provider sources.</h1>
                    <p class="max-w-2xl text-sm leading-7 text-gray-600 dark:text-gray-400">
                        Question Bank & Announcements are now separated into dedicated admin pages. Add, edit,
                        deactivate, or remove category questions here, then use Announcements for message templates.
                    </p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <button type="button" @click="questionModal = 'create'" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">Add question</button>
                        <a href="{{ route('admin.announcements') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">Announcement Templates</a>
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

        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 lg:p-6">
            <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $selectedCategoryName ? $selectedCategoryName.' Question Bank' : 'Category Question Banks' }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $selectedCategoryName ? 'Viewing the selected category from the admin sidebar dropdown.' : 'Active prompts are included in the shared catalog and local PH coach context.' }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    @if ($selectedCategoryId)
                        <a href="{{ route('admin.question-bank', array_filter(['search' => $questionSearchTerm ?: null, 'per_page' => $selectedQuestionListSize], fn ($value) => $value !== null && $value !== '')) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">All categories</a>
                    @endif
                    <button type="button" @click="questionModal = 'create'" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">New question</button>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.question-bank') }}" class="mb-5 rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                @if ($selectedCategoryId)
                    <input type="hidden" name="category" value="{{ $selectedCategoryId }}" />
                @endif

                <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_170px_auto] lg:items-end">
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Search questions</span>
                        <input type="search" name="search" x-model.debounce.100ms="questionSearch" value="{{ $questionSearchTerm }}" placeholder="Search by question, guidance, source, or order" class="h-11 w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-500 dark:focus:border-brand-800" />
                    </label>

                    <label class="space-y-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Show list</span>
                        <select name="per_page" x-model="questionListSize" class="h-11 w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:border-brand-800">
                            @foreach ($questionListSizeOptions as $listSize)
                                <option value="{{ $listSize }}" @selected($selectedQuestionListSize === $listSize)>{{ $listSize }}</option>
                            @endforeach
                        </select>
                    </label>

                    <div class="flex flex-wrap gap-2">
                        <button type="submit" class="inline-flex h-11 items-center justify-center rounded-xl bg-brand-500 px-5 text-sm font-medium text-white transition hover:bg-brand-600">Search</button>
                        <button type="button" x-show="questionSearch !== ''" x-cloak @click="questionSearch = ''" class="inline-flex h-11 items-center justify-center rounded-xl border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">Clear</button>
                    </div>
                </div>
            </form>

            <div class="grid gap-5">
                @foreach ($questionBanks as $bank)
                    <article data-question-bank class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-950/20">
                        <div class="flex flex-col gap-4 border-b border-gray-200 bg-gray-50 p-5 sm:flex-row sm:items-start sm:justify-between dark:border-gray-800 dark:bg-white/5">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-600 dark:text-brand-300">{{ strtoupper($bank['id']) }}</p>
                                <h3 class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $bank['name'] }}</h3>
                                <p class="mt-3 max-w-3xl text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $bank['description'] }}</p>
                            </div>
                            <div class="flex flex-col items-start gap-2 sm:items-end">
                                <span class="w-fit rounded-full bg-white px-3 py-1 text-xs font-medium text-gray-700 shadow-theme-xs dark:bg-gray-800 dark:text-gray-300">
                                    {{ $bank['questionCount'] }} active
                                </span>
                                <span data-question-count-label class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                    Showing {{ $bank['visibleQuestionCount'] }} of {{ $bank['matchingQuestionCount'] }} {{ $questionSearchTerm !== '' ? 'matching ' : '' }}{{ $bank['matchingQuestionCount'] === 1 ? 'question' : 'questions' }}
                                </span>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[1040px] divide-y divide-gray-200 text-left dark:divide-gray-800">
                                <thead class="bg-white dark:bg-gray-900">
                                    <tr>
                                        <th scope="col" class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Source</th>
                                        <th scope="col" class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Status</th>
                                        <th scope="col" class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Question</th>
                                        <th scope="col" class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Order</th>
                                        <th scope="col" class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Updated</th>
                                        <th scope="col" class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-gray-950/20">
                                    @forelse ($bank['questions'] as $question)
                                        <tr data-question-row data-search="{{ $question['searchText'] }}" @class([
                                            'align-top transition hover:bg-gray-50 dark:hover:bg-white/[0.03]',
                                            'hidden' => ! $question['visibleByDefault'],
                                        ])>
                                            <td class="w-[160px] px-5 py-4">
                                                <span class="inline-flex rounded-full bg-brand-50 px-2.5 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/15 dark:text-brand-300">{{ $question['providerLabel'] }}</span>
                                            </td>
                                            <td class="w-[120px] px-5 py-4">
                                                <span @class([
                                                    'inline-flex rounded-full px-2.5 py-1 text-xs font-medium',
                                                    'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-300' => $question['isActive'],
                                                    'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' => ! $question['isActive'],
                                                ])>{{ $question['isActive'] ? 'Active' : 'Inactive' }}</span>
                                            </td>
                                            <td class="min-w-[430px] px-5 py-4">
                                                <p class="text-sm leading-6 text-gray-700 dark:text-gray-300">{{ $question['question'] }}</p>
                                                @if ($question['guidance'])
                                                    <p class="mt-2 text-xs leading-5 text-gray-500 dark:text-gray-400">{{ $question['guidance'] }}</p>
                                                @endif
                                            </td>
                                            <td class="w-[90px] px-5 py-4 text-sm font-medium text-gray-700 dark:text-gray-300">{{ $question['sortOrder'] }}</td>
                                            <td class="w-[180px] px-5 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $question['updatedAt'] }}</td>
                                            <td class="w-[170px] px-5 py-4">
                                                <div class="flex justify-end gap-2">
                                                    <button type="button" @click="questionModal = 'edit-{{ $question['id'] }}'" class="inline-flex items-center justify-center rounded-lg border border-brand-200 bg-brand-50 px-3 py-2 text-xs font-medium text-brand-700 transition hover:bg-brand-100 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-300">Edit</button>
                                                    <button type="button" @click="questionModal = 'delete-{{ $question['id'] }}'" class="inline-flex items-center justify-center rounded-lg border border-error-200 bg-error-50 px-3 py-2 text-xs font-medium text-error-700 transition hover:bg-error-100 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-300">Delete</button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                    @endforelse
                                    <tr data-question-empty-row @class(['hidden' => $bank['visibleQuestionCount'] > 0])>
                                        <td colspan="6" data-question-empty-text class="px-5 py-8 text-sm text-gray-500 dark:text-gray-400">
                                            {{ $questionSearchTerm !== '' ? 'No questions match your search.' : 'No managed questions in this category yet.' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="border-t border-gray-200 bg-gray-50/70 p-5 dark:border-gray-800 dark:bg-white/[0.03]">
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

        <div x-show="questionModal === 'create'" x-cloak x-transition.opacity class="fixed bottom-0 left-0 right-0 top-0 z-[99999] flex h-[100dvh] items-center justify-center overflow-y-auto overscroll-contain p-4 sm:p-6" :style="{ left: window.innerWidth >= 1280 ? (($store.sidebar.isExpanded || $store.sidebar.isHovered) ? '290px' : '90px') : '0px' }" role="dialog" aria-modal="true" aria-labelledby="create-question-title">
            <div class="absolute inset-0 min-h-full bg-gray-950/55 backdrop-blur-[18px]" @click="questionModal = null"></div>
            <section @click.stop x-transition.scale.origin.center class="relative max-h-[calc(100vh-2rem)] w-full max-w-3xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xl dark:border-gray-800 dark:bg-gray-900">
                <button type="button" @click="questionModal = null" class="absolute right-4 top-4 z-10 inline-flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-xl leading-none text-gray-500 transition hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white" aria-label="Close create question form">&times;</button>
                <div class="custom-scrollbar max-h-[calc(100vh-2rem)] overflow-y-auto p-5 lg:p-6">
                    <div class="mb-5 pr-12">
                        <h2 id="create-question-title" class="text-lg font-semibold text-gray-900 dark:text-white">Add Question</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tag the source as Local PH coach or one of the AI providers.</p>
                    </div>

                    <form method="POST" action="{{ route('admin.question-bank.questions.store') }}" class="space-y-5">
                        @csrf
                        <input type="hidden" name="question_modal" value="create" />
                        @include('pages.admin.partials.question-bank-form', [
                            'question' => null,
                            'modalKey' => 'create',
                            'defaultCategoryId' => $selectedCategoryId,
                            'categoryOptions' => $categoryOptions,
                            'providerOptions' => $providerOptions,
                        ])
                    </form>
                </div>
            </section>
        </div>

        @foreach ($questionBanks as $bank)
            @foreach ($bank['questions'] as $question)
                <div x-show="questionModal === 'edit-{{ $question['id'] }}'" x-cloak x-transition.opacity class="fixed bottom-0 left-0 right-0 top-0 z-[99999] flex h-[100dvh] items-center justify-center overflow-y-auto overscroll-contain p-4 sm:p-6" :style="{ left: window.innerWidth >= 1280 ? (($store.sidebar.isExpanded || $store.sidebar.isHovered) ? '290px' : '90px') : '0px' }" role="dialog" aria-modal="true" aria-labelledby="edit-question-title-{{ $question['id'] }}">
                    <div class="absolute inset-0 min-h-full bg-gray-950/55 backdrop-blur-[18px]" @click="questionModal = null"></div>
                    <section @click.stop x-transition.scale.origin.center class="relative max-h-[calc(100vh-2rem)] w-full max-w-3xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xl dark:border-gray-800 dark:bg-gray-900">
                        <button type="button" @click="questionModal = null" class="absolute right-4 top-4 z-10 inline-flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-xl leading-none text-gray-500 transition hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white" aria-label="Close edit question form">&times;</button>
                        <div class="custom-scrollbar max-h-[calc(100vh-2rem)] overflow-y-auto p-5 lg:p-6">
                            <div class="mb-5 pr-12">
                                <h2 id="edit-question-title-{{ $question['id'] }}" class="text-lg font-semibold text-gray-900 dark:text-white">Edit Question</h2>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Question #{{ $question['id'] }} in {{ $bank['name'] }}</p>
                            </div>

                            <form method="POST" action="{{ route('admin.question-bank.questions.update', $question['id']) }}" class="space-y-5">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="question_modal" value="edit-{{ $question['id'] }}" />
                                @include('pages.admin.partials.question-bank-form', [
                                    'question' => $question,
                                    'modalKey' => 'edit-'.$question['id'],
                                    'categoryOptions' => $categoryOptions,
                                    'providerOptions' => $providerOptions,
                                ])
                            </form>
                        </div>
                    </section>
                </div>

                <div x-show="questionModal === 'delete-{{ $question['id'] }}'" x-cloak x-transition.opacity class="fixed bottom-0 left-0 right-0 top-0 z-[99999] flex h-[100dvh] items-center justify-center overflow-y-auto overscroll-contain p-4 sm:p-6" :style="{ left: window.innerWidth >= 1280 ? (($store.sidebar.isExpanded || $store.sidebar.isHovered) ? '290px' : '90px') : '0px' }" role="dialog" aria-modal="true" aria-labelledby="delete-question-title-{{ $question['id'] }}">
                    <div class="absolute inset-0 min-h-full bg-gray-950/55 backdrop-blur-[18px]" @click="questionModal = null"></div>
                    <section @click.stop x-transition.scale.origin.center class="relative w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xl dark:border-gray-800 dark:bg-gray-900 lg:p-6">
                        <h2 id="delete-question-title-{{ $question['id'] }}" class="text-lg font-semibold text-gray-900 dark:text-white">Delete Question</h2>
                        <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $question['question'] }}</p>
                        <form method="POST" action="{{ route('admin.question-bank.questions.destroy', $question['id']) }}" class="mt-5 flex flex-wrap items-center justify-end gap-3">
                            @csrf
                            @method('DELETE')
                            <button type="button" @click="questionModal = null" class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">Cancel</button>
                            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-error-500 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-error-600">Delete question</button>
                        </form>
                    </section>
                </div>
            @endforeach
        @endforeach
    </div>
@endsection
