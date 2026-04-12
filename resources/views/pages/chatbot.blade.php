@extends('layouts.app')

@php
    $chatUser = auth()->user();
@endphp

@section('content')
    <x-common.page-breadcrumb pageTitle="Interview Chatbot" />

    <section class="mb-6 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="border-b border-gray-200 bg-gradient-to-r from-brand-500/10 via-white to-blue-light-500/10 p-6 dark:border-gray-800 dark:from-brand-500/5 dark:via-gray-900 dark:to-blue-light-500/5">
            <div class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr] xl:items-end">
                <div class="max-w-3xl">
                    <span class="mb-3 inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                        Philippines-only interview guidance
                    </span>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white/90 md:text-3xl">
                        Interview Chatbot
                    </h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-gray-600 dark:text-gray-400">
                        Use a dedicated AI interview coach from the sidebar. This page is limited to interview topics in the
                        Philippines and works with the supported AI API options in this project: Gemini, Groq,
                        OpenRouter, Claude, Wisdom Gate, and Cohere.
                    </p>
                    <div class="mt-5 flex flex-wrap gap-3">
                        <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-medium text-gray-700 shadow-theme-xs dark:bg-gray-800 dark:text-gray-300">
                            Interview-only scope
                        </span>
                        <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-medium text-gray-700 shadow-theme-xs dark:bg-gray-800 dark:text-gray-300">
                            Philippine context only
                        </span>
                        <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-medium text-gray-700 shadow-theme-xs dark:bg-gray-800 dark:text-gray-300">
                            6 supported AI APIs
                        </span>
                    </div>
                </div>
                    <div class="rounded-2xl border border-gray-200 bg-white/80 px-4 py-4 backdrop-blur dark:border-gray-800 dark:bg-gray-900/80">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Fallback Behavior</p>
                        <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">PH coach backup</p>
                        <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">
                            If no API key is active, the page still falls back to the built-in Philippine interview coach.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-4 border-b border-gray-200 px-6 py-5 dark:border-gray-800 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-semibold text-gray-900 dark:text-white/90">Live provider status</p>
                <p id="chatbotProviderHealthNote" class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-400">
                    Run a quick backend check to confirm which configured APIs are responding right now.
                </p>
            </div>

            <button
                id="chatbotProviderHealthButton"
                type="button"
                class="inline-flex w-full items-center justify-center rounded-full bg-gray-900 px-4 py-2 text-sm font-medium text-white shadow-theme-xs transition hover:bg-gray-800 sm:w-auto dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                Run Live API Check
            </button>
        </div>

        <div class="grid gap-3 p-6 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
            @foreach ($chatbotProviders as $provider)
                @php
                    $isConfigured = (bool) ($provider['configured'] ?? false);
                @endphp

                <article
                    data-provider-card="{{ $provider['id'] }}"
                    class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-4 dark:border-gray-800 dark:bg-gray-900/70">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white/90">{{ $provider['label'] }}</p>
                            <p data-provider-state class="mt-1 text-xs uppercase tracking-wide text-gray-500">
                                {{ $isConfigured ? 'API key detected' : 'Needs API key' }}
                            </p>
                        </div>
                        <span
                            data-provider-indicator
                            class="inline-flex h-3 w-3 rounded-full {{ $isConfigured ? 'bg-brand-500' : 'bg-amber-400' }}"></span>
                    </div>
                    <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $provider['description'] }}</p>
                    <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                        Model: {{ $provider['model'] ?: 'Dynamic' }}
                    </p>
                    <p data-provider-note class="mt-3 text-xs leading-5 text-gray-500 dark:text-gray-400">
                        {{ $isConfigured ? 'Configured in .env. Run a live check to confirm a real provider response.' : 'Add the provider API key in .env to enable live checks and chatbot replies.' }}
                    </p>
                </article>
            @endforeach
        </div>
    </section>

    <div
        id="chatbotApp"
        data-user-name="{{ $chatUser?->name ?: 'You' }}"
        data-user-avatar="{{ $chatUser?->avatar_url ?: asset('images/user/user-01.jpg') }}"
        data-assistant-name="Interview Chatbot"
        class="space-y-6">
        <section class="grid gap-6 xl:grid-cols-12">
            <article class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] xl:col-span-8">
                <div class="flex flex-col gap-6 border-b border-gray-200 pb-5 dark:border-gray-800 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-2xl">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90">Coach Chat Workspace</h2>
                        <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">
                            Ask for mock interview questions, follow-up questions, sample answers, or help polishing a draft.
                            The assistant stays focused on interviews in the Philippines only.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            data-chat-mode="chat"
                            class="rounded-full border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                            Coach Chat
                        </button>
                        <button
                            type="button"
                            data-chat-mode="question_set"
                            class="rounded-full border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                            Question Builder
                        </button>
                        <button
                            type="button"
                            data-chat-mode="feedback_review"
                            class="rounded-full border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                            Answer Review
                        </button>
                    </div>
                </div>

                <div class="mt-5 grid gap-4 lg:grid-cols-[1fr_auto]">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Interview scope
                        </label>
                        <div id="chatbotCategoryChips" class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                data-category-id="all"
                                data-category-name="All Philippine Interviews"
                                data-category-description="General interview practice across the supported Philippine interview tracks in this project."
                                class="rounded-full border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                                All Philippine Interviews
                            </button>

                            @foreach ($chatbotCategories as $category)
                                <button
                                    type="button"
                                    data-category-id="{{ $category['id'] }}"
                                    data-category-name="{{ $category['name'] }}"
                                    data-category-description="{{ $category['description'] }}"
                                    class="rounded-full border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                                    {{ $category['name'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400" for="chatbotProviderSelect">
                                AI provider
                            </label>
                            <select
                                id="chatbotProviderSelect"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="auto" @selected($chatbotDefaultProviderId === 'auto')>
                                    Auto choose the best available API
                                </option>
                                @foreach ($chatbotProviders as $provider)
                                    <option
                                        value="{{ $provider['id'] }}"
                                        data-configured="{{ $provider['configured'] ? '1' : '0' }}"
                                        @disabled(! $provider['configured'])
                                        @selected($chatbotDefaultProviderId === $provider['id'] && $provider['configured'])>
                                        {{ $provider['label'] }}{{ $provider['configured'] ? '' : ' (Add API key)' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div id="chatbotQuestionCountWrap" class="hidden">
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400" for="chatbotQuestionCount">
                                Generated questions
                            </label>
                            <select
                                id="chatbotQuestionCount"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                @foreach ([3, 5, 10, 15, 20] as $count)
                                    <option value="{{ $count }}" @selected($count === 5)>{{ $count }} questions</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div id="chatbotReviewFields" class="mt-5 hidden space-y-4">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400" for="chatbotCurrentQuestionInput">
                            Current interview question
                        </label>
                        <input
                            id="chatbotCurrentQuestionInput"
                            type="text"
                            placeholder="Example: Tell me about a project you are proud of."
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400" for="chatbotAnswerDraftInput">
                            Answer draft to review
                        </label>
                        <textarea
                            id="chatbotAnswerDraftInput"
                            rows="5"
                            placeholder="Paste the answer you want the interview chatbot to review."
                            class="dark:bg-dark-900 w-full rounded-2xl border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"></textarea>
                    </div>
                </div>

                <div id="chatbotStatus" class="mt-5 hidden rounded-xl border px-4 py-3 text-sm"></div>

                <div id="chatbotConversation" class="custom-scrollbar mt-5 max-h-[420px] space-y-4 overflow-y-auto pr-1 sm:max-h-[560px]"></div>

                <form id="chatbotComposerForm" class="mt-5 space-y-4">
                    <div>
                        <label
                            id="chatbotComposerLabel"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400"
                            for="chatbotComposerInput">
                            Ask the interview coach
                        </label>
                        <textarea
                            id="chatbotComposerInput"
                            rows="4"
                            placeholder="Ask for interview coaching, sample questions, model answers, or follow-up questions."
                            class="dark:bg-dark-900 w-full rounded-2xl border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"></textarea>
                        <p
                            id="chatbotComposerHelp"
                            class="mt-2 text-xs leading-5 text-gray-500 dark:text-gray-400">
                            The assistant will refuse unrelated topics and stay inside Philippine interview coaching.
                        </p>
                    </div>

                    <div class="flex flex-col-reverse items-stretch gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <button
                            id="chatbotClearButton"
                            type="button"
                            class="inline-flex w-full items-center justify-center rounded-lg border border-gray-300 px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 sm:w-auto dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                            Clear Conversation
                        </button>

                        <button
                            id="chatbotSendButton"
                            type="submit"
                            class="inline-flex w-full items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 sm:w-auto">
                            Send To Interview Chatbot
                        </button>
                    </div>
                </form>
            </article>

            <div class="space-y-6 xl:col-span-4">
                <article class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Current Scope</h3>
                    <p id="chatbotScopeTitle" class="mt-4 text-lg font-semibold text-gray-900 dark:text-white/90">
                        All Philippine Interviews
                    </p>
                    <p id="chatbotScopeDescription" class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">
                        General interview practice across the supported Philippine interview tracks in this project.
                    </p>
                    <div class="mt-4 rounded-2xl border border-gray-200 bg-gray-50 px-4 py-4 dark:border-gray-800 dark:bg-gray-900/70">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Active provider route</p>
                        <p id="chatbotProviderSummary" class="mt-2 text-sm font-semibold text-gray-900 dark:text-white/90">
                            Auto choose the best available API
                        </p>
                    </div>
                </article>

                <article class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Quick Prompts</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Tap a prompt to fill or send an interview request faster.
                            </p>
                        </div>
                    </div>
                    <div id="chatbotQuickPrompts" class="mt-4 flex flex-wrap gap-2"></div>
                </article>

                <article class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Latest Result</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Generated questions or review feedback will appear here.
                            </p>
                        </div>
                    </div>

                    <div id="chatbotResultMeta" class="mt-4 rounded-2xl border border-brand-100 bg-brand-50/70 px-4 py-4 text-sm leading-6 text-gray-600 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-gray-300">
                        Start a conversation to generate interview help from the chatbot.
                    </div>

                    <div id="chatbotGeneratedQuestions" class="mt-4 space-y-3"></div>
                    <div id="chatbotFeedbackPanel" class="mt-4 space-y-4"></div>
                </article>

                <article class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Guardrails</h3>
                    <ul class="mt-4 space-y-3 text-sm leading-6 text-gray-600 dark:text-gray-400">
                        <li class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-900/70">
                            The chatbot is limited to interview preparation and interview-answer coaching only.
                        </li>
                        <li class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-900/70">
                            Responses stay in a Philippine interview context for students, fresh graduates, and early-career applicants.
                        </li>
                        <li class="rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-900/70">
                            The supported APIs are surfaced here, and the page falls back gracefully when keys are missing.
                        </li>
                    </ul>
                </article>
            </div>
        </section>
    </div>
@endsection
