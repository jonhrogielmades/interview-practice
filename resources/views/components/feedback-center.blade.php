<section
    class="mb-6 overflow-hidden rounded-2xl border border-gray-200/50 bg-white/80 backdrop-blur-2xl transition-all duration-300 dark:border-white/5 dark:bg-gray-900/80">
    <div
        class="border-b border-gray-200/50 bg-gradient-to-r from-brand-500/10 via-white to-emerald-500/10 p-6 dark:border-white/5 dark:from-brand-500/5 dark:via-gray-900 dark:to-emerald-500/5">
        <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-3xl">
                <span
                    class="mb-3 inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                    AI-Based Interview Practice System
                </span>
                <h1 class="bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-2xl font-bold text-transparent dark:from-white dark:to-gray-400 md:text-3xl">
                    Feedback Center
                </h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-gray-600 dark:text-gray-400">
                    Review saved evaluations, spot repeated strengths, and focus on the coaching themes that show up
                    most across your recent practice sessions.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-4">
                <div
                    class="rounded-2xl border border-gray-200/50 bg-white/80 px-4 py-3 shadow-theme-xs backdrop-blur-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-theme-md dark:border-white/5 dark:bg-gray-900/80">
                    <p class="text-xs uppercase tracking-wide text-gray-500">
                        Evaluations
                    </p>
                    <p
                        id="feedbackEntryCountValue"
                        class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">
                        0
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200/50 bg-white/80 px-4 py-3 shadow-theme-xs backdrop-blur-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-theme-md dark:border-white/5 dark:bg-gray-900/80">
                    <p class="text-xs uppercase tracking-wide text-gray-500">
                        Avg Score
                    </p>
                    <p
                        id="feedbackAverageScoreValue"
                        class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">
                        0/100
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200/50 bg-white/80 px-4 py-3 shadow-theme-xs backdrop-blur-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-theme-md dark:border-white/5 dark:bg-gray-900/80">
                    <p class="text-xs uppercase tracking-wide text-gray-500">
                        Top Category
                    </p>
                    <p
                        id="feedbackTopCategoryValue"
                        class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">
                        None
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200/50 bg-white/80 px-4 py-3 shadow-theme-xs backdrop-blur-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-theme-md dark:border-white/5 dark:bg-gray-900/80">
                    <p class="text-xs uppercase tracking-wide text-gray-500">
                        Sessions
                    </p>
                    <p
                        id="feedbackSessionCountValue"
                        class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">
                        0
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<div id="feedbackCenterApp" class="space-y-6">
    <section class="grid gap-6 xl:grid-cols-12">
        <article
            class="rounded-2xl border border-gray-200/50 bg-white/80 p-5 shadow-theme-xs backdrop-blur-xl transition-all duration-300 hover:-translate-y-1.5 hover:shadow-theme-md dark:border-white/5 dark:bg-gray-900/80 xl:col-span-8">
            <div class="mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">
                    Search And Filter
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Narrow your review by search term, category, or result type.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <div class="md:col-span-2">
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                        for="feedbackSearchInput">
                        Search Feedback
                    </label>
                    <input
                        id="feedbackSearchInput"
                        type="search"
                        placeholder="Search feedback, strengths, or suggestions"
                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                </div>

                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                        for="feedbackCategoryFilter">
                        Category
                    </label>
                    <select
                        id="feedbackCategoryFilter"
                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="all">All Categories</option>
                    </select>
                </div>

                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                        for="feedbackTypeFilter">
                        View
                    </label>
                    <select
                        id="feedbackTypeFilter"
                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="all">Evaluations + Sessions</option>
                        <option value="evaluations">Evaluations Only</option>
                        <option value="sessions">Sessions Only</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <div class="flex flex-wrap gap-3">
                        <a
                            href="{{ route('practice') }}"
                            class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                            Open Practice
                        </a>
                        <button
                            id="feedbackClearHistoryBtn"
                            type="button"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                            Clear Saved Sessions
                        </button>
                    </div>
                </div>
            </div>

            <div
                id="feedbackCenterStatus"
                class="mt-4 hidden rounded-xl border px-4 py-3 text-sm"></div>
        </article>

        <article
            class="rounded-2xl border border-gray-200/50 bg-white/80 p-5 shadow-theme-xs backdrop-blur-xl transition-all duration-300 hover:-translate-y-1.5 hover:shadow-theme-md dark:border-white/5 dark:bg-gray-900/80 xl:col-span-4">
            <div class="mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">
                    Coaching Digest
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Quick signals pulled from your saved history.
                </p>
            </div>

            <div
                id="feedbackInsightSummary"
                class="rounded-2xl border border-brand-100 bg-brand-50/70 p-5 text-sm leading-6 text-gray-600 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-gray-300">
                Start a practice session and submit an answer to generate feedback insights here.
            </div>

            <div class="mt-5 space-y-5">
                <div>
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white/90">
                        Most Common Strengths
                    </h4>
                    <ul id="feedbackStrengthList" class="mt-3 space-y-2"></ul>
                </div>

                <div>
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white/90">
                        Most Common Improvement Areas
                    </h4>
                    <ul id="feedbackImprovementList" class="mt-3 space-y-2"></ul>
                </div>
            </div>
        </article>
    </section>

    <section class="grid gap-6 xl:grid-cols-12">
        <article
            id="feedbackEvaluationSection"
            class="rounded-2xl border border-gray-200/50 bg-white/80 p-5 shadow-theme-xs backdrop-blur-xl transition-all duration-300 hover:-translate-y-1.5 hover:shadow-theme-md dark:border-white/5 dark:bg-gray-900/80 xl:col-span-7">
            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">
                        Saved Evaluations
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Individual answer reviews saved from the Practice page.
                    </p>
                </div>
                <span
                    id="feedbackEvaluationsMeta"
                    class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    0 items
                </span>
            </div>

            <div id="feedbackEvaluationList" class="space-y-4"></div>
        </article>

        <article
            id="feedbackSessionSection"
            class="rounded-2xl border border-gray-200/50 bg-white/80 p-5 shadow-theme-xs backdrop-blur-xl transition-all duration-300 hover:-translate-y-1.5 hover:shadow-theme-md dark:border-white/5 dark:bg-gray-900/80 xl:col-span-5">
            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">
                        Session History
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Full-session summaries with average score and focus areas.
                    </p>
                </div>
                <span
                    id="feedbackSessionsMeta"
                    class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    0 items
                </span>
            </div>

            <div id="feedbackSessionList" class="space-y-4"></div>
        </article>
    </section>
</div>
