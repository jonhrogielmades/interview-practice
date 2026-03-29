import { PRACTICE_SESSIONS_UPDATED_EVENT, readPracticeSessions } from "./practice-storage";

export function initCategoryInsights() {
    const app = document.getElementById("categoryInsightsApp");

    if (!app) {
        return;
    }

    const elements = {
        practiced: document.getElementById("categoryInsightsPracticed"),
        strongest: document.getElementById("categoryInsightsStrongest"),
        ready: document.getElementById("categoryInsightsReady"),
        need: document.getElementById("categoryInsightsNeed"),
        search: document.getElementById("categoryInsightsSearch"),
        sort: document.getElementById("categoryInsightsSort"),
        listMeta: document.getElementById("categoryInsightsListMeta"),
        list: document.getElementById("categoryInsightsList"),
        detail: document.getElementById("categoryInsightsDetail")
    };

    let sessions = [];
    let categories = [];
    let selectedKey = null;

    hydrateData();
    selectedKey = categories[0]?.key ?? null;
    renderSummaryMetrics();
    renderCategoryList();
    renderDetail();

    elements.search.addEventListener("input", () => {
        renderCategoryList();
        renderDetail();
    });

    elements.sort.addEventListener("change", () => {
        renderCategoryList();
        renderDetail();
    });

    window.addEventListener(PRACTICE_SESSIONS_UPDATED_EVENT, () => {
        hydrateData();
        renderSummaryMetrics();
        renderCategoryList();
        renderDetail();
    });

    function average(values) {
        if (!values.length) {
            return 0;
        }

        return Number((values.reduce((sum, value) => sum + value, 0) / values.length).toFixed(1));
    }

    function hydrateData() {
        sessions = readPracticeSessions();
        categories = buildCategories(sessions);
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

    function renderEmptyState(title, body) {
        return `
            <div class="rounded-2xl border border-dashed border-gray-300 px-5 py-10 text-center dark:border-gray-700">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">${escapeHtml(title)}</h3>
                <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">${escapeHtml(body)}</p>
            </div>
        `;
    }

    function buildCategories(items) {
        const grouped = new Map();

        items.forEach((session) => {
            const key = session.categoryId || session.categoryName || "unknown";
            const current = grouped.get(key) || {
                key,
                categoryId: session.categoryId || key,
                categoryName: session.categoryName || "Unknown Category",
                categoryDescription: session.categoryDescription || "Saved practice category insights.",
                sessions: [],
                totalScore: 0,
                answeredCount: 0,
                questionCount: 0,
                criteria: {
                    clarity: [],
                    relevance: [],
                    grammar: [],
                    professionalism: []
                },
                lastPracticed: session.savedAt || null
            };

            current.sessions.push(session);
            current.totalScore += Number(session.averageScore) || 0;
            current.answeredCount += Number(session.answeredCount) || 0;
            current.questionCount += Number(session.questionCount) || 0;
            current.criteria.clarity.push(Number(session.criteriaAverages?.clarity) || 0);
            current.criteria.relevance.push(Number(session.criteriaAverages?.relevance) || 0);
            current.criteria.grammar.push(Number(session.criteriaAverages?.grammar) || 0);
            current.criteria.professionalism.push(Number(session.criteriaAverages?.professionalism) || 0);

            if (!current.lastPracticed || new Date(session.savedAt || 0) > new Date(current.lastPracticed)) {
                current.lastPracticed = session.savedAt;
            }

            grouped.set(key, current);
        });

        return Array.from(grouped.values()).map((item) => {
            const averageScore = Number((item.totalScore / item.sessions.length).toFixed(1));
            const readiness = Math.round(averageScore * 10);
            const criteriaAverages = {
                clarity: average(item.criteria.clarity),
                relevance: average(item.criteria.relevance),
                grammar: average(item.criteria.grammar),
                professionalism: average(item.criteria.professionalism)
            };
            const strongestCriterion = Object.entries(criteriaAverages).sort((left, right) => right[1] - left[1])[0];
            const weakestCriterion = Object.entries(criteriaAverages).sort((left, right) => left[1] - right[1])[0];
            const completionRate = item.questionCount ? Math.round((item.answeredCount / item.questionCount) * 100) : 0;

            return {
                ...item,
                averageScore,
                readiness,
                ready: readiness >= 75,
                completionRate,
                criteriaAverages,
                strongestCriterion,
                weakestCriterion
            };
        });
    }

    function getFilteredCategories() {
        const search = elements.search.value.trim().toLowerCase();
        const sort = elements.sort.value;

        const filtered = categories.filter((category) => {
            if (!search) {
                return true;
            }

            return [
                category.categoryName,
                category.categoryDescription,
                category.strongestCriterion?.[0],
                category.weakestCriterion?.[0]
            ].some((value) => String(value || "").toLowerCase().includes(search));
        });

        filtered.sort((left, right) => {
            if (sort === "recent") {
                return new Date(right.lastPracticed || 0) - new Date(left.lastPracticed || 0);
            }

            if (sort === "sessions") {
                return right.sessions.length - left.sessions.length;
            }

            if (sort === "name") {
                return left.categoryName.localeCompare(right.categoryName);
            }

            return right.averageScore - left.averageScore;
        });

        return filtered;
    }

    function renderSummaryMetrics() {
        const strongestCategory = categories.slice().sort((left, right) => right.averageScore - left.averageScore)[0];
        const nextFocus = categories.slice().sort((left, right) => {
            if (left.averageScore === right.averageScore) {
                return left.sessions.length - right.sessions.length;
            }

            return left.averageScore - right.averageScore;
        })[0];

        elements.practiced.textContent = String(categories.length);
        elements.strongest.textContent = strongestCategory?.categoryName ?? "None";
        elements.ready.textContent = String(categories.filter((category) => category.ready).length);
        elements.need.textContent = nextFocus?.categoryName ?? "None";
    }

    function renderCategoryList() {
        const filtered = getFilteredCategories();

        if (!filtered.length) {
            elements.listMeta.textContent = "0 categories shown";
            elements.list.innerHTML = renderEmptyState(
                "No matching categories",
                "Try a different search term or save more practice sessions first."
            );
            return;
        }

        if (!filtered.some((category) => category.key === selectedKey)) {
            selectedKey = filtered[0].key;
        }

        elements.listMeta.textContent = `${filtered.length} categor${filtered.length === 1 ? "y" : "ies"} shown`;
        elements.list.innerHTML = filtered.map((category) => {
            const active = category.key === selectedKey;
            const badgeClasses = category.ready
                ? "bg-success-100 text-success-700 dark:bg-success-500/10 dark:text-success-300"
                : "bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300";

            return `
                <button
                    type="button"
                    data-category-key="${escapeHtml(category.key)}"
                    class="${active ? "border-brand-300 bg-brand-50 dark:border-brand-500/30 dark:bg-brand-500/10" : "border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-900/70"} w-full rounded-2xl border p-4 text-left transition hover:border-brand-300 hover:bg-brand-50 dark:hover:border-brand-500/30 dark:hover:bg-brand-500/10"
                >
                    <div class="flex items-center justify-between gap-3">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white/90">${escapeHtml(category.categoryName)}</h4>
                        <span class="rounded-full px-3 py-1 text-xs font-medium ${badgeClasses}">
                            ${category.ready ? "Ready" : "Build"}
                        </span>
                    </div>
                    <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">${escapeHtml(category.categoryDescription)}</p>
                    <div class="mt-4 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>${category.sessions.length} session${category.sessions.length === 1 ? "" : "s"}</span>
                        <span>${category.averageScore.toFixed(1)}/10 average</span>
                    </div>
                </button>
            `;
        }).join("");

        elements.list.querySelectorAll("[data-category-key]").forEach((button) => {
            button.addEventListener("click", () => {
                selectedKey = button.dataset.categoryKey;
                renderCategoryList();
                renderDetail();
            });
        });
    }

    function renderDetail() {
        const filtered = getFilteredCategories();

        if (!filtered.length) {
            elements.detail.innerHTML = renderEmptyState(
                "No category insight available",
                "Save interview practice sessions to unlock category analytics."
            );
            return;
        }

        const selectedCategory = filtered.find((category) => category.key === selectedKey) || filtered[0];
        const readinessClasses = selectedCategory.ready
            ? "bg-success-100 text-success-700 dark:bg-success-500/10 dark:text-success-300"
            : "bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300";
        const weakestAdvice = {
            clarity: "Organize answers into clear opening, example, and closing points.",
            relevance: "Use more question-specific keywords and direct supporting details.",
            grammar: "Tighten sentence flow and finish responses with complete thoughts.",
            professionalism: "Use more polished, confident wording that sounds interview-ready."
        };

        const recentSessions = selectedCategory.sessions
            .slice()
            .sort((left, right) => new Date(right.savedAt || 0) - new Date(left.savedAt || 0))
            .slice(0, 4);

        elements.detail.innerHTML = `
            <div class="space-y-6">
                <div class="flex flex-col gap-4 border-b border-gray-200 pb-5 dark:border-gray-800 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-3xl">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="rounded-full px-3 py-1 text-xs font-medium ${readinessClasses}">
                                ${selectedCategory.ready ? "Ready Category" : "Needs Another Round"}
                            </span>
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                Last practiced ${formatDate(selectedCategory.lastPracticed)}
                            </span>
                        </div>
                        <h3 class="mt-4 text-xl font-semibold text-gray-900 dark:text-white/90">${escapeHtml(selectedCategory.categoryName)}</h3>
                        <p class="mt-2 text-sm leading-7 text-gray-600 dark:text-gray-400">${escapeHtml(selectedCategory.categoryDescription)}</p>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-gray-50 px-5 py-4 text-center dark:border-gray-800 dark:bg-gray-900/70">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Readiness</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white/90">${selectedCategory.readiness}%</p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Average Score</p>
                        <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">${selectedCategory.averageScore.toFixed(1)}/10</p>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Saved Sessions</p>
                        <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">${selectedCategory.sessions.length}</p>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Completion Rate</p>
                        <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">${selectedCategory.completionRate}%</p>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Best Dimension</p>
                        <p class="mt-2 text-lg font-semibold text-gray-900 capitalize dark:text-white/90">${escapeHtml(selectedCategory.strongestCriterion[0])}</p>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-800 dark:bg-gray-900/70">
                        <h4 class="text-base font-semibold text-gray-900 dark:text-white/90">Criteria Breakdown</h4>
                        <div class="mt-5 space-y-4">
                            ${Object.entries(selectedCategory.criteriaAverages).map(([label, value]) => `
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
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white/90">Coaching Signal</h4>
                            <p class="mt-3 text-sm leading-7 text-gray-600 dark:text-gray-400">
                                Your strongest signal in this category is <strong class="capitalize text-gray-900 dark:text-white/90">${escapeHtml(selectedCategory.strongestCriterion[0])}</strong>, while the next improvement opportunity is <strong class="capitalize text-gray-900 dark:text-white/90">${escapeHtml(selectedCategory.weakestCriterion[0])}</strong>.
                            </p>
                            <p class="mt-3 text-sm leading-7 text-gray-600 dark:text-gray-400">
                                ${escapeHtml(weakestAdvice[selectedCategory.weakestCriterion[0]] || "Continue practicing to build more consistency across dimensions.")}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-brand-100 bg-brand-50/70 p-5 dark:border-brand-500/20 dark:bg-brand-500/5">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white/90">Recommendation</h4>
                            <p class="mt-3 text-sm leading-7 text-gray-600 dark:text-gray-400">
                                ${selectedCategory.ready
                                    ? "This category is already in a strong range. Use another round to maintain momentum and sharpen edge cases."
                                    : "This category is the right place for another focused session. A short practice round here should produce the fastest readiness gain."}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-800 dark:bg-gray-900/70">
                    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white/90">Recent Sessions</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Latest saved sessions for this category.</p>
                        </div>
                        <a href="/practice" class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-300">Practice Again</a>
                    </div>

                    <div class="space-y-3">
                        ${recentSessions.map((session) => `
                            <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950/40">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white/90">${formatDate(session.savedAt, true)}</p>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">${escapeHtml(session.focusMode)} with ${escapeHtml(session.pacingMode)}</p>
                                    </div>
                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        ${Number(session.averageScore).toFixed(1)}/10 average
                                    </span>
                                </div>
                            </div>
                        `).join("")}
                    </div>
                </div>
            </div>
        `;
    }
}
