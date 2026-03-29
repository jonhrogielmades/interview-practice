<section
    class="mb-6 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
    <div
        class="border-b border-gray-200 bg-gradient-to-r from-brand-500/10 via-white to-cyan-500/10 p-6 dark:border-gray-800 dark:from-brand-500/5 dark:via-gray-900 dark:to-cyan-500/5">
        <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-3xl">
                <span
                    class="mb-3 inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                    AI-Based Interview Practice System
                </span>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white/90 md:text-3xl">
                    Session Setup
                </h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-gray-600 dark:text-gray-400">
                    Configure your default interview settings here, then open Practice with your preferred question
                    count, pacing, and coaching style already in place.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
                <div
                    class="rounded-2xl border border-gray-200 bg-white/80 px-4 py-3 backdrop-blur dark:border-gray-800 dark:bg-gray-900/80">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Saved Defaults</p>
                    <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">Reusable</p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white/80 px-4 py-3 backdrop-blur dark:border-gray-800 dark:bg-gray-900/80">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Modes</p>
                    <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">Coach + Pace</p>
                </div>
                <div
                    class="rounded-2xl border border-gray-200 bg-white/80 px-4 py-3 backdrop-blur dark:border-gray-800 dark:bg-gray-900/80">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Launch</p>
                    <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">Practice Ready</p>
                </div>
            </div>
        </div>
    </div>
</section>

<div id="sessionSetupApp" class="grid items-start gap-6 xl:grid-cols-12">
    <section class="flex flex-col gap-6 xl:col-span-8">
        <article class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">
                    Core Defaults
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    These values will be preloaded on the Practice page.
                </p>
            </div>

            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                        for="setupQuestionCount">
                        Question Count
                    </label>
                    <select
                        id="setupQuestionCount"
                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></select>
                </div>

                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                        for="setupCoachMode">
                        Coach Focus
                    </label>
                    <select
                        id="setupCoachMode"
                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></select>
                </div>

                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                        for="setupPacingMode">
                        Pacing Mode
                    </label>
                    <select
                        id="setupPacingMode"
                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></select>
                </div>
            </div>
        </article>

        <article class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">
                    Session Preferences
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Add context so each mock interview starts from the right baseline.
                </p>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                        for="setupPreferredCategory">
                        Preferred Category
                    </label>
                    <select
                        id="setupPreferredCategory"
                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></select>
                </div>

                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                        for="setupVoiceMode">
                        Response Preference
                    </label>
                    <select
                        id="setupVoiceMode"
                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></select>
                </div>
            </div>

            <div class="mt-5">
                <label
                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                    for="setupNotes">
                    Setup Notes
                </label>
                <textarea
                    id="setupNotes"
                    class="dark:bg-dark-900 min-h-[140px] w-full rounded-2xl border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                    placeholder="Example: focus on shorter answers, include metrics, and start with Job Interview this week."></textarea>

                <div class="mt-2 flex items-center justify-between gap-3 text-xs text-gray-500 dark:text-gray-400">
                    <span>Saved notes carry into Practice so each session starts with one clear reminder.</span>
                    <span id="setupNotesCount">0 / 500</span>
                </div>
            </div>
        </article>

        <article
            class="rounded-2xl border border-brand-100 bg-brand-50/60 p-5 dark:border-brand-500/20 dark:bg-brand-500/5">
            <div class="mb-4">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">
                    Setup Guidance
                </h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    A few strong defaults usually make sessions feel more intentional than changing everything each
                    time.
                </p>
            </div>

            <div class="grid gap-3 md:grid-cols-3">
                <div
                    class="rounded-xl border border-white/70 bg-white/80 p-4 dark:border-white/10 dark:bg-gray-900/70">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white/90">
                        Quick Drill
                    </p>
                    <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">
                        Best for daily repetition and confidence building.
                    </p>
                </div>
                <div
                    class="rounded-xl border border-white/70 bg-white/80 p-4 dark:border-white/10 dark:bg-gray-900/70">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white/90">
                        Technical Depth
                    </p>
                    <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">
                        Helpful when you want more detail, tradeoffs, and system language in your answers.
                    </p>
                </div>
                <div
                    class="rounded-xl border border-white/70 bg-white/80 p-4 dark:border-white/10 dark:bg-gray-900/70">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white/90">
                        Deep Dive Pace
                    </p>
                    <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">
                        Better when you want space for structured, example-rich responses.
                    </p>
                </div>
            </div>
        </article>

        <article class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-4">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">
                    Category Preview
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Quick context for the category that will be ready first on Practice.
                </p>
            </div>

            <div class="space-y-4">
                <p
                    id="summaryCategoryDescription"
                    class="text-sm leading-6 text-gray-600 dark:text-gray-400">
                    Common employment questions about strengths, skills, and goals.
                </p>

                <div
                    class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-4 dark:border-gray-800 dark:bg-gray-900/70">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Sample Question</p>
                    <p
                        id="summaryCategoryQuestion"
                        class="mt-2 text-sm font-medium leading-6 text-gray-900 dark:text-white/90">
                        Tell me about yourself and why you are a good fit for this position.
                    </p>
                </div>

                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500">Focus Keywords</p>
                    <div id="summaryCategoryKeywords" class="mt-3 flex flex-wrap gap-2"></div>
                </div>
            </div>
        </article>
    </section>

    <section class="flex flex-col gap-6 xl:col-span-4">
        <article class="h-full rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">
                    Saved Summary
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Live preview of the defaults you are about to save.
                </p>
            </div>

            <div class="space-y-3">
                <div
                    class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/70">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Question Count</p>
                    <strong
                        id="summaryQuestionCount"
                        class="mt-2 block text-sm font-semibold text-gray-900 dark:text-white/90">
                        Quick Drill (3 questions)
                    </strong>
                </div>
                <div
                    class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/70">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Coach Focus</p>
                    <strong
                        id="summaryCoachMode"
                        class="mt-2 block text-sm font-semibold text-gray-900 dark:text-white/90">
                        Balanced Coach
                    </strong>
                </div>
                <div
                    class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/70">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Pacing</p>
                    <strong
                        id="summaryPacingMode"
                        class="mt-2 block text-sm font-semibold text-gray-900 dark:text-white/90">
                        Standard (03:00)
                    </strong>
                </div>
                <div
                    class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/70">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Preferred Category</p>
                    <strong
                        id="summaryCategory"
                        class="mt-2 block text-sm font-semibold text-gray-900 dark:text-white/90">
                        Job Interview
                    </strong>
                </div>
                <div
                    class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/70">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Response Preference</p>
                    <strong
                        id="summaryVoiceMode"
                        class="mt-2 block text-sm font-semibold text-gray-900 dark:text-white/90">
                        Text First
                    </strong>
                </div>
                <div
                    class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/70">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Estimated Session Time</p>
                    <strong
                        id="summaryEstimatedTime"
                        class="mt-2 block text-sm font-semibold text-gray-900 dark:text-white/90">
                        09:00 total
                    </strong>
                </div>
            </div>
        </article>

        <article class="h-full rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-4">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">
                    Notes Preview
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Keep a short reminder for how you want the next mock to feel.
                </p>
            </div>

            <p
                id="summaryNotes"
                class="rounded-2xl border border-dashed border-gray-300 px-4 py-5 text-sm leading-6 text-gray-500 dark:border-gray-700 dark:text-gray-400">
                No notes saved yet.
            </p>
        </article>

        <article class="h-full rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">
                    Actions
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Save your defaults, reset them, or move directly into practice.
                </p>
            </div>

            <div class="flex flex-col gap-3">
                <button
                    id="saveSessionSetupBtn"
                    type="button"
                    class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                    Save Defaults
                </button>
                <button
                    id="resetSessionSetupBtn"
                    type="button"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    Reset Defaults
                </button>
                <a
                    id="openPracticeBtn"
                    href="{{ route('practice', ['launch' => 'setup']) }}"
                    class="inline-flex items-center justify-center rounded-lg border border-brand-300 px-4 py-3 text-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:border-brand-500/40 dark:text-brand-300 dark:hover:bg-brand-500/10">
                    Open Practice
                </a>
            </div>

            <div
                id="sessionSetupStatus"
                aria-live="polite"
                class="mt-4 hidden rounded-xl border px-4 py-3 text-sm"></div>
        </article>
    </section>
</div>