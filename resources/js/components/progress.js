import {
    PRACTICE_SESSIONS_UPDATED_EVENT,
    clearPracticeSessions,
    readPracticeSessions,
    removePracticeSession,
    writePracticeSessions
} from "./practice-storage";
import {
    buildManuscriptRubric,
    formatRubricScore,
    getReadinessLabel
} from "./manuscript-rubric";

export function initProgress() {
    const progressRoot = document.getElementById("progressApp");

    if (!progressRoot) {
        return;
    }

    const elements = {
        progressTotalSessions: document.getElementById("progressTotalSessions"),
        progressAverageScore: document.getElementById("progressAverageScore"),
        progressWeeklyGoal: document.getElementById("progressWeeklyGoal"),
        progressWeeklyGoalSubtext: document.getElementById("progressWeeklyGoalSubtext"),
        progressStreakDays: document.getElementById("progressStreakDays"),
        progressTrendCanvas: document.getElementById("progressTrendCanvas"),
        trendLegend: document.getElementById("trendLegend"),
        sessionHistoryContainer: document.getElementById("sessionHistoryContainer"),
        sessionReviewContainer: document.getElementById("sessionReviewContainer"),
        categoryBreakdownList: document.getElementById("categoryBreakdownList"),
        progressSummaryText: document.getElementById("progressSummaryText"),
        studyPlanWeakSkill: document.getElementById("studyPlanWeakSkill"),
        personalizedStudyPlanContainer: document.getElementById("personalizedStudyPlanContainer"),
        practiceCalendarList: document.getElementById("practiceCalendarList"),
        progressCapstoneRubricGrid: document.getElementById("progressCapstoneRubricGrid"),
        criteriaAnalyticsGrid: document.getElementById("criteriaAnalyticsGrid"),
        competencyGapList: document.getElementById("competencyGapList"),
        recentPerformanceCards: document.getElementById("recentPerformanceCards"),
        progressAchievementList: document.getElementById("progressAchievementList"),
        readinessCertificateContainer: document.getElementById("readinessCertificateContainer"),
        progressCertificateDownloadBtn: document.getElementById("progressCertificateDownloadBtn"),
        progressExportJsonBtn: document.getElementById("progressExportJsonBtn"),
        progressExportCsvBtn: document.getElementById("progressExportCsvBtn"),
        progressClearHistoryBtn: document.getElementById("progressClearHistoryBtn"),
        progressStatus: document.getElementById("progressStatus")
    };

    let sessions = [];
    let categoryBreakdown = [];
    let criteriaAverages = {
        clarity: 0,
        relevance: 0,
        grammar: 0,
        professionalism: 0
    };
    let sessionsLastSevenDays = [];
    let streakDays = 0;
    let overallAverage = 0;
    let capstoneAverages = {
        verbal: 0,
        nonVerbal: 0,
        overall: 0,
        readinessLabel: "No data yet"
    };
    let competencyGaps = [];
    let studyPlan = [];
    let practiceCalendar = [];
    let isMutating = false;

    hydrateData();
    renderEverything();
    wireExports();

    elements.progressClearHistoryBtn.addEventListener("click", async () => {
        if (isMutating) {
            return;
        }

        if (!sessions.length) {
            showStatus("info", "There are no saved sessions to clear.");
            return;
        }

        if (!window.confirm("Clear all saved sessions and their evaluation history?")) {
            return;
        }

        try {
            setMutating(true);
            await clearPracticeSessions();
            hydrateData();
            renderEverything();
            showStatus("success", "All saved sessions were cleared.");
        } catch (error) {
            console.error(error);
            showStatus("warning", "Saved sessions could not be cleared right now.");
        } finally {
            setMutating(false);
        }
    });

    elements.sessionHistoryContainer.addEventListener("click", async (event) => {
        const deleteButton = event.target.closest("[data-delete-session-id]");

        if (!deleteButton || isMutating) {
            return;
        }

        const sessionId = deleteButton.dataset.deleteSessionId;
        const session = sessions.find((item) => item.id === sessionId);

        if (!window.confirm(`Delete the saved ${session?.categoryName || "practice"} session and its AI feedback?`)) {
            return;
        }

        try {
            setMutating(true);
            await removePracticeSession(sessionId);
            hydrateData();
            renderEverything();
            showStatus("success", "The saved session was deleted.");
        } catch (error) {
            console.error(error);
            showStatus("warning", "That saved session could not be deleted right now.");
        } finally {
            setMutating(false);
        }
    });

    elements.sessionReviewContainer.addEventListener("click", async (event) => {
        const saveButton = event.target.closest("[data-save-adviser-session]");

        if (!saveButton || isMutating) {
            return;
        }

        const sessionId = saveButton.dataset.saveAdviserSession;
        const textarea = elements.sessionReviewContainer.querySelector(`[data-adviser-session-comment="${cssEscape(sessionId)}"]`);
        const comment = String(textarea?.value || "").trim();

        if (!sessionId) {
            return;
        }

        try {
            setMutating(true);
            await saveAdviserSessionComment(sessionId, comment);
            hydrateData();
            renderEverything();
            showStatus("success", comment ? "Teacher/adviser comment saved." : "Teacher/adviser comment cleared.");
        } catch (error) {
            console.error(error);
            showStatus("warning", "The teacher/adviser comment could not be saved right now.");
        } finally {
            setMutating(false);
        }
    });

    window.addEventListener(PRACTICE_SESSIONS_UPDATED_EVENT, () => {
        hideStatus();
        hydrateData();
        renderEverything();
    });

    function average(values) {
        if (!values.length) {
            return 0;
        }

        return Number((values.reduce((sum, value) => sum + value, 0) / values.length).toFixed(1));
    }

    function hydrateData() {
        sessions = readPracticeSessions();
        categoryBreakdown = buildCategoryBreakdown(sessions);
        criteriaAverages = buildCriteriaAverages(sessions);
        sessionsLastSevenDays = sessions.filter((session) => {
            const savedAt = new Date(session.savedAt || 0).getTime();
            const sevenDaysAgo = Date.now() - (7 * 24 * 60 * 60 * 1000);
            return savedAt >= sevenDaysAgo;
        });
        streakDays = calculateStreakDays(sessions);
        overallAverage = average(sessions.map((session) => Number(session.averageScore) || 0));
        capstoneAverages = buildCapstoneAverages(sessions);
        competencyGaps = buildCompetencyGaps(sessions);
        studyPlan = buildPersonalizedStudyPlan(competencyGaps, sessions);
        practiceCalendar = buildPracticeCalendar(studyPlan);
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

    function escapeHtml(value) {
        return String(value ?? "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");
    }

    function cssEscape(value) {
        if (window.CSS?.escape) {
            return window.CSS.escape(String(value ?? ""));
        }

        return String(value ?? "").replace(/[^a-zA-Z0-9_-]/g, "\\$&");
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

        elements.progressStatus.className = `mt-4 rounded-xl border px-4 py-3 text-sm ${styles[type] || styles.info}`;
        elements.progressStatus.textContent = text;
        elements.progressStatus.classList.remove("hidden");
    }

    function hideStatus() {
        elements.progressStatus.classList.add("hidden");
        elements.progressStatus.textContent = "";
    }

    function updateActionState() {
        const exportDisabled = sessions.length === 0 || isMutating;
        const clearDisabled = sessions.length === 0 || isMutating;

        elements.progressExportJsonBtn.disabled = exportDisabled;
        elements.progressExportCsvBtn.disabled = exportDisabled;
        elements.progressClearHistoryBtn.disabled = clearDisabled;
        if (elements.progressCertificateDownloadBtn) {
            elements.progressCertificateDownloadBtn.disabled = exportDisabled;
        }

        [
            elements.progressExportJsonBtn,
            elements.progressExportCsvBtn,
            elements.progressClearHistoryBtn,
            elements.progressCertificateDownloadBtn
        ].filter(Boolean).forEach((element) => {
            element.classList.toggle("cursor-not-allowed", element.disabled);
            element.classList.toggle("opacity-60", element.disabled);
        });

        elements.sessionHistoryContainer.querySelectorAll("[data-delete-session-id]").forEach((button) => {
            button.disabled = isMutating;
            button.classList.toggle("cursor-not-allowed", isMutating);
            button.classList.toggle("opacity-60", isMutating);
        });

        elements.sessionReviewContainer.querySelectorAll("[data-save-adviser-session]").forEach((button) => {
            button.disabled = isMutating;
            button.classList.toggle("cursor-not-allowed", isMutating);
            button.classList.toggle("opacity-60", isMutating);
        });
    }

    function setMutating(nextValue) {
        isMutating = nextValue;
        updateActionState();
    }

    function renderDeleteSessionButton(sessionId) {
        return `
            <button
                type="button"
                data-delete-session-id="${escapeHtml(sessionId)}"
                class="inline-flex items-center justify-center rounded-lg border border-rose-200 px-3 py-2 text-xs font-medium text-rose-600 transition hover:bg-rose-50 dark:border-rose-500/30 dark:text-rose-300 dark:hover:bg-rose-500/10 ${isMutating ? "cursor-not-allowed opacity-60" : ""}"
                ${isMutating ? "disabled" : ""}
            >
                Delete Session
            </button>
        `;
    }

    function renderEverything() {
        renderMetrics();
        renderTrendChart();
        renderSessionHistory();
        renderSessionReview();
        renderCategoryCards();
        renderSummary();
        renderStudyPlan();
        renderCapstoneRubric();
        renderCriteriaAnalytics();
        renderCompetencyGaps();
        renderRecentCards();
        renderReadinessCertificate();
        renderAchievements();
        updateActionState();
    }

    function buildCategoryBreakdown(items) {
        const grouped = new Map();

        items.forEach((session) => {
            const key = session.categoryName || "Unknown Category";
            const current = grouped.get(key) || {
                categoryName: key,
                count: 0,
                total: 0
            };

            current.count += 1;
            current.total += Number(session.averageScore) || 0;
            grouped.set(key, current);
        });

        return Array.from(grouped.values())
            .map((item) => ({
                ...item,
                averageScore: Number((item.total / item.count).toFixed(1))
            }))
            .sort((left, right) => right.averageScore - left.averageScore);
    }

    function buildCriteriaAverages(items) {
        if (!items.length) {
            return {
                clarity: 0,
                relevance: 0,
                grammar: 0,
                professionalism: 0
            };
        }

        const totals = items.reduce((result, session) => {
            const criteria = session.criteriaAverages || {};
            result.clarity += Number(criteria.clarity) || 0;
            result.relevance += Number(criteria.relevance) || 0;
            result.grammar += Number(criteria.grammar) || 0;
            result.professionalism += Number(criteria.professionalism) || 0;
            return result;
        }, {
            clarity: 0,
            relevance: 0,
            grammar: 0,
            professionalism: 0
        });

        return {
            clarity: Number((totals.clarity / items.length).toFixed(1)),
            relevance: Number((totals.relevance / items.length).toFixed(1)),
            grammar: Number((totals.grammar / items.length).toFixed(1)),
            professionalism: Number((totals.professionalism / items.length).toFixed(1))
        };
    }

    function getAllAnswers(items = sessions) {
        return items.flatMap((session) => Array.isArray(session.answers) ? session.answers : []);
    }

    function averageCriteria(items, keys) {
        if (!items.length) {
            return 0;
        }

        const values = items.map((session) => {
            const criteria = session.criteriaAverages || {};
            const keyedValues = keys.map((key) => Number(criteria[key]) || 0).filter((value) => value > 0);
            return keyedValues.length ? average(keyedValues) : 0;
        }).filter((value) => value > 0);

        return values.length ? average(values) : 0;
    }

    function buildSpeakingHabitAverages(items) {
        const answers = getAllAnswers(items);
        const habits = answers
            .map((answer) => answer.feedbackSummary?.speakingHabits || null)
            .filter((habit) => habit && typeof habit === "object");

        if (!habits.length) {
            return {
                fillerRate: 0,
                totalFillers: 0,
                longPauses: 0
            };
        }

        return {
            fillerRate: average(habits.map((habit) => Number(habit.fillerRate) || 0)),
            totalFillers: Math.round(habits.reduce((sum, habit) => sum + (Number(habit.totalFillers) || 0), 0)),
            longPauses: Math.round(habits.reduce((sum, habit) => sum + (Number(habit.longPauseCount) || 0), 0))
        };
    }

    function buildCompetencyGaps(items) {
        if (!items.length) {
            return [
                { key: "clarity", label: "Clarity", score: 0, target: 8, recommendation: "Complete one short answer drill and focus on main point, example, and result." },
                { key: "confidence", label: "Confidence", score: 0, target: 8, recommendation: "Record one answer aloud and remove uncertain phrases before retrying." },
                { key: "bodyLanguage", label: "Body Language", score: 0, target: 8, recommendation: "Run a camera check for posture, eye contact, and calm movement." }
            ];
        }

        const habitAverages = buildSpeakingHabitAverages(items);
        const confidenceBase = average([
            averageCriteria(items, ["professionalism"]),
            overallAverage || 0
        ].filter((value) => value > 0));
        const confidencePenalty = Math.min(2, habitAverages.fillerRate / 5);
        const bodyLanguage = averageCriteria(items, ["eyeContact", "posture", "headMovement", "facialComposure"]);
        const gaps = [
            {
                key: "confidence",
                label: "Confidence",
                score: roundToOne(Math.max(0, confidenceBase - confidencePenalty)),
                target: 8,
                recommendation: habitAverages.fillerRate >= 4
                    ? "Practice a voice rehearsal and replace filler words with short silent pauses."
                    : "Practice one pressure question with a stronger opening sentence."
            },
            {
                key: "clarity",
                label: "Clarity",
                score: criteriaAverages.clarity,
                target: 8,
                recommendation: "Use a direct answer, one example, and a closing result."
            },
            {
                key: "grammar",
                label: "Grammar",
                score: criteriaAverages.grammar,
                target: 8,
                recommendation: "Use shorter complete sentences and polish transitions before submitting."
            },
            {
                key: "relevance",
                label: "Relevance",
                score: criteriaAverages.relevance,
                target: 8,
                recommendation: "Mirror the question language and connect every example back to the prompt."
            },
            {
                key: "professionalism",
                label: "Professionalism",
                score: criteriaAverages.professionalism,
                target: 8,
                recommendation: "Strengthen word choice with responsibility, teamwork, learning, and results."
            },
            {
                key: "bodyLanguage",
                label: "Body Language",
                score: bodyLanguage,
                target: 8,
                recommendation: bodyLanguage > 0
                    ? "Run a camera presence check and keep eye contact, posture, and head movement steady."
                    : "Turn on camera during practice to capture non-verbal readiness signals."
            }
        ];

        return gaps
            .map((gap) => ({
                ...gap,
                score: roundToOne(gap.score || 0),
                gap: roundToOne(Math.max(0, gap.target - (gap.score || 0)))
            }))
            .sort((left, right) => {
                if (left.score === 0 && right.score !== 0) return 1;
                if (right.score === 0 && left.score !== 0) return -1;
                return right.gap - left.gap;
            });
    }

    function roundToOne(value) {
        return Number((Number(value) || 0).toFixed(1));
    }

    function buildPersonalizedStudyPlan(gaps, items) {
        const weakest = gaps.filter((gap) => gap.score > 0).slice(0, 4);
        const source = weakest.length ? weakest : gaps.slice(0, 4);
        const latestCategory = items[0]?.categoryName || "your main interview track";
        const planSeeds = [
            ["Monday", "Clarity Drill", "Practice a 90-second answer with main point, example, and result."],
            ["Tuesday", "STAR Answer Rewrite", "Rewrite one saved answer into situation, task, action, and result."],
            ["Wednesday", "Voice Rehearsal", "Record the answer aloud and reduce filler words."],
            ["Thursday", "Panel Follow-up", "Answer one follow-up from HR, technical, scholarship, or admission perspective."],
            ["Friday", "Difficult Mode", "Practice one pressure question and defend your strongest evidence."]
        ];

        return planSeeds.map(([day, title, fallback], index) => {
            const gap = source[index % Math.max(source.length, 1)];

            return {
                day,
                title: gap ? `${gap.label} ${title}` : title,
                focus: gap?.label || "Interview readiness",
                action: gap?.recommendation || fallback,
                context: latestCategory,
                target: gap?.target || 8
            };
        });
    }

    function buildPracticeCalendar(plan) {
        const calendarDays = ["Monday", "Tuesday", "Wednesday", "Thursday"];

        return calendarDays.map((day) => {
            const planItem = plan.find((item) => item.day === day) || plan[0];

            return {
                day,
                label: planItem?.focus || "Practice",
                detail: planItem?.action || "Complete one interview drill."
            };
        });
    }

    function buildCapstoneAverages(items) {
        if (!items.length) {
            return {
                verbal: 0,
                nonVerbal: 0,
                overall: 0,
                readinessLabel: "No data yet"
            };
        }

        const rubrics = items.map((session) => {
            const savedCriteria = session.criteriaAverages || {};
            const latestAnswer = Array.isArray(session.answers) && session.answers.length
                ? session.answers[session.answers.length - 1]
                : null;
            const savedOverall = Number(savedCriteria.manuscriptOverall) || 0;

            if (savedOverall > 0) {
                return {
                    verbal: Number(savedCriteria.manuscriptVerbal) || 0,
                    nonVerbal: Number(savedCriteria.manuscriptNonVerbal) || 0,
                    overall: savedOverall,
                    readinessLabel: getReadinessLabel(savedOverall)
                };
            }

            return buildManuscriptRubric(
                savedCriteria,
                latestAnswer?.feedbackSummary?.visualSnapshot || {},
                latestAnswer?.feedbackSummary?.processEvaluations || {}
            );
        });

        const averageRubric = {
            verbal: average(rubrics.map((rubric) => Number(rubric.verbal) || 0)),
            nonVerbal: average(rubrics.map((rubric) => Number(rubric.nonVerbal) || 0)),
            overall: average(rubrics.map((rubric) => Number(rubric.overall) || 0))
        };

        return {
            ...averageRubric,
            readinessLabel: getReadinessLabel(averageRubric.overall)
        };
    }

    function calculateStreakDays(items) {
        if (!items.length) {
            return 0;
        }

        const uniqueDays = Array.from(new Set(items.map((session) => {
            const savedAt = new Date(session.savedAt || 0);
            return `${savedAt.getFullYear()}-${savedAt.getMonth()}-${savedAt.getDate()}`;
        })))
            .map((value) => {
                const [year, month, day] = value.split("-").map(Number);
                return new Date(year, month, day);
            })
            .sort((left, right) => right - left);

        let streak = 1;

        for (let index = 1; index < uniqueDays.length; index += 1) {
            const previous = uniqueDays[index - 1];
            const current = uniqueDays[index];
            const diffDays = Math.round((previous - current) / (24 * 60 * 60 * 1000));

            if (diffDays === 1) {
                streak += 1;
            } else {
                break;
            }
        }

        return streak;
    }

    function renderMetrics() {
        elements.progressTotalSessions.textContent = String(sessions.length);
        elements.progressAverageScore.textContent = overallAverage.toFixed(1);
        elements.progressWeeklyGoal.textContent = `${sessionsLastSevenDays.length}/3`;
        elements.progressStreakDays.textContent = String(streakDays);

        if (sessionsLastSevenDays.length >= 3) {
            elements.progressWeeklyGoalSubtext.textContent = "Weekly goal achieved.";
        } else {
            const remaining = 3 - sessionsLastSevenDays.length;
            elements.progressWeeklyGoalSubtext.textContent = `${remaining} more session${remaining === 1 ? "" : "s"} to reach your goal.`;
        }
    }

    function renderTrendChart() {
        const canvas = elements.progressTrendCanvas;
        const legend = elements.trendLegend;
        legend.innerHTML = "";

        if (!sessions.length) {
            const context = canvas.getContext("2d");
            context.clearRect(0, 0, canvas.width, canvas.height);
            context.fillStyle = "#6B7280";
            context.font = "14px sans-serif";
            context.fillText("No saved sessions yet.", 24, 40);
            legend.innerHTML = `<span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">Save a practice session to unlock trend analytics.</span>`;
            return;
        }

        const orderedSessions = sessions.slice().reverse();
        const clientWidth = canvas.clientWidth || 900;
        const clientHeight = 250;
        const ratio = window.devicePixelRatio || 1;
        canvas.width = clientWidth * ratio;
        canvas.height = clientHeight * ratio;

        const context = canvas.getContext("2d");
        context.setTransform(ratio, 0, 0, ratio, 0, 0);
        context.clearRect(0, 0, clientWidth, clientHeight);

        const padding = { top: 20, right: 24, bottom: 40, left: 32 };
        const chartWidth = clientWidth - padding.left - padding.right;
        const chartHeight = clientHeight - padding.top - padding.bottom;
        const values = orderedSessions.map((session) => Number(session.averageScore) || 0);
        const points = values.map((value, index) => {
            const x = padding.left + ((chartWidth / Math.max(values.length - 1, 1)) * index);
            const y = padding.top + chartHeight - ((value / 10) * chartHeight);
            return { x, y, value };
        });

        context.strokeStyle = "#E5E7EB";
        context.lineWidth = 1;

        for (let marker = 0; marker <= 5; marker += 1) {
            const y = padding.top + ((chartHeight / 5) * marker);
            context.beginPath();
            context.moveTo(padding.left, y);
            context.lineTo(clientWidth - padding.right, y);
            context.stroke();
        }

        context.fillStyle = "#6B7280";
        context.font = "11px sans-serif";

        for (let labelIndex = 0; labelIndex < orderedSessions.length; labelIndex += 1) {
            const point = points[labelIndex];
            const dateLabel = formatDate(orderedSessions[labelIndex].savedAt).split(",")[0];
            context.fillText(dateLabel, point.x - 18, clientHeight - 12);
        }

        context.strokeStyle = "#465FFF";
        context.lineWidth = 3;
        context.beginPath();
        points.forEach((point, index) => {
            if (index === 0) {
                context.moveTo(point.x, point.y);
            } else {
                context.lineTo(point.x, point.y);
            }
        });
        context.stroke();

        points.forEach((point) => {
            context.beginPath();
            context.fillStyle = "#465FFF";
            context.arc(point.x, point.y, 4, 0, Math.PI * 2);
            context.fill();
        });

        const bestScore = Math.max(...values);
        const latestScore = values[values.length - 1];
        legend.innerHTML = `
            <span class="rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">Latest ${latestScore.toFixed(1)}/10</span>
            <span class="rounded-full bg-success-50 px-3 py-1 text-xs font-medium text-success-600 dark:bg-success-500/10 dark:text-success-300">Best ${bestScore.toFixed(1)}/10</span>
            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">${sessions.length} total sessions</span>
        `;
    }

    function renderSessionHistory() {
        if (!sessions.length) {
            elements.sessionHistoryContainer.innerHTML = renderEmptyState(
                "No session history yet",
                "Complete and end a practice session to store it here."
            );
            return;
        }

        elements.sessionHistoryContainer.innerHTML = sessions.map((session) => `
            <article class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white/90">${escapeHtml(session.categoryName)}</h4>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">${formatDate(session.savedAt, true)}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                            ${Number(session.averageScore).toFixed(1)}/10 average
                        </span>
                        ${renderDeleteSessionButton(session.id)}
                    </div>
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-4">
                    <div class="rounded-xl border border-gray-200 bg-white px-3 py-3 dark:border-gray-800 dark:bg-gray-950/40">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Answered</p>
                        <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white/90">${session.answeredCount}/${session.questionCount}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white px-3 py-3 dark:border-gray-800 dark:bg-gray-950/40">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Focus</p>
                        <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white/90">${escapeHtml(session.focusMode)}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white px-3 py-3 dark:border-gray-800 dark:bg-gray-950/40">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Pacing</p>
                        <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white/90">${escapeHtml(session.pacingMode)}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white px-3 py-3 dark:border-gray-800 dark:bg-gray-950/40">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Status</p>
                        <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white/90">${session.completed ? "Completed" : "Partial"}</p>
                    </div>
                </div>
            </article>
        `).join("");
    }

    function getSessionAdviserReview(session) {
        const firstAnswer = Array.isArray(session.answers) ? session.answers[0] : null;
        return firstAnswer?.feedbackSummary?.adviserReview || {};
    }

    async function saveAdviserSessionComment(sessionId, comment) {
        const updatedSessions = sessions.map((session) => {
            if (session.id !== sessionId) {
                return session;
            }

            const answers = Array.isArray(session.answers) && session.answers.length
                ? session.answers
                : [{
                    questionIndex: 0,
                    questionNumber: 1,
                    question: "Teacher / adviser review",
                    answer: "",
                    average: 0,
                    clarity: 0,
                    relevance: 0,
                    grammar: 0,
                    professionalism: 0,
                    matchedKeywords: 0,
                    elapsedSeconds: 0,
                    inputMode: "Review",
                    feedbackSummary: {}
                }];

            return {
                ...session,
                answers: answers.map((answer, index) => index === 0
                    ? {
                        ...answer,
                        feedbackSummary: {
                            ...(answer.feedbackSummary || {}),
                            adviserReview: comment
                                ? {
                                    reviewer: "Teacher / Adviser",
                                    sessionComment: comment,
                                    updatedAt: new Date().toISOString(),
                                    status: "Reviewed"
                                }
                                : {}
                        }
                    }
                    : answer)
            };
        });

        await writePracticeSessions(updatedSessions);
    }

    function renderAnswerVersions(answer) {
        const versions = answer.feedbackSummary?.answerVersions || [];

        if (!Array.isArray(versions) || versions.length < 2) {
            return "";
        }

        const first = versions[0];
        const latest = versions[versions.length - 1];

        return `
            <div class="mt-4 rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Answer Improvement Version History</p>
                    <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-gray-700 shadow-theme-xs dark:bg-gray-800 dark:text-gray-300">
                        ${versions.length} versions
                    </span>
                </div>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    ${[
                        ["First Answer", first],
                        ["Improved Answer", latest]
                    ].map(([label, version]) => `
                        <div class="rounded-xl border border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-gray-950/40">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">${label}</p>
                            <p class="content-break mt-2 max-h-36 overflow-y-auto text-xs leading-5 text-gray-600 dark:text-gray-400">${escapeHtml(version.answer).replace(/\n/g, "<br>")}</p>
                        </div>
                    `).join("")}
                </div>
            </div>
        `;
    }

    function renderSpeakingHabits(answer) {
        const habits = answer.feedbackSummary?.speakingHabits || null;

        if (!habits || typeof habits !== "object" || Number(habits.totalWords || 0) === 0) {
            return "";
        }

        const fillerCounts = habits.fillerCounts || {};
        const topFillers = Object.entries(fillerCounts)
            .filter(([, count]) => Number(count) > 0)
            .sort((left, right) => Number(right[1]) - Number(left[1]))
            .slice(0, 3)
            .map(([label, count]) => `${label}: ${count}`)
            .join(", ");

        return `
            <div class="mt-4 rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Filler Word And Speaking Habits</p>
                    <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-gray-700 shadow-theme-xs dark:bg-gray-800 dark:text-gray-300">
                        ${Number(habits.totalFillers || 0)} fillers
                    </span>
                </div>
                <div class="mt-3 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-xl border border-gray-200 bg-white px-3 py-3 dark:border-gray-800 dark:bg-gray-950/40">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Filler Rate</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white/90">${Number(habits.fillerRate || 0).toFixed(1)}%</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white px-3 py-3 dark:border-gray-800 dark:bg-gray-950/40">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Long Pauses</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white/90">${Number(habits.longPauseCount || 0)}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white px-3 py-3 dark:border-gray-800 dark:bg-gray-950/40">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Top Fillers</p>
                        <p class="content-break mt-1 text-sm font-semibold text-gray-900 dark:text-white/90">${escapeHtml(topFillers || "None")}</p>
                    </div>
                </div>
                <p class="mt-3 text-xs leading-5 text-gray-500 dark:text-gray-400">${escapeHtml(habits.summary || "Speaking habit data saved with this answer.")}</p>
            </div>
        `;
    }

    function renderAnswerContextBadges(answer) {
        const panel = answer.feedbackSummary?.panel;
        const documentAnalysis = answer.feedbackSummary?.documentAnalysis;
        const badges = [
            panel?.role ? `Panel: ${panel.role}` : "",
            documentAnalysis?.categoryName ? `Document: ${documentAnalysis.categoryName}` : ""
        ].filter(Boolean);

        if (!badges.length) {
            return "";
        }

        return `
            <div class="mt-3 flex flex-wrap gap-2">
                ${badges.map((badge) => `
                    <span class="rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                        ${escapeHtml(badge)}
                    </span>
                `).join("")}
            </div>
        `;
    }

    function renderSessionReview() {
        if (!sessions.length) {
            elements.sessionReviewContainer.innerHTML = renderEmptyState(
                "No detailed sessions yet",
                "Saved answers will appear here after you complete a session."
            );
            return;
        }

        elements.sessionReviewContainer.innerHTML = sessions.map((session) => {
            const adviserReview = getSessionAdviserReview(session);

            return `
            <details class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                <summary class="cursor-pointer list-none">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white/90">${escapeHtml(session.categoryName)} review</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">${formatDate(session.savedAt, true)}</p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            ${session.answers.length} saved answer${session.answers.length === 1 ? "" : "s"}
                        </span>
                    </div>
                </summary>

                <div class="mt-4 space-y-4">
                    <div class="rounded-2xl border border-brand-100 bg-brand-50/60 p-4 dark:border-brand-500/20 dark:bg-brand-500/5">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h5 class="text-sm font-semibold text-gray-900 dark:text-white/90">Teacher / Adviser Review Mode</h5>
                                <p class="mt-1 text-xs leading-5 text-gray-500 dark:text-gray-400">
                                    Save a review comment for this student's session.
                                </p>
                            </div>
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-gray-700 shadow-theme-xs dark:bg-gray-800 dark:text-gray-300">
                                ${adviserReview.updatedAt ? `Updated ${formatDate(adviserReview.updatedAt)}` : "Not reviewed"}
                            </span>
                        </div>
                        <textarea
                            data-adviser-session-comment="${escapeHtml(session.id)}"
                            rows="3"
                            class="mt-3 min-h-[92px] w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                            placeholder="Add comments about strengths, revisions, or next practice tasks.">${escapeHtml(adviserReview.sessionComment || "")}</textarea>
                        <button
                            type="button"
                            data-save-adviser-session="${escapeHtml(session.id)}"
                            class="mt-3 inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                            Save Adviser Comment
                        </button>
                    </div>

                    ${session.answers.map((answer) => `
                        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950/40">
                            <p class="text-xs uppercase tracking-wide text-gray-500">Question ${answer.questionNumber}</p>
                            <h5 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white/90">${escapeHtml(answer.question)}</h5>
                            ${renderAnswerContextBadges(answer)}
                            <p class="mt-3 text-sm leading-7 text-gray-600 dark:text-gray-400">${escapeHtml(answer.answer)}</p>
                            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                                <div class="rounded-xl bg-gray-50 px-3 py-3 dark:bg-gray-900">
                                    <p class="text-xs uppercase tracking-wide text-gray-500">Average</p>
                                    <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white/90">${Number(answer.average).toFixed(1)}/10</p>
                                </div>
                                <div class="rounded-xl bg-gray-50 px-3 py-3 dark:bg-gray-900">
                                    <p class="text-xs uppercase tracking-wide text-gray-500">Elapsed</p>
                                    <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white/90">${formatDuration(answer.elapsedSeconds || 0)}</p>
                                </div>
                                <div class="rounded-xl bg-gray-50 px-3 py-3 dark:bg-gray-900">
                                    <p class="text-xs uppercase tracking-wide text-gray-500">Input</p>
                                    <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white/90">${escapeHtml(answer.inputMode || "Text")}</p>
                                </div>
                            </div>
                            ${renderSpeakingHabits(answer)}
                            ${renderAnswerVersions(answer)}
                        </div>
                    `).join("")}
                </div>
            </details>
        `;
        }).join("");
    }

    function renderCategoryCards() {
        if (!categoryBreakdown.length) {
            elements.categoryBreakdownList.innerHTML = renderEmptyState(
                "No category data yet",
                "Practice in at least one category to see breakdown cards."
            );
            return;
        }

        elements.categoryBreakdownList.innerHTML = categoryBreakdown.map((item) => {
            const width = Math.max(8, Math.min(100, item.averageScore * 10));
            return `
                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                    <div class="flex items-center justify-between gap-3">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white/90">${escapeHtml(item.categoryName)}</h4>
                        <span class="text-sm font-medium text-brand-600 dark:text-brand-300">${item.averageScore.toFixed(1)}/10</span>
                    </div>
                    <div class="mt-3 h-2 rounded-full bg-gray-200 dark:bg-gray-800">
                        <div class="h-2 rounded-full bg-brand-500" style="width: ${width}%"></div>
                    </div>
                    <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">${item.count} saved session${item.count === 1 ? "" : "s"}</p>
                </div>
            `;
        }).join("");
    }

    function renderSummary() {
        if (!sessions.length) {
            elements.progressSummaryText.textContent = "Complete more sessions to unlock stronger score trends and performance insights.";
            return;
        }

        const topCategory = categoryBreakdown[0]?.categoryName || "your main category";
        const bestCriterion = Object.entries(criteriaAverages).sort((left, right) => right[1] - left[1])[0];
        elements.progressSummaryText.textContent = `You have saved ${sessions.length} session${sessions.length === 1 ? "" : "s"} with an internal average of ${overallAverage.toFixed(1)}/10 and a capstone overall of ${capstoneAverages.overall.toFixed(2)}/5. ${topCategory} is currently your strongest category, and ${bestCriterion[0]} is your top scoring criterion.`;
    }

    function renderStudyPlan() {
        if (!elements.personalizedStudyPlanContainer || !elements.practiceCalendarList) {
            return;
        }

        const weakest = competencyGaps.find((gap) => gap.score > 0) || competencyGaps[0];

        elements.studyPlanWeakSkill.textContent = weakest
            ? `Focus: ${weakest.label}`
            : "Awaiting sessions";

        if (!sessions.length) {
            elements.personalizedStudyPlanContainer.innerHTML = renderEmptyState(
                "No personalized plan yet",
                "Save a practice session to generate a score-based weekly plan."
            );
        } else {
            elements.personalizedStudyPlanContainer.innerHTML = studyPlan.map((item) => `
                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-xs uppercase tracking-wide text-gray-500">${escapeHtml(item.day)}</p>
                        <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-gray-700 shadow-theme-xs dark:bg-gray-800 dark:text-gray-300">
                            Target ${Number(item.target).toFixed(1)}/10
                        </span>
                    </div>
                    <h4 class="mt-3 text-sm font-semibold text-gray-900 dark:text-white/90">${escapeHtml(item.title)}</h4>
                    <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">${escapeHtml(item.action)}</p>
                    <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">${escapeHtml(item.context)}</p>
                </div>
            `).join("");
        }

        elements.practiceCalendarList.innerHTML = practiceCalendar.map((item) => `
            <div class="rounded-2xl border border-brand-100 bg-brand-50/60 p-4 dark:border-brand-500/20 dark:bg-brand-500/5">
                <p class="text-xs uppercase tracking-wide text-brand-600 dark:text-brand-300">${escapeHtml(item.day)}</p>
                <h4 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white/90">${escapeHtml(item.label)}</h4>
                <p class="mt-2 text-xs leading-5 text-gray-600 dark:text-gray-400">${escapeHtml(item.detail)}</p>
            </div>
        `).join("");
    }

    function renderCompetencyGaps() {
        if (!elements.competencyGapList) {
            return;
        }

        if (!sessions.length) {
            elements.competencyGapList.innerHTML = renderEmptyState(
                "No competency gaps yet",
                "Saved sessions will populate the confidence, clarity, grammar, relevance, professionalism, and body language view."
            );
            return;
        }

        elements.competencyGapList.innerHTML = competencyGaps.map((gap) => {
            const width = Math.max(5, Math.min(100, gap.score * 10));
            const tone = gap.gap >= 2
                ? "text-amber-600 dark:text-amber-300"
                : "text-success-600 dark:text-success-300";

            return `
                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                    <div class="flex items-center justify-between gap-3">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white/90">${escapeHtml(gap.label)}</h4>
                        <span class="text-sm font-medium ${tone}">${Number(gap.score).toFixed(1)}/10</span>
                    </div>
                    <div class="mt-3 h-2 rounded-full bg-gray-200 dark:bg-gray-800">
                        <div class="h-2 rounded-full bg-brand-500" style="width: ${width}%"></div>
                    </div>
                    <p class="mt-3 text-xs leading-5 text-gray-500 dark:text-gray-400">${escapeHtml(gap.recommendation)}</p>
                </div>
            `;
        }).join("");
    }

    function renderReadinessCertificate() {
        if (!elements.readinessCertificateContainer) {
            return;
        }

        const eligible = sessions.length >= 3 && capstoneAverages.overall >= 3;
        const latestSession = sessions[0];
        const status = eligible ? "Ready to issue" : "Keep practicing";
        const requirementText = eligible
            ? "Minimum practice evidence completed."
            : `${Math.max(0, 3 - sessions.length)} more saved session${Math.max(0, 3 - sessions.length) === 1 ? "" : "s"} recommended before issuing.`;

        elements.readinessCertificateContainer.innerHTML = `
            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Certificate Status</p>
                        <h4 class="mt-2 text-base font-semibold text-gray-900 dark:text-white/90">${status}</h4>
                    </div>
                    <span class="rounded-full ${eligible ? "bg-success-100 text-success-700 dark:bg-success-500/10 dark:text-success-300" : "bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300"} px-3 py-1 text-xs font-medium">
                        ${escapeHtml(capstoneAverages.readinessLabel)}
                    </span>
                </div>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-gray-200 bg-white px-3 py-3 dark:border-gray-800 dark:bg-gray-950/40">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Sessions</p>
                        <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white/90">${sessions.length}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white px-3 py-3 dark:border-gray-800 dark:bg-gray-950/40">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Rubric Overall</p>
                        <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white/90">${capstoneAverages.overall.toFixed(2)}/5</p>
                    </div>
                </div>
                <p class="mt-4 text-sm leading-6 text-gray-600 dark:text-gray-400">
                    ${escapeHtml(requirementText)}${latestSession ? ` Latest track: ${escapeHtml(latestSession.categoryName)}.` : ""}
                </p>
            </div>
        `;
    }

    function renderCapstoneRubric() {
        if (!sessions.length) {
            elements.progressCapstoneRubricGrid.innerHTML = renderEmptyState(
                "No rubric data yet",
                "Save an evaluated session to translate the runtime scores into the manuscript rubric."
            );
            return;
        }

        const cards = [
            ["Verbal", formatRubricScore(capstoneAverages.verbal), "Weighted clarity, relevance, grammar, and professionalism."],
            ["Non-Verbal", formatRubricScore(capstoneAverages.nonVerbal), "Selected eye contact, posture, head movement, and facial composure cues."],
            ["Overall", formatRubricScore(capstoneAverages.overall), "Combined capstone readiness based on verbal and non-verbal weighting."],
            ["Readiness", capstoneAverages.readinessLabel, "Interpretation band from the manuscript scoring scale."]
        ];

        elements.progressCapstoneRubricGrid.innerHTML = cards.map(([label, value, body]) => `
            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                <p class="text-xs uppercase tracking-wide text-gray-500">${label}</p>
                <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">${escapeHtml(value)}</p>
                <p class="mt-2 text-xs leading-5 text-gray-500 dark:text-gray-400">${escapeHtml(body)}</p>
            </div>
        `).join("");
    }

    function renderCriteriaAnalytics() {
        const items = [
            ["Clarity", criteriaAverages.clarity],
            ["Relevance", criteriaAverages.relevance],
            ["Grammar", criteriaAverages.grammar],
            ["Professionalism", criteriaAverages.professionalism]
        ];

        if (!sessions.length) {
            elements.criteriaAnalyticsGrid.innerHTML = renderEmptyState(
                "No criteria analytics yet",
                "Saved answers will populate this area automatically."
            );
            return;
        }

        elements.criteriaAnalyticsGrid.innerHTML = items.map(([label, value]) => `
            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                <p class="text-xs uppercase tracking-wide text-gray-500">${label}</p>
                <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">${Number(value).toFixed(1)}/10</p>
            </div>
        `).join("");
    }

    function renderRecentCards() {
        if (!sessions.length) {
            elements.recentPerformanceCards.innerHTML = renderEmptyState(
                "No recent sessions yet",
                "Your latest three practice sessions will appear here."
            );
            return;
        }

        elements.recentPerformanceCards.innerHTML = sessions.slice(0, 3).map((session) => `
            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                <div class="flex items-center justify-between gap-3">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white/90">${escapeHtml(session.categoryName)}</h4>
                    <span class="text-sm font-medium text-success-600 dark:text-success-300">${Number(session.averageScore).toFixed(1)}/10</span>
                </div>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">${formatDate(session.savedAt)}</p>
                <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-400">${session.answers.length} answer${session.answers.length === 1 ? "" : "s"} saved with ${escapeHtml(session.focusMode)}.</p>
            </div>
        `).join("");
    }

    function renderAchievements() {
        const achievements = [
            {
                title: "First Session Saved",
                body: "Store your first interview result in the progress tracker.",
                unlocked: sessions.length >= 1
            },
            {
                title: "Weekly Goal Hit",
                body: "Save at least three sessions in the last seven days.",
                unlocked: sessionsLastSevenDays.length >= 3
            },
            {
                title: "Category Explorer",
                body: "Practice across at least three interview categories.",
                unlocked: categoryBreakdown.length >= 3
            },
            {
                title: "High Performer",
                body: "Reach an overall average of 8.5 or higher.",
                unlocked: overallAverage >= 8.5
            },
            {
                title: "Consistency Streak",
                body: "Practice on three consecutive active days.",
                unlocked: streakDays >= 3
            }
        ];

        elements.progressAchievementList.innerHTML = achievements.map((achievement) => `
            <div class="rounded-2xl border p-4 ${achievement.unlocked ? "border-success-200 bg-success-50 dark:border-success-500/20 dark:bg-success-500/10" : "border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-900/70"}">
                <div class="flex items-center justify-between gap-3">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white/90">${achievement.title}</h4>
                    <span class="rounded-full px-3 py-1 text-xs font-medium ${achievement.unlocked ? "bg-success-100 text-success-700 dark:bg-success-500/15 dark:text-success-300" : "bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300"}">
                        ${achievement.unlocked ? "Unlocked" : "Locked"}
                    </span>
                </div>
                <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">${achievement.body}</p>
            </div>
        `).join("");
    }

    function downloadFile(filename, content, mimeType) {
        const blob = new Blob([content], { type: mimeType });
        const url = URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.href = url;
        link.download = filename;
        link.click();
        URL.revokeObjectURL(url);
    }

    function wireExports() {
        updateActionState();

        elements.progressExportJsonBtn.addEventListener("click", () => {
            const payload = {
                generatedAt: new Date().toISOString(),
                totalSessions: sessions.length,
                overallAverage,
                capstoneAverages,
                competencyGaps,
                studyPlan,
                sessions
            };

            downloadFile("capstone-progress-report.json", JSON.stringify(payload, null, 2), "application/json");
        });

        elements.progressExportCsvBtn.addEventListener("click", () => {
            const header = [
                "saved_at",
                "category",
                "average_score",
                "capstone_overall",
                "answered_count",
                "question_count",
                "focus_mode",
                "pacing_mode",
                "completed"
            ];
            const rows = sessions.map((session) => [
                ((Array.isArray(session.answers) && session.answers.length) ? session.answers[session.answers.length - 1] : null),
                session
            ]).map(([latestAnswer, session]) => [
                session.savedAt,
                `"${String(session.categoryName).replace(/"/g, "\"\"")}"`,
                Number(session.averageScore).toFixed(1),
                Number(
                    session.criteriaAverages?.manuscriptOverall
                    || buildManuscriptRubric(
                        session.criteriaAverages || {},
                        latestAnswer?.feedbackSummary?.visualSnapshot || {},
                        latestAnswer?.feedbackSummary?.processEvaluations || {}
                    ).overall
                ).toFixed(2),
                session.answeredCount,
                session.questionCount,
                `"${String(session.focusMode).replace(/"/g, "\"\"")}"`,
                `"${String(session.pacingMode).replace(/"/g, "\"\"")}"`,
                session.completed ? "yes" : "no"
            ].join(","));

            downloadFile("capstone-progress-sessions.csv", [header.join(","), ...rows].join("\n"), "text/csv;charset=utf-8");
        });

        elements.progressCertificateDownloadBtn?.addEventListener("click", () => {
            if (!sessions.length) {
                showStatus("info", "Save at least one practice session before downloading a certificate report.");
                return;
            }

            const weakest = competencyGaps[0];
            const certificate = [
                "Interview Readiness Certificate / Report",
                `Generated: ${formatDate(new Date().toISOString(), true)}`,
                `Saved sessions: ${sessions.length}`,
                `Overall average: ${overallAverage.toFixed(1)}/10`,
                `Capstone readiness: ${capstoneAverages.overall.toFixed(2)}/5 (${capstoneAverages.readinessLabel})`,
                weakest ? `Next focus: ${weakest.label} - ${weakest.recommendation}` : "",
                "",
                "Weekly Study Plan:",
                ...studyPlan.map((item) => `${item.day}: ${item.title} - ${item.action}`),
                "",
                "This report is generated by the AI-Based Interview Practice System from saved practice sessions."
            ].filter((line) => line !== "").join("\n");

            downloadFile("interview-readiness-certificate.txt", certificate, "text/plain;charset=utf-8");
        });
    }
}
