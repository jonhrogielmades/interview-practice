import {
    PRACTICE_SESSIONS_UPDATED_EVENT,
    clearPracticeSessions,
    readPracticeSessions,
    removePracticeSession
} from "./practice-storage";

export function initSessionReview() {
    const app = document.getElementById("sessionReviewApp");

    if (!app) {
        return;
    }

    const elements = {
        totalSessions: document.getElementById("sessionReviewTotalSessions"),
        totalQuestions: document.getElementById("sessionReviewTotalQuestions"),
        bestScore: document.getElementById("sessionReviewBestScore"),
        focusDimension: document.getElementById("sessionReviewFocusDimension"),
        search: document.getElementById("sessionReviewSearch"),
        categoryFilter: document.getElementById("sessionReviewCategoryFilter"),
        sort: document.getElementById("sessionReviewSort"),
        clearHistoryBtn: document.getElementById("sessionReviewClearHistoryBtn"),
        status: document.getElementById("sessionReviewStatus"),
        listMeta: document.getElementById("sessionReviewListMeta"),
        sessionList: document.getElementById("sessionReviewSessionList"),
        detail: document.getElementById("sessionReviewDetail")
    };

    let sessions = [];
    let categoryOptions = [];
    let selectedSessionId = null;
    let isMutating = false;

    hydrateData();
    selectedSessionId = sessions[0]?.id ?? null;
    populateCategoryFilter();
    renderSummaryMetrics();
    renderSessionList();
    renderDetail();

    elements.search.addEventListener("input", () => {
        renderSessionList();
        renderDetail();
    });

    elements.categoryFilter.addEventListener("change", () => {
        renderSessionList();
        renderDetail();
    });

    elements.sort.addEventListener("change", () => {
        renderSessionList();
        renderDetail();
    });

    elements.clearHistoryBtn.addEventListener("click", async () => {
        if (isMutating) {
            return;
        }

        if (!sessions.length) {
            showStatus("info", "There are no saved sessions to clear.");
            return;
        }

        if (!window.confirm("Clear all saved sessions and their AI feedback history?")) {
            return;
        }

        try {
            setMutating(true);
            await clearPracticeSessions();
            selectedSessionId = null;
            hydrateData();
            populateCategoryFilter();
            renderSummaryMetrics();
            renderSessionList();
            renderDetail();
            showStatus("success", "All saved sessions were cleared.");
        } catch (error) {
            console.error(error);
            showStatus("warning", "Saved sessions could not be cleared right now.");
        } finally {
            setMutating(false);
        }
    });

    elements.detail.addEventListener("click", async (event) => {
        const deleteButton = event.target.closest("[data-delete-session-id]");

        if (!deleteButton || isMutating) {
            return;
        }

        const sessionId = deleteButton.dataset.deleteSessionId;
        const session = sessions.find((item) => item.id === sessionId);

        if (!window.confirm(`Delete the saved session for ${session?.categoryName || "this category"} and all linked answer feedback?`)) {
            return;
        }

        try {
            setMutating(true);
            await removePracticeSession(sessionId);
            hydrateData();
            selectedSessionId = sessions[0]?.id ?? null;
            populateCategoryFilter();
            renderSummaryMetrics();
            renderSessionList();
            renderDetail();
            showStatus("success", "The selected saved session was deleted.");
        } catch (error) {
            console.error(error);
            showStatus("warning", "That saved session could not be deleted right now.");
        } finally {
            setMutating(false);
        }
    });

    window.addEventListener(PRACTICE_SESSIONS_UPDATED_EVENT, () => {
        hideStatus();
        hydrateData();
        populateCategoryFilter();
        renderSummaryMetrics();
        renderSessionList();
        renderDetail();
    });

    function average(values) {
        if (!values.length) {
            return 0;
        }

        return Number((values.reduce((sum, value) => sum + value, 0) / values.length).toFixed(1));
    }

    function hydrateData() {
        sessions = readPracticeSessions().map(normalizeSession);
        categoryOptions = Array.from(new Set(sessions.map((session) => session.categoryName))).sort((left, right) =>
            left.localeCompare(right)
        );
    }

    function escapeHtml(value) {
        return String(value ?? "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");
    }

    function formatDate(dateValue, withTime = false) {
        const formatter = new Intl.DateTimeFormat("en-US", withTime ? {
            month: "short",
            day: "numeric",
            year: "numeric",
            hour: "numeric",
            minute: "2-digit"
        } : {
            month: "short",
            day: "numeric",
            year: "numeric"
        });

        return formatter.format(new Date(dateValue));
    }

    function formatDuration(totalSeconds = 0) {
        const minutes = Math.floor(totalSeconds / 60);
        const seconds = totalSeconds % 60;

        return `${String(minutes).padStart(2, "0")}:${String(seconds).padStart(2, "0")}`;
    }

    function formatDimension(label) {
        if (!label) {
            return "None";
        }

        return label.charAt(0).toUpperCase() + label.slice(1);
    }

    function renderEmptyState(title, body) {
        return `
            <div class="rounded-2xl border border-dashed border-gray-300 px-5 py-10 text-center dark:border-gray-700">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">${escapeHtml(title)}</h3>
                <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">${escapeHtml(body)}</p>
            </div>
        `;
    }

    function showStatus(type, text) {
        const styles = {
            success: "border-success-200 bg-success-50 text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-300",
            info: "border-gray-200 bg-gray-50 text-gray-700 dark:border-gray-700 dark:bg-gray-900/70 dark:text-gray-300",
            warning: "border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300"
        };

        elements.status.className = `mt-4 rounded-xl border px-4 py-3 text-sm ${styles[type] || styles.info}`;
        elements.status.textContent = text;
        elements.status.classList.remove("hidden");
    }

    function hideStatus() {
        elements.status.classList.add("hidden");
        elements.status.textContent = "";
    }

    function setMutating(nextValue) {
        isMutating = nextValue;
        updateActionState();
    }

    function updateActionState() {
        elements.clearHistoryBtn.disabled = isMutating || sessions.length === 0;
        elements.clearHistoryBtn.classList.toggle("cursor-not-allowed", elements.clearHistoryBtn.disabled);
        elements.clearHistoryBtn.classList.toggle("opacity-60", elements.clearHistoryBtn.disabled);

        elements.detail.querySelectorAll("[data-delete-session-id]").forEach((button) => {
            button.disabled = isMutating;
            button.classList.toggle("cursor-not-allowed", isMutating);
            button.classList.toggle("opacity-60", isMutating);
        });
    }

    function getCriteriaEntries(criteria) {
        return [
            ["clarity", Number(criteria?.clarity) || 0],
            ["relevance", Number(criteria?.relevance) || 0],
            ["grammar", Number(criteria?.grammar) || 0],
            ["professionalism", Number(criteria?.professionalism) || 0]
        ];
    }

    function getWeakestCriterion(criteria) {
        return getCriteriaEntries(criteria)
            .sort((left, right) => left[1] - right[1] || left[0].localeCompare(right[0]))[0]?.[0] ?? null;
    }

    function getStrongestCriterion(criteria) {
        return getCriteriaEntries(criteria)
            .sort((left, right) => right[1] - left[1] || left[0].localeCompare(right[0]))[0]?.[0] ?? null;
    }

    function normalizeSession(session) {
        const answers = Array.isArray(session.answers) ? session.answers : [];
        const criteriaAverages = {
            clarity: Number(session.criteriaAverages?.clarity) || 0,
            relevance: Number(session.criteriaAverages?.relevance) || 0,
            grammar: Number(session.criteriaAverages?.grammar) || 0,
            professionalism: Number(session.criteriaAverages?.professionalism) || 0
        };
        const weakDimension = getWeakestCriterion(criteriaAverages);
        const strongDimension = getStrongestCriterion(criteriaAverages);
        const totalElapsedSeconds = answers.reduce((sum, answer) => sum + (Number(answer.elapsedSeconds) || 0), 0);
        const searchText = [
            session.categoryName,
            session.categoryDescription,
            session.focusMode,
            session.pacingMode,
            weakDimension,
            strongDimension,
            ...answers.flatMap((answer) => [answer.question, answer.answer, answer.inputMode])
        ]
            .filter(Boolean)
            .join(" ")
            .toLowerCase();

        return {
            ...session,
            answers,
            criteriaAverages,
            weakDimension,
            strongDimension,
            totalElapsedSeconds,
            savedAnswerCount: answers.length || Number(session.answeredCount) || 0,
            searchText
        };
    }

    function populateCategoryFilter() {
        elements.categoryFilter.innerHTML = [
            '<option value="">All Categories</option>',
            ...categoryOptions.map((category) => `<option value="${escapeHtml(category)}">${escapeHtml(category)}</option>`)
        ].join("");
    }

    function renderSummaryMetrics() {
        const totalQuestions = sessions.reduce((sum, session) => sum + session.savedAnswerCount, 0);
        const bestScore = sessions.length
            ? Math.max(...sessions.map((session) => Number(session.averageScore) || 0))
            : 0;
        const weakDimensionCounts = sessions.reduce((counts, session) => {
            if (session.weakDimension) {
                counts[session.weakDimension] = (counts[session.weakDimension] || 0) + 1;
            }

            return counts;
        }, {});
        const focusDimension = Object.entries(weakDimensionCounts)
            .sort((left, right) => right[1] - left[1] || left[0].localeCompare(right[0]))[0]?.[0] ?? null;

        elements.totalSessions.textContent = String(sessions.length);
        elements.totalQuestions.textContent = String(totalQuestions);
        elements.bestScore.textContent = sessions.length ? `${bestScore.toFixed(1)}/10` : "0";
        elements.focusDimension.textContent = formatDimension(focusDimension);
    }

    function getFilteredSessions() {
        const search = elements.search.value.trim().toLowerCase();
        const category = elements.categoryFilter.value;
        const sort = elements.sort.value;

        const filtered = sessions.filter((session) => {
            const matchesSearch = !search || session.searchText.includes(search);
            const matchesCategory = !category || session.categoryName === category;

            return matchesSearch && matchesCategory;
        });

        filtered.sort((left, right) => {
            if (sort === "score") {
                return (Number(right.averageScore) || 0) - (Number(left.averageScore) || 0);
            }

            if (sort === "category") {
                return String(left.categoryName).localeCompare(String(right.categoryName))
                    || new Date(right.savedAt || 0) - new Date(left.savedAt || 0);
            }

            return new Date(right.savedAt || 0) - new Date(left.savedAt || 0);
        });

        return filtered;
    }

    function renderSessionList() {
        const filtered = getFilteredSessions();

        if (!filtered.some((session) => session.id === selectedSessionId)) {
            selectedSessionId = filtered[0]?.id ?? null;
        }

        elements.listMeta.textContent = `${filtered.length} saved session${filtered.length === 1 ? "" : "s"}`;

        if (!filtered.length) {
            elements.sessionList.innerHTML = renderEmptyState(
                "No matching sessions",
                "Try a different filter or complete another practice session."
            );
            return;
        }

        elements.sessionList.innerHTML = filtered.map((session) => {
            const isActive = session.id === selectedSessionId;
            const weakLabel = formatDimension(session.weakDimension);

            return `
                <button
                    type="button"
                    data-session-id="${escapeHtml(session.id)}"
                    class="${isActive ? "border-brand-300 bg-brand-50 dark:border-brand-500/30 dark:bg-brand-500/10" : "border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-900/70"} w-full rounded-2xl border p-4 text-left transition hover:border-brand-300 hover:bg-brand-50 dark:hover:border-brand-500/30 dark:hover:bg-brand-500/10"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white/90">${escapeHtml(session.categoryName)}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">${formatDate(session.savedAt, true)}</p>
                        </div>
                        <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-brand-600 shadow-theme-xs dark:bg-gray-800 dark:text-brand-300">
                            ${Number(session.averageScore).toFixed(1)}/10
                        </span>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-xl border border-white/70 bg-white/80 px-3 py-3 dark:border-white/10 dark:bg-gray-950/40">
                            <p class="text-xs uppercase tracking-wide text-gray-500">Focus</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white/90">${escapeHtml(session.focusMode)}</p>
                        </div>
                        <div class="rounded-xl border border-white/70 bg-white/80 px-3 py-3 dark:border-white/10 dark:bg-gray-950/40">
                            <p class="text-xs uppercase tracking-wide text-gray-500">Weak Spot</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white/90">${escapeHtml(weakLabel)}</p>
                        </div>
                    </div>

                    <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                        ${session.savedAnswerCount} saved answer${session.savedAnswerCount === 1 ? "" : "s"} with ${escapeHtml(session.pacingMode)} pacing.
                    </p>
                </button>
            `;
        }).join("");

        elements.sessionList.querySelectorAll("[data-session-id]").forEach((button) => {
            button.addEventListener("click", () => {
                selectedSessionId = button.dataset.sessionId;
                renderSessionList();
                renderDetail();
            });
        });

        updateActionState();
    }

    function renderDetail() {
        const filtered = getFilteredSessions();

        if (!filtered.length) {
            elements.detail.innerHTML = renderEmptyState(
                "No session selected",
                "Saved sessions will appear here with question-by-question detail."
            );
            updateActionState();
            return;
        }

        const session = filtered.find((item) => item.id === selectedSessionId) || filtered[0];
        const weakAdvice = {
            clarity: "Tighten your structure so each answer lands with a clearer opening, example, and closing thought.",
            relevance: "Pull your examples closer to the exact prompt and mirror the language used in the question.",
            grammar: "Slow down slightly and polish sentence endings so ideas feel cleaner and easier to follow.",
            professionalism: "Use more confident, interview-ready wording that sounds deliberate instead of casual."
        };

        elements.detail.innerHTML = `
            <div class="space-y-6">
                <div class="flex flex-col gap-4 border-b border-gray-200 pb-6 dark:border-gray-800 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-3xl">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
                                ${session.completed ? "Completed Session" : "Partial Session"}
                            </span>
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                ${formatDate(session.savedAt, true)}
                            </span>
                        </div>

                        <h3 class="mt-4 text-xl font-semibold text-gray-900 dark:text-white/90">${escapeHtml(session.categoryName)}</h3>
                        <p class="mt-2 text-sm leading-7 text-gray-600 dark:text-gray-400">
                            ${escapeHtml(session.categoryDescription || "Review the full saved session, including each question, answer, and supporting interview signals.")}
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 sm:min-w-[220px]">
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 px-5 py-4 text-center dark:border-gray-800 dark:bg-gray-900/70">
                            <p class="text-xs uppercase tracking-wide text-gray-500">Session Average</p>
                            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white/90">${Number(session.averageScore).toFixed(1)}/10</p>
                        </div>
                        <a
                            href="/practice"
                            class="inline-flex items-center justify-center rounded-lg border border-brand-300 px-4 py-3 text-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:border-brand-500/40 dark:text-brand-300 dark:hover:bg-brand-500/10">
                            Practice Again
                        </a>
                        <button
                            type="button"
                            data-delete-session-id="${escapeHtml(session.id)}"
                            class="inline-flex items-center justify-center rounded-lg border border-rose-200 px-4 py-3 text-sm font-medium text-rose-600 transition hover:bg-rose-50 dark:border-rose-500/30 dark:text-rose-300 dark:hover:bg-rose-500/10 ${isMutating ? "cursor-not-allowed opacity-60" : ""}"
                            ${isMutating ? "disabled" : ""}
                        >
                            Delete Session
                        </button>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Saved Answers</p>
                        <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">${session.savedAnswerCount}/${Number(session.questionCount) || session.savedAnswerCount}</p>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Coach Mode</p>
                        <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">${escapeHtml(session.focusMode)}</p>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Pacing</p>
                        <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">${escapeHtml(session.pacingMode)}</p>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Review Focus</p>
                        <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">${escapeHtml(formatDimension(session.weakDimension))}</p>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-800 dark:bg-gray-900/70">
                        <h4 class="text-base font-semibold text-gray-900 dark:text-white/90">Criteria Snapshot</h4>
                        <div class="mt-5 space-y-4">
                            ${getCriteriaEntries(session.criteriaAverages).map(([label, value]) => `
                                <div>
                                    <div class="mb-1 flex items-center justify-between">
                                        <span class="text-sm capitalize text-gray-700 dark:text-gray-300">${escapeHtml(label)}</span>
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">${Number(value).toFixed(1)}/10</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-gray-200 dark:bg-gray-800">
                                        <div class="h-2 rounded-full bg-brand-500" style="width: ${Math.max(8, Math.min(100, Number(value) * 10))}%"></div>
                                    </div>
                                </div>
                            `).join("")}
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-800 dark:bg-gray-900/70">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white/90">Review Signal</h4>
                            <p class="mt-3 text-sm leading-7 text-gray-600 dark:text-gray-400">
                                Strongest dimension: <strong class="capitalize text-gray-900 dark:text-white/90">${escapeHtml(formatDimension(session.strongDimension))}</strong>.
                                Most likely next improvement area: <strong class="capitalize text-gray-900 dark:text-white/90">${escapeHtml(formatDimension(session.weakDimension))}</strong>.
                            </p>
                            <p class="mt-3 text-sm leading-7 text-gray-600 dark:text-gray-400">
                                ${escapeHtml(weakAdvice[session.weakDimension] || "Keep practicing to build more consistency across the full session.")}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-brand-100 bg-brand-50/70 p-5 dark:border-brand-500/20 dark:bg-brand-500/5">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white/90">Session Recap</h4>
                            <p class="mt-3 text-sm leading-7 text-gray-600 dark:text-gray-400">
                                ${session.savedAnswerCount} answer${session.savedAnswerCount === 1 ? "" : "s"} saved,
                                ${formatDuration(session.totalElapsedSeconds)} total recorded response time,
                                and an overall average of ${Number(session.averageScore).toFixed(1)}/10.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-800 dark:bg-gray-900/70">
                    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white/90">Question-by-Question Review</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Each saved answer is preserved with timing, input mode, and score breakdown.</p>
                        </div>
                        <a
                            href="/progress"
                            class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-300">
                            Open Progress
                        </a>
                    </div>

                    <div class="space-y-4">
                        ${session.answers.length ? session.answers.map((answer) => {
                            const answerCriteria = {
                                clarity: Number(answer.clarity) || 0,
                                relevance: Number(answer.relevance) || 0,
                                grammar: Number(answer.grammar) || 0,
                                professionalism: Number(answer.professionalism) || 0
                            };

                            return `
                                <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950/40">
                                    <div class="flex flex-col gap-3 border-b border-gray-200 pb-4 dark:border-gray-800 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <p class="text-xs uppercase tracking-wide text-gray-500">Question ${Number(answer.questionNumber) || 0}</p>
                                            <h5 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white/90">${escapeHtml(answer.question)}</h5>
                                        </div>
                                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                            ${Number(answer.average).toFixed(1)}/10 average
                                        </span>
                                    </div>

                                    <div class="mt-4 grid gap-4 lg:grid-cols-[1.2fr_0.8fr]">
                                        <div>
                                            <p class="text-xs uppercase tracking-wide text-gray-500">Your Answer</p>
                                            <p class="mt-2 text-sm leading-7 text-gray-600 dark:text-gray-400">${escapeHtml(answer.answer || "No answer saved.")}</p>
                                        </div>

                                        <div class="grid gap-3">
                                            <div class="rounded-xl bg-gray-50 px-3 py-3 dark:bg-gray-900">
                                                <p class="text-xs uppercase tracking-wide text-gray-500">Elapsed</p>
                                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white/90">${formatDuration(answer.elapsedSeconds || 0)}</p>
                                            </div>
                                            <div class="rounded-xl bg-gray-50 px-3 py-3 dark:bg-gray-900">
                                                <p class="text-xs uppercase tracking-wide text-gray-500">Input Mode</p>
                                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white/90">${escapeHtml(answer.inputMode || "Text")}</p>
                                            </div>
                                            <div class="rounded-xl bg-gray-50 px-3 py-3 dark:bg-gray-900">
                                                <p class="text-xs uppercase tracking-wide text-gray-500">Matched Keywords</p>
                                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white/90">${Number(answer.matchedKeywords) || 0}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                        ${getCriteriaEntries(answerCriteria).map(([label, value]) => `
                                            <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900">
                                                <div class="flex items-center justify-between gap-3">
                                                    <p class="text-xs uppercase tracking-wide text-gray-500">${escapeHtml(label)}</p>
                                                    <p class="text-sm font-semibold text-gray-900 dark:text-white/90">${Number(value).toFixed(1)}/10</p>
                                                </div>
                                                <div class="mt-3 h-2 rounded-full bg-gray-200 dark:bg-gray-800">
                                                    <div class="h-2 rounded-full bg-brand-500" style="width: ${Math.max(8, Math.min(100, Number(value) * 10))}%"></div>
                                                </div>
                                            </div>
                                        `).join("")}
                                    </div>
                                </div>
                            `;
                        }).join("") : renderEmptyState(
                            "No answer detail available",
                            "This session was saved without question-level answer data."
                        )}
                    </div>
                </div>
            </div>
        `;

        updateActionState();
    }
}
