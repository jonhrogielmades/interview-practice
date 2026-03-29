import {
    PRACTICE_SESSIONS_UPDATED_EVENT,
    clearPracticeSessions,
    readPracticeSessions,
    removePracticeSession
} from "./practice-storage";
import { normalizeFeedbackSummary } from "./feedback-utils";

export function initFeedbackCenter() {
    const app = document.getElementById("feedbackCenterApp");

    if (!app) {
        return;
    }

    const elements = {
        entryCountValue: document.getElementById("feedbackEntryCountValue"),
        averageScoreValue: document.getElementById("feedbackAverageScoreValue"),
        topCategoryValue: document.getElementById("feedbackTopCategoryValue"),
        sessionCountValue: document.getElementById("feedbackSessionCountValue"),
        searchInput: document.getElementById("feedbackSearchInput"),
        categoryFilter: document.getElementById("feedbackCategoryFilter"),
        typeFilter: document.getElementById("feedbackTypeFilter"),
        clearHistoryBtn: document.getElementById("feedbackClearHistoryBtn"),
        status: document.getElementById("feedbackCenterStatus"),
        insightSummary: document.getElementById("feedbackInsightSummary"),
        strengthList: document.getElementById("feedbackStrengthList"),
        improvementList: document.getElementById("feedbackImprovementList"),
        evaluationSection: document.getElementById("feedbackEvaluationSection"),
        evaluationMeta: document.getElementById("feedbackEvaluationsMeta"),
        evaluationList: document.getElementById("feedbackEvaluationList"),
        sessionSection: document.getElementById("feedbackSessionSection"),
        sessionsMeta: document.getElementById("feedbackSessionsMeta"),
        sessionList: document.getElementById("feedbackSessionList")
    };

    let sessions = [];
    let isMutating = false;

    loadData();
    renderAll();

    elements.searchInput.addEventListener("input", () => {
        hideStatus();
        renderAll();
    });

    elements.categoryFilter.addEventListener("change", () => {
        hideStatus();
        renderAll();
    });

    elements.typeFilter.addEventListener("change", () => {
        hideStatus();
        renderAll();
    });

    window.addEventListener(PRACTICE_SESSIONS_UPDATED_EVENT, () => {
        hideStatus();
        loadData();
        renderAll();
    });

    elements.evaluationList.addEventListener("click", async (event) => {
        const deleteButton = event.target.closest("[data-delete-session-id]");

        if (!deleteButton || isMutating) {
            return;
        }

        await handleDeleteSession(
            deleteButton.dataset.deleteSessionId,
            "Delete this saved session and its linked AI evaluations?"
        );
    });

    elements.sessionList.addEventListener("click", async (event) => {
        const deleteButton = event.target.closest("[data-delete-session-id]");

        if (!deleteButton || isMutating) {
            return;
        }

        await handleDeleteSession(
            deleteButton.dataset.deleteSessionId,
            "Delete this saved session and all of its feedback history?"
        );
    });

    elements.clearHistoryBtn.addEventListener("click", async () => {
        if (isMutating) {
            return;
        }

        if (!sessions.length) {
            showStatus("info", "There is no saved feedback history to clear.");
            return;
        }

        if (!window.confirm("Clear all saved evaluations, feedback, and session history?")) {
            return;
        }

        try {
            setMutating(true);
            await clearPracticeSessions();
            loadData();
            renderAll();
            showStatus("success", "Saved feedback history was cleared.");
        } catch (error) {
            console.error(error);
            showStatus("warning", "Saved feedback history could not be cleared right now.");
        } finally {
            setMutating(false);
        }
    });

    function average(values) {
        if (!values.length) {
            return 0;
        }

        return Number((values.reduce((sum, value) => sum + value, 0) / values.length).toFixed(1));
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

    function toHundredScale(score) {
        return Math.round((Number(score) || 0) * 10);
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

        [
            ...elements.evaluationList.querySelectorAll("[data-delete-session-id]"),
            ...elements.sessionList.querySelectorAll("[data-delete-session-id]")
        ].forEach((button) => {
            button.disabled = isMutating;
            button.classList.toggle("cursor-not-allowed", isMutating);
            button.classList.toggle("opacity-60", isMutating);
        });
    }

    async function handleDeleteSession(sessionId, confirmMessage) {
        if (!sessionId) {
            showStatus("warning", "That saved session could not be identified.");
            return;
        }

        if (!window.confirm(confirmMessage)) {
            return;
        }

        try {
            setMutating(true);
            await removePracticeSession(sessionId);
            loadData();
            renderAll();
            showStatus("success", "The saved session and its AI feedback were deleted.");
        } catch (error) {
            console.error(error);
            showStatus("warning", "That saved session could not be deleted right now.");
        } finally {
            setMutating(false);
        }
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

    function normalizeSession(session) {
        const criteriaAverages = {
            clarity: Number(session.criteriaAverages?.clarity) || 0,
            relevance: Number(session.criteriaAverages?.relevance) || 0,
            grammar: Number(session.criteriaAverages?.grammar) || 0,
            professionalism: Number(session.criteriaAverages?.professionalism) || 0
        };
        const weakDimension = getWeakestCriterion(criteriaAverages);
        const sessionSearchText = [
            session.categoryName,
            session.categoryDescription,
            session.focusMode,
            session.pacingMode,
            weakDimension,
            session.completed ? "completed" : "partial"
        ]
            .filter(Boolean)
            .join(" ")
            .toLowerCase();

        const answers = (Array.isArray(session.answers) ? session.answers : []).map((answer) => {
            const scoreData = {
                clarity: Number(answer.clarity) || 0,
                relevance: Number(answer.relevance) || 0,
                grammar: Number(answer.grammar) || 0,
                professionalism: Number(answer.professionalism) || 0,
                average: Number(answer.average) || 0,
                matchedKeywords: Number(answer.matchedKeywords) || 0
            };
            const feedbackSummary = normalizeFeedbackSummary(answer.answer, scoreData, answer.feedbackSummary);
            const searchText = [
                session.categoryName,
                session.focusMode,
                session.pacingMode,
                answer.question,
                answer.answer,
                answer.inputMode,
                ...feedbackSummary.strengths,
                ...feedbackSummary.improvements
            ]
                .filter(Boolean)
                .join(" ")
                .toLowerCase();

            return {
                sessionId: session.id,
                sessionSavedAt: session.savedAt,
                categoryName: session.categoryName || "Unknown Category",
                focusMode: session.focusMode || "Balanced Coach",
                pacingMode: session.pacingMode || "Standard",
                questionNumber: Number(answer.questionNumber) || 0,
                question: answer.question || "Untitled question",
                answer: answer.answer || "",
                inputMode: answer.inputMode || "Text",
                elapsedSeconds: Number(answer.elapsedSeconds) || 0,
                ...scoreData,
                feedbackSummary,
                searchText
            };
        });

        return {
            ...session,
            categoryName: session.categoryName || "Unknown Category",
            criteriaAverages,
            weakDimension,
            answers,
            searchText: `${sessionSearchText} ${answers.map((answer) => answer.searchText).join(" ")}`.trim()
        };
    }

    function loadData() {
        sessions = readPracticeSessions().map(normalizeSession);
        populateCategoryFilter();
    }

    function populateCategoryFilter() {
        const previousValue = elements.categoryFilter.value || "all";
        const categories = Array.from(new Set(sessions.map((session) => session.categoryName)))
            .sort((left, right) => left.localeCompare(right));

        elements.categoryFilter.innerHTML = [
            '<option value="all">All Categories</option>',
            ...categories.map((category) => `<option value="${escapeHtml(category)}">${escapeHtml(category)}</option>`)
        ].join("");

        const nextValue = categories.includes(previousValue) ? previousValue : "all";
        elements.categoryFilter.value = nextValue;
    }

    function getFilteredSessions() {
        const search = elements.searchInput.value.trim().toLowerCase();
        const category = elements.categoryFilter.value;

        return sessions.filter((session) => {
            const matchesCategory = category === "all" || session.categoryName === category;
            const matchesSearch = !search || session.searchText.includes(search);

            return matchesCategory && matchesSearch;
        });
    }

    function getFilteredEvaluations(filteredSessions) {
        const search = elements.searchInput.value.trim().toLowerCase();

        return filteredSessions
            .flatMap((session) => session.answers)
            .filter((evaluation) => !search || evaluation.searchText.includes(search))
            .sort((left, right) => {
                const savedAtDiff = new Date(right.sessionSavedAt || 0) - new Date(left.sessionSavedAt || 0);
                if (savedAtDiff !== 0) {
                    return savedAtDiff;
                }

                return (Number(left.questionNumber) || 0) - (Number(right.questionNumber) || 0);
            });
    }

    function getTopCategory(filteredEvaluations, filteredSessions) {
        const grouped = new Map();

        if (filteredEvaluations.length) {
            filteredEvaluations.forEach((evaluation) => {
                const current = grouped.get(evaluation.categoryName) || { count: 0, total: 0 };
                current.count += 1;
                current.total += Number(evaluation.average) || 0;
                grouped.set(evaluation.categoryName, current);
            });
        } else {
            filteredSessions.forEach((session) => {
                const current = grouped.get(session.categoryName) || { count: 0, total: 0 };
                current.count += 1;
                current.total += Number(session.averageScore) || 0;
                grouped.set(session.categoryName, current);
            });
        }

        return Array.from(grouped.entries())
            .map(([categoryName, item]) => ({
                categoryName,
                count: item.count,
                averageScore: item.count ? item.total / item.count : 0
            }))
            .sort((left, right) => right.averageScore - left.averageScore || right.count - left.count || left.categoryName.localeCompare(right.categoryName))[0]?.categoryName ?? "None";
    }

    function renderSectionVisibility() {
        const view = elements.typeFilter.value;
        const showEvaluations = view !== "sessions";
        const showSessions = view !== "evaluations";

        elements.evaluationSection.classList.toggle("hidden", !showEvaluations);
        elements.sessionSection.classList.toggle("hidden", !showSessions);
        elements.evaluationSection.classList.toggle("xl:col-span-12", showEvaluations && !showSessions);
        elements.sessionSection.classList.toggle("xl:col-span-12", showSessions && !showEvaluations);
    }

    function renderRankedList(items, emptyText) {
        if (!items.length) {
            return `
                <li class="rounded-xl border border-dashed border-gray-300 px-4 py-3 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    ${escapeHtml(emptyText)}
                </li>
            `;
        }

        return items.map((item) => `
            <li class="flex items-start justify-between gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm dark:border-gray-800 dark:bg-gray-900/70">
                <span class="leading-6 text-gray-700 dark:text-gray-300">${escapeHtml(item.label)}</span>
                <span class="rounded-full bg-white px-2.5 py-1 text-xs font-medium text-gray-700 shadow-theme-xs dark:bg-gray-800 dark:text-gray-300">${item.count}</span>
            </li>
        `).join("");
    }

    function renderDeleteSessionButton(sessionId) {
        return `
            <button
                type="button"
                data-delete-session-id="${escapeHtml(sessionId)}"
                class="inline-flex items-center justify-center rounded-lg border border-rose-200 px-3 py-2 text-xs font-medium text-rose-600 transition hover:bg-rose-50 dark:border-rose-500/30 dark:text-rose-300 dark:hover:bg-rose-500/10"
                ${isMutating ? "disabled" : ""}
            >
                Delete Session
            </button>
        `;
    }

    function renderSummaryCards(filteredSessions, filteredEvaluations) {
        const averageScore = filteredEvaluations.length
            ? average(filteredEvaluations.map((evaluation) => evaluation.average))
            : average(filteredSessions.map((session) => Number(session.averageScore) || 0));

        elements.entryCountValue.textContent = String(filteredEvaluations.length);
        elements.averageScoreValue.textContent = `${toHundredScale(averageScore)}/100`;
        elements.topCategoryValue.textContent = getTopCategory(filteredEvaluations, filteredSessions);
        elements.sessionCountValue.textContent = String(filteredSessions.length);
    }

    function renderDigest(filteredSessions, filteredEvaluations) {
        if (!filteredSessions.length) {
            elements.insightSummary.textContent = "Start a practice session and submit an answer to generate feedback insights here.";
            elements.strengthList.innerHTML = renderRankedList([], "No saved strengths yet.");
            elements.improvementList.innerHTML = renderRankedList([], "No saved improvement areas yet.");
            return;
        }

        const strengthCounts = new Map();
        const improvementCounts = new Map();

        filteredEvaluations.forEach((evaluation) => {
            evaluation.feedbackSummary.strengths.forEach((strength) => {
                strengthCounts.set(strength, (strengthCounts.get(strength) || 0) + 1);
            });

            evaluation.feedbackSummary.improvements.forEach((improvement) => {
                improvementCounts.set(improvement, (improvementCounts.get(improvement) || 0) + 1);
            });
        });

        const topStrengths = Array.from(strengthCounts.entries())
            .map(([label, count]) => ({ label, count }))
            .sort((left, right) => right.count - left.count || left.label.localeCompare(right.label))
            .slice(0, 3);
        const topImprovements = Array.from(improvementCounts.entries())
            .map(([label, count]) => ({ label, count }))
            .sort((left, right) => right.count - left.count || left.label.localeCompare(right.label))
            .slice(0, 3);
        const topCategory = getTopCategory(filteredEvaluations, filteredSessions);

        elements.insightSummary.textContent = `You have ${filteredEvaluations.length} saved evaluation${filteredEvaluations.length === 1 ? "" : "s"} across ${filteredSessions.length} session${filteredSessions.length === 1 ? "" : "s"} in this view. ${topCategory} is the current top category, while ${formatDimension(filteredSessions[0]?.weakDimension)} appears most recently as a coaching focus.`;
        elements.strengthList.innerHTML = renderRankedList(topStrengths, "No repeated strengths found yet.");
        elements.improvementList.innerHTML = renderRankedList(topImprovements, "No repeated improvement areas found yet.");
    }

    function renderEvaluations(filteredEvaluations) {
        elements.evaluationMeta.textContent = `${filteredEvaluations.length} item${filteredEvaluations.length === 1 ? "" : "s"}`;

        if (!filteredEvaluations.length) {
            elements.evaluationList.innerHTML = renderEmptyState(
                "No saved evaluations",
                "Submit an answer on the Practice page to store evaluation feedback here."
            );
            return;
        }

        elements.evaluationList.innerHTML = filteredEvaluations.map((evaluation) => `
            <article class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                <div class="flex flex-col gap-3 border-b border-gray-200 pb-4 dark:border-gray-800 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-gray-700 shadow-theme-xs dark:bg-gray-800 dark:text-gray-300">
                                ${escapeHtml(evaluation.categoryName)}
                            </span>
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-gray-700 shadow-theme-xs dark:bg-gray-800 dark:text-gray-300">
                                Question ${Number(evaluation.questionNumber) || 0}
                            </span>
                        </div>
                        <h4 class="mt-3 text-sm font-semibold text-gray-900 dark:text-white/90">${escapeHtml(evaluation.question)}</h4>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">${formatDate(evaluation.sessionSavedAt, true)} with ${escapeHtml(evaluation.focusMode)}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                            ${toHundredScale(evaluation.average)}/100
                        </span>
                        ${renderDeleteSessionButton(evaluation.sessionId)}
                    </div>
                </div>

                <div class="mt-4 grid gap-4 lg:grid-cols-[1.1fr_0.9fr]">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Saved Answer</p>
                        <p class="mt-2 text-sm leading-7 text-gray-600 dark:text-gray-400">${escapeHtml(evaluation.answer || "No saved answer text.")}</p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-1">
                        <div class="rounded-xl border border-gray-200 bg-white px-3 py-3 dark:border-gray-800 dark:bg-gray-950/40">
                            <p class="text-xs uppercase tracking-wide text-gray-500">Input Mode</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white/90">${escapeHtml(evaluation.inputMode)}</p>
                        </div>
                        <div class="rounded-xl border border-gray-200 bg-white px-3 py-3 dark:border-gray-800 dark:bg-gray-950/40">
                            <p class="text-xs uppercase tracking-wide text-gray-500">Elapsed</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white/90">${formatDuration(evaluation.elapsedSeconds)}</p>
                        </div>
                        <div class="rounded-xl border border-gray-200 bg-white px-3 py-3 dark:border-gray-800 dark:bg-gray-950/40">
                            <p class="text-xs uppercase tracking-wide text-gray-500">Matched Keywords</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white/90">${Number(evaluation.matchedKeywords) || 0}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950/40">
                        <h5 class="text-sm font-semibold text-gray-900 dark:text-white/90">Strengths</h5>
                        <ul class="mt-3 space-y-2">
                            ${evaluation.feedbackSummary.strengths.map((item) => `
                                <li class="rounded-xl bg-success-50 px-3 py-2 text-sm leading-6 text-success-700 dark:bg-success-500/10 dark:text-success-300">${escapeHtml(item)}</li>
                            `).join("")}
                        </ul>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950/40">
                        <h5 class="text-sm font-semibold text-gray-900 dark:text-white/90">Improvement Areas</h5>
                        <ul class="mt-3 space-y-2">
                            ${evaluation.feedbackSummary.improvements.map((item) => `
                                <li class="rounded-xl bg-amber-50 px-3 py-2 text-sm leading-6 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">${escapeHtml(item)}</li>
                            `).join("")}
                        </ul>
                    </div>
                </div>
            </article>
        `).join("");
    }

    function renderSessions(filteredSessions) {
        elements.sessionsMeta.textContent = `${filteredSessions.length} item${filteredSessions.length === 1 ? "" : "s"}`;

        if (!filteredSessions.length) {
            elements.sessionList.innerHTML = renderEmptyState(
                "No saved sessions",
                "Finish and save a practice session to see full-session feedback history here."
            );
            return;
        }

        elements.sessionList.innerHTML = filteredSessions.map((session) => `
            <article class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white/90">${escapeHtml(session.categoryName)}</h4>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">${formatDate(session.savedAt, true)}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                            ${toHundredScale(session.averageScore)}/100 avg
                        </span>
                        ${renderDeleteSessionButton(session.id)}
                    </div>
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-gray-200 bg-white px-3 py-3 dark:border-gray-800 dark:bg-gray-950/40">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Coach Mode</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white/90">${escapeHtml(session.focusMode)}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white px-3 py-3 dark:border-gray-800 dark:bg-gray-950/40">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Pacing</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white/90">${escapeHtml(session.pacingMode)}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white px-3 py-3 dark:border-gray-800 dark:bg-gray-950/40">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Saved Answers</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white/90">${session.answers.length}/${Number(session.questionCount) || session.answers.length}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white px-3 py-3 dark:border-gray-800 dark:bg-gray-950/40">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Focus Area</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white/90">${escapeHtml(formatDimension(session.weakDimension))}</p>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-between gap-3 text-sm">
                    <span class="text-gray-500 dark:text-gray-400">${session.completed ? "Completed session" : "Partial session"}</span>
                    <a href="/session-review" class="font-medium text-brand-600 hover:text-brand-700 dark:text-brand-300">
                        Open Session Review
                    </a>
                </div>
            </article>
        `).join("");
    }

    function renderAll() {
        const filteredSessions = getFilteredSessions();
        const filteredEvaluations = getFilteredEvaluations(filteredSessions);

        renderSectionVisibility();
        renderSummaryCards(filteredSessions, filteredEvaluations);
        renderDigest(filteredSessions, filteredEvaluations);
        renderEvaluations(filteredEvaluations);
        renderSessions(filteredSessions);
        updateActionState();
    }
}
