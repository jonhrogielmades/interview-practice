<section
    class="mb-6 overflow-hidden rounded-2xl border border-gray-200/50 bg-white/80 backdrop-blur-2xl transition-all duration-300 dark:border-white/5 dark:bg-gray-900/80">
    <div
        class="border-b border-gray-200/50 bg-gradient-to-r from-sky-500/10 via-white to-emerald-500/10 p-6 dark:border-white/5 dark:from-sky-500/5 dark:via-gray-900 dark:to-emerald-500/5">
        <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-3xl">
                <span
                    class="mb-3 inline-flex items-center rounded-full bg-sky-50 px-3 py-1 text-xs font-medium text-sky-700 dark:bg-sky-500/10 dark:text-sky-300">
                    Category Analytics And Readiness
                </span>
                <h1 class="bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-2xl font-bold text-transparent dark:from-white dark:to-gray-400 md:text-3xl">
                    Category Insights
                </h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-gray-600 dark:text-gray-400">
                    Compare interview categories, see which scenarios are strongest, and identify where another practice
                    round will improve readiness fastest.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-4">
                <div
                    class="rounded-2xl border border-gray-200/50 bg-white/80 px-4 py-3 shadow-theme-xs backdrop-blur-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-theme-md dark:border-white/5 dark:bg-gray-900/80">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Categories Practiced</p>
                    <p id="categoryInsightsPracticed" class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">
                        0
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Tracked from saved sessions</p>
                </div>

                <div
                    class="rounded-2xl border border-gray-200/50 bg-white/80 px-4 py-3 shadow-theme-xs backdrop-blur-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-theme-md dark:border-white/5 dark:bg-gray-900/80">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Strongest Category</p>
                    <p id="categoryInsightsStrongest" class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">
                        None
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Highest average score</p>
                </div>

                <div
                    class="rounded-2xl border border-gray-200/50 bg-white/80 px-4 py-3 shadow-theme-xs backdrop-blur-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-theme-md dark:border-white/5 dark:bg-gray-900/80">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Ready Categories</p>
                    <p id="categoryInsightsReady" class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">
                        0
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">At 75+ average</p>
                </div>

                <div
                    class="rounded-2xl border border-gray-200/50 bg-white/80 px-4 py-3 shadow-theme-xs backdrop-blur-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-theme-md dark:border-white/5 dark:bg-gray-900/80">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Next Focus</p>
                    <p id="categoryInsightsNeed" class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">
                        None
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Best place to improve</p>
                </div>
            </div>
        </div>
    </div>
</section>

<div id="categoryInsightsApp" class="grid gap-6 xl:grid-cols-12">
    <section class="space-y-6 xl:col-span-4">
        <article class="rounded-2xl border border-gray-200/50 bg-white/80 p-5 shadow-theme-xs backdrop-blur-xl transition-all duration-300 hover:-translate-y-1.5 hover:shadow-theme-md dark:border-white/5 dark:bg-gray-900/80">
            <div class="mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Compare Categories</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Search for a category, then sort by score, recency, or practice volume.
                </p>
            </div>

            <div class="space-y-4">
                <div>
                    <label for="categoryInsightsSearch"
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Search
                    </label>
                    <input id="categoryInsightsSearch" type="search"
                        placeholder="Behavioral, relevance, stakeholder..."
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                </div>

                <div>
                    <label for="categoryInsightsSort"
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Sort
                    </label>
                    <select id="categoryInsightsSort"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        <option value="score">Highest Score</option>
                        <option value="recent">Most Recent</option>
                        <option value="sessions">Most Practiced</option>
                        <option value="name">Category Name</option>
                    </select>
                </div>
            </div>
        </article>

        <article class="rounded-2xl border border-gray-200/50 bg-white/80 p-5 shadow-theme-xs backdrop-blur-xl transition-all duration-300 hover:-translate-y-1.5 hover:shadow-theme-md dark:border-white/5 dark:bg-gray-900/80">
            <div class="mb-5 flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Category List</h3>
                    <p id="categoryInsightsListMeta" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        0 categories shown
                    </p>
                </div>
                <a href="{{ route('progress') }}"
                    class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-300">
                    Open Progress
                </a>
            </div>

            <div id="categoryInsightsList" class="space-y-3"></div>
        </article>
    </section>

    <section class="space-y-6 xl:col-span-8">
        <article class="rounded-2xl border border-gray-200/50 bg-white/80 p-5 shadow-theme-xs backdrop-blur-xl transition-all duration-300 hover:-translate-y-1.5 hover:shadow-theme-md dark:border-white/5 dark:bg-gray-900/80">
            <div id="categoryInsightsDetail"></div>
        </article>
    </section>
</div>
