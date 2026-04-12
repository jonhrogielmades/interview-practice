<section
    class="mb-6 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
    <div
        class="border-b border-gray-200 bg-gradient-to-r from-brand-500/10 via-white to-orange-500/10 p-6 dark:border-gray-800 dark:from-brand-500/5 dark:via-gray-900 dark:to-orange-500/5">
        <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-3xl">
                <span
                    class="mb-3 inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                    AI-Based Interview Practice System
                </span>

                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white/90 md:text-3xl">
                    AI Avatar Interview Simulation
                </h1>

                <p class="mt-3 max-w-2xl text-sm leading-6 text-gray-600 dark:text-gray-400">
                    Launch a category, answer through text or voice, and review verbal plus selected non-verbal
                    coaching inside one capstone-aligned practice workspace.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
                <div
                    class="rounded-2xl border border-gray-200 bg-white/80 px-4 py-3 backdrop-blur dark:border-gray-800 dark:bg-gray-900/80">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Tracks</p>
                    <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">4 Tracks</p>
                </div>

                <div
                    class="rounded-2xl border border-gray-200 bg-white/80 px-4 py-3 backdrop-blur dark:border-gray-800 dark:bg-gray-900/80">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Response Modes</p>
                    <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">Text + Voice</p>
                </div>

                <div
                    class="rounded-2xl border border-gray-200 bg-white/80 px-4 py-3 backdrop-blur dark:border-gray-800 dark:bg-gray-900/80">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Review</p>
                    <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">Saved To Workspace</p>
                </div>
            </div>
        </div>
    </div>
</section>

<div id="practiceApp" class="grid items-stretch gap-6 xl:grid-cols-12">
    <section class="flex flex-col gap-6 xl:col-span-4">
        <article
            id="practiceSetupSection"
            class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Session Setup</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Configure how your next mock interview should run.
                </p>
            </div>

            <div class="space-y-4">
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                        for="questionCountSelect">
                        Question Count
                    </label>
                    <select
                        id="questionCountSelect"
                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="3">Quick Drill (3 questions)</option>
                        <option value="5">Full Mock (5 questions)</option>
                        <option value="10">Extended Practice (10 questions)</option>
                        <option value="15">Intensive Round (15 questions)</option>
                        <option value="20">Marathon Mock (20 questions)</option>
                    </select>
                </div>

                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                        for="focusModeSelect">
                        Coach Focus
                    </label>
                    <select
                        id="focusModeSelect"
                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></select>
                </div>

                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                        for="pacingModeSelect">
                        Pacing Mode
                    </label>
                    <select
                        id="pacingModeSelect"
                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></select>
                </div>
            </div>
        </article>

        <article class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Choose Practice Category</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Choose the interview category you want before the field builder and interview modal open.
                </p>
            </div>

            <div class="rounded-2xl border border-dashed border-gray-300 px-4 py-4 dark:border-gray-700">
                <p class="text-sm leading-6 text-gray-600 dark:text-gray-400">
                    Learning Activities can bring the drill, level, and target score here, but the category stays yours
                    to choose first.
                </p>
            </div>

            <button
                id="openPracticeCategoryModalBtn"
                type="button"
                class="mt-4 inline-flex w-full items-center justify-center rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 sm:w-auto">
                Choose Category
            </button>
        </article>
    </section>

    <section class="xl:col-span-8">
        <div class="flex h-full flex-col gap-6">
            <article class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div
                    class="flex flex-col gap-4 border-b border-gray-200 pb-5 dark:border-gray-800 md:flex-row md:items-start md:justify-between">
                    <div class="max-w-2xl">
                        <p class="text-xs font-medium uppercase tracking-[0.2em] text-brand-600 dark:text-brand-300">
                            Interview Workspace Modal
                        </p>
                        <h3 id="practiceModalCategoryName" class="mt-2 text-xl font-semibold text-gray-900 dark:text-white/90">
                            Select a track to launch
                        </h3>
                        <p id="practiceModalSummaryText" class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">
                            Choose a category from the sidebar. The interview flow and AI avatar interviewer will open in a modal.
                        </p>
                    </div>

                    <div class="flex w-full flex-col items-start gap-3 md:w-auto md:items-end">
                        <span
                            id="practiceModalStateTag"
                            class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            Waiting
                        </span>

                        <button
                            id="openPracticeModalBtn"
                            type="button"
                            disabled
                            class="inline-flex w-full items-center justify-center rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto">
                            Open Interview Modal
                        </button>

                        <button
                            id="editPracticeFieldBtn"
                            type="button"
                            disabled
                            class="inline-flex w-full items-center justify-center rounded-lg border border-gray-300 px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03] sm:w-auto">
                            Edit Target Field
                        </button>
                    </div>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div
                        class="flex h-full flex-col justify-between rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/70">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Active Category</p>
                        <strong
                            id="practiceModalActiveCategory"
                            class="mt-2 block text-sm font-semibold text-gray-900 dark:text-white/90">
                            None selected
                        </strong>
                    </div>

                    <div
                        class="flex h-full flex-col justify-between rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/70">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Answers Reviewed</p>
                        <strong
                            id="practiceModalAnsweredValue"
                            class="mt-2 block text-sm font-semibold text-gray-900 dark:text-white/90">
                            0 / 0
                        </strong>
                    </div>

                    <div
                        class="flex h-full flex-col justify-between rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/70">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Workspace</p>
                        <strong
                            id="practiceModalWorkspaceValue"
                            class="mt-2 block text-sm font-semibold text-gray-900 dark:text-white/90">
                            Closed
                        </strong>
                    </div>

                    <div
                        class="flex h-full flex-col justify-between rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/70">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Target Field</p>
                        <strong
                            id="practiceModalFieldValue"
                            class="mt-2 block text-sm font-semibold text-gray-900 dark:text-white/90">
                            Not set
                        </strong>
                        <p
                            id="practiceModalFieldMeta"
                            class="mt-2 text-xs leading-5 text-gray-500 dark:text-gray-400">
                            Choose a sidebar track to create a field with the chatbot.
                        </p>
                    </div>
                </div>
            </article>

            <article class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-200 pb-5 dark:border-gray-800">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">AI Question Generator</h3>
                    <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">
                        After you choose a category, the workspace uses the AI question chatbot to build a fresh,
                        category-backed set of interview questions and sync the active prompt with the avatar voice.
                    </p>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-3">
                    <div
                        class="flex h-full flex-col justify-between rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/70">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Question Sets</p>
                        <strong class="mt-2 block text-sm font-semibold text-gray-900 dark:text-white/90">
                            Dynamic Per Category
                        </strong>
                    </div>

                    <div
                        class="flex h-full flex-col justify-between rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/70">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Generation Mode</p>
                        <strong class="mt-2 block text-sm font-semibold text-gray-900 dark:text-white/90">
                            Chatbot-Driven
                        </strong>
                    </div>

                    <div
                        class="flex h-full flex-col justify-between rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/70">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Voice Sync</p>
                        <strong class="mt-2 block text-sm font-semibold text-gray-900 dark:text-white/90">
                            Interviewer Reads Active Question
                        </strong>
                    </div>
                </div>
            </article>
        </div>
    </section>
</div>

<div
    id="practiceCategoryModal"
    x-data="{
        viewportWidth: window.innerWidth,
        handleResize: null,
        init() {
            this.handleResize = () => {
                this.viewportWidth = window.innerWidth;
            };

            this.handleResize();
            window.addEventListener('resize', this.handleResize);
        },
        destroy() {
            if (this.handleResize) {
                window.removeEventListener('resize', this.handleResize);
            }
        },
        get isDesktop() {
            return this.viewportWidth >= 1280;
        },
        get sidebarOffset() {
            if (!this.isDesktop) {
                return 0;
            }

            return ($store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen) ? 290 : 90;
        },
        get topOffset() {
            return this.viewportWidth >= 640 ? 88 : 64;
        },
        get wrapperPadding() {
            if (this.viewportWidth >= 1280) {
                return 24;
            }

            if (this.viewportWidth >= 640) {
                return 20;
            }

            return 12;
        },
        get wrapperStyle() {
            return `top: ${this.topOffset}px; left: ${this.sidebarOffset}px; right: 0; bottom: 0; padding: ${this.wrapperPadding}px;`;
        }
    }"
    class="fixed z-[10010] hidden items-start justify-center"
    :style="wrapperStyle"
    aria-hidden="true"
    aria-labelledby="practiceCategoryModalTitle"
    aria-modal="true"
    role="dialog">
    <div id="practiceCategoryModalBackdrop" class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>

    <div
        class="relative z-10 flex w-full max-w-4xl flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-800 dark:bg-gray-950 sm:rounded-3xl">
        <div
            class="flex shrink-0 items-start justify-between gap-4 border-b border-gray-200 px-4 py-4 dark:border-gray-800 sm:px-6">
            <div class="max-w-2xl">
                <p class="text-xs font-medium uppercase tracking-[0.2em] text-brand-600 dark:text-brand-300">
                    Practice Category
                </p>
                <h2 id="practiceCategoryModalTitle" class="mt-1 text-lg font-semibold text-gray-900 dark:text-white/90">
                    Choose category first
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Pick the interview category for this practice before the field builder and interview workspace open.
                </p>
            </div>

            <button
                id="closePracticeCategoryModalBtn"
                type="button"
                class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-500 transition hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white sm:h-11 sm:w-11">
                <span class="sr-only">Close category modal</span>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z"
                        fill="currentColor" />
                </svg>
            </button>
        </div>

        <div class="max-h-[calc(100dvh-160px)] overflow-y-auto p-4 sm:p-6">
            <div id="practiceCategoryList" class="grid gap-3 sm:grid-cols-2"></div>
        </div>
    </div>
</div>

<div
    id="practiceFieldModal"
    x-data="{
        viewportWidth: window.innerWidth,
        handleResize: null,
        init() {
            this.handleResize = () => {
                this.viewportWidth = window.innerWidth;
            };

            this.handleResize();
            window.addEventListener('resize', this.handleResize);
        },
        destroy() {
            if (this.handleResize) {
                window.removeEventListener('resize', this.handleResize);
            }
        },
        get isDesktop() {
            return this.viewportWidth >= 1280;
        },
        get sidebarOffset() {
            if (!this.isDesktop) {
                return 0;
            }

            return ($store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen) ? 290 : 90;
        },
        get topOffset() {
            return this.viewportWidth >= 640 ? 88 : 64;
        },
        get wrapperPadding() {
            if (this.viewportWidth >= 1280) {
                return 24;
            }

            if (this.viewportWidth >= 640) {
                return 20;
            }

            return 12;
        },
        get wrapperStyle() {
            return `top: ${this.topOffset}px; left: ${this.sidebarOffset}px; right: 0; bottom: 0; padding: ${this.wrapperPadding}px;`;
        }
    }"
    class="fixed z-[10010] hidden items-start justify-center"
    :style="wrapperStyle"
    aria-hidden="true"
    aria-labelledby="practiceFieldModalTitle"
    aria-modal="true"
    role="dialog">
    <div id="practiceFieldModalBackdrop" class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>

    <div
        class="relative z-10 flex h-full w-full max-w-5xl flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-800 dark:bg-gray-950 sm:rounded-3xl">
        <div
            class="flex shrink-0 items-start justify-between gap-4 border-b border-gray-200 px-4 py-4 dark:border-gray-800 sm:px-6">
            <div class="max-w-2xl">
                <p class="text-xs font-medium uppercase tracking-[0.2em] text-brand-600 dark:text-brand-300">
                    Field Builder
                </p>
                <h2 id="practiceFieldModalTitle" class="mt-1 text-lg font-semibold text-gray-900 dark:text-white/90">
                    Create your practice field
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Use the chatbot to turn your chosen category into a specific role, course, or focus before opening the interview workspace.
                </p>
            </div>

            <button
                id="closePracticeFieldModalBtn"
                type="button"
                class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-500 transition hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white sm:h-11 sm:w-11">
                <span class="sr-only">Close field builder modal</span>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z"
                        fill="currentColor" />
                </svg>
            </button>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto p-4 sm:p-6">
            <div class="grid gap-6 xl:grid-cols-12">
                <section class="space-y-5 xl:col-span-5">
                    <article class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-800 dark:bg-gray-900/70">
                        <div class="flex flex-wrap items-center gap-3">
                            <span
                                id="practiceFieldModalStatusTag"
                                class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                                Waiting for details
                            </span>
                            <span
                                id="practiceFieldProviderValue"
                                class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                Provider: Auto
                            </span>
                        </div>

                        <h3
                            id="practiceFieldModalCategoryName"
                            class="mt-4 text-xl font-semibold text-gray-900 dark:text-white/90">
                            Choose a category first
                        </h3>
                        <p
                            id="practiceFieldModalCategoryDescription"
                            class="content-break mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">
                            Your selected category will appear here together with starter suggestions for the chatbot.
                        </p>

                        <div class="mt-5">
                            <label
                                class="mb-1.5 block text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400"
                                for="practiceFieldProviderSelect">
                                Provider
                            </label>
                            <select
                                id="practiceFieldProviderSelect"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            </select>
                            <p
                                id="practiceFieldProviderHelpText"
                                class="content-break mt-2 text-xs leading-5 text-gray-500 dark:text-gray-400">
                                Choose which API should build the field plan.
                            </p>
                        </div>
                    </article>

                    <article class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Quick Picks</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Start with a field suggestion, then refine it with the chatbot.
                                </p>
                            </div>
                        </div>

                        <div id="practiceFieldSuggestionChips" class="mt-4 flex flex-wrap gap-2"></div>
                    </article>

                    <article class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Current Field Plan</h3>
                        <strong
                            id="practiceFieldPreviewTitle"
                            class="content-break mt-4 block text-lg font-semibold text-gray-900 dark:text-white/90">
                            No field created yet
                        </strong>
                        <p
                            id="practiceFieldPreviewSummary"
                            class="content-break mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">
                            Tell the chatbot what role, course, or specialization you want so the practice questions can be tailored before the interview modal opens.
                        </p>
                    </article>
                </section>

                <section class="xl:col-span-7">
                    <article class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="border-b border-gray-200 pb-5 dark:border-gray-800">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Field Chatbot</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Describe what you need, let the chatbot shape the field, then continue to the interview workspace.
                            </p>
                        </div>

                        <div
                            id="practiceFieldChatMessages"
                            class="mt-5 flex min-h-[180px] max-h-[280px] flex-col gap-4 overflow-y-auto rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/70 sm:min-h-[220px] sm:max-h-[320px]"></div>

                        <div class="mt-5 space-y-4">
                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                                    for="practiceFieldInput">
                                    Target field, role, or course
                                </label>
                                <input
                                    id="practiceFieldInput"
                                    type="text"
                                    class="dark:bg-dark-900 h-12 w-full rounded-2xl border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                    placeholder="Example: Junior Laravel Developer" />
                            </div>

                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                                    for="practiceFieldNeedInput">
                                    What do you need from this practice?
                                </label>
                                <textarea
                                    id="practiceFieldNeedInput"
                                    rows="4"
                                    class="min-h-[130px] w-full rounded-2xl border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                    placeholder="Example: I want remote-entry-level questions that focus on APIs, debugging, and explaining my capstone clearly."></textarea>
                            </div>
                        </div>

                        <div class="mt-5 flex flex-wrap gap-3">
                            <button
                                id="practiceFieldGenerateBtn"
                                type="button"
                                class="inline-flex w-full items-center justify-center rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 sm:w-auto">
                                Build With Chatbot
                            </button>

                            <button
                                id="practiceFieldResetBtn"
                                type="button"
                                class="inline-flex w-full items-center justify-center rounded-lg border border-gray-300 px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03] sm:w-auto">
                                Reset
                            </button>

                            <button
                                id="practiceFieldApplyBtn"
                                type="button"
                                class="inline-flex w-full items-center justify-center rounded-lg border border-brand-300 px-4 py-3 text-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:border-brand-500/40 dark:text-brand-300 dark:hover:bg-brand-500/10 sm:w-auto">
                                Continue To Practice
                            </button>
                        </div>

                        <div
                            id="practiceFieldChatStatus"
                            class="mt-5 hidden rounded-2xl border px-4 py-3 text-sm"></div>
                    </article>
                </section>
            </div>
        </div>
    </div>
</div>

<div
    id="practiceSessionModal"
    x-data="{
        viewportWidth: window.innerWidth,
        handleResize: null,
        init() {
            this.handleResize = () => {
                this.viewportWidth = window.innerWidth;
            };

            this.handleResize();
            window.addEventListener('resize', this.handleResize);
        },
        destroy() {
            if (this.handleResize) {
                window.removeEventListener('resize', this.handleResize);
            }
        },
        get isDesktop() {
            return this.viewportWidth >= 1280;
        },
        get sidebarOffset() {
            if (!this.isDesktop) {
                return 0;
            }

            return ($store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen) ? 290 : 90;
        },
        get topOffset() {
            return this.viewportWidth >= 640 ? 88 : 64;
        },
        get wrapperPadding() {
            if (this.viewportWidth >= 1280) {
                return 24;
            }

            if (this.viewportWidth >= 640) {
                return 20;
            }

            return 12;
        },
        get wrapperStyle() {
            return `top: ${this.topOffset}px; left: ${this.sidebarOffset}px; right: 0; bottom: 0; padding: ${this.wrapperPadding}px;`;
        }
    }"
    class="fixed z-[9999] hidden items-start justify-center"
    :style="wrapperStyle"
    aria-hidden="true"
    aria-labelledby="practiceModalTitle"
    aria-modal="true"
    role="dialog">
    <div id="practiceSessionModalBackdrop" class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>

    <div
        class="relative z-10 flex h-full w-full max-w-[1500px] flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-800 dark:bg-gray-950 sm:rounded-3xl">
        <div
            class="flex shrink-0 items-start justify-between gap-4 border-b border-gray-200 px-4 py-4 dark:border-gray-800 sm:px-5 lg:px-6">
            <div>
                <p class="text-xs font-medium uppercase tracking-[0.2em] text-brand-600 dark:text-brand-300">
                    Practice Modal
                </p>
                <h2 id="practiceModalTitle" class="mt-1 text-lg font-semibold text-gray-900 dark:text-white/90">
                    Interview Workspace
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Continue your selected interview flow and use the AI interviewer in one modal view.
                </p>
            </div>

            <button
                id="closePracticeModalBtn"
                type="button"
                class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-500 transition hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white sm:h-11 sm:w-11">
                <span class="sr-only">Close interview modal</span>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z"
                        fill="currentColor" />
                </svg>
            </button>
        </div>

        <div class="min-h-0 overflow-y-auto p-3 sm:p-5 lg:p-6">
            <div class="grid items-stretch gap-4 lg:gap-6 xl:grid-cols-12">
                <section class="min-w-0 xl:col-span-7">
                    <article
                        class="flex h-full flex-col rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                        <div
                            class="flex flex-col gap-4 border-b border-gray-200 pb-5 dark:border-gray-800 md:flex-row md:items-start md:justify-between">
                            <div>
                                <h3 id="selectedCategoryName" class="text-lg font-semibold text-gray-900 dark:text-white/90">
                                    Select a track to start
                                </h3>
                                <p
                                    id="selectedCategoryDescription"
                                    class="content-break mt-1 text-sm leading-6 text-gray-500 dark:text-gray-400">
                                    Your chosen interview type will load a fresh AI-generated question set.
                                </p>
                            </div>

                            <span
                                id="questionCounter"
                                class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                Question 0 of 0
                            </span>
                        </div>

                        <div class="mt-5 flex flex-wrap gap-3">
                            <span
                                id="practiceStatusTag"
                                class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">
                                Awaiting category
                            </span>
                            <span
                                id="practiceLabelTag"
                                class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                No active session
                            </span>
                            <span
                                id="selectedPracticeFieldTag"
                                class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                Field not set
                            </span>
                        </div>

                        <div class="mt-5 grid gap-3 sm:grid-cols-3">
                            <div
                                class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/70">
                                <p class="text-xs uppercase tracking-wide text-gray-500">Coach Focus</p>
                                <strong
                                    id="coachModeValue"
                                    class="mt-2 block text-sm font-semibold text-gray-900 dark:text-white/90">
                                    Balanced Coach
                                </strong>
                            </div>

                            <div
                                class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/70">
                                <p class="text-xs uppercase tracking-wide text-gray-500">Target Time</p>
                                <strong
                                    id="timerTargetValue"
                                    class="mt-2 block text-sm font-semibold text-gray-900 dark:text-white/90">
                                    03:00
                                </strong>
                            </div>

                            <div
                                class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/70">
                                <p class="text-xs uppercase tracking-wide text-gray-500">Current Timer</p>
                                <strong
                                    id="questionTimerValue"
                                    class="mt-2 block text-sm font-semibold text-gray-900 dark:text-white/90">
                                    00:00
                                </strong>
                            </div>
                        </div>

                        <div class="mt-5 h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                            <div
                                id="questionProgressFill"
                                class="h-full rounded-full bg-brand-500 transition-all duration-300"
                                style="width: 0%;"></div>
                        </div>

                        <div class="mt-6 space-y-6">
                            <div>
                                <p class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-500">Current question</p>
                                <h2
                                    id="currentQuestionText"
                                    class="content-break text-lg font-semibold leading-7 text-gray-900 sm:text-xl sm:leading-8 dark:text-white/90">
                                    Choose a track from the sidebar to begin your interview simulation.
                                </h2>
                            </div>

                            <div
                                class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-800 dark:bg-gray-900/70">
                                <div
                                    class="flex flex-col gap-3 border-b border-gray-200 pb-4 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <strong class="text-sm font-semibold text-gray-900 dark:text-white/90">Coach Guidance</strong>
                                    </div>
                                    <span
                                        id="coachModeTag"
                                        class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                                        Balanced Coach
                                    </span>
                                </div>

                                <p id="coachTipText" class="mt-4 text-sm leading-6 text-gray-600 dark:text-gray-400">
                                    Choose a track from the sidebar to load your coach guidance and answer keywords.
                                </p>

                                <div id="questionKeywordTags" class="mt-4 flex flex-wrap gap-2"></div>
                            </div>

                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                                    for="responseInput">
                                    Your Response
                                </label>

                                <textarea
                                    id="responseInput"
                                    class="dark:bg-dark-900 min-h-[180px] w-full rounded-2xl border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 sm:min-h-[220px]"
                                    placeholder="Type your answer here or use voice input if supported by your browser."></textarea>

                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    Blank answers are not accepted. Complete your response before submitting.
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-3">
                                <button
                                    id="startVoiceBtn"
                                    type="button"
                                    class="inline-flex w-full items-center justify-center rounded-lg border border-gray-300 px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03] sm:w-auto">
                                    Start Voice Input
                                </button>

                                <button
                                    id="stopVoiceBtn"
                                    type="button"
                                    class="inline-flex w-full items-center justify-center rounded-lg border border-gray-300 px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03] sm:w-auto">
                                    Stop Voice Input
                                </button>

                                <button
                                    id="submitAnswerBtn"
                                    type="button"
                                    class="inline-flex w-full items-center justify-center rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto">
                                    Submit Answer
                                </button>

                                <button
                                    id="nextQuestionBtn"
                                    type="button"
                                    class="inline-flex w-full items-center justify-center rounded-lg border border-gray-300 px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03] sm:w-auto">
                                    Next Question
                                </button>

                                <button
                                    id="endSessionBtn"
                                    type="button"
                                    class="inline-flex w-full items-center justify-center rounded-lg border border-error-300 px-4 py-3 text-sm font-medium text-error-600 transition hover:bg-error-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-error-500/40 dark:text-error-300 dark:hover:bg-error-500/10 sm:w-auto">
                                    End Session
                                </button>
                            </div>

                            <div
                                id="voiceStatus"
                                aria-live="polite"
                                class="flex items-start gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-600 sm:items-center dark:border-gray-800 dark:bg-gray-900/70 dark:text-gray-400">
                                <span id="voiceStatusDot" class="h-2.5 w-2.5 rounded-full bg-gray-400"></span>
                                <span id="voiceStatusText" class="content-break">Voice input is idle.</span>
                            </div>

                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Realtime speech-to-text works best in Chrome or Edge while running on localhost or HTTPS.
                            </p>

                            <div id="practiceMessage" aria-live="polite" class="hidden rounded-xl border px-4 py-3 text-sm"></div>
                        </div>
                    </article>
                </section>

                <section class="grid auto-rows-fr gap-6 xl:col-span-5">
                    <article
                        id="aiInterviewerSection"
                        class="flex h-full flex-col rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                        <div
                            class="flex flex-col gap-4 border-b border-gray-200 pb-5 dark:border-gray-800 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">AI Interviewer</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    The avatar asks the question aloud, while the camera helps surface selected
                                    non-verbal cues such as eye contact, posture, head movement, and facial composure.
                                </p>
                            </div>

                            <span
                                id="interviewerStatusTag"
                                class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                Camera Off
                            </span>
                        </div>

                        <div class="mt-5 space-y-4">
                            <div
                                class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                                <div class="flex min-w-0 items-start gap-4">
                                    <div
                                        id="avatarOrb"
                                        class="flex h-16 w-16 items-center justify-center rounded-full bg-brand-50 text-base font-semibold text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                                        AI
                                    </div>

                                    <div class="min-w-0">
                                        <strong
                                            id="avatarSpeechStatus"
                                            class="block text-sm font-semibold text-gray-900 dark:text-white/90">
                                            Avatar idle
                                        </strong>
                                        <p id="avatarLineText" class="content-break mt-1 text-sm leading-6 text-gray-600 dark:text-gray-400">
                                            Start the camera, then choose a category. I will read each interview question aloud.
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-4 flex flex-wrap gap-3">
                                    <button
                                        id="startCameraBtn"
                                        type="button"
                                        class="inline-flex w-full items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03] sm:w-auto">
                                        Start Camera
                                    </button>

                                    <button
                                        id="stopCameraBtn"
                                        type="button"
                                        class="inline-flex w-full items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03] sm:w-auto">
                                        Stop Camera
                                    </button>

                                    <button
                                        id="askQuestionAloudBtn"
                                        type="button"
                                        class="inline-flex w-full items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 sm:w-auto">
                                        Ask Current Question
                                    </button>
                                </div>
                            </div>

                            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-black dark:border-gray-800">
                                <video id="faceCameraVideo" autoplay muted playsinline class="h-56 w-full object-cover sm:h-64"></video>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-2">
                                <div
                                    class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/70">
                                    <p class="text-xs uppercase tracking-wide text-gray-500">Camera</p>
                                    <strong
                                        id="cameraStateValue"
                                        class="mt-2 block text-sm font-semibold text-gray-900 dark:text-white/90">
                                        Off
                                    </strong>
                                </div>

                                <div
                                    class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/70">
                                    <p class="text-xs uppercase tracking-wide text-gray-500">Face Status</p>
                                    <strong
                                        id="faceStateValue"
                                        class="mt-2 block text-sm font-semibold text-gray-900 dark:text-white/90">
                                        Not detected
                                    </strong>
                                </div>

                                <div
                                    class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/70">
                                    <p class="text-xs uppercase tracking-wide text-gray-500">Avatar Voice</p>
                                    <strong
                                        id="avatarVoiceValue"
                                        class="mt-2 block text-sm font-semibold text-gray-900 dark:text-white/90">
                                        Ready
                                    </strong>
                                </div>

                                <div
                                    class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/70">
                                    <p class="text-xs uppercase tracking-wide text-gray-500">Body Language</p>
                                    <strong
                                        id="bodyLanguageValue"
                                        class="mt-2 block text-sm font-semibold text-gray-900 dark:text-white/90">
                                        Waiting
                                    </strong>
                                </div>

                                <div
                                    class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/70">
                                    <p class="text-xs uppercase tracking-wide text-gray-500">Facial Expression</p>
                                    <strong
                                        id="facialExpressionValue"
                                        class="mt-2 block text-sm font-semibold text-gray-900 dark:text-white/90">
                                        Waiting
                                    </strong>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Live Presence Coaching</p>
                                        <strong
                                            id="livePresenceSummary"
                                            class="mt-2 block text-sm font-semibold text-gray-900 dark:text-white/90">
                                            Camera coaching is waiting
                                        </strong>
                                    </div>

                                    <span
                                        id="livePresenceTag"
                                        class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-medium text-gray-700 shadow-theme-xs dark:bg-gray-800 dark:text-gray-300">
                                        Standby
                                    </span>
                                </div>

                                <p id="livePresenceTip" class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-400">
                                    Start the camera to unlock selected non-verbal coaching for eye contact, posture,
                                    head movement, and facial composure.
                                </p>
                            </div>

                            <div class="grid gap-4 xl:grid-cols-2">
                                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                                    <div class="flex items-center justify-between gap-3">
                                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white/90">Body Language Algorithms</h4>
                                        <span class="text-xs uppercase tracking-wide text-gray-500">3+ checks</span>
                                    </div>

                                    <div id="bodyLanguageAlgorithms" class="mt-4 space-y-3"></div>
                                </div>

                                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                                    <div class="flex items-center justify-between gap-3">
                                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white/90">Facial Presence Algorithms</h4>
                                        <span class="text-xs uppercase tracking-wide text-gray-500">3+ checks</span>
                                    </div>

                                    <div id="facialExpressionAlgorithms" class="mt-4 space-y-3"></div>
                                </div>
                            </div>
                        </div>
                    </article>

                    <article
                        id="practiceQuestionAgentSection"
                        class="flex h-full flex-col rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                        <div
                            class="flex flex-col gap-4 border-b border-gray-200 pb-5 dark:border-gray-800 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">AI Question Chatbot</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Generates fresh category-based questions and keeps the active prompt synced with the interviewer voice.
                                </p>
                            </div>

                            <span
                                id="practiceQuestionAgentStatusTag"
                                class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                Waiting
                            </span>
                        </div>

                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            <div
                                class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/70">
                                <p class="text-xs uppercase tracking-wide text-gray-500">Question Source</p>
                                <strong
                                    id="practiceQuestionAgentSourceValue"
                                    class="mt-2 block text-sm font-semibold text-gray-900 dark:text-white/90">
                                    Awaiting category
                                </strong>
                            </div>

                            <div
                                class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/70">
                                <label
                                    for="practiceQuestionAgentProviderSelect"
                                    class="block text-xs uppercase tracking-wide text-gray-500">
                                    Provider
                                </label>
                                <select
                                    id="practiceQuestionAgentProviderSelect"
                                    class="dark:bg-dark-900 mt-2 h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                </select>
                                <p
                                    id="practiceQuestionAgentProviderValue"
                                    class="content-break mt-2 text-xs leading-5 text-gray-500 dark:text-gray-400">
                                    Current route: Auto
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Conversation</p>
                            <div id="practiceQuestionAgentMessages" class="mt-3 flex min-h-[160px] max-h-[240px] flex-col gap-4 overflow-y-auto pr-1 sm:min-h-[180px] sm:max-h-[260px]"></div>
                        </div>

                        <div class="mt-5">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Quick Actions</p>
                            <div id="practiceQuestionAgentQuickActions" class="mt-3 flex flex-wrap gap-2"></div>
                        </div>

                        <div class="mt-5 rounded-2xl border border-dashed border-gray-300 px-4 py-4 dark:border-gray-700">
                            <label for="practiceQuestionAgentInput" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Instruction For The Chatbot
                            </label>
                            <textarea
                                id="practiceQuestionAgentInput"
                                rows="4"
                                class="mt-3 min-h-[120px] w-full rounded-2xl border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                placeholder="Example: Generate harder job interview questions for a fresh graduate applying for a remote role."></textarea>

                            <div class="mt-4 flex flex-wrap gap-3">
                                <button
                                    id="practiceQuestionAgentGenerateBtn"
                                    type="button"
                                    class="inline-flex w-full items-center justify-center rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto">
                                    Generate Questions
                                </button>

                                <button
                                    id="practiceQuestionAgentRegenerateBtn"
                                    type="button"
                                    class="inline-flex w-full items-center justify-center rounded-lg border border-gray-300 px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03] sm:w-auto">
                                    Regenerate Set
                                </button>
                            </div>

                            <p id="practiceQuestionAgentSummaryText" class="content-break mt-3 text-sm leading-6 text-gray-500 dark:text-gray-400">
                                Select a category and the chatbot will build a fresh question set for the workspace.
                            </p>
                        </div>
                    </article>
                </section>
            </div>
        </div>
    </div>
</div>
