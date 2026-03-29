<section
    class="mb-6 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
    <div
        class="border-b border-gray-200 bg-gradient-to-r from-emerald-500/10 via-white to-brand-500/10 p-6 dark:border-gray-800 dark:from-emerald-500/5 dark:via-gray-900 dark:to-brand-500/5">
        <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-3xl">
                <span
                    class="mb-3 inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
                    Detailed Saved-Session Inspection
                </span>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white/90 md:text-3xl">
                    Session Review
                </h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-gray-600 dark:text-gray-400">
                    Inspect saved interviews question by question, compare strengths against weaker dimensions, and
                    revisit what you actually answered.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-4">
                <div
                    class="rounded-2xl border border-gray-200 bg-white/80 px-4 py-3 backdrop-blur dark:border-gray-800 dark:bg-gray-900/80">
                    <p class="text-xs uppercase tracking-wide text-gray-500">
                        Saved Sessions
                    </p>
                    <p
                        id="sessionReviewTotalSessions"
                        class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">
                        0
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Available for review
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white/80 px-4 py-3 backdrop-blur dark:border-gray-800 dark:bg-gray-900/80">
                    <p class="text-xs uppercase tracking-wide text-gray-500">
                        Questions Saved
                    </p>
                    <p
                        id="sessionReviewTotalQuestions"
                        class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">
                        0
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Across all sessions
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white/80 px-4 py-3 backdrop-blur dark:border-gray-800 dark:bg-gray-900/80">
                    <p class="text-xs uppercase tracking-wide text-gray-500">
                        Best Saved Score
                    </p>
                    <p
                        id="sessionReviewBestScore"
                        class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">
                        0
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Highest session average
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white/80 px-4 py-3 backdrop-blur dark:border-gray-800 dark:bg-gray-900/80">
                    <p class="text-xs uppercase tracking-wide text-gray-500">
                        Current Focus
                    </p>
                    <p
                        id="sessionReviewFocusDimension"
                        class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">
                        Clarity
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Most common weak spot
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<div id="sessionReviewApp" class="grid gap-6 xl:grid-cols-12">
    <section class="space-y-6 xl:col-span-4">
        <article class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">
                    Find A Session
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Search by category, coach mode, prompt, or what you answered.
                </p>
            </div>

            <div class="space-y-4">
                <div>
                    <label
                        for="sessionReviewSearch"
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Search
                    </label>
                    <input
                        id="sessionReviewSearch"
                        type="search"
                        placeholder="Behavioral, clarity, stakeholder, metrics..."
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label
                            for="sessionReviewCategoryFilter"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Category
                        </label>
                        <select
                            id="sessionReviewCategoryFilter"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800"></select>
                    </div>
                    <div>
                        <label
                            for="sessionReviewSort"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Sort
                        </label>
                        <select
                            id="sessionReviewSort"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                            <option value="recent">Most Recent</option>
                            <option value="score">Highest Score</option>
                            <option value="category">Category</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a
                        href="{{ route('practice') }}"
                        class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                        Open Practice
                    </a>
                    <button
                        id="sessionReviewClearHistoryBtn"
                        type="button"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                        Clear Saved Sessions
                    </button>
                </div>
            </div>

            <div
                id="sessionReviewStatus"
                class="mt-4 hidden rounded-xl border px-4 py-3 text-sm"></div>
        </article>

        <article class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-5 flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">
                        Saved Sessions
                    </h3>
                    <p
                        id="sessionReviewListMeta"
                        class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        0 saved sessions
                    </p>
                </div>
                <a
                    href="{{ route('progress') }}"
                    class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-300">
                    Open Progress
                </a>
            </div>

            <div id="sessionReviewSessionList" class="space-y-3"></div>
        </article>
    </section>

    <section class="space-y-6 xl:col-span-8">
        <article class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div id="sessionReviewDetail"></div>
        </article>
    </section>
</div>
