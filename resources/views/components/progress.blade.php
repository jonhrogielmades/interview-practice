<section
    class="mb-6 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
    <div
        class="border-b border-gray-200 bg-gradient-to-r from-brand-500/10 via-white to-sky-500/10 p-6 dark:border-gray-800 dark:from-brand-500/5 dark:via-gray-900 dark:to-sky-500/5">
        <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-3xl">
                <span
                    class="mb-3 inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                    AI-Based Interview Practice System
                </span>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white/90 md:text-3xl">
                    Performance Tracking
                </h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-gray-600 dark:text-gray-400">
                    Review session history, score trends, category-level performance, and practice consistency over time.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-4">
                <div
                    class="rounded-2xl border border-gray-200 bg-white/80 px-4 py-3 backdrop-blur dark:border-gray-800 dark:bg-gray-900/80">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Overall Sessions</p>
                    <p id="progressTotalSessions" class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">0</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Saved interview practices</p>
                </div>

                <div
                    class="rounded-2xl border border-gray-200 bg-white/80 px-4 py-3 backdrop-blur dark:border-gray-800 dark:bg-gray-900/80">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Overall Average</p>
                    <p id="progressAverageScore" class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">0.0</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Average interview score</p>
                </div>

                <div
                    class="rounded-2xl border border-gray-200 bg-white/80 px-4 py-3 backdrop-blur dark:border-gray-800 dark:bg-gray-900/80">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Weekly Goal</p>
                    <p id="progressWeeklyGoal" class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">0/3</p>
                    <p id="progressWeeklyGoalSubtext" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Last 7 days of practice
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-gray-200 bg-white/80 px-4 py-3 backdrop-blur dark:border-gray-800 dark:bg-gray-900/80">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Practice Streak</p>
                    <p id="progressStreakDays" class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">0</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Consecutive active days</p>
                </div>
            </div>
        </div>
    </div>
</section>

<div id="progressApp" class="grid gap-6 xl:grid-cols-12">
    <section class="space-y-6 xl:col-span-8">
        <article id="sessionReviewSection"
            class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Average Score Trend</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Session-by-session performance using a lightweight canvas chart.
                </p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                <canvas id="progressTrendCanvas" width="900" height="250" class="block h-[250px] w-full"></canvas>
            </div>
            <div id="trendLegend" class="mt-4 flex flex-wrap gap-3"></div>
        </article>

        <article id="categoryBreakdownSection"
            class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Session History</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Detailed record of saved practice sessions and their resulting performance.
                </p>
            </div>
            <div id="sessionHistoryContainer" class="space-y-4"></div>
        </article>

        <article id="exportSection"
            class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Detailed Session Review</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Open saved sessions to inspect answers, pacing, and coaching signals.
                </p>
            </div>
            <div id="sessionReviewContainer" class="space-y-4"></div>
        </article>

        <article class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Achievements</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Track consistency, category coverage, and readiness milestones.
                </p>
            </div>
            <div id="progressAchievementList" class="space-y-3"></div>
        </article>
    </section>

    <section class="space-y-6 xl:col-span-4">
        <article class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Category Breakdown</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Average performance for each interview scenario.
                </p>
            </div>
            <div id="categoryBreakdownList" class="space-y-3"></div>
        </article>

        <article class="rounded-2xl border border-brand-100 bg-brand-50/60 p-5 dark:border-brand-500/20 dark:bg-brand-500/5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Overall Summary</h3>
                <p id="progressSummaryText" class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">
                    Complete more sessions to unlock stronger score trends and performance insights.
                </p>
            </article>

        <article class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Capstone Rubric</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Weighted 1-to-5 view of verbal, selected non-verbal, and overall readiness.
                </p>
            </div>
            <div id="progressCapstoneRubricGrid" class="grid gap-3 sm:grid-cols-2"></div>
        </article>

        <article class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Criteria Analytics</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Average interview quality across key dimensions.
                </p>
            </div>
            <div id="criteriaAnalyticsGrid" class="grid gap-3 sm:grid-cols-2"></div>
        </article>

        <article class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Recent Performance Cards</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Fast review of your latest three saved sessions.
                </p>
            </div>
            <div id="recentPerformanceCards" class="space-y-3"></div>
        </article>

        <article class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Export Data</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Download your saved report and session summaries.
                </p>
            </div>

            <div class="flex flex-col gap-3">
                <button id="progressExportJsonBtn" type="button"
                    class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                    Export Capstone JSON
                </button>
                <button id="progressExportCsvBtn" type="button"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    Export Session CSV
                </button>
                <a href="{{ route('practice') }}"
                    class="inline-flex items-center justify-center rounded-lg border border-brand-300 px-4 py-3 text-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:border-brand-500/40 dark:text-brand-300 dark:hover:bg-brand-500/10">
                    Open Practice
                </a>
                <button id="progressClearHistoryBtn" type="button"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    Clear Saved Sessions
                </button>
            </div>

            <div id="progressStatus" class="mt-4 hidden rounded-xl border px-4 py-3 text-sm"></div>
        </article>
    </section>
</div>
