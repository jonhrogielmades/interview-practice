import { practiceData, readSessionSetup } from "./practice-config";
import { appendPracticeSession } from "./practice-storage";
import { buildFeedbackSummary, normalizeFeedbackSummary } from "./feedback-utils";
import { buildManuscriptRubric, formatRubricScore } from "./manuscript-rubric";
import { requestWorkspace, requestWorkspaceBlob } from "./workspace-api";

export function initPractice() {
    const practiceRoot = document.getElementById("practiceApp");

    if (!practiceRoot) {
        return;
    }

    const rawChatbotBootstrap = window.__INTERVIEW_CHATBOT__ || {};
    const rawInterviewerAudioBootstrap = window.__INTERVIEW_AUDIO__ || {};
    const state = {
        selectedCategory: null,
        selectedFieldPlan: null,
        pendingCategory: null,
        fieldPlanByCategoryId: {},
        sessionId: null,
        questionIndex: 0,
        questionCount: 3,
        activeQuestions: [],
        answeredCount: 0,
        timerSeconds: 0,
        timerTarget: 180,
        timerInterval: null,
        timerPaused: false,
        timerAlertShown: false,
        feedbackHistory: [],
        speechRecognition: null,
        recognitionActive: false,
        recognitionShouldRestart: false,
        recognitionBaseText: "",
        recognitionInterimText: "",
        restartVoiceTimeout: null,
        voiceConversationEnabled: false,
        voiceAutoSubmitTimeout: null,
        questionSpeechTimeout: null,
        interviewerSpeechActive: false,
        interviewerSpeechRate: 1,
        answerSubmitting: false,
        isApplyingTranscript: false,
        manualInputUsed: false,
        voiceInputUsed: false,
        currentMode: "Text",
        sessionStartedAt: null,
        sessionSaved: false,
        savingSession: false,
        feedbackLoading: false,
        questionSourceLabel: "Awaiting category",
        questionSetSummary: "Select a category and the chatbot will build a fresh question set for the workspace.",
        questionAgentHistory: [],
        questionAgentLoading: false,
        questionAgentStatusText: "Waiting",
        questionAgentStatusTone: "neutral",
        questionAgentProviderCatalog: normalizeQuestionAgentProviders(rawChatbotBootstrap.providers),
        questionAgentSelectedProviderId: resolveQuestionAgentDefaultProvider(rawChatbotBootstrap),
        questionAgentResolvedProviderLabel: null,
        questionAgentRequestId: 0,
        fieldBuilderHistory: [],
        fieldBuilderLoading: false,
        fieldBuilderStatusText: "Waiting for details",
        fieldBuilderStatusTone: "neutral",
        fieldBuilderRequestId: 0,
        liveVisualSnapshot: null,
        lastProcessEvaluations: null,
        learningActivityContext: null,
        practiceStarted: false,
        welcomeDelivered: false,
        openingIntroductionSpeaking: false,
        openingIntroductionQuestionPending: false,
        openingIntroductionFocusPending: false,
        openingConversationActive: false,
        openingConversationAcknowledging: false,
        openingConversationCompleted: false,
        openingConversationFocusPending: false,
        openingConversationReply: "",
        autoAdvanceTimeout: null
    };

    const VOICE_AUTO_SUBMIT_DELAY_MS = 2600;
    const VOICE_COMMAND_HELP_TEXT = "You can say: repeat question, next question, pause, continue, send answer, clear answer, camera on, camera off, give me a hint, new questions, or end interview.";

    const elements = {
        questionCountSelect: document.getElementById("questionCountSelect"),
        focusModeSelect: document.getElementById("focusModeSelect"),
        pacingModeSelect: document.getElementById("pacingModeSelect"),
        practiceCategoryModal: document.getElementById("practiceCategoryModal"),
        practiceCategoryModalBackdrop: document.getElementById("practiceCategoryModalBackdrop"),
        practiceCategoryList: document.getElementById("practiceCategoryList"),
        openPracticeCategoryModalBtn: document.getElementById("openPracticeCategoryModalBtn"),
        closePracticeCategoryModalBtn: document.getElementById("closePracticeCategoryModalBtn"),
        practiceFieldModal: document.getElementById("practiceFieldModal"),
        practiceFieldModalBackdrop: document.getElementById("practiceFieldModalBackdrop"),
        closePracticeFieldModalBtn: document.getElementById("closePracticeFieldModalBtn"),
        practiceFieldModalStatusTag: document.getElementById("practiceFieldModalStatusTag"),
        practiceFieldProviderValue: document.getElementById("practiceFieldProviderValue"),
        practiceFieldProviderSelect: document.getElementById("practiceFieldProviderSelect"),
        practiceFieldProviderHelpText: document.getElementById("practiceFieldProviderHelpText"),
        practiceFieldModalCategoryName: document.getElementById("practiceFieldModalCategoryName"),
        practiceFieldModalCategoryDescription: document.getElementById("practiceFieldModalCategoryDescription"),
        practiceFieldSuggestionChips: document.getElementById("practiceFieldSuggestionChips"),
        practiceFieldPreviewTitle: document.getElementById("practiceFieldPreviewTitle"),
        practiceFieldPreviewSummary: document.getElementById("practiceFieldPreviewSummary"),
        practiceFieldChatMessages: document.getElementById("practiceFieldChatMessages"),
        practiceFieldInput: document.getElementById("practiceFieldInput"),
        practiceFieldNeedInput: document.getElementById("practiceFieldNeedInput"),
        practiceFieldGenerateBtn: document.getElementById("practiceFieldGenerateBtn"),
        practiceFieldResetBtn: document.getElementById("practiceFieldResetBtn"),
        practiceFieldApplyBtn: document.getElementById("practiceFieldApplyBtn"),
        practiceFieldChatStatus: document.getElementById("practiceFieldChatStatus"),
        practiceSessionModal: document.getElementById("practiceSessionModal"),
        practiceSessionModalBackdrop: document.getElementById("practiceSessionModalBackdrop"),
        openPracticeModalBtn: document.getElementById("openPracticeModalBtn"),
        editPracticeFieldBtn: document.getElementById("editPracticeFieldBtn"),
        closePracticeModalBtn: document.getElementById("closePracticeModalBtn"),
        practiceQuestionAgentModal: document.getElementById("practiceQuestionAgentModal"),
        practiceQuestionAgentModalBackdrop: document.getElementById("practiceQuestionAgentModalBackdrop"),
        openPracticeQuestionAgentModalBtn: document.getElementById("openPracticeQuestionAgentModalBtn"),
        closePracticeQuestionAgentModalBtn: document.getElementById("closePracticeQuestionAgentModalBtn"),
        practiceModalCategoryName: document.getElementById("practiceModalCategoryName"),
        practiceModalSummaryText: document.getElementById("practiceModalSummaryText"),
        practiceModalStateTag: document.getElementById("practiceModalStateTag"),
        practiceModalActiveCategory: document.getElementById("practiceModalActiveCategory"),
        practiceModalAnsweredValue: document.getElementById("practiceModalAnsweredValue"),
        practiceModalWorkspaceValue: document.getElementById("practiceModalWorkspaceValue"),
        practiceModalFieldValue: document.getElementById("practiceModalFieldValue"),
        practiceModalFieldMeta: document.getElementById("practiceModalFieldMeta"),
        selectedCategoryName: document.getElementById("selectedCategoryName"),
        selectedCategoryDescription: document.getElementById("selectedCategoryDescription"),
        questionCounter: document.getElementById("questionCounter"),
        practiceStatusTag: document.getElementById("practiceStatusTag"),
        practiceLabelTag: document.getElementById("practiceLabelTag"),
        selectedPracticeFieldTag: document.getElementById("selectedPracticeFieldTag"),
        openingIntroductionSection: document.getElementById("openingIntroductionSection"),
        openingIntroductionText: document.getElementById("openingIntroductionText"),
        coachModeValue: document.getElementById("coachModeValue"),
        timerTargetValue: document.getElementById("timerTargetValue"),
        questionTimerValue: document.getElementById("questionTimerValue"),
        questionProgressFill: document.getElementById("questionProgressFill"),
        currentQuestionText: document.getElementById("currentQuestionText"),
        coachModeTag: document.getElementById("coachModeTag"),
        coachTipText: document.getElementById("coachTipText"),
        questionKeywordTags: document.getElementById("questionKeywordTags"),
        responseInput: document.getElementById("responseInput"),
        startPracticeBtn: document.getElementById("startPracticeBtn"),
        startVoiceBtn: document.getElementById("startVoiceBtn"),
        stopVoiceBtn: document.getElementById("stopVoiceBtn"),
        submitAnswerBtn: document.getElementById("submitAnswerBtn"),
        nextQuestionBtn: document.getElementById("nextQuestionBtn"),
        endSessionBtn: document.getElementById("endSessionBtn"),
        askQuestionAloudBtn: document.getElementById("askQuestionAloudBtn"),
        voiceStatusText: document.getElementById("voiceStatusText"),
        voiceStatusDot: document.getElementById("voiceStatusDot"),
        practiceMessage: document.getElementById("practiceMessage"),
        feedbackContent: document.getElementById("feedbackContent"),
        printFeedbackBtn: document.getElementById("printFeedbackBtn"),
        learningHighlightTitle: document.getElementById("learningHighlightTitle"),
        learningHighlightText: document.getElementById("learningHighlightText"),
        learningHighlightTag: document.getElementById("learningHighlightTag"),
        learningModulesList: document.getElementById("learningModulesList"),
        learningActivitiesList: document.getElementById("learningActivitiesList"),
        inputModeValue: document.getElementById("inputModeValue"),
        answeredCountValue: document.getElementById("answeredCountValue"),
        paceModeValue: document.getElementById("paceModeValue"),
        bodyLanguageValue: document.getElementById("bodyLanguageValue"),
        facialExpressionValue: document.getElementById("facialExpressionValue"),
        livePresenceSummary: document.getElementById("livePresenceSummary"),
        livePresenceTip: document.getElementById("livePresenceTip"),
        livePresenceTag: document.getElementById("livePresenceTag"),
        bodyLanguageAlgorithms: document.getElementById("bodyLanguageAlgorithms"),
        facialExpressionAlgorithms: document.getElementById("facialExpressionAlgorithms"),
        practiceQuestionAgentStatusTag: document.getElementById("practiceQuestionAgentStatusTag"),
        practiceQuestionAgentSourceValue: document.getElementById("practiceQuestionAgentSourceValue"),
        practiceQuestionAgentProviderSelect: document.getElementById("practiceQuestionAgentProviderSelect"),
        practiceQuestionAgentProviderValue: document.getElementById("practiceQuestionAgentProviderValue"),
        practiceQuestionAgentMessages: document.getElementById("practiceQuestionAgentMessages"),
        practiceQuestionAgentQuickActions: document.getElementById("practiceQuestionAgentQuickActions"),
        practiceQuestionAgentInput: document.getElementById("practiceQuestionAgentInput"),
        practiceQuestionAgentGenerateBtn: document.getElementById("practiceQuestionAgentGenerateBtn"),
        practiceQuestionAgentRegenerateBtn: document.getElementById("practiceQuestionAgentRegenerateBtn"),
        practiceQuestionAgentSummaryText: document.getElementById("practiceQuestionAgentSummaryText")
    };

    const interviewerControls = {
        startCamera: () => {},
        askCurrentQuestion: () => {},
        playOpeningIntroduction: ({ onSettled } = {}) => {
            onSettled?.({ status: "unavailable" });
        },
        speakOpeningAcknowledgement: ({ onSettled } = {}) => {
            onSettled?.({ status: "unavailable" });
            return false;
        },
        speakAssistantMessage: ({ onSettled } = {}) => {
            onSettled?.({ status: "unavailable" });
            return false;
        },
        stopCamera: () => {},
        stopSpeaking: () => {}
    };

    const learningActivityProgression = {
        "quick-drill": {
            label: "Quick Drill",
            levels: [
                { level: 1, label: "Level 1", targetScore: 7.0, questionFocus: "Answer one warm-up question with a direct point and one example." },
                { level: 2, label: "Level 2", targetScore: 8.0, questionFocus: "Answer a stronger follow-up question with clearer detail and outcome." },
                { level: 3, label: "Level 3", targetScore: 8.5, questionFocus: "Answer a challenge question with polished confidence and concise evidence." }
            ]
        },
        "star-response": {
            label: "STAR Response Drill",
            levels: [
                { level: 1, label: "Level 1", targetScore: 7.0, questionFocus: "Build the answer around situation, task, action, and result." },
                { level: 2, label: "Level 2", targetScore: 8.0, questionFocus: "Add measurable results and connect the example to the interview goal." },
                { level: 3, label: "Level 3", targetScore: 8.5, questionFocus: "Handle a follow-up question while keeping the STAR answer concise." }
            ]
        },
        "voice-rehearsal": {
            label: "Voice Rehearsal Sprint",
            levels: [
                { level: 1, label: "Level 1", targetScore: 7.0, questionFocus: "Deliver one answer clearly within the saved pacing target." },
                { level: 2, label: "Level 2", targetScore: 8.0, questionFocus: "Improve pacing, transitions, and confidence in a follow-up answer." },
                { level: 3, label: "Level 3", targetScore: 8.5, questionFocus: "Give a polished spoken answer with minimal filler and strong closing." }
            ]
        },
        "camera-check": {
            label: "Camera Presence Check",
            levels: [
                { level: 1, label: "Level 1", targetScore: 7.0, questionFocus: "Answer while keeping posture and camera framing steady." },
                { level: 2, label: "Level 2", targetScore: 8.0, questionFocus: "Maintain eye contact orientation and calm head movement through a follow-up." },
                { level: 3, label: "Level 3", targetScore: 8.5, questionFocus: "Deliver a harder answer with composed facial presence and steady pacing." }
            ]
        },
        "follow-up-sprint": {
            label: "Follow-up Sprint",
            levels: [
                { level: 1, label: "Level 1", targetScore: 7.0, questionFocus: "Answer one follow-up question using the last feedback area." },
                { level: 2, label: "Level 2", targetScore: 8.0, questionFocus: "Give a clearer follow-up answer with stronger evidence and reflection." },
                { level: 3, label: "Level 3", targetScore: 8.5, questionFocus: "Handle a deeper follow-up with a polished, interview-ready response." }
            ]
        }
    };

    function normalizeQuestionAgentProviders(providers) {
        const fallbackProviders = [
            {
                id: "auto",
                label: "Auto",
                description: "Try configured AI APIs in priority order, then fall back to the local PH coach.",
                configured: true,
                type: "router",
                model: null
            },
            {
                id: "local",
                label: "Local PH coach",
                description: "Built-in fallback for Philippine interview coaching.",
                configured: true,
                type: "fallback",
                model: null
            }
        ];
        const source = Array.isArray(providers) && providers.length > 0 ? providers : fallbackProviders;

        return source.map((provider) => ({
            id: String(provider?.id || ""),
            label: String(provider?.label || provider?.id || "Provider"),
            description: String(provider?.description || ""),
            configured: provider?.configured !== false,
            type: String(provider?.type || "remote"),
            model: provider?.model ? String(provider.model) : null
        }));
    }

    function resolveQuestionAgentDefaultProvider(bootstrap) {
        const providers = normalizeQuestionAgentProviders(bootstrap?.providers);
        const requestedId = typeof bootstrap?.defaultProviderId === "string"
            ? bootstrap.defaultProviderId
            : "auto";
        const requestedProvider = providers.find((provider) => provider.id === requestedId);

        if (requestedProvider && (requestedProvider.configured || requestedProvider.id === "auto" || requestedProvider.id === "local")) {
            return requestedProvider.id;
        }

        return "auto";
    }

    function getSelectedFocusMode() {
        return practiceData.focusModes[Number(elements.focusModeSelect.value)] || practiceData.focusModes[0];
    }

    function getSelectedPacingMode() {
        return practiceData.pacingModes[Number(elements.pacingModeSelect.value)] || practiceData.pacingModes[0];
    }

    function formatTime(totalSeconds) {
        const minutes = String(Math.floor(totalSeconds / 60)).padStart(2, "0");
        const seconds = String(totalSeconds % 60).padStart(2, "0");
        return `${minutes}:${seconds}`;
    }

    function escapeHtml(value) {
        return String(value ?? "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/\"/g, "&quot;")
            .replace(/'/g, "&#39;");
    }

    const RESPONSE_ALGORITHM_NAMES = [
        "Keyword Coverage",
        "STAR Structure",
        "Outcome Evidence",
        "Professional Tone"
    ];
    const BODY_LANGUAGE_ALGORITHM_NAMES = [
        "Eye Contact Orientation",
        "Posture Stability",
        "Head Movement Control",
        "Camera Framing Support"
    ];
    const FACIAL_EXPRESSION_ALGORITHM_NAMES = [
        "Facial Composure",
        "Eye Engagement",
        "Jaw Relaxation",
        "Brow Calmness"
    ];

    function clampScore(value, fallback = 0) {
        const numeric = Number(value);

        if (!Number.isFinite(numeric)) {
            return fallback;
        }

        return Math.max(0, Math.min(10, numeric));
    }

    function roundScore(value, fallback = 0) {
        return Number(clampScore(value, fallback).toFixed(1));
    }

    function getLearningActivityMeta(activityId) {
        return learningActivityProgression[String(activityId || "")] || null;
    }

    function getLearningActivityLevel(activityId, requestedLevel = 1) {
        const meta = getLearningActivityMeta(activityId);

        if (!meta) {
            return null;
        }

        const levels = meta.levels || [];
        const numericLevel = Number(requestedLevel);

        return levels.find((level) => Number(level.level) === numericLevel) || levels[0] || null;
    }

    function setLearningActivityContext(activityId, moduleId = "", requestedLevel = 1, requestedTarget = null) {
        const meta = getLearningActivityMeta(activityId);
        const currentLevel = getLearningActivityLevel(activityId, requestedLevel);

        if (!meta || !currentLevel) {
            state.learningActivityContext = null;
            return null;
        }

        const targetScore = Number.isFinite(Number(requestedTarget))
            ? roundScore(requestedTarget, currentLevel.targetScore)
            : roundScore(currentLevel.targetScore, 7);
        const levels = meta.levels || [];
        const nextLevel = levels.find((level) => Number(level.level) > Number(currentLevel.level)) || null;

        state.learningActivityContext = {
            activityId: String(activityId),
            moduleId: String(moduleId || ""),
            activityLabel: meta.label,
            level: Number(currentLevel.level),
            levelLabel: currentLevel.label,
            targetScore,
            questionFocus: currentLevel.questionFocus,
            nextLevel: nextLevel ? {
                level: Number(nextLevel.level),
                levelLabel: nextLevel.label,
                targetScore: roundScore(nextLevel.targetScore, targetScore),
                questionFocus: nextLevel.questionFocus
            } : null
        };

        return state.learningActivityContext;
    }

    function buildLearningActivityInstruction() {
        const context = state.learningActivityContext;

        if (!context) {
            return "";
        }

        const nextLevelText = context.nextLevel
            ? `If the user passes, the next level is ${context.nextLevel.levelLabel} with a ${context.nextLevel.targetScore.toFixed(1)} / 10 target.`
            : "If the user passes, this is the final activity level.";

        return [
            `Learning activity: ${context.activityLabel}.`,
            `Current level: ${context.levelLabel}.`,
            `Target score: ${context.targetScore.toFixed(1)} / 10.`,
            `Question focus: ${context.questionFocus}`,
            "Generate questions that match this activity level and stay inside the selected interview category.",
            nextLevelText
        ].join(" ");
    }

    function buildLearningActivityScoreMessage(score) {
        const context = state.learningActivityContext;

        if (!context) {
            return "";
        }

        const scoreLabel = `${roundScore(score, 0).toFixed(1)} / 10`;
        const targetLabel = `${context.targetScore.toFixed(1)} / 10`;

        if (roundScore(score, 0) < context.targetScore) {
            return ` Learning activity score: ${scoreLabel}. Target: ${targetLabel}. Try ${context.activityLabel} ${context.levelLabel} again before moving to the next level.`;
        }

        if (!context.nextLevel) {
            return ` Learning activity score: ${scoreLabel}. Target: ${targetLabel}. Passed. You completed the final level for ${context.activityLabel}.`;
        }

        const nextUrl = new URL(window.location.href);
        nextUrl.searchParams.set("source", "learning-lab");
        nextUrl.searchParams.set("activity", context.activityId);
        nextUrl.searchParams.set("level", String(context.nextLevel.level));
        nextUrl.searchParams.set("target", context.nextLevel.targetScore.toFixed(1));

        if (context.moduleId) {
            nextUrl.searchParams.set("module", context.moduleId);
        }

        if (state.selectedCategory?.id) {
            nextUrl.searchParams.set("category", state.selectedCategory.id);
        }

        return ` Learning activity score: ${scoreLabel}. Target: ${targetLabel}. Passed. Next level: ${context.nextLevel.levelLabel} with a ${context.nextLevel.targetScore.toFixed(1)} / 10 target. Open ${nextUrl.pathname}${nextUrl.search} to continue.`;
    }

    function averageScore(values, fallback = 0) {
        const numericValues = values
            .filter((value) => value !== null && value !== undefined && value !== "")
            .map((value) => Number(value))
            .filter((value) => Number.isFinite(value));

        if (!numericValues.length) {
            return roundScore(fallback);
        }

        return roundScore(numericValues.reduce((sum, value) => sum + value, 0) / numericValues.length);
    }

    function getScoreTone(score, { available = true } = {}) {
        if (!available || score === null || score === undefined) {
            return "neutral";
        }

        const normalized = clampScore(score);

        if (normalized >= 8) return "success";
        if (normalized >= 6) return "neutral";
        if (normalized >= 4) return "warning";
        return "error";
    }

    function getToneBadgeClass(tone = "neutral", subtle = false) {
        const baseClass = subtle
            ? "inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-medium"
            : "inline-flex items-center rounded-full px-3 py-1 text-xs font-medium";
        const tones = {
            success: "bg-success-100 text-success-700 dark:bg-success-500/10 dark:text-success-300",
            neutral: "bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300",
            warning: "bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300",
            error: "bg-error-100 text-error-700 dark:bg-error-500/10 dark:text-error-300"
        };

        return `${baseClass} ${tones[tone] || tones.neutral}`;
    }

    function getScoreLabel(score, fallback = "Waiting") {
        if (score === null || score === undefined || score === "") {
            return fallback;
        }

        return `${roundScore(score).toFixed(1)} / 10`;
    }

    function createUnavailableAlgorithms(names, detail, status = "Waiting") {
        return names.map((name) => ({
            name,
            score: null,
            detail,
            status,
            available: false
        }));
    }

    function createUnavailableProcessEvaluation(label, summary, algorithmNames) {
        return {
            label,
            average: null,
            summary,
            status: "Waiting",
            available: false,
            algorithms: createUnavailableAlgorithms(algorithmNames, summary)
        };
    }

    function createDefaultVisualSnapshot(reason = "Turn on the camera to unlock selected non-verbal coaching.") {
        return {
            headline: "Camera coaching is waiting",
            tip: reason,
            tag: "Standby",
            tagTone: "neutral",
            criteria: {
                eyeContactScore: null,
                postureScore: null,
                headMovementScore: null,
                facialComposureScore: null,
                eyeContactLabel: reason,
                postureLabel: reason,
                headMovementLabel: reason,
                facialComposureLabel: reason
            },
            bodyLanguage: createUnavailableProcessEvaluation(
                "Body Language",
                reason,
                BODY_LANGUAGE_ALGORITHM_NAMES
            ),
            facialExpressions: createUnavailableProcessEvaluation(
                "Facial Expressions",
                reason,
                FACIAL_EXPRESSION_ALGORITHM_NAMES
            )
        };
    }

    function getLiveVisualSnapshot() {
        return state.liveVisualSnapshot || createDefaultVisualSnapshot();
    }

    function getPersistedVisualSnapshot() {
        const visualSnapshot = getLiveVisualSnapshot();
        const criteria = visualSnapshot.criteria || {};

        return {
            bodyLanguageScore: visualSnapshot.bodyLanguage.average,
            facialExpressionScore: visualSnapshot.facialExpressions.average,
            eyeContactScore: criteria.eyeContactScore,
            postureScore: criteria.postureScore,
            headMovementScore: criteria.headMovementScore,
            facialComposureScore: criteria.facialComposureScore,
            bodyLanguageLabel: visualSnapshot.bodyLanguage.available
                ? visualSnapshot.bodyLanguage.summary
                : visualSnapshot.bodyLanguage.status,
            facialExpressionLabel: visualSnapshot.facialExpressions.available
                ? visualSnapshot.facialExpressions.summary
                : visualSnapshot.facialExpressions.status,
            eyeContactLabel: criteria.eyeContactLabel || visualSnapshot.bodyLanguage.status,
            postureLabel: criteria.postureLabel || visualSnapshot.bodyLanguage.status,
            headMovementLabel: criteria.headMovementLabel || visualSnapshot.bodyLanguage.status,
            facialComposureLabel: criteria.facialComposureLabel || visualSnapshot.facialExpressions.status,
            tip: visualSnapshot.tip
        };
    }

    function summarizeScoreBand(score, high, medium, low) {
        const normalized = clampScore(score);

        if (normalized >= 8) return high;
        if (normalized >= 6) return medium;
        return low;
    }

    function renderAlgorithmCards(algorithms, emptyText = "No algorithms available yet.") {
        if (!Array.isArray(algorithms) || algorithms.length === 0) {
            return `
                <div class="rounded-xl border border-dashed border-gray-300 px-4 py-4 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    ${escapeHtml(emptyText)}
                </div>
            `;
        }

        return algorithms.map((algorithm) => {
            const tone = getScoreTone(algorithm.score, { available: algorithm.available !== false });
            const label = algorithm.available === false
                ? escapeHtml(String(algorithm.status || "Waiting"))
                : escapeHtml(getScoreLabel(algorithm.score, "0.0 / 10"));

            return `
                <div class="rounded-xl border border-gray-200 bg-white/80 px-4 py-3 dark:border-gray-700 dark:bg-gray-950/40">
                    <div class="flex items-start justify-between gap-3">
                        <strong class="text-sm font-semibold text-gray-900 dark:text-white/90">${escapeHtml(algorithm.name || "Algorithm")}</strong>
                        <span class="${getToneBadgeClass(tone, true)}">${label}</span>
                    </div>
                    <p class="mt-2 text-xs leading-5 text-gray-600 dark:text-gray-400">${escapeHtml(algorithm.detail || "No detail available.")}</p>
                </div>
            `;
        }).join("");
    }

    function renderProcessEvaluationPanel(process) {
        const tone = getScoreTone(process?.average, { available: process?.available !== false });
        const valueLabel = process?.available === false
            ? escapeHtml(String(process?.status || "Waiting"))
            : escapeHtml(getScoreLabel(process?.average, "0.0 / 10"));

        return `
            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/70">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">${escapeHtml(process?.label || "Process")}</p>
                        <strong class="mt-2 block text-sm font-semibold text-gray-900 dark:text-white/90">${escapeHtml(process?.summary || "No process summary yet.")}</strong>
                    </div>
                    <span class="${getToneBadgeClass(tone)}">${valueLabel}</span>
                </div>

                <div class="mt-4 space-y-3">
                    ${renderAlgorithmCards(process?.algorithms || [], "Process algorithms will appear here.")}
                </div>
            </div>
        `;
    }

    function isPracticeModalOpen() {
        return Boolean(elements.practiceSessionModal) && !elements.practiceSessionModal.classList.contains("hidden");
    }

    function isCategoryModalOpen() {
        return Boolean(elements.practiceCategoryModal) && !elements.practiceCategoryModal.classList.contains("hidden");
    }

    function isQuestionAgentModalOpen() {
        return Boolean(elements.practiceQuestionAgentModal) && !elements.practiceQuestionAgentModal.classList.contains("hidden");
    }

    function lockBodyScroll() {
        const activeModalCount = Number(document.body.dataset.practiceModalCount || "0") + 1;

        document.body.dataset.practiceModalCount = String(activeModalCount);
        document.body.style.overflow = "hidden";
    }

    function unlockBodyScroll() {
        const activeModalCount = Math.max(Number(document.body.dataset.practiceModalCount || "0") - 1, 0);

        if (activeModalCount === 0) {
            delete document.body.dataset.practiceModalCount;
            document.body.style.overflow = "";
            return;
        }

        document.body.dataset.practiceModalCount = String(activeModalCount);
    }

    function setPracticeModalStateTag(text, tone = "neutral") {
        const tones = {
            neutral: "inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300",
            success: "inline-flex items-center rounded-full bg-success-100 px-3 py-1 text-xs font-medium text-success-700 dark:bg-success-500/10 dark:text-success-300",
            warning: "inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-700 dark:bg-amber-500/10 dark:text-amber-300"
        };

        elements.practiceModalStateTag.textContent = text;
        elements.practiceModalStateTag.className = tones[tone] || tones.neutral;
    }

    function isFieldModalOpen() {
        return Boolean(elements.practiceFieldModal) && !elements.practiceFieldModal.classList.contains("hidden");
    }

    function setFieldBuilderStatusTag(text, tone = "neutral") {
        const tones = {
            neutral: "inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-300",
            success: "inline-flex items-center rounded-full bg-success-100 px-3 py-1 text-xs font-medium text-success-700 dark:bg-success-500/10 dark:text-success-300",
            warning: "inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-700 dark:bg-amber-500/10 dark:text-amber-300",
            error: "inline-flex items-center rounded-full bg-error-100 px-3 py-1 text-xs font-medium text-error-700 dark:bg-error-500/10 dark:text-error-300"
        };

        state.fieldBuilderStatusText = text;
        state.fieldBuilderStatusTone = tone;
        elements.practiceFieldModalStatusTag.textContent = text;
        elements.practiceFieldModalStatusTag.className = tones[tone] || tones.neutral;
    }

    function showFieldChatStatus(type, text) {
        const baseClass = "mt-5 rounded-2xl border px-4 py-3 text-sm";
        const tones = {
            success: "border-success-200 bg-success-50 text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-300",
            warning: "border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300",
            error: "border-error-200 bg-error-50 text-error-700 dark:border-error-500/20 dark:bg-error-500/10 dark:text-error-300",
            info: "border-gray-200 bg-gray-50 text-gray-700 dark:border-gray-700 dark:bg-gray-900/70 dark:text-gray-300"
        };

        elements.practiceFieldChatStatus.className = `${baseClass} ${tones[type] || tones.info}`;
        elements.practiceFieldChatStatus.textContent = text;
        elements.practiceFieldChatStatus.classList.remove("hidden");
    }

    function clearFieldChatStatus() {
        elements.practiceFieldChatStatus.classList.add("hidden");
        elements.practiceFieldChatStatus.textContent = "";
    }

    function getSelectedFieldTitle() {
        const activePlan = state.selectedCategory ? (getFieldPlanForCategory(state.selectedCategory) || state.selectedFieldPlan) : state.selectedFieldPlan;
        return String(activePlan?.title || "").trim();
    }

    function getSelectedFieldSummary() {
        const activePlan = state.selectedCategory ? (getFieldPlanForCategory(state.selectedCategory) || state.selectedFieldPlan) : state.selectedFieldPlan;
        return String(activePlan?.summary || "").trim();
    }

    function buildFieldSummaryLine(fieldPlan = state.selectedFieldPlan) {
        const activePlan = fieldPlan || (state.selectedCategory ? getFieldPlanForCategory(state.selectedCategory) : null);
        const title = String(activePlan?.title || "").trim();
        const summary = String(activePlan?.summary || "").trim();

        if (!title) {
            return "";
        }

        return summary ? `Field: ${title}. ${summary}` : `Field: ${title}.`;
    }

    function buildFieldInstructionSuffix(fieldPlan = state.selectedFieldPlan) {
        const activePlan = fieldPlan || (state.selectedCategory ? getFieldPlanForCategory(state.selectedCategory) : null);
        const instruction = String(activePlan?.instruction || "").trim();
        const summaryLine = buildFieldSummaryLine(activePlan);

        return [instruction, summaryLine]
            .filter(Boolean)
            .join(" ");
    }

    function getFieldPlanForCategory(category = state.selectedCategory) {
        const categoryId = String(category?.id || "").trim();

        if (!categoryId) {
            return null;
        }

        return state.fieldPlanByCategoryId[categoryId] || null;
    }

    function updateFieldSummaryUI() {
        const activePlan = state.selectedCategory ? (getFieldPlanForCategory(state.selectedCategory) || state.selectedFieldPlan) : state.selectedFieldPlan;
        const fieldTitle = String(activePlan?.title || "").trim();
        const fieldSummary = String(activePlan?.summary || "").trim();
        const fieldMeta = fieldSummary || "Choose a sidebar track to create a field with the chatbot.";

        elements.practiceModalFieldValue.textContent = fieldTitle || "Not set";
        elements.practiceModalFieldMeta.textContent = fieldMeta;
        elements.selectedPracticeFieldTag.textContent = fieldTitle ? `Field: ${fieldTitle}` : "Field not set";
        elements.editPracticeFieldBtn.disabled = !state.selectedCategory && !state.pendingCategory;
    }

    function getQuestionAgentProviderLabel() {
        const provider = getQuestionAgentProviderById(state.questionAgentSelectedProviderId) || getQuestionAgentProviderById("auto");
        return getQuestionAgentProviderSummary(provider) || "Auto";
    }

    function updatePracticeModalSummary() {
        const workspaceVisible = isPracticeModalOpen();
        const questionTotal = state.selectedCategory ? (getActiveQuestionCount() || state.questionCount) : 0;
        const answeredLabel = `${state.answeredCount} / ${questionTotal}`;
        const fieldTitle = getSelectedFieldTitle();
        const fieldSummary = getSelectedFieldSummary();

        elements.practiceModalAnsweredValue.textContent = answeredLabel;
        elements.practiceModalWorkspaceValue.textContent = workspaceVisible ? "Visible" : "Hidden";
        elements.practiceModalFieldValue.textContent = fieldTitle || "Not set";
        elements.practiceModalFieldMeta.textContent = fieldSummary || "Choose a sidebar track to create a field with the chatbot.";
        elements.editPracticeFieldBtn.disabled = !state.selectedCategory && !state.pendingCategory;

        if (!state.selectedCategory) {
            if (state.pendingCategory) {
                elements.practiceModalCategoryName.textContent = `${state.pendingCategory.name} selected`;
                elements.practiceModalSummaryText.textContent = `Finish the field builder for ${state.pendingCategory.name} before opening the workspace.`;
                elements.practiceModalActiveCategory.textContent = state.pendingCategory.name;
                elements.openPracticeModalBtn.textContent = "Focus Live Interview";
                elements.openPracticeModalBtn.disabled = true;
                elements.editPracticeFieldBtn.disabled = false;
                setPracticeModalStateTag("Field setup", "warning");
                return;
            }

            elements.practiceModalCategoryName.textContent = "Choose a track for live practice";
            elements.practiceModalSummaryText.textContent = "Choose a track from the sidebar. The interviewer will ask aloud and continue after each submitted answer.";
            elements.practiceModalActiveCategory.textContent = "None selected";
            elements.openPracticeModalBtn.textContent = "Focus Live Interview";
            elements.openPracticeModalBtn.disabled = true;
            elements.editPracticeFieldBtn.disabled = true;
            setPracticeModalStateTag("Waiting", "neutral");
            return;
        }

        elements.practiceModalCategoryName.textContent = `${state.selectedCategory.name} workspace`;
        elements.practiceModalActiveCategory.textContent = state.selectedCategory.name;
        elements.editPracticeFieldBtn.disabled = false;

        if (state.questionAgentLoading) {
            elements.practiceModalSummaryText.textContent = `Generating a fresh ${state.questionCount}-question set for ${state.selectedCategory.name}${fieldTitle ? ` focused on ${fieldTitle}` : ""} inside the Interview Workspace.`;
            elements.openPracticeModalBtn.textContent = workspaceVisible ? "Focus Live Interview" : "Enter Live Interview";
            elements.openPracticeModalBtn.disabled = false;
            setPracticeModalStateTag("Preparing", "warning");
            return;
        }

        if (workspaceVisible) {
            elements.openPracticeModalBtn.textContent = "Focus Live Interview";
            elements.openPracticeModalBtn.disabled = false;

            if (getActiveQuestionCount() > 0) {
                elements.practiceModalSummaryText.textContent = `${state.questionSetSummary} Continue your live interview below.`;
                setPracticeModalStateTag("Live", "success");
                return;
            }

            elements.practiceModalSummaryText.textContent = `${state.selectedCategory.name}${fieldTitle ? ` for ${fieldTitle}` : ""} is open below. Generate a fresh question set for the interviewer.`;
            setPracticeModalStateTag("Visible", "neutral");
            return;
        }

        elements.openPracticeModalBtn.disabled = false;
        elements.openPracticeModalBtn.textContent = "Enter Live Interview";

        if (state.timerPaused) {
            elements.practiceModalSummaryText.textContent = `${state.selectedCategory.name}${fieldTitle ? ` for ${fieldTitle}` : ""} is paused. Reopen the workspace to continue from the current question.`;
            setPracticeModalStateTag("Paused", "warning");
            return;
        }

        elements.practiceModalSummaryText.textContent = getActiveQuestionCount() > 0
            ? `${state.questionSetSummary} Open the workspace to continue the interview and AI interviewer tools.`
            : `${state.selectedCategory.name}${fieldTitle ? ` for ${fieldTitle}` : ""} is ready. Open the workspace to generate a fresh AI question set.`;
        setPracticeModalStateTag("Ready", "neutral");
    }

    function syncOpeningIntroduction() {
        if (!elements.openingIntroductionSection || !elements.openingIntroductionText) {
            return;
        }

        const shouldShow = Boolean(state.selectedCategory) && getActiveQuestionCount() > 0 && state.questionIndex === 0;

        elements.openingIntroductionSection.classList.toggle("hidden", !shouldShow);

        if (!shouldShow) {
            return;
        }

        elements.openingIntroductionText.textContent = buildInterviewerWelcomeLead();
    }

    function startTimer({ reset = true } = {}) {
        if (state.timerInterval) {
            clearInterval(state.timerInterval);
            state.timerInterval = null;
        }

        if (reset) {
            state.timerSeconds = 0;
            state.timerAlertShown = false;
        }

        elements.questionTimerValue.textContent = formatTime(state.timerSeconds);
        state.timerPaused = false;

        state.timerInterval = setInterval(() => {
            state.timerSeconds += 1;
            elements.questionTimerValue.textContent = formatTime(state.timerSeconds);

            if (!state.timerAlertShown && state.timerSeconds >= state.timerTarget) {
                state.timerAlertShown = true;
                showMessage("warning", `Target time reached for this answer (${formatTime(state.timerTarget)}).`);
            }
        }, 1000);
    }

    function stopTimer({ pause = false } = {}) {
        if (state.timerInterval) {
            clearInterval(state.timerInterval);
            state.timerInterval = null;
        }

        state.timerPaused = pause && Boolean(state.selectedCategory);
    }

    function pauseTimer() {
        if (!state.timerInterval) {
            return;
        }

        stopTimer({ pause: true });
        updatePracticeModalSummary();
    }

    function resumeTimerIfNeeded() {
        if (!state.selectedCategory || !state.timerPaused) {
            return;
        }

        startTimer({ reset: false });
    }

    function cancelAutoAdvance() {
        if (!state.autoAdvanceTimeout) {
            return;
        }

        window.clearTimeout(state.autoAdvanceTimeout);
        state.autoAdvanceTimeout = null;
    }

    function cancelVoiceAutoSubmit() {
        if (!state.voiceAutoSubmitTimeout) {
            return;
        }

        window.clearTimeout(state.voiceAutoSubmitTimeout);
        state.voiceAutoSubmitTimeout = null;
    }

    function cancelPendingQuestionSpeech() {
        if (!state.questionSpeechTimeout) {
            return;
        }

        window.clearTimeout(state.questionSpeechTimeout);
        state.questionSpeechTimeout = null;
    }

    function openPracticeModal({ focusResponse = false, playOpening = false } = {}) {
        if (!elements.practiceSessionModal || !state.selectedCategory) {
            return;
        }

        if (!isPracticeModalOpen()) {
            elements.practiceSessionModal.classList.remove("hidden");
            elements.practiceSessionModal.setAttribute("aria-hidden", "false");
        }

        resumeTimerIfNeeded();
        updatePracticeModalSummary();
        elements.practiceSessionModal.scrollIntoView({ behavior: "smooth", block: "start" });

        if (playOpening) {
            const introductionStarted = playOpeningIntroductionIfNeeded({
                startQuestionAfter: Boolean(getActiveQuestionCount() > 0),
                focusResponse
            });

            if (introductionStarted) {
                return;
            }
        }

        if (focusResponse) {
            window.setTimeout(() => {
                elements.responseInput.focus();
            }, 0);
        }
    }

    function handleStartPracticeClick() {
        if (state.selectedCategory) {
            openPracticeModal({ focusResponse: Boolean(getActiveQuestionCount() > 0) });
            return;
        }

        if (state.pendingCategory) {
            openPracticeFieldModal(state.pendingCategory, {
                prefillNeed: getFieldPlanForCategory(state.pendingCategory)?.userNeed || ""
            });
            return;
        }

        openCategoryModal();
    }

    function openCategoryModal() {
        if (!elements.practiceCategoryModal) {
            return;
        }

        if (!isCategoryModalOpen()) {
            elements.practiceCategoryModal.classList.remove("hidden");
            elements.practiceCategoryModal.classList.add("flex");
            elements.practiceCategoryModal.setAttribute("aria-hidden", "false");
            lockBodyScroll();
        }

        window.setTimeout(() => {
            elements.practiceCategoryList?.querySelector("button")?.focus();
        }, 0);
    }

    function closeCategoryModal({ returnFocus = true } = {}) {
        if (!elements.practiceCategoryModal || !isCategoryModalOpen()) {
            return;
        }

        elements.practiceCategoryModal.classList.add("hidden");
        elements.practiceCategoryModal.classList.remove("flex");
        elements.practiceCategoryModal.setAttribute("aria-hidden", "true");
        unlockBodyScroll();

        if (returnFocus) {
            elements.openPracticeCategoryModalBtn?.focus();
        }
    }

    function openQuestionAgentModal({ focusInput = false } = {}) {
        if (!elements.practiceQuestionAgentModal) {
            return;
        }

        if (!isQuestionAgentModalOpen()) {
            elements.practiceQuestionAgentModal.classList.remove("hidden");
            elements.practiceQuestionAgentModal.classList.add("flex");
            elements.practiceQuestionAgentModal.setAttribute("aria-hidden", "false");
            lockBodyScroll();
        }

        if (focusInput) {
            window.setTimeout(() => {
                elements.practiceQuestionAgentInput.focus();
            }, 0);
        }
    }

    function closeQuestionAgentModal({ returnFocus = true } = {}) {
        if (!elements.practiceQuestionAgentModal || !isQuestionAgentModalOpen()) {
            return;
        }

        elements.practiceQuestionAgentModal.classList.add("hidden");
        elements.practiceQuestionAgentModal.classList.remove("flex");
        elements.practiceQuestionAgentModal.setAttribute("aria-hidden", "true");
        unlockBodyScroll();

        if (returnFocus) {
            elements.openPracticeQuestionAgentModalBtn?.focus();
        }
    }

    function closePracticeModal({ returnFocus = true } = {}) {
        if (!elements.practiceSessionModal || !isPracticeModalOpen()) {
            return;
        }

        pauseTimer();
        cancelPendingQuestionSpeech();
        stopVoiceInput({ commitTranscript: false });
        interviewerControls.stopSpeaking();
        interviewerControls.stopCamera();

        elements.practiceSessionModal.classList.add("hidden");
        elements.practiceSessionModal.setAttribute("aria-hidden", "true");

        updatePracticeModalSummary();

        if (returnFocus) {
            elements.openPracticeModalBtn.focus();
        }
    }

    function showMessage(type, text) {
        const baseClass = "rounded-xl border px-4 py-3 text-sm";
        const map = {
            success: "border-success-200 bg-success-50 text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-300",
            warning: "border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300",
            error: "border-error-200 bg-error-50 text-error-700 dark:border-error-500/20 dark:bg-error-500/10 dark:text-error-300",
            info: "border-gray-200 bg-gray-50 text-gray-700 dark:border-gray-700 dark:bg-gray-900/70 dark:text-gray-300"
        };

        elements.practiceMessage.className = `${baseClass} ${map[type] || map.info}`;
        elements.practiceMessage.textContent = text;
        elements.practiceMessage.classList.remove("hidden");
    }

    function clearMessage() {
        elements.practiceMessage.classList.add("hidden");
        elements.practiceMessage.textContent = "";
    }

    function truncateText(value, maxLength = 96) {
        if (value.length <= maxLength) {
            return value;
        }

        return `${value.slice(0, maxLength - 3)}...`;
    }

    function createSessionId() {
        return `session-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;
    }

    function populateSelects() {
        elements.focusModeSelect.innerHTML = practiceData.focusModes
            .map((mode, index) => `<option value="${index}">${mode.label}</option>`)
            .join("");

        elements.pacingModeSelect.innerHTML = practiceData.pacingModes
            .map((mode, index) => `<option value="${index}">${mode.label} (${formatTime(mode.seconds)})</option>`)
            .join("");
    }

    function getCategoryButtonClass(isActive = false) {
        if (isActive) {
            return "category-btn flex h-full min-h-[120px] w-full flex-col rounded-xl border border-brand-300 bg-brand-50 px-4 py-4 text-left transition sm:min-h-[140px] dark:border-brand-500/30 dark:bg-brand-500/10";
        }

        return "category-btn flex h-full min-h-[120px] w-full flex-col rounded-xl border border-gray-200 bg-gray-50 px-4 py-4 text-left transition sm:min-h-[140px] hover:border-brand-300 hover:bg-brand-50 dark:border-gray-800 dark:bg-gray-900/60 dark:hover:border-brand-500/30 dark:hover:bg-brand-500/10";
    }

    function renderCategories() {
        if (!elements.practiceCategoryList) {
            return;
        }

        elements.practiceCategoryList.innerHTML = practiceData.categories
            .map((category) => `
                <button
                    type="button"
                    aria-label="Choose ${category.name}"
                    data-category-id="${category.id}"
                    class="${getCategoryButtonClass()}"
                >
                    <strong class="content-break block text-sm font-semibold text-gray-900 dark:text-white/90">${category.name}</strong>
                    <span class="content-break mt-2 block text-sm leading-6 text-gray-600 dark:text-gray-400">${category.description}</span>
                </button>
            `)
            .join("");

        document.querySelectorAll(".category-btn").forEach((button) => {
            button.addEventListener("click", () => {
                const category = practiceData.categories.find((item) => item.id === button.dataset.categoryId);
                if (category) {
                    closeCategoryModal({ returnFocus: false });
                    selectCategory(category);
                }
            });
        });
    }

    function buildFieldBuilderWelcomeMessage(category, fieldPlan = null) {
        if (!category) {
            return "Choose a category first, then I can help you create a focused practice field before the interview workspace opens.";
        }

        if (fieldPlan?.title) {
            return `Your current field for ${category.name} is ${fieldPlan.title}. Refine it or build a new one with the chatbot before continuing to practice.`;
        }

        return `Tell me the ${String(category.fieldLabel || "field").toLowerCase()} you want for ${category.name}, and I will turn it into a focused practice setup before the workspace opens.`;
    }

    function buildFallbackFieldSummary(category, title, userNeed = "") {
        const detail = String(userNeed || "").trim();
        const detailLine = detail ? ` ${truncateText(detail, 140)}` : "";

        if (!category) {
            return `${title} is ready for interview practice.${detailLine}`;
        }

        if (category.id === "job") {
            return `${title} is set as your job target, so practice will focus on hiring-fit, strengths, experience, and role expectations in the Philippines.${detailLine}`;
        }

        if (category.id === "scholarship") {
            return `${title} is set as your scholarship study focus, so practice will stay aligned with your goals, discipline, need, and future contribution.${detailLine}`;
        }

        if (category.id === "admission") {
            return `${title} is set as your admission course focus, so practice will cover course fit, readiness, motivation, and future plans.${detailLine}`;
        }

        return `${title} is set as your IT practice focus, so questions will center on projects, tools, problem-solving, and junior-role fit.${detailLine}`;
    }

    function buildFallbackFieldInstruction(category, title, userNeed = "") {
        const detail = String(userNeed || "").trim();
        const baseInstruction = category?.id === "scholarship"
            ? `Generate scholarship interview questions for a student pursuing ${title} in the Philippines.`
            : category?.id === "admission"
                ? `Generate college admission interview questions for a student applying to ${title} in the Philippines.`
                : category?.id === "it"
                    ? `Generate Philippine IT interview questions focused on ${title}.`
                    : `Generate Philippine job interview questions focused on ${title}.`;

        return detail ? `${baseInstruction} Use this additional context: ${detail}` : baseInstruction;
    }

    function normalizeFieldPlan(category, fieldPlan = {}, fallback = {}) {
        const title = String(fieldPlan?.title || fallback.title || "").replace(/\s+/g, " ").trim();

        if (!title) {
            return null;
        }

        const userNeed = String(fieldPlan?.userNeed || fallback.userNeed || "").replace(/\s+/g, " ").trim();
        const summary = String(
            fieldPlan?.summary
            || fallback.summary
            || buildFallbackFieldSummary(category, title, userNeed)
        ).replace(/\s+/g, " ").trim();
        const instruction = String(
            fieldPlan?.instruction
            || fallback.instruction
            || buildFallbackFieldInstruction(category, title, userNeed)
        ).replace(/\s+/g, " ").trim();
        const suggestionSource = [
            title,
            ...(Array.isArray(fieldPlan?.suggestions) ? fieldPlan.suggestions : []),
            ...(Array.isArray(category?.fieldSuggestions) ? category.fieldSuggestions : [])
        ];
        const suggestions = Array.from(new Set(
            suggestionSource
                .map((item) => String(item || "").replace(/\s+/g, " ").trim())
                .filter(Boolean)
        )).slice(0, 4);

        return {
            title,
            summary,
            instruction,
            suggestions,
            userNeed,
            provider: String(fieldPlan?.provider || fallback.provider || "").trim(),
            categoryId: String(category?.id || "")
        };
    }

    function createFieldPlanFromInputs(category = state.pendingCategory || state.selectedCategory) {
        return normalizeFieldPlan(category, {}, {
            title: elements.practiceFieldInput.value,
            userNeed: elements.practiceFieldNeedInput.value
        });
    }

    function renderFieldSuggestionChips(category = state.pendingCategory || state.selectedCategory) {
        const activeCategory = category;
        const activePlan = activeCategory ? getFieldPlanForCategory(activeCategory) : null;
        const suggestions = Array.from(new Set([
            ...(activePlan?.suggestions || []),
            ...(activeCategory?.fieldSuggestions || [])
        ])).filter(Boolean);

        elements.practiceFieldSuggestionChips.innerHTML = suggestions.length === 0
            ? `<span class="text-sm text-gray-500 dark:text-gray-400">Choose a category to unlock field suggestions.</span>`
            : suggestions.map((suggestion) => `
                <button
                    type="button"
                    data-field-suggestion="${escapeHtml(suggestion)}"
                    class="content-break inline-flex max-w-full items-center rounded-full border border-gray-200 bg-white px-3 py-1.5 text-left text-xs font-medium text-gray-600 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700 dark:border-gray-700 dark:bg-gray-900/70 dark:text-gray-300 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-200"
                >
                    ${escapeHtml(suggestion)}
                </button>
            `).join("");

        elements.practiceFieldSuggestionChips.querySelectorAll("[data-field-suggestion]").forEach((button) => {
            button.addEventListener("click", () => {
                elements.practiceFieldInput.value = String(button.dataset.fieldSuggestion || "");
                const preview = createFieldPlanFromInputs(activeCategory);

                if (preview) {
                    elements.practiceFieldPreviewTitle.textContent = preview.title;
                    elements.practiceFieldPreviewSummary.textContent = preview.summary;
                }

                clearFieldChatStatus();
                syncFieldBuilderControls();
                elements.practiceFieldInput.focus();
            });
        });
    }

    function renderFieldBuilderMessages() {
        if (state.fieldBuilderHistory.length === 0) {
            elements.practiceFieldChatMessages.innerHTML = `
                <div class="content-break rounded-2xl border border-dashed border-gray-300 px-4 py-5 text-sm leading-6 text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    Share the role, course, or specialization you want, and the chatbot will prepare a focused field before practice begins.
                </div>
            `;
            return;
        }

        elements.practiceFieldChatMessages.innerHTML = state.fieldBuilderHistory.map((item) => {
            const isAssistant = item.role === "assistant";
            const wrapperClass = isAssistant ? "items-start" : "items-end";
            const bubbleClass = isAssistant
                ? "border-gray-200 bg-white text-gray-700 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300"
                : "border-brand-200 bg-brand-50 text-brand-700 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-200";

            return `
                <div class="flex flex-col ${wrapperClass} gap-2">
                    <span class="text-xs font-medium uppercase tracking-wide text-gray-400">${isAssistant ? "Field Chatbot" : "Your Goal"}</span>
                    <div class="content-break max-w-full rounded-2xl border px-4 py-3 text-sm leading-6 ${bubbleClass}">
                        ${escapeHtml(item.text).replace(/\n/g, "<br>")}
                    </div>
                </div>
            `;
        }).join("");

        elements.practiceFieldChatMessages.scrollTo({
            top: elements.practiceFieldChatMessages.scrollHeight,
            behavior: "auto"
        });
    }

    function syncFieldBuilderControls() {
        const hasCategory = Boolean(state.pendingCategory || state.selectedCategory);
        const hasDraft = Boolean(String(elements.practiceFieldInput.value || "").trim());

        if (elements.practiceFieldProviderSelect) {
            elements.practiceFieldProviderSelect.disabled = state.fieldBuilderLoading;
        }
        elements.practiceFieldGenerateBtn.disabled = !hasCategory || state.fieldBuilderLoading;
        elements.practiceFieldGenerateBtn.textContent = state.fieldBuilderLoading ? "Building..." : "Build With Chatbot";
        elements.practiceFieldResetBtn.disabled = !hasCategory || state.fieldBuilderLoading;
        elements.practiceFieldApplyBtn.disabled = !hasCategory || state.fieldBuilderLoading || !hasDraft;
    }

    function syncFieldBuilderModal(category = state.pendingCategory || state.selectedCategory) {
        const activeCategory = category;
        const activePlan = activeCategory ? getFieldPlanForCategory(activeCategory) : null;
        const previewPlan = createFieldPlanFromInputs(activeCategory) || activePlan;
        const selectedProvider = getQuestionAgentProviderById(state.questionAgentSelectedProviderId) || getQuestionAgentProviderById("auto");
        const allApiLabels = state.questionAgentProviderCatalog
            .filter((provider) => provider.type === "remote")
            .map((provider) => getQuestionAgentProviderSummary(provider))
            .filter(Boolean)
            .join(", ");
        const configuredApiCount = state.questionAgentProviderCatalog.filter((provider) => provider.type === "remote" && provider.configured).length;
        let providerHelpText = "Choose which API should build the field plan.";

        if (selectedProvider?.id === "auto") {
            providerHelpText = configuredApiCount > 0
                ? `Available APIs: ${allApiLabels}. Auto uses your configured provider order before local fallback.`
                : `Available APIs: ${allApiLabels}. Add API keys in .env to enable them, or keep using the local PH coach.`;
        } else if (selectedProvider?.id === "local") {
            providerHelpText = "Use the built-in local PH coach without calling an external API.";
        } else if (selectedProvider?.configured) {
            providerHelpText = `${selectedProvider.description}${selectedProvider.model ? ` Model: ${selectedProvider.model}.` : ""}`;
        } else if (selectedProvider) {
            providerHelpText = `${selectedProvider.description} Add its API key in .env to enable it.`;
        }

        elements.practiceFieldProviderValue.textContent = `Provider: ${getQuestionAgentProviderLabel()}`;
        if (elements.practiceFieldProviderSelect) {
            elements.practiceFieldProviderSelect.value = state.questionAgentSelectedProviderId;
        }
        if (elements.practiceFieldProviderHelpText) {
            elements.practiceFieldProviderHelpText.textContent = providerHelpText;
        }
        elements.practiceFieldModalCategoryName.textContent = activeCategory ? activeCategory.name : "Choose a category first";
        elements.practiceFieldModalCategoryDescription.textContent = activeCategory
            ? activeCategory.description
            : "Your selected category will appear here together with starter suggestions for the chatbot.";
        if (activeCategory) {
            elements.practiceFieldInput.placeholder = activeCategory.fieldPlaceholder || "Example: Junior Laravel Developer";
            elements.practiceFieldNeedInput.placeholder = activeCategory.fieldNeedPlaceholder || "Describe what you need from this practice.";
        }

        elements.practiceFieldPreviewTitle.textContent = previewPlan?.title || "No field created yet";
        elements.practiceFieldPreviewSummary.textContent = previewPlan?.summary
            || "Tell the chatbot what role, course, or specialization you want so the practice questions can be tailored before the workspace opens.";

        renderFieldSuggestionChips(activeCategory);
        renderFieldBuilderMessages();
        syncFieldBuilderControls();
        updateFieldSummaryUI();
    }

    function resetFieldBuilderForCategory(category, { prefillNeed = "", preserveInputs = false } = {}) {
        const activePlan = getFieldPlanForCategory(category);

        state.pendingCategory = category;
        if (state.selectedCategory?.id === category?.id) {
            state.selectedFieldPlan = activePlan || state.selectedFieldPlan;
        }
        state.fieldBuilderHistory = [{
            role: "assistant",
            text: buildFieldBuilderWelcomeMessage(category, activePlan)
        }];

        if (!preserveInputs) {
            elements.practiceFieldInput.value = activePlan?.title || "";
            elements.practiceFieldNeedInput.value = activePlan?.userNeed || String(prefillNeed || "").trim();
        }

        clearFieldChatStatus();
        setFieldBuilderStatusTag(activePlan ? "Field draft ready" : "Waiting for details", activePlan ? "success" : "neutral");
        updateSelectedCategoryButtons();
        updatePracticeModalSummary();
        syncFieldBuilderModal(category);
    }

    function openPracticeFieldModal(category, { prefillNeed = "", preserveInputs = false, focusInput = true } = {}) {
        if (!elements.practiceFieldModal || !category) {
            return;
        }

        resetFieldBuilderForCategory(category, { prefillNeed, preserveInputs });

        if (!isFieldModalOpen()) {
            elements.practiceFieldModal.classList.remove("hidden");
            elements.practiceFieldModal.classList.add("flex");
            elements.practiceFieldModal.setAttribute("aria-hidden", "false");
            lockBodyScroll();
        }

        if (focusInput) {
            window.setTimeout(() => {
                if (String(elements.practiceFieldInput.value || "").trim()) {
                    elements.practiceFieldNeedInput.focus();
                    return;
                }

                elements.practiceFieldInput.focus();
            }, 0);
        }
    }

    function closePracticeFieldModal({ returnFocus = true, preservePending = false } = {}) {
        if (!elements.practiceFieldModal || !isFieldModalOpen()) {
            return;
        }

        elements.practiceFieldModal.classList.add("hidden");
        elements.practiceFieldModal.classList.remove("flex");
        elements.practiceFieldModal.setAttribute("aria-hidden", "true");
        unlockBodyScroll();

        if (!preservePending) {
            state.pendingCategory = state.selectedCategory || null;
            state.selectedFieldPlan = state.selectedCategory
                ? getFieldPlanForCategory(state.selectedCategory) || state.selectedFieldPlan
                : null;
        }

        updateSelectedCategoryButtons();
        updateFieldSummaryUI();
        updatePracticeModalSummary();

        if (returnFocus) {
            if (state.selectedCategory || state.pendingCategory) {
                elements.editPracticeFieldBtn.focus();
            } else if (!elements.openPracticeModalBtn.disabled) {
                elements.openPracticeModalBtn.focus();
            }
        }
    }

    async function buildFieldPlanWithChatbot() {
        const category = state.pendingCategory || state.selectedCategory;

        if (!category || state.fieldBuilderLoading) {
            return;
        }

        const draftTitle = String(elements.practiceFieldInput.value || "").replace(/\s+/g, " ").trim();
        const draftNeed = String(elements.practiceFieldNeedInput.value || "").replace(/\s+/g, " ").trim();
        const message = [
            draftTitle ? `${category.fieldLabel || "Field"}: ${draftTitle}.` : "",
            draftNeed || `Create a focused ${String(category.fieldLabel || "field").toLowerCase()} for ${category.name}.`
        ].filter(Boolean).join(" ");
        const requestId = state.fieldBuilderRequestId + 1;

        state.fieldBuilderRequestId = requestId;
        state.fieldBuilderLoading = true;
        state.fieldBuilderHistory.push({ role: "user", text: message });
        setFieldBuilderStatusTag("Building field plan", "warning");
        renderFieldBuilderMessages();
        syncFieldBuilderControls();
        clearFieldChatStatus();

        try {
            const payload = await requestWorkspace("chatbot", {
                method: "POST",
                body: {
                    message,
                    mode: "field_builder",
                    providerId: state.questionAgentSelectedProviderId,
                    categoryId: category.id
                }
            });

            if (requestId !== state.fieldBuilderRequestId) {
                return;
            }

            applyQuestionAgentProviderCatalog(payload.availableProviders, payload.requestedProviderId || state.questionAgentSelectedProviderId);

            const fieldPlan = normalizeFieldPlan(category, payload.fieldPlan || {}, {
                title: draftTitle || category.fieldSuggestions?.[0] || category.name,
                userNeed: draftNeed,
                provider: payload.provider
            });

            if (!fieldPlan) {
                throw new Error("The field chatbot returned an empty field plan.");
            }

            state.fieldPlanByCategoryId[category.id] = fieldPlan;
            if (!state.selectedCategory || state.selectedCategory.id === category.id) {
                state.selectedFieldPlan = fieldPlan;
            }
            state.fieldBuilderHistory.push({
                role: "assistant",
                text: String(payload.reply || fieldPlan.summary || `${fieldPlan.title} is ready for practice.`)
            });
            elements.practiceFieldInput.value = fieldPlan.title;
            syncFieldBuilderModal(category);
            setFieldBuilderStatusTag(payload.usedFallback ? "Field plan ready" : "AI field ready", payload.usedFallback ? "neutral" : "success");
            showFieldChatStatus("success", `${fieldPlan.title} is ready. Continue to practice when you are ready.`);
        } catch (error) {
            console.error(error);

            const fallbackPlan = normalizeFieldPlan(category, {}, {
                title: draftTitle || category.fieldSuggestions?.[0] || category.name,
                userNeed: draftNeed
            });

            if (fallbackPlan) {
                state.fieldPlanByCategoryId[category.id] = fallbackPlan;
                if (!state.selectedCategory || state.selectedCategory.id === category.id) {
                    state.selectedFieldPlan = fallbackPlan;
                }
                state.fieldBuilderHistory.push({
                    role: "assistant",
                    text: `${fallbackPlan.title} is ready using the local field builder fallback. You can continue to practice or refine the draft.`
                });
                elements.practiceFieldInput.value = fallbackPlan.title;
                syncFieldBuilderModal(category);
            }

            setFieldBuilderStatusTag("Field builder unavailable", "error");
            showFieldChatStatus("warning", "The chatbot could not finish the field plan right now, but you can still continue with the current draft.");
        } finally {
            if (requestId === state.fieldBuilderRequestId) {
                state.fieldBuilderLoading = false;
                syncFieldBuilderControls();
                updatePracticeModalSummary();
            }
        }
    }

    async function applyFieldPlanAndContinue() {
        const category = state.pendingCategory || state.selectedCategory;
        const fieldPlan = createFieldPlanFromInputs(category);

        if (!category) {
            showFieldChatStatus("warning", "Choose a category first.");
            return;
        }

        if (!fieldPlan) {
            showFieldChatStatus("warning", "Enter a field, role, or course first, or let the chatbot build one for you.");
            return;
        }

        state.fieldPlanByCategoryId[category.id] = fieldPlan;
        state.selectedFieldPlan = fieldPlan;
        setFieldBuilderStatusTag("Field confirmed", "success");
        updateFieldSummaryUI();
        closePracticeFieldModal({ returnFocus: false, preservePending: true });
        await selectCategory(category, { skipFieldModal: true });
    }

    function buildAnswerOutlineTemplate() {
        const focus = getSelectedFocusMode();
        const categoryName = state.selectedCategory?.name || "interview";
        const fieldTitle = getSelectedFieldTitle();
        const question = getActiveQuestion();

        return [
            question ? `Question: ${question}` : "Question: Add your answer focus.",
            "",
            "1. Direct answer:",
            "2. Situation or context:",
            "3. Action you took:",
            "4. Result or lesson:",
            `5. Why this fits ${fieldTitle || categoryName}:`,
            "",
            `Coach reminder: ${focus.tip}`
        ].join("\n");
    }

    function applyLearningAction(action) {
        if (action === "load-outline") {
            if (!state.selectedCategory || getActiveQuestionCount() === 0) {
                showMessage("warning", "Select a category and generate a question set first.");
                return;
            }

            const template = buildAnswerOutlineTemplate();
            const currentText = elements.responseInput.value.trim();

            elements.responseInput.value = currentText
                ? `${currentText}\n\n${template}`
                : template;
            elements.responseInput.focus();
            updateManualResponseState();
            showMessage("info", "A guided answer outline was added to help structure the next response.");
            return;
        }

        if (action === "start-camera") {
            interviewerControls.startCamera();
            showMessage("info", "Camera coaching has been requested so you can review selected non-verbal cues.");
            return;
        }

        if (action === "replay-question") {
            askQuestionLikeConversation();
            showMessage("info", "The current question is being replayed aloud by the interviewer.");
            return;
        }

        if (action === "start-voice") {
            if (!state.speechRecognition) {
                showMessage("warning", "Voice input is not supported in this browser. Use Chrome or Edge on localhost or HTTPS.");
                return;
            }

            startVoiceInput();
        }
    }

    function buildLearningModules() {
        const categoryName = state.selectedCategory?.name || "next interview";
        const fieldTitle = getSelectedFieldTitle();
        const pacing = getSelectedPacingMode();
        const visualSnapshot = getLiveVisualSnapshot();
        const responseProcess = state.lastProcessEvaluations?.response || createUnavailableProcessEvaluation(
            "Answer Process",
            "Submit an answer to unlock answer-process analysis.",
            RESPONSE_ALGORITHM_NAMES
        );
        const bodyScore = visualSnapshot.bodyLanguage.average;
        const faceScore = visualSnapshot.facialExpressions.average;
        const totalQuestions = getActiveQuestionCount() || state.questionCount;

        return [
            {
                title: "Answer Blueprint",
                tag: responseProcess.available === false ? "Ready" : getScoreLabel(responseProcess.average, "Ready"),
                tone: responseProcess.available === false ? "neutral" : getScoreTone(responseProcess.average),
                summary: state.selectedCategory
                    ? `Build a ${getSelectedFocusMode().label.toLowerCase()} response for ${categoryName}${fieldTitle ? ` focused on ${fieldTitle}` : ""}.`
                    : "Select a category to generate a guided answer blueprint for the current prompt.",
                meta: state.selectedCategory
                    ? `Question ${Math.min(state.questionIndex + 1, Math.max(totalQuestions, 1))} of ${Math.max(totalQuestions, 1)}`
                    : "Waiting for category",
                action: "load-outline",
                actionLabel: "Load Outline",
                enabled: Boolean(state.selectedCategory && getActiveQuestionCount() > 0)
            },
            {
                title: "Delivery Rehearsal",
                tag: `${pacing.label} pace`,
                tone: state.currentMode === "Hybrid" ? "success" : "neutral",
                summary: state.selectedCategory
                    ? `Rehearse with ${state.currentMode.toLowerCase()} mode, ${formatTime(state.timerTarget)} timing, and one concise example per answer.`
                    : "Open a session to rehearse answer pacing, flow, and spoken delivery.",
                meta: state.speechRecognition
                    ? "Voice rehearsal is available"
                    : "Use text rehearsal in this browser",
                action: state.speechRecognition ? "start-voice" : "replay-question",
                actionLabel: state.speechRecognition ? "Mic On" : "Repeat Question",
                enabled: Boolean(state.selectedCategory)
            },
            {
                title: "Visual Presence",
                tag: bodyScore === null && faceScore === null
                    ? "Standby"
                    : `${averageScore([bodyScore, faceScore]).toFixed(1)} / 10`,
                tone: bodyScore === null && faceScore === null
                    ? "neutral"
                    : getScoreTone(averageScore([bodyScore, faceScore])),
                summary: visualSnapshot.tip,
                meta: bodyScore === null && faceScore === null
                    ? "Turn on the camera for live coaching"
                    : `Body ${getScoreLabel(bodyScore, "0.0 / 10")} • Face ${getScoreLabel(faceScore, "0.0 / 10")}`,
                action: "start-camera",
                actionLabel: "Camera On",
                enabled: true
            }
        ];
    }

    function buildLearningActivities() {
        const visualSnapshot = getLiveVisualSnapshot();
        const responseProcess = state.lastProcessEvaluations?.response;
        const categoryName = state.selectedCategory?.name || "the selected interview track";

        return [
            {
                title: "STAR Response Drill",
                tag: "Structure",
                tone: responseProcess?.available ? getScoreTone(responseProcess.average) : "neutral",
                summary: responseProcess?.available
                    ? `The latest answer process scored ${getScoreLabel(responseProcess.average, "0.0 / 10")}. Add a STAR outline before answering in ${categoryName}.`
                    : `Prepare the next ${categoryName} answer with a situation, task, action, and result.`,
                action: "load-outline",
                actionLabel: "Add STAR Outline",
                enabled: Boolean(state.selectedCategory && getActiveQuestionCount() > 0)
            },
            {
                title: "Question Replay Drill",
                tag: "Listening",
                tone: "success",
                summary: `Hear the active ${categoryName} question again so the answer flows naturally before typing or speaking.`,
                action: "replay-question",
                actionLabel: "Replay Question",
                enabled: Boolean(state.selectedCategory)
            },
            {
                title: "Voice Rehearsal Sprint",
                tag: state.speechRecognition ? "Speech ready" : "Browser limited",
                tone: state.speechRecognition ? "success" : "warning",
                summary: state.speechRecognition
                    ? "Practice your answer aloud and let the browser capture a first draft in real time."
                    : "Switch to Chrome or Edge on localhost or HTTPS to unlock voice-first rehearsal.",
                action: "start-voice",
                actionLabel: "Mic On",
                enabled: Boolean(state.selectedCategory && state.speechRecognition)
            },
            {
                title: "Camera Presence Check",
                tag: visualSnapshot.bodyLanguage.available
                    ? getScoreLabel(visualSnapshot.bodyLanguage.average, "0.0 / 10")
                    : "Standby",
                tone: visualSnapshot.bodyLanguage.available
                    ? getScoreTone(visualSnapshot.bodyLanguage.average)
                    : "neutral",
                summary: visualSnapshot.bodyLanguage.available
                    ? visualSnapshot.bodyLanguage.summary
                    : "Turn on the camera to check centering, posture, head movement, and facial composure.",
                action: "start-camera",
                actionLabel: visualSnapshot.bodyLanguage.available ? "Refresh Camera" : "Camera On",
                enabled: true
            }
        ];
    }

    function renderLearningCards(items, variant = "module") {
        return items.map((item) => `
            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/70">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <strong class="block text-sm font-semibold text-gray-900 dark:text-white/90">${escapeHtml(item.title)}</strong>
                        <p class="content-break mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">${escapeHtml(item.summary)}</p>
                    </div>
                    <span class="${getToneBadgeClass(item.tone || "neutral", true)}">${escapeHtml(item.tag || "Ready")}</span>
                </div>

                <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                    <p class="text-xs leading-5 text-gray-500 dark:text-gray-400">${escapeHtml(item.meta || (variant === "module" ? "Adaptive module" : "Practice drill"))}</p>
                    <button
                        type="button"
                        data-learning-action="${escapeHtml(item.action || "")}"
                        ${item.enabled ? "" : "disabled"}
                        class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                        ${escapeHtml(item.actionLabel || "Open")}
                    </button>
                </div>
            </div>
        `).join("");
    }

    function renderLearningLab() {
        if (
            !elements.learningHighlightTitle
            || !elements.learningHighlightText
            || !elements.learningHighlightTag
            || !elements.learningModulesList
            || !elements.learningActivitiesList
        ) {
            return;
        }

        const modules = buildLearningModules();
        const activities = buildLearningActivities();
        const visualSnapshot = getLiveVisualSnapshot();
        const responseProcess = state.lastProcessEvaluations?.response;

        let highlightedModule = modules[0];

        if (visualSnapshot.bodyLanguage.available && clampScore(visualSnapshot.bodyLanguage.average, 10) < 6.5) {
            highlightedModule = modules[2];
        } else if (responseProcess?.available && clampScore(responseProcess.average, 10) < 7) {
            highlightedModule = modules[0];
        } else if (state.selectedCategory) {
            highlightedModule = modules[1];
        }

        elements.learningHighlightTitle.textContent = highlightedModule?.title || "Choose a category to begin";
        elements.learningHighlightText.textContent = highlightedModule?.summary || "Adaptive modules will appear here.";
        elements.learningHighlightTag.textContent = highlightedModule?.tag || "Standby";
        elements.learningHighlightTag.className = getToneBadgeClass(highlightedModule?.tone || "neutral");
        elements.learningModulesList.innerHTML = renderLearningCards(modules, "module");
        elements.learningActivitiesList.innerHTML = renderLearningCards(activities, "activity");
    }

    function renderLivePresenceCoaching(snapshot = getLiveVisualSnapshot()) {
        const bodyLanguage = snapshot.bodyLanguage || createDefaultVisualSnapshot().bodyLanguage;
        const facialExpressions = snapshot.facialExpressions || createDefaultVisualSnapshot().facialExpressions;
        const combinedScore = averageScore([
            bodyLanguage.average,
            facialExpressions.average
        ], 0);
        const combinedAvailable = bodyLanguage.available || facialExpressions.available;

        elements.bodyLanguageValue.textContent = bodyLanguage.available
            ? getScoreLabel(bodyLanguage.average, "0.0 / 10")
            : String(bodyLanguage.status || "Waiting");
        elements.facialExpressionValue.textContent = facialExpressions.available
            ? getScoreLabel(facialExpressions.average, "0.0 / 10")
            : String(facialExpressions.status || "Waiting");
        elements.livePresenceSummary.textContent = snapshot.headline || "Camera coaching is waiting";
        elements.livePresenceTip.textContent = snapshot.tip || "Turn on the camera to unlock live coaching.";
        elements.livePresenceTag.textContent = snapshot.tag || (combinedAvailable ? getScoreLabel(combinedScore, "0.0 / 10") : "Standby");
        elements.livePresenceTag.className = getToneBadgeClass(
            snapshot.tagTone || (combinedAvailable ? getScoreTone(combinedScore) : "neutral")
        );
        elements.bodyLanguageAlgorithms.innerHTML = renderAlgorithmCards(
            bodyLanguage.algorithms || [],
            "Body-language algorithms will appear once the camera is ready."
        );
        elements.facialExpressionAlgorithms.innerHTML = renderAlgorithmCards(
            facialExpressions.algorithms || [],
            "Facial-expression algorithms will appear once the camera is ready."
        );
    }

    function updateTipsPanel() {
        if (elements.inputModeValue) {
            elements.inputModeValue.textContent = state.currentMode;
        }

        if (elements.answeredCountValue) {
            elements.answeredCountValue.textContent = String(state.answeredCount);
        }

        const pacing = getSelectedPacingMode();
        if (elements.paceModeValue) {
            elements.paceModeValue.textContent = pacing.label;
        }

        renderLivePresenceCoaching();
        renderLearningLab();
        updateFieldSummaryUI();
        updatePracticeModalSummary();
        syncOpeningIntroduction();
    }

    function setVoiceStatus(text, tone = "idle") {
        const toneClasses = {
            idle: "h-2.5 w-2.5 rounded-full bg-gray-400",
            listening: "h-2.5 w-2.5 rounded-full bg-success-500",
            processing: "h-2.5 w-2.5 rounded-full bg-brand-500",
            warning: "h-2.5 w-2.5 rounded-full bg-amber-500",
            error: "h-2.5 w-2.5 rounded-full bg-error-500"
        };

        elements.voiceStatusText.textContent = text;
        elements.voiceStatusDot.className = toneClasses[tone] || toneClasses.idle;
    }

    function syncCurrentMode() {
        if (state.voiceInputUsed && state.manualInputUsed) {
            state.currentMode = "Hybrid";
        } else if (state.voiceInputUsed) {
            state.currentMode = "Voice";
        } else {
            state.currentMode = "Text";
        }

        updateTipsPanel();
    }

    function getResponseText() {
        return String(elements.responseInput.value || "").trim();
    }

    function getResponseWordCount(value = getResponseText()) {
        return String(value || "").split(/\s+/).filter(Boolean).length;
    }

    function escapeRegExp(value) {
        return String(value || "").replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
    }

    function isVoiceAutoSubmitReady(value = getResponseText()) {
        const answer = String(value || "").trim();

        return answer.length >= 8 && getResponseWordCount(answer) >= 2;
    }

    function normalizeVoiceCommandText(value) {
        return String(value || "")
            .toLowerCase()
            .replace(/[^\p{L}\p{N}\s]/gu, " ")
            .replace(/\s+/g, " ")
            .trim();
    }

    function stripVoiceCommandLeadIn(value) {
        let command = normalizeVoiceCommandText(value);

        for (let index = 0; index < 3; index += 1) {
            const nextCommand = command
                .replace(/^(hey\s+)?(ai|interviewer|coach|assistant|sir|maam|ma am)\s+/u, "")
                .replace(/^(please|kindly|can you|could you|would you|will you|i need you to|i want you to|i would like you to|let us|lets)\s+/u, "")
                .replace(/^(just|okay|ok)\s+/u, "")
                .trim();

            if (nextCommand === command) {
                break;
            }

            command = nextCommand;
        }

        return command;
    }

    function includesVoicePhrase(text, phrases) {
        return phrases.some((phrase) => {
            const normalizedPhrase = normalizeVoiceCommandText(phrase);

            return new RegExp(`(^|\\s)${escapeRegExp(normalizedPhrase)}(\\s|$)`, "u").test(text);
        });
    }

    function isCommandLikeSpeech(rawText, commandText) {
        if (!commandText) {
            return false;
        }

        const normalizedRaw = normalizeVoiceCommandText(rawText);
        const hasLeadIn = normalizedRaw !== commandText;
        const firstWord = commandText.split(/\s+/)[0] || "";
        const directCommandWords = [
            "repeat", "say", "pause", "hold", "wait", "resume", "continue", "proceed",
            "next", "skip", "pass", "submit", "send", "save", "done", "finish", "end",
            "stop", "turn", "start", "open", "close", "mute", "unmute", "camera", "mic",
            "microphone", "clear", "reset", "restart", "delete", "help", "commands", "hint",
            "guide", "outline", "structure", "time", "progress", "slower", "faster", "normal",
            "regenerate", "new", "different", "sample", "advice", "tips", "coach", "give",
            "how", "what", "where"
        ];

        return hasLeadIn || directCommandWords.includes(firstWord);
    }

    function detectTrailingSubmitCommand(value) {
        const normalized = normalizeVoiceCommandText(value);

        return /(?:^|\s)(that is my answer|thats my answer|that is all|thats all|send my answer|send the answer|submit my answer|submit the answer|save my answer|done answering|i am done|im done|finished answering)$/.test(normalized);
    }

    function detectVoiceConversationCommand(value = getResponseText()) {
        const normalizedOriginal = normalizeVoiceCommandText(value);
        const normalized = stripVoiceCommandLeadIn(value);
        const wordCount = getResponseWordCount(normalized);
        const commandLike = isCommandLikeSpeech(value, normalized);

        if (!normalized) {
            return null;
        }

        if (detectTrailingSubmitCommand(value)) {
            return { intent: "submit-answer", source: "local" };
        }

        if (!commandLike || wordCount > (normalizedOriginal === normalized ? 14 : 28)) {
            return null;
        }

        if (
            includesVoicePhrase(normalized, ["repeat", "repeat question", "say again", "read it again", "again please", "one more time", "what is the question", "what was the question"])
        ) {
            return { intent: "repeat-question", source: "local" };
        }

        if (normalized === "help" || normalized === "commands" || includesVoicePhrase(normalized, ["what can i say", "what can you do", "voice commands", "show commands"])) {
            return { intent: "command-help", source: "local" };
        }

        if (includesVoicePhrase(normalized, ["pause", "hold on", "wait", "one moment", "give me a second", "give me a minute"])) {
            return { intent: "pause-interview", source: "local" };
        }

        if (includesVoicePhrase(normalized, ["continue", "resume", "go on", "proceed", "start now", "start interview", "continue interview"])) {
            return { intent: "resume-interview", source: "local" };
        }

        if (
            includesVoicePhrase(normalized, ["next question", "skip question", "pass question", "move on", "move to next question"])
            || normalized === "next"
            || normalized === "skip"
            || normalized === "pass"
        ) {
            return { intent: "next-question", source: "local" };
        }

        if (includesVoicePhrase(normalized, ["send answer", "submit answer", "save answer", "review my answer", "check my answer", "score my answer"])) {
            return { intent: "submit-answer", source: "local" };
        }

        if (includesVoicePhrase(normalized, ["end interview", "finish interview", "stop interview", "end session", "finish session", "stop practice", "i am done with the interview", "im done with the interview"])) {
            return { intent: "end-interview", source: "local" };
        }

        if (includesVoicePhrase(normalized, ["stop listening", "turn off mic", "mic off", "microphone off", "mute mic", "mute microphone"])) {
            return { intent: "mic-off", source: "local" };
        }

        if (includesVoicePhrase(normalized, ["mic on", "microphone on", "turn on mic", "turn on microphone", "start listening"])) {
            return { intent: "mic-on", source: "local" };
        }

        if (includesVoicePhrase(normalized, ["camera on", "turn on camera", "start camera", "open camera"])) {
            return { intent: "camera-on", source: "local" };
        }

        if (includesVoicePhrase(normalized, ["camera off", "turn off camera", "stop camera", "close camera"])) {
            return { intent: "camera-off", source: "local" };
        }

        if (includesVoicePhrase(normalized, ["clear answer", "delete answer", "reset answer", "restart answer", "start over", "clear my answer"])) {
            return { intent: "clear-answer", source: "local" };
        }

        if (includesVoicePhrase(normalized, ["hint", "give me a hint", "outline", "guide me", "help me answer", "structure my answer", "answer structure"])) {
            return { intent: "answer-outline", source: "local" };
        }

        if (includesVoicePhrase(normalized, ["sample answer", "model answer", "example answer", "what should i say", "how should i answer", "give me advice", "give me tips", "coach me"])) {
            return { intent: "coach-advice", source: "provider" };
        }

        if (includesVoicePhrase(normalized, ["new questions", "new question set", "fresh questions", "different questions", "regenerate questions", "replace questions"])) {
            return { intent: "regenerate-questions", source: "provider" };
        }

        if (includesVoicePhrase(normalized, ["how much time", "time check", "check time", "timer", "time left"])) {
            return { intent: "time-check", source: "local" };
        }

        if (includesVoicePhrase(normalized, ["progress", "question number", "what number", "how many questions", "where am i"])) {
            return { intent: "progress-check", source: "local" };
        }

        if (includesVoicePhrase(normalized, ["speak slower", "slow down", "slower"])) {
            return { intent: "speech-slower", source: "local" };
        }

        if (includesVoicePhrase(normalized, ["speak faster", "faster"])) {
            return { intent: "speech-faster", source: "local" };
        }

        if (includesVoicePhrase(normalized, ["normal speed", "speak normally", "normal pace"])) {
            return { intent: "speech-normal", source: "local" };
        }

        if (includesVoicePhrase(normalized, ["quick pace", "quick timer", "short timer"])) {
            return { intent: "set-pacing", value: "Quick", source: "local" };
        }

        if (includesVoicePhrase(normalized, ["standard pace", "standard timer", "normal timer"])) {
            return { intent: "set-pacing", value: "Standard", source: "local" };
        }

        if (includesVoicePhrase(normalized, ["extended pace", "long timer", "more time"])) {
            return { intent: "set-pacing", value: "Extended", source: "local" };
        }

        if (includesVoicePhrase(normalized, ["confidence coach", "confidence mode"])) {
            return { intent: "set-focus", value: "Confidence Coach", source: "local" };
        }

        if (includesVoicePhrase(normalized, ["clarity coach", "clarity mode"])) {
            return { intent: "set-focus", value: "Clarity Coach", source: "local" };
        }

        if (includesVoicePhrase(normalized, ["professional coach", "professional mode"])) {
            return { intent: "set-focus", value: "Professional Coach", source: "local" };
        }

        if (includesVoicePhrase(normalized, ["balanced coach", "balanced mode"])) {
            return { intent: "set-focus", value: "Balanced Coach", source: "local" };
        }

        return null;
    }

    function shouldResumeVoiceConversation() {
        const openingConversationListening = isOpeningConversationListening();

        return Boolean(
            state.voiceConversationEnabled
            && state.selectedCategory
            && getActiveQuestionCount() > 0
            && !state.answerSubmitting
            && !state.feedbackLoading
            && !state.savingSession
            && !state.questionAgentLoading
            && (!isOpeningIntroductionBlockingQuestion() || openingConversationListening)
            && !state.interviewerSpeechActive
        );
    }

    function scheduleVoiceAutoSubmit() {
        cancelVoiceAutoSubmit();

        if (!state.voiceConversationEnabled || !state.recognitionActive) {
            return;
        }

        state.voiceAutoSubmitTimeout = window.setTimeout(() => {
            state.voiceAutoSubmitTimeout = null;
            autoSubmitVoiceAnswer();
        }, VOICE_AUTO_SUBMIT_DELAY_MS);
    }

    function pauseVoiceForInterviewer({ commitTranscript = false } = {}) {
        cancelVoiceAutoSubmit();

        if (!state.speechRecognition || !state.recognitionActive) {
            return;
        }

        stopVoiceInput({
            commitTranscript,
            disableConversation: false,
            quiet: true
        });
    }

    function handleInterviewerQuestionSettled(result = {}) {
        const status = String(result?.status || "ended");

        state.interviewerSpeechActive = false;
        cancelPendingQuestionSpeech();
        updateSessionActionButtons();

        if (["stopped", "superseded"].includes(status)) {
            return;
        }

        if (!shouldResumeVoiceConversation()) {
            return;
        }

        startVoiceInput({ automatic: true });
    }

    function askQuestionLikeConversation() {
        cancelPendingQuestionSpeech();
        state.interviewerSpeechActive = true;
        pauseVoiceForInterviewer({ commitTranscript: false });
        updateSessionActionButtons();

        state.questionSpeechTimeout = window.setTimeout(() => {
            state.questionSpeechTimeout = null;
            const speechStarted = interviewerControls.askCurrentQuestion({
                onSettled: handleInterviewerQuestionSettled
            });

            if (!speechStarted) {
                handleInterviewerQuestionSettled({ status: "unavailable" });
                return;
            }

            if (typeof speechStarted.then === "function") {
                speechStarted.then((started) => {
                    if (!started) {
                        handleInterviewerQuestionSettled({ status: "unavailable" });
                    }
                }).catch((error) => {
                    console.error(error);
                    handleInterviewerQuestionSettled({ status: "error" });
                });
            }
        }, 180);

        return true;
    }

    function speakAssistantMessageLikeConversation(text, { resumeListening = true, label = "Interviewer responding" } = {}) {
        const message = String(text || "").trim();

        if (!message) {
            if (resumeListening && shouldResumeVoiceConversation()) {
                startVoiceInput({ automatic: true });
            }

            return false;
        }

        state.interviewerSpeechActive = true;
        pauseVoiceForInterviewer({ commitTranscript: false });
        elements.practiceLabelTag.textContent = label;
        updateSessionActionButtons();

        const settleAssistantMessage = (result = {}) => {
            const status = String(result?.status || "ended");

            state.interviewerSpeechActive = false;
            updateSessionActionButtons();

            if (["stopped", "superseded"].includes(status)) {
                return;
            }

            if (resumeListening && shouldResumeVoiceConversation()) {
                startVoiceInput({ automatic: true });
            }
        };

        const speechStarted = interviewerControls.speakAssistantMessage({
            text: message,
            onSettled: settleAssistantMessage
        });

        if (!speechStarted) {
            settleAssistantMessage({ status: "unavailable" });
            return false;
        }

        if (typeof speechStarted.then === "function") {
            speechStarted.then((started) => {
                if (!started) {
                    settleAssistantMessage({ status: "unavailable" });
                }
            }).catch((error) => {
                console.error(error);
                settleAssistantMessage({ status: "error" });
            });
        }

        return true;
    }

    function beginPracticeRound({ speakQuestion = true, focusResponse = true } = {}) {
        if (!state.selectedCategory) {
            showMessage("warning", "Choose a category before entering practice.");
            return false;
        }

        if (getActiveQuestionCount() === 0) {
            showMessage("warning", "Generate a question set before entering practice.");
            return false;
        }

        if (shouldDeliverInterviewerWelcome()) {
            playOpeningIntroductionIfNeeded({
                startQuestionAfter: speakQuestion,
                focusResponse
            });
            return false;
        }

        if (isOpeningIntroductionBlockingQuestion()) {
            queueFirstQuestionAfterOpening({ focusResponse });
            return false;
        }

        if (!state.practiceStarted) {
            state.practiceStarted = true;
            startTimer({ reset: true });
            elements.practiceLabelTag.textContent = "Prompt live";
            updateSessionActionButtons();
        }

        clearMessage();

        if (speakQuestion) {
            askQuestionLikeConversation();
        }

        if (focusResponse) {
            window.setTimeout(() => {
                elements.responseInput.focus();
            }, 0);
        }

        return true;
    }

    function autoStartCurrentQuestion({ focusResponse = true } = {}) {
        return beginPracticeRound({ speakQuestion: true, focusResponse });
    }

    function updateSessionActionButtons() {
        const hasActiveSession = Boolean(state.selectedCategory) && getActiveQuestionCount() > 0;
        const speechSupported = Boolean(state.speechRecognition);
        const canStartPractice = hasActiveSession && !state.savingSession && !state.questionAgentLoading && !state.feedbackLoading;
        const openingSpeechBlocking = isOpeningIntroductionBlockingQuestion();
        const canControlQuestion = canStartPractice && !openingSpeechBlocking;

        if (elements.startPracticeBtn) {
            elements.startPracticeBtn.disabled = !canControlQuestion;
            elements.startPracticeBtn.textContent = openingSpeechBlocking
                ? "Opening Introduction..."
                : state.practiceStarted ? "Repeat Question" : "Interviewer Will Ask";
        }

        elements.startVoiceBtn.disabled = !speechSupported || !hasActiveSession || openingSpeechBlocking || state.recognitionActive || state.voiceConversationEnabled || state.savingSession || state.questionAgentLoading || state.feedbackLoading;
        elements.startVoiceBtn.textContent = state.recognitionActive
            ? "Mic Live"
            : state.voiceConversationEnabled ? "Mic Waiting" : "Mic On";
        elements.stopVoiceBtn.disabled = !speechSupported || (!state.recognitionActive && !state.voiceConversationEnabled);
        elements.submitAnswerBtn.disabled = !hasActiveSession || openingSpeechBlocking || state.answerSubmitting || state.savingSession || state.questionAgentLoading || state.feedbackLoading;
        elements.nextQuestionBtn.disabled = !hasActiveSession || openingSpeechBlocking || state.savingSession || state.questionAgentLoading || state.feedbackLoading;
        elements.endSessionBtn.disabled = !state.selectedCategory || state.answerSubmitting || state.savingSession || state.feedbackLoading;
        if (elements.askQuestionAloudBtn) {
            elements.askQuestionAloudBtn.disabled = !hasActiveSession || openingSpeechBlocking || state.answerSubmitting || state.savingSession || state.questionAgentLoading || state.feedbackLoading;
            elements.askQuestionAloudBtn.textContent = openingSpeechBlocking ? "Opening Intro Playing" : "Repeat Question";
        }
        if (elements.printFeedbackBtn) {
            elements.printFeedbackBtn.disabled = state.feedbackHistory.length === 0;
        }
        syncQuestionAgentControls();
        syncFieldBuilderControls();
    }

    function combineTranscriptSegments(...segments) {
        return segments
            .map((segment) => String(segment || "").replace(/\s+/g, " ").trim())
            .filter(Boolean)
            .join(" ")
            .trim();
    }

    function applyRecognitionDraft() {
        state.isApplyingTranscript = true;
        elements.responseInput.value = combineTranscriptSegments(state.recognitionBaseText, state.recognitionInterimText);
        state.isApplyingTranscript = false;
    }

    function commitRecognitionInterimText() {
        if (!state.recognitionInterimText) {
            return;
        }

        state.recognitionBaseText = combineTranscriptSegments(state.recognitionBaseText, state.recognitionInterimText);
        state.recognitionInterimText = "";
        state.voiceInputUsed = true;
        applyRecognitionDraft();
        syncCurrentMode();
    }

    function resetRecognitionDraft(value = "") {
        state.recognitionBaseText = String(value || "").trim();
        state.recognitionInterimText = "";
        state.manualInputUsed = Boolean(state.recognitionBaseText);
        state.voiceInputUsed = false;
        syncCurrentMode();
    }

    function cancelRecognitionRestart() {
        if (state.restartVoiceTimeout) {
            clearTimeout(state.restartVoiceTimeout);
            state.restartVoiceTimeout = null;
        }
    }

    function stopVoiceInput({ commitTranscript = true, disableConversation = true, quiet = false } = {}) {
        cancelVoiceAutoSubmit();
        cancelRecognitionRestart();
        if (disableConversation) {
            state.voiceConversationEnabled = false;
            if (state.openingConversationActive && !state.openingConversationAcknowledging) {
                state.openingConversationActive = false;
                state.openingConversationFocusPending = false;
                state.openingConversationReply = "";

                if (!state.practiceStarted && state.selectedCategory && getActiveQuestionCount() > 0) {
                    elements.practiceLabelTag.textContent = "Ready for the interviewer";
                }
            }
        }
        state.recognitionShouldRestart = false;

        if (commitTranscript) {
            commitRecognitionInterimText();
        } else {
            state.recognitionInterimText = "";
            applyRecognitionDraft();
        }

        if (state.speechRecognition && state.recognitionActive) {
            if (!quiet) {
                setVoiceStatus("Turning mic off...", "processing");
            }
            updateSessionActionButtons();
            try {
                state.speechRecognition.stop();
            } catch (error) {
                console.error(error);
            }
        } else {
            if (!quiet) {
                setVoiceStatus(
                    state.speechRecognition
                        ? "Mic is idle."
                        : "Mic transcription is not supported in this browser. Use Chrome or Edge on localhost/HTTPS.",
                    state.speechRecognition ? "idle" : "warning"
                );
            }
            updateSessionActionButtons();
        }
    }

    function updateManualResponseState() {
        const nextValue = elements.responseInput.value.trim();

        state.recognitionBaseText = nextValue;

        if (state.recognitionActive) {
            state.recognitionInterimText = "";
        }

        state.manualInputUsed = Boolean(nextValue);

        if (!nextValue && !state.recognitionActive) {
            state.voiceInputUsed = false;
        }

        syncCurrentMode();
    }

    function startVoiceInput({ automatic = false } = {}) {
        const openingConversationListening = isOpeningConversationListening();

        if (!state.selectedCategory) {
            if (!automatic) {
                showMessage("warning", "Choose a category before turning on the mic.");
            }
            return;
        }

        if (getActiveQuestionCount() === 0) {
            if (!automatic) {
                showMessage("warning", "Generate a question set before turning on the mic.");
            }
            return;
        }

        if (!state.speechRecognition) {
            if (!automatic) {
                showMessage("warning", "Mic transcription is not supported in this browser.");
            }
            return;
        }

        state.voiceConversationEnabled = true;

        if (state.interviewerSpeechActive || (isOpeningIntroductionBlockingQuestion() && !openingConversationListening)) {
            setVoiceStatus("Mic will listen after the interviewer finishes speaking.", "processing");
            updateSessionActionButtons();
            return;
        }

        if (state.feedbackLoading || state.savingSession || state.answerSubmitting || state.questionAgentLoading) {
            setVoiceStatus("Mic will listen when the interviewer is ready.", "processing");
            updateSessionActionButtons();
            return;
        }

        if (state.recognitionActive) {
            if (!automatic) {
                showMessage("info", "Mic is already active.");
            }
            return;
        }

        if (!state.practiceStarted && !openingConversationListening) {
            const practiceReady = beginPracticeRound({ speakQuestion: true, focusResponse: false });

            if (!practiceReady) {
                return;
            }

            if (state.interviewerSpeechActive) {
                setVoiceStatus("Mic will listen after the interviewer finishes speaking.", "processing");
                updateSessionActionButtons();
                return;
            }
        }

        cancelRecognitionRestart();
        state.recognitionShouldRestart = true;
        state.recognitionBaseText = elements.responseInput.value.trim();
        state.recognitionInterimText = "";
        state.manualInputUsed = Boolean(state.recognitionBaseText);
        setVoiceStatus(
            automatic
                ? openingConversationListening ? "Mic is listening for your greeting..." : "Mic is listening for your answer..."
                : "Requesting microphone access...",
            "processing"
        );
        updateSessionActionButtons();
        clearMessage();

        try {
            state.speechRecognition.start();
        } catch (error) {
            state.recognitionShouldRestart = false;
            updateSessionActionButtons();

            if (error?.name === "InvalidStateError") {
                setVoiceStatus("Mic is already turning on...", "processing");
                return;
            }

            console.error(error);
            setVoiceStatus("Mic could not turn on.", "error");
            showMessage(
                "error",
                "Mic transcription could not turn on. Use Chrome or Edge on localhost/HTTPS and allow microphone access."
            );
        }
    }

    function getRecognitionErrorMessage(errorCode) {
        const messages = {
            "aborted": "Voice input was stopped.",
            "audio-capture": "No microphone was detected. Connect a microphone and try again.",
            "network": "Speech recognition lost its connection. Check your browser network access and try again.",
            "no-speech": "No speech was detected. Speak clearly into your microphone.",
            "not-allowed": "Microphone access was blocked. Allow microphone access in the browser and try again.",
            "service-not-allowed": "This browser blocked the speech-recognition service for this page."
        };

        return messages[errorCode] || "Mic transcription encountered an error. Please try again.";
    }

    function getQuestionAgentToneClasses() {
        return {
            neutral: "inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300",
            success: "inline-flex items-center rounded-full bg-success-100 px-3 py-1 text-xs font-medium text-success-700 dark:bg-success-500/10 dark:text-success-300",
            warning: "inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-700 dark:bg-amber-500/10 dark:text-amber-300",
            error: "inline-flex items-center rounded-full bg-error-100 px-3 py-1 text-xs font-medium text-error-700 dark:bg-error-500/10 dark:text-error-300"
        };
    }

    function getActiveQuestionCount() {
        return state.activeQuestions.length;
    }

    function getActiveQuestion() {
        return state.activeQuestions[state.questionIndex] || "";
    }

    function shouldDeliverInterviewerWelcome() {
        return state.questionIndex === 0 && !state.welcomeDelivered && getActiveQuestionCount() > 0;
    }

    function isOpeningConversationListening() {
        return Boolean(
            state.questionIndex === 0
            && state.openingConversationActive
            && !state.openingConversationAcknowledging
        );
    }

    function isOpeningIntroductionBlockingQuestion() {
        return Boolean(
            state.questionIndex === 0
            && (
                state.openingIntroductionSpeaking
                || state.openingConversationActive
                || state.openingConversationAcknowledging
            )
        );
    }

    function shouldStartOpeningConversation() {
        return Boolean(
            state.questionIndex === 0
            && !state.practiceStarted
            && !state.openingConversationCompleted
            && state.voiceConversationEnabled
            && state.speechRecognition
        );
    }

    function queueFirstQuestionAfterOpening({ focusResponse = true } = {}) {
        if (!state.selectedCategory || getActiveQuestionCount() === 0 || state.questionIndex !== 0) {
            return false;
        }

        state.openingIntroductionQuestionPending = true;
        state.openingIntroductionFocusPending = state.openingIntroductionFocusPending || Boolean(focusResponse);
        elements.practiceLabelTag.textContent = state.openingConversationActive
            ? "Opening conversation live"
            : "Opening introduction live";
        showMessage(
            "info",
            state.openingConversationActive
                ? "The interviewer is finishing the quick greeting. The first question will follow automatically."
                : "The opening introduction is still speaking. The first question will follow automatically after it finishes."
        );
        updateSessionActionButtons();

        return true;
    }

    function startOpeningConversationListening({ focusResponse = true } = {}) {
        state.openingConversationActive = true;
        state.openingConversationAcknowledging = false;
        state.openingConversationCompleted = false;
        state.openingConversationFocusPending = Boolean(focusResponse);
        state.openingConversationReply = "";
        elements.responseInput.value = "";
        resetRecognitionDraft();
        elements.practiceLabelTag.textContent = "Opening conversation";
        showMessage("info", "Answer the greeting naturally. The first interview question will follow automatically.");
        setVoiceStatus("Mic is ready for your quick greeting.", "processing");
        updateSessionActionButtons();
        startVoiceInput({ automatic: true });
    }

    function finishOpeningIntroduction(result = {}) {
        const status = String(result?.status || "ended");
        const introductionWasHeard = status === "ended";
        const canContinueAfterFallback = ["empty", "error", "unavailable"].includes(status);
        const shouldStartQuestion = state.openingIntroductionQuestionPending
            && (introductionWasHeard || canContinueAfterFallback)
            && isPracticeModalOpen()
            && Boolean(state.selectedCategory)
            && getActiveQuestionCount() > 0
            && state.questionIndex === 0
            && !state.questionAgentLoading
            && !state.feedbackLoading;
        const focusResponse = state.openingIntroductionFocusPending;

        state.openingIntroductionSpeaking = false;
        state.interviewerSpeechActive = false;
        state.openingIntroductionQuestionPending = false;
        state.openingIntroductionFocusPending = false;
        state.welcomeDelivered = introductionWasHeard || (canContinueAfterFallback && shouldStartQuestion);
        updateSessionActionButtons();

        if (shouldStartQuestion) {
            if (introductionWasHeard && shouldStartOpeningConversation()) {
                startOpeningConversationListening({ focusResponse });
                return;
            }

            if (introductionWasHeard) {
                clearMessage();
            } else {
                showMessage("warning", "The opening voice could not play, so the interviewer is moving straight to the first question.");
            }

            autoStartCurrentQuestion({ focusResponse });
            return;
        }

        if (!state.practiceStarted && state.selectedCategory && getActiveQuestionCount() > 0) {
            elements.practiceLabelTag.textContent = "Ready for the interviewer";
        }

        if (["empty", "error", "unavailable"].includes(status)) {
            showMessage("warning", "The opening voice could not play. The first question is ready on screen.");
        }
    }

    function playOpeningIntroductionIfNeeded({ startQuestionAfter = false, focusResponse = false } = {}) {
        if (state.openingIntroductionSpeaking) {
            if (startQuestionAfter) {
                queueFirstQuestionAfterOpening({ focusResponse });
            }

            return true;
        }

        if (!shouldDeliverInterviewerWelcome() || !isPracticeModalOpen()) {
            return false;
        }

        state.openingIntroductionSpeaking = true;
        state.interviewerSpeechActive = true;
        state.openingIntroductionQuestionPending = Boolean(startQuestionAfter);
        state.openingIntroductionFocusPending = Boolean(focusResponse);
        elements.practiceLabelTag.textContent = "Opening introduction live";
        pauseVoiceForInterviewer({ commitTranscript: false });
        updateSessionActionButtons();
        interviewerControls.playOpeningIntroduction({
            onSettled: finishOpeningIntroduction
        });

        return true;
    }

    function getInterviewerWelcomeDeliveryCue() {
        const focusLabel = String(getSelectedFocusMode()?.label || "");

        switch (focusLabel) {
            case "Confidence Coach":
                return "speak with calm confidence";
            case "Clarity Coach":
                return "keep your answer clear and well structured";
            case "Professional Coach":
                return "keep your answer polished and professional";
            default:
                return "keep your answer clear and relevant";
        }
    }

    function buildInterviewerWelcomeLead() {
        const categoryName = String(state.selectedCategory?.name || "interview practice").trim();
        const fieldTitle = getSelectedFieldTitle();
        const totalQuestions = getActiveQuestionCount();
        const questionLabel = totalQuestions === 1 ? "question" : "questions";
        const sessionContext = fieldTitle
            ? `Today we will focus on ${fieldTitle} in the ${categoryName} track.`
            : `Today we will focus on the ${categoryName} track.`;

        return [
            "Good day, and thank you for joining this practice interview.",
            sessionContext,
            `I will guide you through ${Math.max(totalQuestions, 1)} ${questionLabel}, one at a time.`,
            "Before we begin, tell me briefly how you are feeling today. If you are a little nervous, that is completely normal.",
            `Take a breath, answer naturally, and ${getInterviewerWelcomeDeliveryCue()}.`,
            state.speechRecognition
                ? "After your short reply, I will start the first question."
                : "After this opening, I will begin with the first question."
        ].join(" ");
    }

    function buildSpokenQuestionPrompt(question) {
        const normalizedQuestion = String(question || "").trim();

        if (!normalizedQuestion) {
            return "";
        }

        return state.questionIndex === 0
            ? `First question. ${normalizedQuestion}`
            : normalizedQuestion;
    }

    function getQuestionAgentProviderById(providerId = "") {
        return state.questionAgentProviderCatalog.find((provider) => provider.id === providerId) || null;
    }

    function getQuestionAgentProviderSummary(provider) {
        if (!provider) {
            return "Auto";
        }

        return provider.label.replace(/\s+API$/i, "");
    }

    function applyQuestionAgentProviderCatalog(providers = [], requestedProviderId = state.questionAgentSelectedProviderId) {
        const catalog = normalizeQuestionAgentProviders(providers);
        const requestedProvider = catalog.find((provider) => provider.id === requestedProviderId);

        state.questionAgentProviderCatalog = catalog;
        state.questionAgentSelectedProviderId = requestedProvider && (requestedProvider.configured || requestedProvider.id === "auto" || requestedProvider.id === "local")
            ? requestedProvider.id
            : resolveQuestionAgentDefaultProvider({
                providers: catalog,
                defaultProviderId: "auto"
            });

        renderQuestionAgentProviderOptions();
        syncFieldBuilderModal();
    }

    function renderQuestionAgentProviderOptions() {
        if (!elements.practiceQuestionAgentProviderSelect && !elements.practiceFieldProviderSelect) {
            return;
        }

        const optionMarkup = state.questionAgentProviderCatalog.map((provider) => {
            const isAvailable = provider.configured || provider.id === "auto" || provider.id === "local";
            const selected = provider.id === state.questionAgentSelectedProviderId ? " selected" : "";
            const disabled = isAvailable ? "" : " disabled";
            const optionLabel = provider.configured || provider.id === "auto" || provider.id === "local"
                ? provider.label
                : `${provider.label} (Add API key)`;

            return `<option value="${escapeHtml(provider.id)}"${selected}${disabled}>${escapeHtml(optionLabel)}</option>`;
        }).join("");

        if (elements.practiceQuestionAgentProviderSelect) {
            elements.practiceQuestionAgentProviderSelect.innerHTML = optionMarkup;
        }

        if (elements.practiceFieldProviderSelect) {
            elements.practiceFieldProviderSelect.innerHTML = optionMarkup;
        }
    }

    function setQuestionAgentStatus(text, tone = "neutral") {
        const tones = getQuestionAgentToneClasses();

        state.questionAgentStatusText = text;
        state.questionAgentStatusTone = tone;
        elements.practiceQuestionAgentStatusTag.textContent = text;
        elements.practiceQuestionAgentStatusTag.className = tones[tone] || tones.neutral;
    }

    function truncateForButton(value, maxLength = 62) {
        const text = String(value || "").trim();
        return text.length > maxLength ? `${text.slice(0, maxLength - 3)}...` : text;
    }

    function buildQuestionAgentWelcomeMessage(category = null) {
        if (!category) {
            return "Select a category and I will generate a fresh Philippine-style interview question set for the workspace.";
        }

        const fieldPlan = getFieldPlanForCategory(category) || (state.selectedCategory?.id === category.id ? state.selectedFieldPlan : null);
        const fieldTitle = String(fieldPlan?.title || "").trim();

        return `I am ready to build ${state.questionCount} fresh ${category.name} questions${fieldTitle ? ` for ${fieldTitle}` : ""} for this workspace. Add an instruction or use a quick action to tailor the set.`;
    }

    function buildDefaultQuestionAgentInstruction(category) {
        return `Generate ${state.questionCount} fresh interview questions for ${category.name} in the Philippine setting.`;
    }

    function buildQuestionGenerationInstruction(category, instruction = "") {
        const baseInstruction = String(instruction || "").trim() || buildDefaultQuestionAgentInstruction(category);
        const fieldPlan = getFieldPlanForCategory(category) || (state.selectedCategory?.id === category.id ? state.selectedFieldPlan : null);
        const fieldSuffix = buildFieldInstructionSuffix(fieldPlan);
        const learningActivityInstruction = buildLearningActivityInstruction();

        return [baseInstruction, learningActivityInstruction, fieldSuffix]
            .filter(Boolean)
            .join(" ");
    }

    function getQuestionAgentQuickActions(category = state.selectedCategory) {
        if (!category) {
            return [];
        }

        if (category.id === "job") {
            return [
                "Generate beginner-friendly questions for a fresh graduate.",
                "Generate harder questions for a career shifter.",
                "Generate questions for a remote or hybrid role.",
                "Generate behavioral questions for an office team role."
            ];
        }

        if (category.id === "scholarship") {
            return [
                "Generate questions focused on financial need and family responsibilities.",
                "Generate questions focused on academic goals and discipline.",
                "Generate leadership and community service questions.",
                "Generate tougher follow-up questions for scholarship panels."
            ];
        }

        if (category.id === "admission") {
            return [
                "Generate questions about course fit and motivation.",
                "Generate questions about academic readiness and study habits.",
                "Generate questions about family support and future goals.",
                "Generate tougher admission follow-up questions."
            ];
        }

        return [
            "Generate entry-level IT questions for a fresh graduate.",
            "Generate questions about capstone and project experience.",
            "Generate questions about debugging and problem-solving.",
            "Generate questions for a remote junior developer role."
        ];
    }

    function renderQuestionAgentQuickActions() {
        const prompts = getQuestionAgentQuickActions();

        elements.practiceQuestionAgentQuickActions.innerHTML = prompts.length === 0
            ? `<span class="text-sm text-gray-500 dark:text-gray-400">Choose a category to unlock tailored question-generation prompts.</span>`
            : prompts.map((prompt) => `
                <button
                    type="button"
                    data-question-agent-prompt="${escapeHtml(prompt)}"
                    title="${escapeHtml(prompt)}"
                    class="content-break inline-flex max-w-full items-center rounded-full border border-gray-200 bg-white px-3 py-1.5 text-left text-xs font-medium text-gray-600 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700 dark:border-gray-700 dark:bg-gray-900/70 dark:text-gray-300 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-200"
                >
                    ${escapeHtml(truncateForButton(prompt))}
                </button>
            `).join("");

        elements.practiceQuestionAgentQuickActions.querySelectorAll("[data-question-agent-prompt]").forEach((button) => {
            button.addEventListener("click", () => {
                const prompt = button.dataset.questionAgentPrompt || "";
                generateQuestionSet(prompt);
            });
        });
    }

    function renderQuestionAgentMessages() {
        if (state.questionAgentHistory.length === 0) {
            elements.practiceQuestionAgentMessages.innerHTML = `
                <div class="content-break rounded-2xl border border-dashed border-gray-300 px-4 py-5 text-sm leading-6 text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    Select a category to start the AI question generator.
                </div>
            `;
            return;
        }

        elements.practiceQuestionAgentMessages.innerHTML = state.questionAgentHistory.map((item) => {
            const isAssistant = item.role === "assistant";
            const wrapperClass = isAssistant ? "items-start" : "items-end";
            const bubbleClass = isAssistant
                ? "border-gray-200 bg-white text-gray-700 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300"
                : "border-brand-200 bg-brand-50 text-brand-700 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-200";
            const label = isAssistant ? "AI Question Chatbot" : "Instruction";

            return `
                <div class="flex flex-col ${wrapperClass} gap-2">
                    <span class="text-xs font-medium uppercase tracking-wide text-gray-400">${label}</span>
                    <div class="content-break max-w-full rounded-2xl border px-4 py-3 text-sm leading-6 ${bubbleClass}">
                        ${escapeHtml(item.text).replace(/\n/g, "<br>")}
                    </div>
                </div>
            `;
        }).join("");

        elements.practiceQuestionAgentMessages.scrollTo({
            top: elements.practiceQuestionAgentMessages.scrollHeight,
            behavior: "auto"
        });
    }

    function syncQuestionAgentPresentation() {
        const requestedProvider = getQuestionAgentProviderById(state.questionAgentSelectedProviderId) || getQuestionAgentProviderById("auto");
        const providerLabel = state.questionAgentResolvedProviderLabel
            || getQuestionAgentProviderSummary(requestedProvider)
            || "Auto";
        const providerNote = requestedProvider?.description
            || "Choose which AI provider should handle question generation and feedback review for this session.";

        elements.practiceQuestionAgentSourceValue.textContent = state.questionSourceLabel;
        if (elements.practiceQuestionAgentProviderSelect) {
            elements.practiceQuestionAgentProviderSelect.value = state.questionAgentSelectedProviderId;
        }
        elements.practiceQuestionAgentProviderValue.textContent = `Current route: ${providerLabel}. ${providerNote}`;
        elements.practiceQuestionAgentSummaryText.textContent = state.questionSetSummary;
    }

    function syncQuestionAgentControls() {
        const hasCategory = Boolean(state.selectedCategory);
        const hasQuestionSet = getActiveQuestionCount() > 0;

        if (elements.practiceQuestionAgentProviderSelect) {
            elements.practiceQuestionAgentProviderSelect.disabled = state.questionAgentLoading;
        }
        elements.practiceQuestionAgentInput.disabled = !hasCategory || state.questionAgentLoading;
        elements.practiceQuestionAgentGenerateBtn.disabled = !hasCategory || state.questionAgentLoading;
        elements.practiceQuestionAgentGenerateBtn.textContent = state.questionAgentLoading ? "Generating..." : "Generate Questions";
        elements.practiceQuestionAgentRegenerateBtn.disabled = !hasCategory || state.questionAgentLoading || !hasQuestionSet;
    }

    function resetQuestionAgentConversation(category = null) {
        const fieldPlan = getFieldPlanForCategory(category) || (state.selectedCategory?.id === category?.id ? state.selectedFieldPlan : null);
        const fieldTitle = String(fieldPlan?.title || "").trim();

        state.questionAgentHistory = [{ role: "assistant", text: buildQuestionAgentWelcomeMessage(category) }];
        state.questionAgentResolvedProviderLabel = null;
        state.questionSetSummary = category
            ? `Select or refine an instruction to generate ${state.questionCount} fresh questions for ${category.name}${fieldTitle ? ` focused on ${fieldTitle}` : ""}.`
            : "Select a category and the chatbot will build a fresh question set for the workspace.";
        state.questionSourceLabel = category ? "Ready to generate" : "Awaiting category";
        elements.practiceQuestionAgentInput.value = "";
        setQuestionAgentStatus(category ? "Ready" : "Waiting", "neutral");
        renderQuestionAgentMessages();
        renderQuestionAgentQuickActions();
        syncQuestionAgentPresentation();
        syncQuestionAgentControls();
    }

    function sanitizeGeneratedQuestions(questions) {
        if (!Array.isArray(questions)) {
            return [];
        }

        return Array.from(new Set(
            questions
                .map((question) => String(question || "").trim())
                .filter(Boolean)
        )).slice(0, state.questionCount);
    }

    function buildQuestionAgentReplyMessage(summary, questions) {
        const questionLines = questions.map((question, index) => `${index + 1}. ${question}`).join("\n");
        return questionLines ? `${summary}\n\n${questionLines}` : summary;
    }

    function resetSessionProgress() {
        cancelAutoAdvance();
        cancelPendingQuestionSpeech();
        stopTimer();
        stopVoiceInput({ commitTranscript: false });
        interviewerControls.stopSpeaking();
        state.practiceStarted = false;
        state.sessionId = createSessionId();
        state.questionIndex = 0;
        state.activeQuestions = [];
        state.answeredCount = 0;
        state.feedbackHistory = [];
        state.lastProcessEvaluations = null;
        state.sessionStartedAt = new Date().toISOString();
        state.sessionSaved = false;
        state.feedbackLoading = false;
        state.welcomeDelivered = false;
        state.openingIntroductionSpeaking = false;
        state.openingIntroductionQuestionPending = false;
        state.openingIntroductionFocusPending = false;
        state.openingConversationActive = false;
        state.openingConversationAcknowledging = false;
        state.openingConversationCompleted = false;
        state.openingConversationFocusPending = false;
        state.openingConversationReply = "";
        elements.responseInput.value = "";
        elements.questionTimerValue.textContent = "00:00";
        resetRecognitionDraft();
        resetFeedbackPlaceholder();
    }

    function showQuestionSetLoadingState(category) {
        const fieldTitle = getSelectedFieldTitle();

        elements.selectedCategoryName.textContent = category.name;
        elements.selectedCategoryDescription.textContent = `Generating ${state.questionCount} fresh AI interview questions for ${category.name}${fieldTitle ? ` focused on ${fieldTitle}` : ""}.`;
        elements.currentQuestionText.textContent = "The AI question generator is preparing your first interview question.";
        elements.practiceStatusTag.textContent = "Generating questions";
        elements.practiceStatusTag.className = "inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-700 dark:bg-amber-500/10 dark:text-amber-300";
        elements.practiceLabelTag.textContent = "AI question set loading";
        elements.selectedPracticeFieldTag.textContent = fieldTitle ? `Field: ${fieldTitle}` : "Field not set";
        renderKeywords(category.keywords);
        updateFocusModeDisplay();
        updateQuestionProgress();
        updateFieldSummaryUI();
    }

    function applyGeneratedQuestionSet(questions, payload, category) {
        state.activeQuestions = questions;
        state.questionIndex = 0;
        state.answeredCount = 0;
        state.feedbackHistory = [];
        state.lastProcessEvaluations = null;
        state.sessionId = createSessionId();
        state.sessionStartedAt = new Date().toISOString();
        state.sessionSaved = false;
        state.questionSourceLabel = payload?.usedFallback ? "Local generated set" : "AI-generated set";
        state.questionSetSummary = [
            String(payload?.reply || `${questions.length} AI-generated interview questions are ready.`).trim(),
            state.learningActivityContext
                ? `${state.learningActivityContext.activityLabel} ${state.learningActivityContext.levelLabel}: target ${state.learningActivityContext.targetScore.toFixed(1)} / 10.`
                : "",
            getSelectedFieldTitle() ? `Focus: ${getSelectedFieldTitle()}.` : ""
        ].filter(Boolean).join(" ");
        state.questionAgentResolvedProviderLabel = String(payload?.provider || "");
        loadCurrentQuestion();
    }

    async function generateQuestionSet(instruction = "", { keepVoiceConversation = false } = {}) {
        if (!state.selectedCategory || state.questionAgentLoading) {
            return;
        }

        const category = state.selectedCategory;
        const learningActivityContext = state.learningActivityContext;
        const hasExistingProgress = state.feedbackHistory.length > 0;
        const message = buildQuestionGenerationInstruction(category, instruction || elements.practiceQuestionAgentInput.value || "");
        const requestId = state.questionAgentRequestId + 1;

        state.questionAgentRequestId = requestId;
        state.questionAgentLoading = true;
        state.questionAgentHistory.push({ role: "user", text: message });
        state.questionSourceLabel = "Generating question set";
        state.questionSetSummary = `Generating ${state.questionCount} fresh questions for ${category.name}.`;
        renderQuestionAgentMessages();
        syncQuestionAgentPresentation();
        setQuestionAgentStatus("Generating", "warning");
        syncQuestionAgentControls();
        resetSessionProgress();
        if (keepVoiceConversation && state.speechRecognition) {
            state.voiceConversationEnabled = true;
            setVoiceStatus("Mic will continue after the new questions are ready.", "processing");
        }
        state.learningActivityContext = learningActivityContext;
        showQuestionSetLoadingState(category);
        updateTipsPanel();
        updateSessionActionButtons();
        openPracticeModal({ focusResponse: false });

        try {
            const payload = await requestWorkspace("chatbot", {
                method: "POST",
                body: {
                    message,
                    mode: "question_set",
                    questionCount: state.questionCount,
                    providerId: state.questionAgentSelectedProviderId,
                    categoryId: category.id
                }
            });

            if (requestId !== state.questionAgentRequestId || state.selectedCategory?.id !== category.id) {
                return;
            }

            const questions = sanitizeGeneratedQuestions(payload.generatedQuestions);

            if (questions.length === 0) {
                throw new Error("The chatbot returned an empty question set.");
            }

            applyQuestionAgentProviderCatalog(payload.availableProviders, payload.requestedProviderId || state.questionAgentSelectedProviderId);
            state.questionAgentHistory.push({
                role: "assistant",
                text: buildQuestionAgentReplyMessage(
                    String(payload.reply || "Your fresh question set is ready."),
                    questions
                )
            });
            applyGeneratedQuestionSet(questions, payload, category);
            renderQuestionAgentMessages();
            renderQuestionAgentQuickActions();
            syncQuestionAgentPresentation();
            setQuestionAgentStatus(payload.usedFallback ? "Local set ready" : "Question set ready", payload.usedFallback ? "neutral" : "success");
            syncQuestionAgentControls();
            updateTipsPanel();
            updateSessionActionButtons();
            if (isQuestionAgentModalOpen()) {
                closeQuestionAgentModal({ returnFocus: false });
            }
            openPracticeModal({
                focusResponse: true,
                playOpening: true
            });
            showMessage(
                hasExistingProgress ? "info" : "success",
                hasExistingProgress
                    ? "A new AI question set is ready. The interviewer will continue automatically."
                    : "Your AI-generated question set is ready. The interviewer will ask the first question automatically."
            );
        } catch (error) {
            if (requestId !== state.questionAgentRequestId || state.selectedCategory?.id !== category.id) {
                return;
            }

            console.error(error);
            state.questionSourceLabel = "Generation failed";
            state.questionSetSummary = `The chatbot could not generate a new question set for ${category.name}. Try again in a moment.`;
            state.questionAgentHistory.push({
                role: "assistant",
                text: "I could not generate a fresh question set right now. Try the same category again or adjust your instruction."
            });
            elements.selectedCategoryName.textContent = category.name;
            elements.selectedCategoryDescription.textContent = state.questionSetSummary;
            elements.currentQuestionText.textContent = "The AI question generator is temporarily unavailable for this category.";
            elements.practiceStatusTag.textContent = "Generation unavailable";
            elements.practiceStatusTag.className = "inline-flex items-center rounded-full bg-error-100 px-3 py-1 text-xs font-medium text-error-700 dark:bg-error-500/10 dark:text-error-300";
            elements.practiceLabelTag.textContent = "Try generating again";
            updateQuestionProgress();
            renderQuestionAgentMessages();
            syncQuestionAgentPresentation();
            setQuestionAgentStatus("Unavailable", "error");
            showMessage("warning", "The chatbot could not generate a fresh question set right now.");
        } finally {
            if (requestId === state.questionAgentRequestId) {
                state.questionAgentLoading = false;
                syncQuestionAgentControls();
                updateSessionActionButtons();
                updatePracticeModalSummary();
            }
        }
    }

    function updateFocusModeDisplay() {
        const selectedFocus = getSelectedFocusMode();

        elements.coachModeValue.textContent = selectedFocus.label;
        elements.coachModeTag.textContent = selectedFocus.label;
        elements.coachTipText.textContent = state.selectedCategory
            ? getActiveQuestionCount() > 0
                ? selectedFocus.tip
                : `${selectedFocus.tip} Your AI question set is still being prepared.`
            : `${selectedFocus.tip} Choose a track from the sidebar to enter live practice.`;
    }

    function updateSelectedCategoryButtons() {
        const activeCategoryId = state.pendingCategory?.id || state.selectedCategory?.id;

        document.querySelectorAll(".category-btn").forEach((button) => {
            const isActive = button.dataset.categoryId === activeCategoryId;
            button.className = getCategoryButtonClass(isActive);
        });
    }

    function renderKeywords(keywords) {
        elements.questionKeywordTags.innerHTML = keywords
            .map((keyword) => `
                <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-medium text-gray-700 shadow-theme-xs dark:bg-gray-800 dark:text-gray-300">
                    ${keyword}
                </span>
            `)
            .join("");
    }

    function updateQuestionProgress() {
        if (!state.selectedCategory) {
            elements.questionCounter.textContent = "Question 0 of 0";
            elements.questionProgressFill.style.width = "0%";
            return;
        }

        const totalQuestions = getActiveQuestionCount();

        if (totalQuestions === 0) {
            elements.questionCounter.textContent = state.questionAgentLoading
                ? `Preparing ${state.questionCount} questions`
                : "Question set not ready";
            elements.questionProgressFill.style.width = "0%";
            return;
        }

        const current = Math.min(state.questionIndex + 1, totalQuestions);
        const progress = (current / totalQuestions) * 100;

        elements.questionCounter.textContent = `Question ${current} of ${totalQuestions}`;
        elements.questionProgressFill.style.width = `${progress}%`;
    }

    function loadCurrentQuestion() {
        if (!state.selectedCategory || getActiveQuestionCount() === 0) {
            return;
        }

        const question = getActiveQuestion();

        cancelAutoAdvance();
        cancelPendingQuestionSpeech();
        interviewerControls.stopSpeaking();
        stopVoiceInput({
            commitTranscript: false,
            disableConversation: false,
            quiet: state.voiceConversationEnabled
        });
        stopTimer();
        resetRecognitionDraft();
        state.practiceStarted = false;
        state.timerSeconds = 0;
        state.timerAlertShown = false;
        state.timerPaused = false;

        elements.currentQuestionText.textContent = question;
        elements.questionTimerValue.textContent = "00:00";
        updateFocusModeDisplay();
        elements.selectedCategoryName.textContent = state.selectedCategory.name;
        elements.selectedCategoryDescription.textContent = state.questionSetSummary;
        elements.practiceStatusTag.textContent = "Session active";
        elements.practiceStatusTag.className = "inline-flex items-center rounded-full bg-success-100 px-3 py-1 text-xs font-medium text-success-700 dark:bg-success-500/10 dark:text-success-300";
        elements.practiceLabelTag.textContent = "Interviewer ready";
        elements.selectedPracticeFieldTag.textContent = getSelectedFieldTitle() ? `Field: ${getSelectedFieldTitle()}` : "Field not set";

        renderKeywords(state.selectedCategory.keywords);
        updateQuestionProgress();
        elements.responseInput.value = "";
        clearMessage();
        updateTipsPanel();
        updateSessionActionButtons();
    }

    async function selectCategory(category, { skipFieldModal = false, prefillNeed = "" } = {}) {
        const fieldPlan = getFieldPlanForCategory(category);
        const learningActivityContext = state.learningActivityContext;

        if (!skipFieldModal) {
            openPracticeFieldModal(category, { prefillNeed });
            return;
        }

        stopVoiceInput({ commitTranscript: false });
        state.selectedCategory = category;
        state.pendingCategory = category;
        state.selectedFieldPlan = fieldPlan || state.selectedFieldPlan;
        state.questionCount = Number(elements.questionCountSelect.value);

        const selectedPacing = getSelectedPacingMode();

        state.timerTarget = selectedPacing.seconds;
        elements.timerTargetValue.textContent = formatTime(state.timerTarget);

        resetSessionProgress();
        state.learningActivityContext = learningActivityContext;
        updateSelectedCategoryButtons();
        resetQuestionAgentConversation(category);
        showQuestionSetLoadingState(category);
        updateTipsPanel();
        updateSessionActionButtons();
        openPracticeModal({ focusResponse: false });
        await generateQuestionSet();
    }

    function calculateScore(answer, keywords) {
        const trimmed = answer.trim();
        const words = trimmed.split(/\s+/).filter(Boolean);
        const sentences = trimmed
            .split(/[.!?]+/)
            .map((item) => item.trim())
            .filter(Boolean);

        const matchedKeywords = keywords.filter((keyword) => trimmed.toLowerCase().includes(keyword.toLowerCase())).length;
        const clarity = Math.min(10, Math.max(4, Math.round(words.length / 12)));
        const relevance = Math.min(10, Math.max(4, Math.round((matchedKeywords / Math.max(keywords.length, 1)) * 10)));
        const grammar = Math.min(10, Math.max(5, sentences.length > 0 && /[.!?]$/.test(trimmed) ? 8 : 6));
        const professionalism = /thank|responsible|team|learn|improve|experience/i.test(trimmed) ? 9 : 7;

        return {
            clarity,
            relevance,
            grammar,
            professionalism,
            average: Number(((clarity + relevance + grammar + professionalism) / 4).toFixed(1)),
            matchedKeywords
        };
    }

    function cloneProcessEvaluation(process) {
        return {
            label: String(process?.label || "Process"),
            average: process?.average === null || process?.average === undefined ? null : roundScore(process.average),
            summary: String(process?.summary || "No process summary yet."),
            status: String(process?.status || "Ready"),
            available: process?.available !== false,
            algorithms: Array.isArray(process?.algorithms)
                ? process.algorithms.map((algorithm) => ({
                    name: String(algorithm?.name || "Algorithm"),
                    score: algorithm?.score === null || algorithm?.score === undefined ? null : roundScore(algorithm.score),
                    detail: String(algorithm?.detail || "No detail available."),
                    status: String(algorithm?.status || "Ready"),
                    available: algorithm?.available !== false
                }))
                : []
        };
    }

    function buildResponseProcessEvaluation(answer, keywords, scoreData) {
        const trimmed = answer.trim();
        const lower = trimmed.toLowerCase();
        const words = trimmed.split(/\s+/).filter(Boolean);
        const sentences = trimmed
            .split(/[.!?]+/)
            .map((sentence) => sentence.trim())
            .filter(Boolean);
        const keywordMatches = keywords.filter((keyword) => lower.includes(String(keyword).toLowerCase())).length;
        const keywordCoverageScore = roundScore(
            keywords.length
                ? Math.max(3, (keywordMatches / keywords.length) * 10)
                : 6
        );

        const starSignals = [
            /\b(when|during|while|at that time|in my internship|in my project|in our team)\b/i.test(trimmed),
            /\b(task|goal|needed to|was responsible|was asked|objective)\b/i.test(trimmed),
            /\b(created|built|led|solved|organized|improved|handled|developed|designed|implemented|debugged)\b/i.test(trimmed),
            /\b(result|improved|reduced|increased|delivered|achieved|learned|outcome|therefore|because of that)\b/i.test(trimmed)
        ].filter(Boolean).length;
        const starStructureScore = roundScore(Math.max(3, (starSignals / 4) * 10));

        const outcomeMatches = (trimmed.match(/\b(\d+%?|\d+\.\d+|increase|reduced|improved|completed|delivered|result|impact|outcome|faster|better)\b/gi) || []).length;
        const outcomeEvidenceScore = roundScore(Math.min(10, Math.max(3, (outcomeMatches * 2) + (sentences.length >= 3 ? 2 : 0))));

        const fillerMatches = (trimmed.match(/\b(um|uh|maybe|probably|kind of|sort of|i think)\b/gi) || []).length;
        const confidenceMatches = (trimmed.match(/\b(led|built|delivered|improved|learned|organized|supported|resolved|collaborated|achieved|completed)\b/gi) || []).length;
        const toneScore = roundScore(Math.max(3, Math.min(10, 6 + confidenceMatches - (fillerMatches * 1.5))));

        const algorithms = [
            {
                name: "Keyword Coverage",
                score: keywordCoverageScore,
                detail: keywordMatches > 0
                    ? `Matched ${keywordMatches} category keyword${keywordMatches === 1 ? "" : "s"} from the active interview track.`
                    : "Add more category-specific language so the answer sounds closer to the prompt.",
                status: "Ready",
                available: true
            },
            {
                name: "STAR Structure",
                score: starStructureScore,
                detail: starSignals >= 3
                    ? "The answer shows a clear situation, action, and result flow."
                    : "Use a stronger situation-task-action-result structure so the story lands cleanly.",
                status: "Ready",
                available: true
            },
            {
                name: "Outcome Evidence",
                score: outcomeEvidenceScore,
                detail: outcomeMatches > 0
                    ? "The answer includes concrete outcomes or measurable evidence."
                    : "Add a result, metric, or visible impact to make the answer more credible.",
                status: "Ready",
                available: true
            },
            {
                name: "Professional Tone",
                score: toneScore,
                detail: fillerMatches === 0
                    ? "The wording stays direct and interview-appropriate."
                    : "Remove tentative filler phrases so the answer sounds more confident.",
                status: "Ready",
                available: true
            }
        ];
        const average = averageScore(algorithms.map((algorithm) => algorithm.score));

        return {
            label: "Answer Process",
            average,
            summary: summarizeScoreBand(
                average,
                "Your answer process is structured, relevant, and convincingly supported.",
                "Your answer process is workable, but stronger structure and evidence would improve it.",
                "Your answer process needs a clearer structure, stronger evidence, and more direct language."
            ),
            status: "Ready",
            available: true,
            algorithms
        };
    }

    function buildProcessEvaluations(answer, keywords, scoreData) {
        const visualSnapshot = getLiveVisualSnapshot();

        return {
            response: buildResponseProcessEvaluation(answer, keywords, scoreData),
            bodyLanguage: cloneProcessEvaluation(visualSnapshot.bodyLanguage),
            facialExpressions: cloneProcessEvaluation(visualSnapshot.facialExpressions)
        };
    }

    function renderScoreBar(label, value) {
        const width = `${Math.min(100, value * 10)}%`;

        return `
            <div>
                <div class="mb-1 flex items-center justify-between">
                    <span class="text-sm text-gray-700 dark:text-gray-300">${label}</span>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">${value} / 10</span>
                </div>
                <div class="h-2 rounded-full bg-gray-200 dark:bg-gray-800">
                    <div class="h-2 rounded-full bg-brand-500" style="width: ${width};"></div>
                </div>
            </div>
        `;
    }

    function renderCriterionFeedbackCard(label, value, description) {
        return `
            <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-900/70">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs uppercase tracking-wide text-gray-500">${label}</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white/90">${value} / 10</p>
                </div>
                <p class="content-break mt-3 text-sm leading-6 text-gray-600 dark:text-gray-400">${description}</p>
            </div>
        `;
    }

    function renderCapstoneRubricSection(rubric) {
        const items = [
            ["Verbal", formatRubricScore(rubric.verbal), "Weighted clarity, relevance, grammar, and professionalism."],
            ["Non-Verbal", formatRubricScore(rubric.nonVerbal), "Selected eye contact, posture, head movement, and facial composure cues."],
            ["Overall", formatRubricScore(rubric.overall), "Combined capstone score based on the manuscript weighting model."],
            ["Readiness", rubric.readinessLabel || "No data yet", "Interpretation band used for manuscript-style reporting."]
        ];

        return `
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/70">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <strong class="block text-sm font-semibold text-gray-900 dark:text-white/90">Capstone Rubric</strong>
                        <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-400">
                            The runtime scores below are also translated into the manuscript's weighted 1-to-5 rubric.
                        </p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-medium text-gray-700 shadow-theme-xs dark:bg-gray-800 dark:text-gray-300">
                        ${rubric.readinessLabel || "No data yet"}
                    </span>
                </div>

                <div class="mt-4 grid gap-3 md:grid-cols-2">
                    ${items.map(([label, value, description]) => `
                        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-950/40">
                            <p class="text-xs uppercase tracking-wide text-gray-500">${label}</p>
                            <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white/90">${value}</p>
                            <p class="mt-2 text-xs leading-5 text-gray-500 dark:text-gray-400">${description}</p>
                        </div>
                    `).join("")}
                </div>
            </div>
        `;
    }

    function renderProcessEvaluationSection(processEvaluations) {
        const processes = Object.values(processEvaluations || {});

        return `
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/70">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <strong class="block text-sm font-semibold text-gray-900 dark:text-white/90">Algorithm Evaluations</strong>
                        <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-400">
                            These process checks support the manuscript-aligned evaluation flow for answer quality and selected non-verbal cues.
                        </p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-medium text-gray-700 shadow-theme-xs dark:bg-gray-800 dark:text-gray-300">
                        ${processes.length} processes
                    </span>
                </div>

                <div class="mt-4 grid gap-4 xl:grid-cols-3">
                    ${processes.map((process) => renderProcessEvaluationPanel(process)).join("")}
                </div>
            </div>
        `;
    }

    function resetFeedbackPlaceholder() {
        if (!elements.feedbackContent) {
            return;
        }

        elements.feedbackContent.innerHTML = `
            <div class="rounded-2xl border border-dashed border-gray-300 px-5 py-10 text-center dark:border-gray-700">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">No answer evaluated yet</h3>
                <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">
                    Once you submit a response, the system will generate detailed scores, strengths, improvement areas, and suggestions.
                </p>
            </div>
        `;
    }

    function getEntryRubric(entry) {
        return entry?.feedbackSummary?.manuscriptRubric || buildManuscriptRubric(
            entry || {},
            entry?.feedbackSummary?.visualSnapshot || {},
            entry?.feedbackSummary?.processEvaluations || {}
        );
    }

    function buildSessionRecord() {
        if (!state.selectedCategory || state.feedbackHistory.length === 0) {
            return null;
        }

        const sessionId = state.sessionId || createSessionId();
        const criteriaTotals = state.feedbackHistory.reduce((totals, entry) => {
            totals.clarity += entry.clarity;
            totals.relevance += entry.relevance;
            totals.grammar += entry.grammar;
            totals.professionalism += entry.professionalism;
            return totals;
        }, {
            clarity: 0,
            relevance: 0,
            grammar: 0,
            professionalism: 0
        });
        const rubricTotals = state.feedbackHistory.reduce((totals, entry) => {
            const rubric = getEntryRubric(entry);
            const visualSnapshot = entry?.feedbackSummary?.visualSnapshot || {};

            totals.eyeContact += Number(visualSnapshot.eyeContactScore) || 0;
            totals.posture += Number(visualSnapshot.postureScore) || 0;
            totals.headMovement += Number(visualSnapshot.headMovementScore) || 0;
            totals.facialComposure += Number(visualSnapshot.facialComposureScore) || 0;
            totals.manuscriptVerbal += Number(rubric.verbal) || 0;
            totals.manuscriptNonVerbal += Number(rubric.nonVerbal) || 0;
            totals.manuscriptOverall += Number(rubric.overall) || 0;

            return totals;
        }, {
            eyeContact: 0,
            posture: 0,
            headMovement: 0,
            facialComposure: 0,
            manuscriptVerbal: 0,
            manuscriptNonVerbal: 0,
            manuscriptOverall: 0
        });

        const answerCount = state.feedbackHistory.length;
        const focusMode = getSelectedFocusMode();
        const pacingMode = getSelectedPacingMode();

        state.sessionId = sessionId;

        return {
            id: sessionId,
            startedAt: state.sessionStartedAt || new Date().toISOString(),
            savedAt: new Date().toISOString(),
            categoryId: state.selectedCategory.id,
            categoryName: state.selectedCategory.name,
            categoryDescription: [
                buildFieldSummaryLine(),
                state.questionSetSummary
            ].filter(Boolean).join(" "),
            questionCount: getActiveQuestionCount(),
            answeredCount: answerCount,
            focusMode: focusMode.label,
            pacingMode: pacingMode.label,
            timerTargetSeconds: state.timerTarget,
            averageScore: Number((state.feedbackHistory.reduce((sum, entry) => sum + entry.average, 0) / answerCount).toFixed(1)),
            criteriaAverages: {
                clarity: Number((criteriaTotals.clarity / answerCount).toFixed(1)),
                relevance: Number((criteriaTotals.relevance / answerCount).toFixed(1)),
                grammar: Number((criteriaTotals.grammar / answerCount).toFixed(1)),
                professionalism: Number((criteriaTotals.professionalism / answerCount).toFixed(1)),
                eyeContact: Number((rubricTotals.eyeContact / answerCount).toFixed(1)),
                posture: Number((rubricTotals.posture / answerCount).toFixed(1)),
                headMovement: Number((rubricTotals.headMovement / answerCount).toFixed(1)),
                facialComposure: Number((rubricTotals.facialComposure / answerCount).toFixed(1)),
                manuscriptVerbal: Number((rubricTotals.manuscriptVerbal / answerCount).toFixed(2)),
                manuscriptNonVerbal: Number((rubricTotals.manuscriptNonVerbal / answerCount).toFixed(2)),
                manuscriptOverall: Number((rubricTotals.manuscriptOverall / answerCount).toFixed(2))
            },
            completed: answerCount >= getActiveQuestionCount(),
            answers: state.feedbackHistory
                .slice()
                .sort((left, right) => left.questionIndex - right.questionIndex)
                .map((entry) => ({
                    questionIndex: entry.questionIndex,
                    questionNumber: entry.questionNumber,
                    question: entry.question,
                    answer: entry.answer,
                    average: entry.average,
                    clarity: entry.clarity,
                    relevance: entry.relevance,
                    grammar: entry.grammar,
                    professionalism: entry.professionalism,
                    matchedKeywords: entry.matchedKeywords,
                    elapsedSeconds: entry.elapsedSeconds,
                    inputMode: entry.inputMode,
                    feedbackSummary: entry.feedbackSummary
                }))
        };
    }

    async function persistSessionIfNeeded() {
        if (state.sessionSaved) {
            return true;
        }

        const sessionRecord = buildSessionRecord();

        if (!sessionRecord) {
            return false;
        }

        state.savingSession = true;
        updateSessionActionButtons();

        try {
            await appendPracticeSession(sessionRecord);
            state.sessionSaved = true;
            return true;
        } finally {
            state.savingSession = false;
            updateSessionActionButtons();
        }
    }

    function renderFeedback(answer, scoreData, summary) {
        const normalizedSummary = normalizeFeedbackSummary(answer, scoreData, summary);
        const processEvaluations = summary?.processEvaluations || buildProcessEvaluations(answer, state.selectedCategory?.keywords || [], scoreData);
        const manuscriptRubric = summary?.manuscriptRubric || buildManuscriptRubric(
            scoreData,
            summary?.visualSnapshot || {},
            processEvaluations
        );
        const providerMeta = normalizedSummary.provider
            ? `<span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-gray-700 shadow-theme-xs dark:bg-gray-800 dark:text-gray-300">${normalizedSummary.provider}</span>`
            : "";

        if (!elements.feedbackContent) {
            return;
        }

        elements.feedbackContent.innerHTML = `
            <div class="space-y-5">
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                    <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-900/70">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Overall Score</p>
                        <strong class="mt-2 block text-lg font-semibold text-gray-900 dark:text-white/90">${scoreData.average} / 10</strong>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-900/70">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Matched Keywords</p>
                        <strong class="mt-2 block text-lg font-semibold text-gray-900 dark:text-white/90">${scoreData.matchedKeywords}</strong>
                    </div>
                </div>

                <div class="space-y-4">
                    ${renderScoreBar("Clarity", scoreData.clarity)}
                    ${renderScoreBar("Relevance", scoreData.relevance)}
                    ${renderScoreBar("Grammar", scoreData.grammar)}
                    ${renderScoreBar("Professionalism", scoreData.professionalism)}
                </div>

                <div class="grid gap-3 md:grid-cols-2">
                    ${renderCriterionFeedbackCard("Clarity", scoreData.clarity, normalizedSummary.criteria.clarity)}
                    ${renderCriterionFeedbackCard("Relevance", scoreData.relevance, normalizedSummary.criteria.relevance)}
                    ${renderCriterionFeedbackCard("Grammar", scoreData.grammar, normalizedSummary.criteria.grammar)}
                    ${renderCriterionFeedbackCard("Professionalism", scoreData.professionalism, normalizedSummary.criteria.professionalism)}
                </div>

                ${renderCapstoneRubricSection(manuscriptRubric)}

                ${renderProcessEvaluationSection(processEvaluations)}

                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/70">
                    <strong class="block text-sm font-semibold text-gray-900 dark:text-white/90">Strengths</strong>
                    <ul class="mt-3 list-disc space-y-2 pl-5 text-sm leading-6 text-gray-600 dark:text-gray-400">
                        ${normalizedSummary.strengths.length ? normalizedSummary.strengths.map((item) => `<li class="content-break">${item}</li>`).join("") : '<li class="content-break">Your answer has a good starting point.</li>'}
                    </ul>
                </div>

                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/70">
                    <strong class="block text-sm font-semibold text-gray-900 dark:text-white/90">Improvement Areas</strong>
                    <ul class="mt-3 list-disc space-y-2 pl-5 text-sm leading-6 text-gray-600 dark:text-gray-400">
                        ${normalizedSummary.improvements.length ? normalizedSummary.improvements.map((item) => `<li class="content-break">${item}</li>`).join("") : '<li class="content-break">Keep practicing to improve consistency.</li>'}
                    </ul>
                </div>

                <div class="rounded-xl border border-brand-100 bg-brand-50 p-4 dark:border-brand-500/20 dark:bg-brand-500/10">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <strong class="block text-sm font-semibold text-gray-900 dark:text-white/90">Feedback Summary</strong>
                        ${providerMeta}
                    </div>
                    <p class="content-break mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">
                        ${normalizedSummary.overall}
                    </p>
                    <p class="content-break mt-3 text-sm leading-6 text-gray-600 dark:text-gray-400">
                        <strong>Next step:</strong> ${normalizedSummary.nextStep}
                    </p>
                </div>
            </div>
        `;
    }

    function continueInterviewAfterAnswer({ saved = true } = {}) {
        cancelAutoAdvance();
        cancelVoiceAutoSubmit();

        if (!state.selectedCategory || getActiveQuestionCount() === 0) {
            return;
        }

        if (state.questionIndex + 1 >= getActiveQuestionCount()) {
            state.voiceConversationEnabled = false;
            state.recognitionShouldRestart = false;
            state.practiceStarted = false;
            stopTimer();
            elements.practiceLabelTag.textContent = "Interview complete";
            updateSessionActionButtons();
            showMessage(
                saved ? "success" : "warning",
                saved
                    ? "That was the final answer. The interview is complete and saved to Progress."
                    : "That was the final answer. The interview is complete, but saving to Progress needs another try."
            );
            return;
        }

        elements.practiceLabelTag.textContent = "Interviewer preparing follow-up";
        updateSessionActionButtons();
        showMessage(
            saved ? "success" : "warning",
            saved
                ? "Answer saved. The interviewer will ask the next question automatically."
                : "Answer evaluated. Saving had an issue, but the interviewer will continue automatically."
        );

        state.autoAdvanceTimeout = window.setTimeout(() => {
            state.autoAdvanceTimeout = null;
            nextQuestion({ automatic: true });
        }, 1200);
    }

    function buildOpeningConversationAcknowledgement(reply) {
        const normalized = String(reply || "").toLowerCase();

        if (/\b(nervous|anxious|worried|scared|stress|stressed|afraid)\b/.test(normalized)) {
            return "Thank you for being honest. A little nervousness is normal in an interview. Take one calm breath, and we will go one question at a time. I will start the interview now.";
        }

        if (/\b(good|great|fine|ready|confident|excited|prepared)\b/.test(normalized)) {
            return "Good, I like that energy. Keep the same natural tone, stay focused, and answer like we are having a real conversation. I will start the interview now.";
        }

        if (/\b(tired|sick|not well|sleepy|exhausted)\b/.test(normalized)) {
            return "Thanks for letting me know. Keep your pace steady, take your time, and focus on one clear answer at a time. I will start the interview now.";
        }

        return "Thank you for sharing. Let us keep this calm and conversational, just like a real interview. I will start the interview now.";
    }

    function finishOpeningConversationAcknowledgement(result = {}) {
        const status = String(result?.status || "ended");
        const focusResponse = state.openingConversationFocusPending;

        state.interviewerSpeechActive = false;
        state.openingConversationActive = false;
        state.openingConversationAcknowledging = false;
        state.openingConversationCompleted = true;
        state.openingConversationFocusPending = false;
        updateSessionActionButtons();

        if (["stopped", "superseded"].includes(status)) {
            return;
        }

        if (!isPracticeModalOpen() || !state.selectedCategory || getActiveQuestionCount() === 0 || state.questionIndex !== 0) {
            return;
        }

        if (["empty", "error", "unavailable"].includes(status)) {
            showMessage("warning", "The greeting response could not play, so the interviewer is starting the first question.");
        } else {
            clearMessage();
        }

        autoStartCurrentQuestion({ focusResponse });
    }

    function submitOpeningConversationReply() {
        if (!isOpeningConversationListening()) {
            return;
        }

        commitRecognitionInterimText();

        const reply = getResponseText();

        if (!isVoiceAutoSubmitReady(reply)) {
            setVoiceStatus("Say a quick greeting or tell the interviewer how you feel.", "listening");
            return;
        }

        const acknowledgement = buildOpeningConversationAcknowledgement(reply);

        state.openingConversationReply = reply;
        state.openingConversationActive = false;
        state.openingConversationAcknowledging = true;
        state.openingConversationCompleted = true;
        state.interviewerSpeechActive = true;

        stopVoiceInput({
            commitTranscript: true,
            disableConversation: false,
            quiet: true
        });
        elements.responseInput.value = "";
        resetRecognitionDraft();
        elements.practiceLabelTag.textContent = "Interviewer responding";
        showMessage("info", "The interviewer is responding to your greeting, then the first question will begin.");
        setVoiceStatus("Interviewer is responding to your greeting.", "processing");
        updateSessionActionButtons();

        const speechStarted = interviewerControls.speakOpeningAcknowledgement({
            text: acknowledgement,
            onSettled: finishOpeningConversationAcknowledgement
        });

        if (!speechStarted) {
            finishOpeningConversationAcknowledgement({ status: "unavailable" });
            return;
        }

        if (typeof speechStarted.then === "function") {
            speechStarted.then((started) => {
                if (!started) {
                    finishOpeningConversationAcknowledgement({ status: "unavailable" });
                }
            }).catch((error) => {
                console.error(error);
                finishOpeningConversationAcknowledgement({ status: "error" });
            });
        }
    }

    function stripTrailingSubmitCommandFromResponse(value) {
        return String(value || "")
            .replace(/\s*(?:that is my answer|that's my answer|thats my answer|that is all|that's all|thats all|send my answer|send the answer|submit my answer|submit the answer|save my answer|done answering|i am done|i'm done|im done|finished answering)\s*\.?$/i, "")
            .trim();
    }

    function prepareVoiceCommandExecution({ keepConversation = true, clearDraft = true } = {}) {
        cancelVoiceAutoSubmit();
        stopVoiceInput({
            commitTranscript: false,
            disableConversation: !keepConversation,
            quiet: true
        });

        if (keepConversation) {
            state.voiceConversationEnabled = true;
        }

        if (clearDraft) {
            elements.responseInput.value = "";
            resetRecognitionDraft();
        }

        updateSessionActionButtons();
    }

    function setPacingModeByLabel(label) {
        const index = practiceData.pacingModes.findIndex((mode) => mode.label === label);

        if (index < 0) {
            return null;
        }

        elements.pacingModeSelect.value = String(index);

        const selectedPacing = getSelectedPacingMode();

        state.timerTarget = selectedPacing.seconds;
        elements.timerTargetValue.textContent = formatTime(state.timerTarget);
        updateTipsPanel();

        return selectedPacing;
    }

    function setFocusModeByLabel(label) {
        const index = practiceData.focusModes.findIndex((mode) => mode.label === label);

        if (index < 0) {
            return null;
        }

        elements.focusModeSelect.value = String(index);
        updateFocusModeDisplay();
        updateTipsPanel();

        return getSelectedFocusMode();
    }

    function buildTimeCheckMessage() {
        const elapsed = formatTime(state.timerSeconds);
        const target = formatTime(state.timerTarget);

        return `Time check. You are at ${elapsed} out of the ${target} target for this answer.`;
    }

    function buildProgressCheckMessage() {
        const totalQuestions = Math.max(getActiveQuestionCount(), 1);
        const currentQuestion = Math.min(state.questionIndex + 1, totalQuestions);
        const answered = state.answeredCount;

        return `Progress check. You are on question ${currentQuestion} of ${totalQuestions}, with ${answered} ${answered === 1 ? "answer" : "answers"} reviewed.`;
    }

    function buildLocalCoachAdviceMessage() {
        const question = getActiveQuestion();
        const focus = getSelectedFocusMode();

        return [
            question ? `For this question, answer directly first: ${question}` : "Use a direct opening sentence first.",
            "Then add one specific example, the action you took, and the result.",
            focus?.tip ? `Coach reminder: ${focus.tip}` : "Keep it clear, natural, and professional."
        ].join(" ");
    }

    function limitSpokenCoachReply(value) {
        const reply = String(value || "").replace(/\s+/g, " ").trim();

        if (reply.length <= 700) {
            return reply;
        }

        return `${reply.slice(0, 680).trim()}...`;
    }

    async function requestCoachAdviceForCommand(sourceText) {
        const question = getActiveQuestion();
        const message = [
            "The user gave a spoken interview command and wants immediate coaching during a live mock interview.",
            sourceText ? `User command: ${sourceText}` : "",
            question ? `Current question: ${question}` : "",
            "Reply briefly in a natural interviewer voice. Give practical guidance only; do not score the answer."
        ].filter(Boolean).join("\n");

        try {
            const payload = await requestWorkspace("chatbot", {
                method: "POST",
                body: {
                    message,
                    mode: "chat",
                    providerId: state.questionAgentSelectedProviderId,
                    categoryId: state.selectedCategory?.id || null,
                    currentQuestion: question,
                    answerDraft: getResponseText(),
                    history: state.questionAgentHistory.slice(-6)
                }
            });

            applyQuestionAgentProviderCatalog(payload.availableProviders, payload.requestedProviderId || state.questionAgentSelectedProviderId);
            state.questionAgentResolvedProviderLabel = String(payload.provider || state.questionAgentResolvedProviderLabel || "");

            return limitSpokenCoachReply(payload.reply || buildLocalCoachAdviceMessage());
        } catch (error) {
            console.error(error);

            return buildLocalCoachAdviceMessage();
        }
    }

    async function handleVoiceConversationCommand(command, { sourceText = "" } = {}) {
        if (!command?.intent) {
            return false;
        }

        switch (command.intent) {
            case "repeat-question":
                prepareVoiceCommandExecution();
                setVoiceStatus("Repeating the question, then I will listen again.", "processing");
                showMessage("info", "Repeating the current question.");
                askQuestionLikeConversation();
                return true;

            case "command-help":
                prepareVoiceCommandExecution();
                showMessage("info", VOICE_COMMAND_HELP_TEXT);
                speakAssistantMessageLikeConversation(VOICE_COMMAND_HELP_TEXT);
                return true;

            case "pause-interview":
                prepareVoiceCommandExecution({ keepConversation: false });
                cancelAutoAdvance();
                pauseTimer();
                interviewerControls.stopSpeaking();
                elements.practiceLabelTag.textContent = "Interview paused";
                showMessage("info", "Interview paused. Turn Mic On or say continue after turning the mic back on.");
                speakAssistantMessageLikeConversation("Paused. Turn Mic On when you are ready to continue.", { resumeListening: false });
                return true;

            case "resume-interview":
                prepareVoiceCommandExecution();
                resumeTimerIfNeeded();

                if (!state.practiceStarted) {
                    beginPracticeRound({ speakQuestion: true, focusResponse: true });
                } else {
                    showMessage("info", "Continuing the interview.");
                    askQuestionLikeConversation();
                }
                return true;

            case "next-question":
                prepareVoiceCommandExecution();
                showMessage("info", "Moving to the next question.");
                nextQuestion({ automatic: true });
                return true;

            case "submit-answer": {
                cancelVoiceAutoSubmit();
                stopVoiceInput({
                    commitTranscript: true,
                    disableConversation: false,
                    quiet: true
                });
                state.voiceConversationEnabled = true;

                const cleanedAnswer = stripTrailingSubmitCommandFromResponse(sourceText);

                if (cleanedAnswer && cleanedAnswer !== sourceText.trim()) {
                    elements.responseInput.value = cleanedAnswer;
                    resetRecognitionDraft(cleanedAnswer);
                }

                if (!isVoiceAutoSubmitReady(getResponseText())) {
                    setVoiceStatus("I need a little more answer before I can submit it.", "warning");
                    showMessage("warning", "Give a short answer first, then say send answer.");
                    startVoiceInput({ automatic: true });
                    return true;
                }

                submitAnswer({ automatic: true });
                return true;
            }

            case "end-interview":
                prepareVoiceCommandExecution({ keepConversation: false });
                await endSession();
                return true;

            case "mic-off":
                prepareVoiceCommandExecution({ keepConversation: false });
                setVoiceStatus("Mic is off.", "idle");
                showMessage("info", "Mic is off. Use Mic On when you want to continue.");
                return true;

            case "mic-on":
                prepareVoiceCommandExecution({ clearDraft: true });
                startVoiceInput({ automatic: true });
                return true;

            case "camera-on":
                prepareVoiceCommandExecution();
                await interviewerControls.startCamera();
                showMessage("info", "Camera command received.");
                speakAssistantMessageLikeConversation("Camera command received. Continue when you are ready.");
                return true;

            case "camera-off":
                prepareVoiceCommandExecution();
                interviewerControls.stopCamera();
                showMessage("info", "Camera is off.");
                speakAssistantMessageLikeConversation("Camera is off. I will keep listening by mic.");
                return true;

            case "clear-answer":
                prepareVoiceCommandExecution();
                showMessage("info", "Answer cleared. Start again when ready.");
                speakAssistantMessageLikeConversation("I cleared your answer. Start again when you are ready.");
                return true;

            case "answer-outline":
                prepareVoiceCommandExecution();
                applyLearningAction("load-outline");
                speakAssistantMessageLikeConversation("I added an answer outline. Use it as a guide, then speak naturally.");
                return true;

            case "coach-advice": {
                prepareVoiceCommandExecution();
                setVoiceStatus("Asking the coach for quick guidance...", "processing");
                const advice = await requestCoachAdviceForCommand(sourceText);

                showMessage("info", advice);
                speakAssistantMessageLikeConversation(advice);
                return true;
            }

            case "regenerate-questions":
                prepareVoiceCommandExecution();
                setVoiceStatus("Generating a fresh question set...", "processing");
                showMessage("info", "Generating a fresh question set from your voice command.");
                await generateQuestionSet("Generate a fresh replacement question set because the user asked by voice command.", {
                    keepVoiceConversation: true
                });
                return true;

            case "time-check": {
                prepareVoiceCommandExecution();
                const message = buildTimeCheckMessage();

                showMessage("info", message);
                speakAssistantMessageLikeConversation(message);
                return true;
            }

            case "progress-check": {
                prepareVoiceCommandExecution();
                const message = buildProgressCheckMessage();

                showMessage("info", message);
                speakAssistantMessageLikeConversation(message);
                return true;
            }

            case "speech-slower":
                prepareVoiceCommandExecution();
                state.interviewerSpeechRate = Math.max(0.82, state.interviewerSpeechRate - 0.12);
                showMessage("info", "Interviewer browser voice speed set slower.");
                speakAssistantMessageLikeConversation("Okay, I will speak a little slower when browser voice is used.");
                return true;

            case "speech-faster":
                prepareVoiceCommandExecution();
                state.interviewerSpeechRate = Math.min(1.18, state.interviewerSpeechRate + 0.12);
                showMessage("info", "Interviewer browser voice speed set faster.");
                speakAssistantMessageLikeConversation("Okay, I will speak a little faster when browser voice is used.");
                return true;

            case "speech-normal":
                prepareVoiceCommandExecution();
                state.interviewerSpeechRate = 1;
                showMessage("info", "Interviewer browser voice speed reset.");
                speakAssistantMessageLikeConversation("Okay, I will return to a normal speaking speed.");
                return true;

            case "set-pacing": {
                prepareVoiceCommandExecution();
                const pacing = setPacingModeByLabel(command.value);
                const message = pacing
                    ? `Pacing updated to ${pacing.label}. Your answer target is ${formatTime(pacing.seconds)}.`
                    : "I could not find that pacing mode.";

                showMessage(pacing ? "info" : "warning", message);
                speakAssistantMessageLikeConversation(message);
                return true;
            }

            case "set-focus": {
                prepareVoiceCommandExecution();
                const focus = setFocusModeByLabel(command.value);
                const message = focus
                    ? `Coach focus updated to ${focus.label}. ${focus.tip}`
                    : "I could not find that coach focus.";

                showMessage(focus ? "info" : "warning", message);
                speakAssistantMessageLikeConversation(message);
                return true;
            }

            default:
                return false;
        }
    }

    async function autoSubmitVoiceAnswer() {
        if (!shouldResumeVoiceConversation() || state.answerSubmitting) {
            return;
        }

        commitRecognitionInterimText();

        if (isOpeningConversationListening()) {
            submitOpeningConversationReply();
            return;
        }

        const answer = getResponseText();
        const command = detectVoiceConversationCommand(answer);

        if (command && await handleVoiceConversationCommand(command, { sourceText: answer })) {
            return;
        }

        if (!isVoiceAutoSubmitReady(answer)) {
            setVoiceStatus("Keep speaking. I will send your answer after a short pause.", "listening");
            return;
        }

        submitAnswer({ automatic: true });
    }

    async function submitAnswer({ automatic = false } = {}) {
        if (state.answerSubmitting) {
            return;
        }

        if (!state.selectedCategory) {
            if (!automatic) {
                showMessage("warning", "Choose a category before submitting an answer.");
            }
            return;
        }

        if (getActiveQuestionCount() === 0) {
            if (!automatic) {
                showMessage("warning", "Generate a question set first before submitting an answer.");
            }
            return;
        }

        if (state.recognitionActive) {
            stopVoiceInput({
                disableConversation: false,
                quiet: automatic
            });
        }

        const answer = getResponseText();
        const activeQuestion = getActiveQuestion();

        if (!activeQuestion) {
            if (!automatic) {
                showMessage("warning", "There is no active interview question yet.");
            }
            return;
        }

        if (!answer) {
            if (!automatic) {
                showMessage("error", "Blank answers are not accepted. Please type or record your response.");
            }
            return;
        }

        state.answerSubmitting = true;
        stopTimer();

        const scoreData = calculateScore(answer, state.selectedCategory.keywords);
        const processEvaluations = buildProcessEvaluations(answer, state.selectedCategory.keywords, scoreData);
        const visualSnapshot = getPersistedVisualSnapshot();
        const manuscriptRubric = buildManuscriptRubric(scoreData, visualSnapshot, processEvaluations);
        const fallbackSummary = {
            ...normalizeFeedbackSummary(answer, scoreData, buildFeedbackSummary(answer, scoreData)),
            processEvaluations,
            visualSnapshot,
            manuscriptRubric
        };

        state.lastProcessEvaluations = processEvaluations;

        state.feedbackLoading = true;
        updateSessionActionButtons();
        renderFeedback(answer, scoreData, fallbackSummary);
        elements.practiceLabelTag.textContent = "Generating AI feedback";
        showMessage("info", "Scoring complete. AI is generating automated feedback based on the criteria.");

        let finalSummary = fallbackSummary;
        let feedbackMessage = "Your answer has been evaluated and saved successfully.";

        try {
            const payload = await requestWorkspace("chatbot", {
                method: "POST",
                body: {
                    message: "Create automated interview feedback based on the criteria for this answer.",
                    mode: "feedback_review",
                    providerId: state.questionAgentSelectedProviderId,
                    categoryId: state.selectedCategory.id,
                    currentQuestion: activeQuestion,
                    answerDraft: answer,
                    criteriaScores: scoreData
                }
            });

            applyQuestionAgentProviderCatalog(payload.availableProviders, payload.requestedProviderId || state.questionAgentSelectedProviderId);
            state.questionAgentResolvedProviderLabel = String(payload.provider || state.questionAgentResolvedProviderLabel || "");
            finalSummary = {
                ...normalizeFeedbackSummary(answer, scoreData, {
                    ...(payload.feedbackSummary || {}),
                    provider: payload.feedbackSummary?.provider || payload.provider || null
                }),
                processEvaluations,
                visualSnapshot,
                manuscriptRubric
            };
            renderFeedback(answer, scoreData, finalSummary);
            feedbackMessage = payload.usedFallback
                ? "Your answer was evaluated and local automated feedback was saved successfully."
                : "Your answer was evaluated and AI automated feedback was saved successfully.";
        } catch (error) {
            console.error(error);
            renderFeedback(answer, scoreData, fallbackSummary);
            feedbackMessage = "Your answer was evaluated and saved, but AI feedback is temporarily unavailable so local automated feedback was used.";
        }

        const historyEntry = {
            questionIndex: state.questionIndex,
            questionNumber: state.questionIndex + 1,
            question: activeQuestion,
            answer,
            elapsedSeconds: state.timerSeconds,
            inputMode: state.currentMode,
            feedbackSummary: finalSummary,
            ...scoreData
        };
        const existingIndex = state.feedbackHistory.findIndex((entry) => entry.questionIndex === state.questionIndex);

        if (existingIndex >= 0) {
            state.feedbackHistory[existingIndex] = historyEntry;
        } else {
            state.feedbackHistory.push(historyEntry);
        }

        state.answeredCount = state.feedbackHistory.length;
        state.sessionSaved = false;
        state.feedbackLoading = false;

        updateTipsPanel();
        updateSessionActionButtons();
        elements.practiceLabelTag.textContent = "Answer evaluated";

        let answerWasSaved = false;

        try {
            await persistSessionIfNeeded();
            answerWasSaved = true;
            showMessage("success", `${feedbackMessage}${buildLearningActivityScoreMessage(scoreData.average)}`);
        } catch (error) {
            console.error(error);
            showMessage("warning", `Your answer was evaluated, but it could not be saved to the database.${buildLearningActivityScoreMessage(scoreData.average)}`);
        }

        continueInterviewAfterAnswer({ saved: answerWasSaved });
        state.answerSubmitting = false;
        updateSessionActionButtons();
    }

    function nextQuestion({ automatic = false } = {}) {
        if (!state.selectedCategory) {
            showMessage("warning", "Please choose a category first.");
            return;
        }

        if (getActiveQuestionCount() === 0) {
            showMessage("warning", "Generate a question set first before moving to the next question.");
            return;
        }

        if (isOpeningIntroductionBlockingQuestion()) {
            queueFirstQuestionAfterOpening({ focusResponse: true });
            return;
        }

        stopVoiceInput({
            commitTranscript: false,
            disableConversation: false,
            quiet: state.voiceConversationEnabled
        });

        if (state.questionIndex + 1 >= getActiveQuestionCount()) {
            showMessage("info", automatic
                ? "The interview has reached the final question."
                : "You have reached the end of this session.");
            elements.practiceLabelTag.textContent = "Session complete";
            state.practiceStarted = false;
            stopTimer();
            updateSessionActionButtons();
            return;
        }

        state.questionIndex += 1;
        loadCurrentQuestion();
        autoStartCurrentQuestion();
    }

    async function endSession() {
        state.questionAgentRequestId += 1;
        state.questionAgentLoading = false;
        cancelAutoAdvance();
        cancelPendingQuestionSpeech();
        stopVoiceInput();
        stopTimer();
        interviewerControls.stopSpeaking();
        interviewerControls.stopCamera();
        let sessionWasSaved = false;

        try {
            sessionWasSaved = await persistSessionIfNeeded();
        } catch (error) {
            console.error(error);
        }

        if (!state.selectedCategory) {
            showMessage("info", "No session is currently active.");
            return;
        }

        elements.selectedCategoryName.textContent = "Select a track for the interview";
        elements.selectedCategoryDescription.textContent = "Your chosen interview type will load a fresh AI-generated question set.";
        elements.questionCounter.textContent = "Question 0 of 0";
        elements.practiceStatusTag.textContent = "Session ended";
        elements.practiceStatusTag.className = "inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300";
        elements.practiceLabelTag.textContent = "No active session";
        elements.currentQuestionText.textContent = "Choose a track from the sidebar to begin your interview simulation.";
        elements.coachTipText.textContent = "Choose a track from the sidebar to load your coach guidance and answer keywords.";
        elements.questionKeywordTags.innerHTML = "";
        elements.questionProgressFill.style.width = "0%";
        elements.responseInput.value = "";
        elements.questionTimerValue.textContent = "00:00";
        resetFeedbackPlaceholder();

        state.selectedCategory = null;
        state.selectedFieldPlan = null;
        state.pendingCategory = null;
        state.sessionId = null;
        state.questionIndex = 0;
        state.activeQuestions = [];
        state.answeredCount = 0;
        state.learningActivityContext = null;
        state.practiceStarted = false;
        state.welcomeDelivered = false;
        state.currentMode = "Text";
        state.sessionStartedAt = null;
        state.sessionSaved = false;
        state.feedbackHistory = [];
        state.lastProcessEvaluations = null;
        state.feedbackLoading = false;
        state.openingIntroductionSpeaking = false;
        state.openingIntroductionQuestionPending = false;
        state.openingIntroductionFocusPending = false;
        state.openingConversationActive = false;
        state.openingConversationAcknowledging = false;
        state.openingConversationCompleted = false;
        state.openingConversationFocusPending = false;
        state.openingConversationReply = "";
        state.questionSourceLabel = "Awaiting category";
        state.questionSetSummary = "Select a category and the chatbot will build a fresh question set for the workspace.";
        state.questionAgentResolvedProviderLabel = null;

        resetRecognitionDraft();
        resetQuestionAgentConversation();
        updateFieldSummaryUI();
        setVoiceStatus(
            state.speechRecognition
                ? "Mic is idle."
                : "Mic transcription is not supported in this browser. Use Chrome or Edge on localhost/HTTPS.",
            state.speechRecognition ? "idle" : "warning"
        );
        updateTipsPanel();
        updateSelectedCategoryButtons();
        updateSessionActionButtons();
        showMessage(
            sessionWasSaved ? "info" : "warning",
            sessionWasSaved
                ? "The practice session has ended and was saved to Progress."
                : "The practice session has ended, but it could not be saved to the database."
        );
    }

    function initSpeechRecognition() {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

        if (!SpeechRecognition) {
            setVoiceStatus(
                "Mic transcription is not supported in this browser. Use Chrome or Edge on localhost/HTTPS. For mobile devices, access via your computer's LAN IP address.",
                "warning"
            );
            updateSessionActionButtons();
            return;
        }

        const recognition = new SpeechRecognition();
        recognition.continuous = true;
        recognition.interimResults = true;
        recognition.lang = document.documentElement.lang || "en-US";
        recognition.maxAlternatives = 1;

        recognition.onstart = () => {
            state.recognitionActive = true;
            setVoiceStatus(
                isOpeningConversationListening()
                    ? "Mic is listening. Say hello and share how you feel."
                    : "Mic is listening. Pause when your answer is complete.",
                "listening"
            );
            updateSessionActionButtons();
            syncCurrentMode();
        };

        recognition.onresult = (event) => {
            let finalTranscript = "";
            let interimTranscript = "";

            for (let i = event.resultIndex; i < event.results.length; i += 1) {
                const transcriptChunk = combineTranscriptSegments(event.results[i][0].transcript);

                if (!transcriptChunk) {
                    continue;
                }

                if (event.results[i].isFinal) {
                    finalTranscript = combineTranscriptSegments(finalTranscript, transcriptChunk);
                } else {
                    interimTranscript = combineTranscriptSegments(interimTranscript, transcriptChunk);
                }
            }

            if (finalTranscript) {
                state.recognitionBaseText = combineTranscriptSegments(state.recognitionBaseText, finalTranscript);
                state.voiceInputUsed = true;
            }

            if (interimTranscript) {
                state.voiceInputUsed = true;
            }

            state.recognitionInterimText = interimTranscript;

            if (finalTranscript || interimTranscript) {
                applyRecognitionDraft();
                syncCurrentMode();
            }

            const isOpeningReply = isOpeningConversationListening();
            setVoiceStatus(
                interimTranscript
                    ? isOpeningReply ? "Listening to your opening reply..." : "Transcribing your answer in real time..."
                    : isOpeningReply ? "Mic is listening for your greeting." : "Mic is listening. Pause when your answer is complete.",
                interimTranscript ? "processing" : "listening"
            );

            if (finalTranscript || interimTranscript) {
                scheduleVoiceAutoSubmit();
            }
        };

        recognition.onerror = (event) => {
            console.error(event);

            if (event.error === "not-allowed" || event.error === "service-not-allowed" || event.error === "audio-capture") {
                state.recognitionShouldRestart = false;
            }

            if (event.error === "aborted") {
                setVoiceStatus("Voice input was stopped.", "idle");
            } else if (event.error === "no-speech") {
                setVoiceStatus("No speech detected. Keep speaking or try again.", "warning");
            } else {
                setVoiceStatus(getRecognitionErrorMessage(event.error), "error");
                showMessage("warning", getRecognitionErrorMessage(event.error));
            }
        };

        recognition.onend = () => {
            state.recognitionActive = false;
            commitRecognitionInterimText();
            updateSessionActionButtons();

            if (state.voiceConversationEnabled && !state.answerSubmitting && isVoiceAutoSubmitReady()) {
                autoSubmitVoiceAnswer();
                return;
            }

            if (state.recognitionShouldRestart && shouldResumeVoiceConversation()) {
                cancelRecognitionRestart();
                setVoiceStatus("Mic paused. Reconnecting...", "processing");
                state.restartVoiceTimeout = window.setTimeout(() => {
                    state.restartVoiceTimeout = null;

                    if (!state.recognitionShouldRestart || !shouldResumeVoiceConversation() || state.recognitionActive) {
                        updateSessionActionButtons();
                        return;
                    }

                    try {
                        state.speechRecognition.start();
                    } catch (error) {
                        console.error(error);
                        state.recognitionShouldRestart = false;
                        setVoiceStatus("Mic could not restart.", "error");
                        updateSessionActionButtons();
                    }
                }, 250);
                return;
            }

            setVoiceStatus(
                state.voiceConversationEnabled
                    ? "Mic is waiting for the interviewer."
                    : "Mic is idle.",
                state.voiceConversationEnabled ? "processing" : "idle"
            );
            updateSessionActionButtons();
        };

        state.speechRecognition = recognition;
        setVoiceStatus("Mic is ready when your session is live.", "idle");
        updateSessionActionButtons();
    }

    function launchSavedSetup(savedSetup) {
        const params = new URLSearchParams(window.location.search);

        if (params.get("launch") !== "setup" || !savedSetup?.savedAt) {
            return;
        }

        const preferredCategory = practiceData.categories.find((item) => item.id === savedSetup.preferredCategoryId);

        if (preferredCategory) {
            selectCategory(preferredCategory, { prefillNeed: savedSetup.notes || "" });
        }

        const responsePreferenceLabel = {
            text: "Text First",
            voice: "Voice First",
            hybrid: "Hybrid"
        };
        const trimmedNotes = savedSetup.notes.trim().replace(/\s+/g, " ");
        let message = "Practice launched with your saved setup defaults.";

        if (preferredCategory) {
            message = `${preferredCategory.name} is loaded and ready for practice.`;
        }

        message += ` ${savedSetup.questionCount} question${savedSetup.questionCount === 1 ? "" : "s"}, ${getSelectedFocusMode().label}, ${getSelectedPacingMode().label} pacing.`;

        if (responsePreferenceLabel[savedSetup.voiceMode]) {
            message += ` Response preference: ${responsePreferenceLabel[savedSetup.voiceMode]}.`;
        }

        if (trimmedNotes) {
            message += ` Note: ${truncateText(trimmedNotes)}`;
        }

        showMessage("success", message);

        if (isFieldModalOpen()) {
            setVoiceStatus("Complete the field setup first, then the interview workspace will open with your saved defaults.", "idle");
            elements.practiceFieldInput.focus();
        } else if (savedSetup.voiceMode === "voice") {
            setVoiceStatus(
                state.speechRecognition
                    ? "Voice First is selected. Use Mic On when you are ready."
                    : "Voice First is selected, but this browser does not support speech recognition.",
                state.speechRecognition ? "idle" : "warning"
            );
            elements.startVoiceBtn.focus();
        } else if (savedSetup.voiceMode === "hybrid") {
            setVoiceStatus("Hybrid mode is ready. Type or use Mic On anytime.", "idle");
            elements.responseInput.focus();
        } else if (preferredCategory) {
            setVoiceStatus("Text First is selected. You can still use voice input anytime.", "idle");
            elements.responseInput.focus();
        }

        params.delete("launch");
        const nextQuery = params.toString();
        const nextUrl = `${window.location.pathname}${nextQuery ? `?${nextQuery}` : ""}${window.location.hash}`;
        window.history.replaceState({}, "", nextUrl);
    }

    function launchCategoryFromQuery(savedSetup) {
        const params = new URLSearchParams(window.location.search);
        const requestedCategoryId = String(params.get("category") || "").trim();
        const launchSource = String(params.get("source") || "").trim();
        const requestedModuleId = String(params.get("module") || "").trim();
        const requestedActivityId = String(params.get("activity") || "").trim();
        const requestedActivityLevel = Number(params.get("level") || 1);
        const requestedActivityTarget = params.get("target");
        const hasLearningLaunchContext = launchSource === "learning-lab" || requestedModuleId || requestedActivityId;

        if (!requestedCategoryId && !hasLearningLaunchContext) {
            return false;
        }

        const learningContext = requestedActivityId
            ? setLearningActivityContext(
                requestedActivityId,
                requestedModuleId,
                requestedActivityLevel,
                requestedActivityTarget
            )
            : null;

        if (!requestedActivityId) {
            state.learningActivityContext = null;
        }

        const note = typeof savedSetup?.notes === "string" ? savedSetup.notes : "";
        const formatContextLabel = (value) => String(value || "")
            .split(/[-_]+/)
            .filter(Boolean)
            .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
            .join(" ");
        const moduleLabels = {
            "answer-blueprint": "Answer Blueprint",
            "delivery-rehearsal": "Delivery Rehearsal",
            "visual-presence": "Visual Presence",
            "reflection-review": "Reflection Review"
        };
        const activityLabels = {
            "quick-drill": "Quick Drill",
            "star-response": "STAR Response Drill",
            "voice-rehearsal": "Voice Rehearsal Sprint",
            "camera-check": "Camera Presence Check",
            "follow-up-sprint": "Follow-up Sprint",
            "track-launch": "Track Launch"
        };

        if (!requestedCategoryId) {
            const messageParts = ["Choose a practice category first to launch from Learning Lab."];

            if (requestedModuleId) {
                messageParts.push(`Module: ${moduleLabels[requestedModuleId] || formatContextLabel(requestedModuleId)}.`);
            }

            if (requestedActivityId) {
                messageParts.push(`Activity: ${activityLabels[requestedActivityId] || formatContextLabel(requestedActivityId)}.`);
            }

            if (learningContext) {
                messageParts.push(`${learningContext.levelLabel} target: ${learningContext.targetScore.toFixed(1)} / 10.`);
            }

            elements.practiceModalCategoryName.textContent = "Choose a category first";
            elements.practiceModalSummaryText.textContent = learningContext
                ? `${learningContext.activityLabel} ${learningContext.levelLabel} is ready. Choose Job Interview, Scholarship Interview, College Admission, or IT / Programming before the drill begins.`
                : "Choose Job Interview, Scholarship Interview, College Admission, or IT / Programming before the drill begins.";
            elements.practiceModalStateTag.textContent = "Choose category";
            elements.practiceModalStateTag.className = "inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-700 dark:bg-amber-500/10 dark:text-amber-300";
            elements.practiceModalActiveCategory.textContent = "None selected";
            elements.selectedCategoryName.textContent = "Choose a category first";
            elements.selectedCategoryDescription.textContent = learningContext
                ? `${learningContext.activityLabel} ${learningContext.levelLabel} is ready. Choose a category, then continue through the field builder.`
                : "Choose a category, then continue through the field builder.";
            elements.currentQuestionText.textContent = "Choose a category below for this Learning Lab practice.";
            elements.coachTipText.textContent = "Choose Job Interview, Scholarship Interview, College Admission, or IT / Programming before the drill begins.";
            updateSessionActionButtons();
            showMessage("info", messageParts.join(" "));
            openCategoryModal();

            return true;
        }

        const requestedCategory = practiceData.categories.find((item) => item.id === requestedCategoryId);

        if (!requestedCategory) {
            return false;
        }

        selectCategory(requestedCategory, { prefillNeed: note });

        const messageParts = [
            launchSource === "learning-lab"
                ? `${requestedCategory.name} was launched from Learning Lab.`
                : `${requestedCategory.name} was selected from the sidebar.`
        ];

        if (requestedModuleId) {
            messageParts.push(`Module: ${moduleLabels[requestedModuleId] || formatContextLabel(requestedModuleId)}.`);
        }

        if (requestedActivityId) {
            messageParts.push(`Activity: ${activityLabels[requestedActivityId] || formatContextLabel(requestedActivityId)}.`);
        }

        if (learningContext) {
            messageParts.push(`${learningContext.levelLabel} target: ${learningContext.targetScore.toFixed(1)} / 10.`);
            messageParts.push("Pass the target to unlock the next level; below target means try this level again.");
        }

        if (note.trim()) {
            messageParts.push(`Saved note: ${truncateText(note.trim().replace(/\s+/g, " "))}`);
        }

        showMessage("info", messageParts.join(" "));
        return true;
    }

    function applySavedSetupDefaults() {
        const savedSetup = readSessionSetup();
        const responsePreferenceLabel = {
            text: "Text First",
            voice: "Voice First",
            hybrid: "Hybrid"
        };

        elements.questionCountSelect.value = String(savedSetup.questionCount);
        elements.focusModeSelect.value = String(savedSetup.focusModeIndex);
        elements.pacingModeSelect.value = String(savedSetup.pacingModeIndex);

        state.questionCount = Number(elements.questionCountSelect.value);

        const selectedPacing = getSelectedPacingMode();
        const selectedFocus = getSelectedFocusMode();

        state.timerTarget = selectedPacing.seconds;
        elements.timerTargetValue.textContent = formatTime(selectedPacing.seconds);
        updateFocusModeDisplay();

        updateTipsPanel();

        if (!savedSetup.savedAt) {
            return savedSetup;
        }

        const preferredCategory = practiceData.categories.find((item) => item.id === savedSetup.preferredCategoryId);
        const messageParts = [
            `Saved defaults loaded: ${savedSetup.questionCount} question${savedSetup.questionCount === 1 ? "" : "s"}`,
            selectedFocus.label,
            `${selectedPacing.label} pace`
        ];
        let message = `${messageParts.join(", ")}.`;

        if (preferredCategory) {
            message += ` Preferred category: ${preferredCategory.name}.`;
        }

        if (responsePreferenceLabel[savedSetup.voiceMode]) {
            message += ` Response preference: ${responsePreferenceLabel[savedSetup.voiceMode]}.`;
        }

        const trimmedNotes = savedSetup.notes.trim().replace(/\s+/g, " ");
        if (trimmedNotes) {
            message += ` Note: ${truncateText(trimmedNotes)}`;
        }

        showMessage("info", message);
        return savedSetup;
    }

    elements.questionCountSelect.addEventListener("change", () => {
        state.questionCount = Number(elements.questionCountSelect.value);
        if (state.selectedCategory) {
            selectCategory(state.selectedCategory, { skipFieldModal: true });
        }
    });

    elements.focusModeSelect.addEventListener("change", () => {
        if (state.selectedCategory) {
            updateFocusModeDisplay();
            updateTipsPanel();
            showMessage("info", `Coach focus updated to ${getSelectedFocusMode().label}.`);
            return;
        }

        updateFocusModeDisplay();
        updateTipsPanel();
    });

    elements.pacingModeSelect.addEventListener("change", () => {
        const selectedPacing = getSelectedPacingMode();

        state.timerTarget = selectedPacing.seconds;
        elements.timerTargetValue.textContent = formatTime(state.timerTarget);
        updateTipsPanel();

        if (state.selectedCategory) {
            showMessage("info", `Pacing mode updated to ${selectedPacing.label}.`);
        }
    });

    elements.responseInput.addEventListener("input", () => {
        if (state.isApplyingTranscript) {
            return;
        }

        updateManualResponseState();
    });

    elements.openPracticeCategoryModalBtn?.addEventListener("click", () => {
        handleStartPracticeClick();
    });

    const handleLearningActionClick = (event) => {
        const trigger = event.target.closest("[data-learning-action]");

        if (!trigger) {
            return;
        }

        applyLearningAction(String(trigger.dataset.learningAction || ""));
    };

    elements.learningModulesList?.addEventListener("click", handleLearningActionClick);
    elements.learningActivitiesList?.addEventListener("click", handleLearningActionClick);

    elements.practiceFieldInput.addEventListener("input", () => {
        syncFieldBuilderModal();
        clearFieldChatStatus();
    });

    elements.practiceFieldNeedInput.addEventListener("input", () => {
        syncFieldBuilderModal();
        clearFieldChatStatus();
    });

    elements.practiceFieldProviderSelect?.addEventListener("change", (event) => {
        state.questionAgentSelectedProviderId = String(event.target.value || "auto");
        state.questionAgentResolvedProviderLabel = null;
        syncQuestionAgentPresentation();
        syncFieldBuilderModal();

        const provider = getQuestionAgentProviderById(state.questionAgentSelectedProviderId);
        showFieldChatStatus("info", `${getQuestionAgentProviderSummary(provider) || "Auto"} will be used for the field builder and the next question generation.`);
    });

    elements.practiceFieldGenerateBtn.addEventListener("click", () => {
        buildFieldPlanWithChatbot();
    });

    elements.practiceFieldResetBtn.addEventListener("click", () => {
        const category = state.pendingCategory || state.selectedCategory;

        if (!category) {
            return;
        }

        elements.practiceFieldInput.value = "";
        elements.practiceFieldNeedInput.value = "";
        state.fieldBuilderHistory = [{
            role: "assistant",
            text: buildFieldBuilderWelcomeMessage(category)
        }];
        setFieldBuilderStatusTag("Waiting for details", "neutral");
        clearFieldChatStatus();
        syncFieldBuilderModal(category);
    });

    elements.practiceFieldApplyBtn.addEventListener("click", () => {
        applyFieldPlanAndContinue();
    });

    elements.closePracticeFieldModalBtn.addEventListener("click", () => {
        closePracticeFieldModal();
    });

    elements.practiceFieldModalBackdrop.addEventListener("click", () => {
        closePracticeFieldModal({ returnFocus: false });
    });

    elements.openPracticeModalBtn.addEventListener("click", () => {
        openPracticeModal({ focusResponse: Boolean(state.selectedCategory && getActiveQuestionCount() > 0) });
    });

    elements.openPracticeQuestionAgentModalBtn?.addEventListener("click", () => {
        openQuestionAgentModal({ focusInput: true });
    });

    elements.editPracticeFieldBtn.addEventListener("click", () => {
        const category = state.pendingCategory || state.selectedCategory;

        if (!category) {
            return;
        }

        openPracticeFieldModal(category, {
            prefillNeed: getFieldPlanForCategory(category)?.userNeed || ""
        });
    });

    elements.closePracticeModalBtn?.addEventListener("click", () => {
        closePracticeModal();
    });

    elements.closePracticeQuestionAgentModalBtn?.addEventListener("click", () => {
        closeQuestionAgentModal();
    });

    elements.closePracticeCategoryModalBtn?.addEventListener("click", () => {
        closeCategoryModal();
    });

    elements.practiceCategoryModalBackdrop?.addEventListener("click", () => {
        closeCategoryModal({ returnFocus: false });
    });

    elements.practiceSessionModalBackdrop?.addEventListener("click", () => {
        closePracticeModal({ returnFocus: false });
    });

    elements.practiceQuestionAgentModalBackdrop?.addEventListener("click", () => {
        closeQuestionAgentModal({ returnFocus: false });
    });

    window.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
            if (isQuestionAgentModalOpen()) {
                closeQuestionAgentModal();
                return;
            }

            if (isFieldModalOpen()) {
                closePracticeFieldModal();
                return;
            }

            if (isCategoryModalOpen()) {
                closeCategoryModal();
                return;
            }
        }
    });

    elements.startVoiceBtn.addEventListener("click", startVoiceInput);
    elements.stopVoiceBtn.addEventListener("click", () => stopVoiceInput());
    elements.startPracticeBtn?.addEventListener("click", () => {
        beginPracticeRound({ speakQuestion: true, focusResponse: true });
    });

    elements.submitAnswerBtn.addEventListener("click", submitAnswer);
    elements.nextQuestionBtn.addEventListener("click", nextQuestion);
    elements.endSessionBtn.addEventListener("click", endSession);
    elements.printFeedbackBtn?.addEventListener("click", () => window.print());
    elements.practiceQuestionAgentGenerateBtn.addEventListener("click", () => {
        generateQuestionSet();
    });
    elements.practiceQuestionAgentRegenerateBtn.addEventListener("click", () => {
        generateQuestionSet();
    });
    elements.practiceQuestionAgentInput.addEventListener("keydown", (event) => {
        if (event.key === "Enter" && !event.shiftKey) {
            event.preventDefault();
            generateQuestionSet();
        }
    });
    elements.practiceQuestionAgentProviderSelect?.addEventListener("change", (event) => {
        state.questionAgentSelectedProviderId = String(event.target.value || "auto");
        state.questionAgentResolvedProviderLabel = null;
        syncQuestionAgentPresentation();
        syncFieldBuilderModal();

        const provider = getQuestionAgentProviderById(state.questionAgentSelectedProviderId);
        if (state.selectedCategory) {
            showMessage("info", `${getQuestionAgentProviderSummary(provider) || "Auto"} will be used for the next question generation and feedback review.`);
        }
    });

    populateSelects();
    renderCategories();
    const savedSetup = applySavedSetupDefaults();
    initSpeechRecognition();
    applyQuestionAgentProviderCatalog(state.questionAgentProviderCatalog, state.questionAgentSelectedProviderId);
    resetQuestionAgentConversation();
    updateTipsPanel();
    syncFieldBuilderModal();
    updateSessionActionButtons();
    const launchedFromCategoryQuery = launchCategoryFromQuery(savedSetup);
    if (!launchedFromCategoryQuery) {
        launchSavedSetup(savedSetup);
    }

    async function initInterviewer() {
        const faceCameraVideo = document.getElementById("faceCameraVideo");
        const startCameraBtn = document.getElementById("startCameraBtn");
        const stopCameraBtn = document.getElementById("stopCameraBtn");
        const askQuestionAloudBtn = document.getElementById("askQuestionAloudBtn");
        const interviewerStatusTag = document.getElementById("interviewerStatusTag");
        const cameraStateValue = document.getElementById("cameraStateValue");
        const faceStateValue = document.getElementById("faceStateValue");
        const avatarVoiceValue = document.getElementById("avatarVoiceValue");
        const avatarSpeechStatus = document.getElementById("avatarSpeechStatus");
        const avatarWordText = document.getElementById("avatarWordText");
        const avatarLineText = document.getElementById("avatarLineText");
        const avatarOrb = document.getElementById("avatarOrb");
        const voiceWaveBars = Array.from(document.querySelectorAll("#voiceWaveBars [data-wave-bar]"));
        const currentQuestionText = document.getElementById("currentQuestionText");
        const faceLandmarkerModelPath = "https://storage.googleapis.com/mediapipe-models/face_landmarker/face_landmarker/float16/1/face_landmarker.task";
        const interviewerAudio = {
            configured: Boolean(rawInterviewerAudioBootstrap?.configured),
            provider: String(rawInterviewerAudioBootstrap?.provider || "browser"),
            providerLabel: String(rawInterviewerAudioBootstrap?.providerLabel || "Hosted voice")
        };

        let FaceLandmarker = null;
        let FilesetResolver = null;
        let faceLandmarker = null;
        let cameraStream = null;
        let animationFrameId = null;
        let hostedAudio = null;
        let hostedAudioUrl = "";
        let hostedAudioAbortController = null;
        let lastVideoTime = -1;
        let lastSpokenQuestion = "";
        let activeSpeechCompletion = null;
        let lastVisualRenderAt = 0;
        let lastLearningRefreshAt = 0;
        const faceCenterHistory = [];
        const voiceWaveState = {
            active: false,
            intensity: 0.16,
            targetIntensity: 0.16,
            tokenEnergy: 0,
            tokenStartedAt: 0,
            pattern: buildWavePattern(null),
            animationFrameId: null,
            fallbackStartTimerId: null,
            fallbackWordTimerIds: [],
            boundaryDetected: false,
            cycleId: 0,
            currentWord: "",
            seeds: voiceWaveBars.map((_, index) => ({
                drift: 1.75 + ((index % 5) * 0.34),
                shimmer: 0.85 + (((index * 7) % 9) * 0.09),
                phase: (index + 1) * 0.73
            }))
        };

        function settleActiveSpeech(cycleId, status = "ended") {
            if (!activeSpeechCompletion || activeSpeechCompletion.cycleId !== cycleId) {
                return;
            }

            const { onSettled } = activeSpeechCompletion;
            activeSpeechCompletion = null;
            onSettled?.({ status });
        }

        function publishVisualSnapshot(snapshot, { force = false } = {}) {
            const now = performance.now();
            state.liveVisualSnapshot = snapshot;

            if (force || (now - lastVisualRenderAt) >= 250) {
                renderLivePresenceCoaching(snapshot);
                lastVisualRenderAt = now;
            }

            if (force || (now - lastLearningRefreshAt) >= 750) {
                renderLearningLab();
                lastLearningRefreshAt = now;
            }
        }

        function showWaitingVisualSnapshot(reason, {
            headline = "Camera coaching is waiting",
            tag = "Standby",
            tone = "neutral"
        } = {}, force = false) {
            const snapshot = createDefaultVisualSnapshot(reason);

            snapshot.headline = headline;
            snapshot.tag = tag;
            snapshot.tagTone = tone;
            publishVisualSnapshot(snapshot, { force });
        }

        function bindInterviewerControls() {
            interviewerControls.startCamera = startCamera;
            interviewerControls.playOpeningIntroduction = ({ onSettled } = {}) => {
                const introduction = buildInterviewerWelcomeLead();

                if (!introduction) {
                    onSettled?.({ status: "empty" });
                    return false;
                }

                return speakText(introduction, { onSettled });
            };
            interviewerControls.speakOpeningAcknowledgement = ({ text = "", onSettled = null } = {}) => {
                if (!text) {
                    onSettled?.({ status: "empty" });
                    return false;
                }

                return speakText(text, { onSettled });
            };
            interviewerControls.speakAssistantMessage = ({ text = "", onSettled = null } = {}) => {
                if (!text) {
                    onSettled?.({ status: "empty" });
                    return false;
                }

                return speakText(text, { onSettled });
            };
            interviewerControls.askCurrentQuestion = ({ onSettled = null } = {}) => {
                if (isOpeningIntroductionBlockingQuestion()) {
                    queueFirstQuestionAfterOpening({ focusResponse: true });
                    return false;
                }

                const question = getCurrentQuestion();
                const spokenPrompt = question
                    ? buildSpokenQuestionPrompt(question)
                    : "Please choose a track to begin the interview.";

                return speakText(spokenPrompt, { onSettled });
            };
            interviewerControls.stopCamera = stopCamera;
            interviewerControls.stopSpeaking = stopSpeaking;
        }

        bindInterviewerControls();

        try {
            ({ FaceLandmarker, FilesetResolver } = await import("https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@latest/vision_bundle.mjs"));
        } catch (error) {
            console.error(error);
            faceStateValue.textContent = "Unavailable";
            avatarLineText.textContent = "Camera and spoken questions are available, but live face detection could not be loaded.";
            showWaitingVisualSnapshot(
                "Selected non-verbal coaching could not be loaded. Spoken questions and typing still work.",
                {
                    headline: "Visual coaching unavailable",
                    tag: "Unavailable",
                    tone: "warning"
                },
                true
            );
        }

        function setStatusTag(text, mode = "neutral") {
            interviewerStatusTag.textContent = text;

            const styles = {
                neutral: "inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300",
                success: "inline-flex items-center rounded-full bg-success-100 px-3 py-1 text-xs font-medium text-success-700 dark:bg-success-500/10 dark:text-success-300",
                warning: "inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-700 dark:bg-amber-500/10 dark:text-amber-300",
                error: "inline-flex items-center rounded-full bg-error-100 px-3 py-1 text-xs font-medium text-error-700 dark:bg-error-500/10 dark:text-error-300"
            };

            interviewerStatusTag.className = styles[mode] || styles.neutral;
        }

        function clampWave(value, min, max) {
            return Math.min(max, Math.max(min, value));
        }

        function setWaveWordLabel(text) {
            voiceWaveState.currentWord = text;

            if (avatarWordText) {
                avatarWordText.textContent = text;
            }
        }

        function clearWaveTimers() {
            if (voiceWaveState.fallbackStartTimerId) {
                clearTimeout(voiceWaveState.fallbackStartTimerId);
                voiceWaveState.fallbackStartTimerId = null;
            }

            voiceWaveState.fallbackWordTimerIds.forEach((timerId) => clearTimeout(timerId));
            voiceWaveState.fallbackWordTimerIds = [];
        }

        function estimateSyllables(word) {
            const normalized = String(word || "")
                .toLowerCase()
                .replace(/[^a-z0-9']/g, "");

            if (!normalized) return 1;

            const vowelGroups = normalized.match(/[aeiouy]+/g) || [];
            const silentEnding = normalized.endsWith("e") && vowelGroups.length > 1 ? 1 : 0;

            return Math.max(1, vowelGroups.length - silentEnding);
        }

        function tokenizeSpeech(text) {
            const source = String(text || "");
            const regex = /[A-Za-z0-9]+(?:['-][A-Za-z0-9]+)*/g;
            const tokens = [];
            let match;

            while ((match = regex.exec(source)) !== null) {
                const word = match[0];
                const start = match.index;
                const end = start + word.length;
                const trailingPunctuation = source.slice(end).match(/^[,.;:!?]+/)?.[0] || "";
                const syllables = estimateSyllables(word);
                const punctuationBoost = /[!?]/.test(trailingPunctuation) ? 0.16 : /[,.;:]/.test(trailingPunctuation) ? 0.08 : 0;
                const energy = clampWave(
                    0.34 + Math.min(0.18, word.length * 0.012) + Math.min(0.24, syllables * 0.05) + punctuationBoost,
                    0.28,
                    0.92
                );

                tokens.push({
                    text: word,
                    displayText: trailingPunctuation ? `${word}${trailingPunctuation}` : word,
                    start,
                    end,
                    syllables,
                    trailingPunctuation,
                    energy
                });
            }

            return tokens;
        }

        function buildWavePattern(token) {
            if (!voiceWaveBars.length) return [];

            const midpoint = (voiceWaveBars.length - 1) / 2;

            if (!token) {
                return voiceWaveBars.map((_, index) => clampWave(
                    1 - (Math.abs(index - midpoint) / Math.max(midpoint + 0.5, 1)),
                    0.18,
                    1
                ));
            }

            const normalized = token.text.toLowerCase();
            const syllables = estimateSyllables(normalized);
            const asciiSum = normalized.split("").reduce((sum, char) => sum + char.charCodeAt(0), 0);
            const center = midpoint + (((asciiSum % 7) - 3) * 0.28);
            const spread = 1.6 + (syllables * 0.5) + Math.min(1.8, normalized.length * 0.08);

            return voiceWaveBars.map((_, index) => {
                const distance = Math.abs(index - center);
                const bell = clampWave(1 - (distance / spread), 0, 1);
                const ripple = clampWave(
                    1 - Math.abs(distance - (1.3 + (syllables * 0.35))) / (spread + 1.2),
                    0,
                    1
                ) * 0.25;

                return clampWave(0.18 + (bell * 0.72) + ripple, 0.18, 1);
            });
        }

        function animateVoiceWave(timestamp) {
            if (!voiceWaveBars.length) return;

            const midpoint = (voiceWaveBars.length - 1) / 2;
            const seconds = timestamp / 1000;
            const tokenElapsed = voiceWaveState.tokenStartedAt ? (timestamp - voiceWaveState.tokenStartedAt) / 1000 : 99;
            const tokenDecay = voiceWaveState.active ? Math.max(0, 1 - (tokenElapsed * 1.3)) : 0;

            voiceWaveState.intensity += (voiceWaveState.targetIntensity - voiceWaveState.intensity) * (voiceWaveState.active ? 0.2 : 0.08);

            voiceWaveBars.forEach((bar, index) => {
                const seed = voiceWaveState.seeds[index];
                const mirror = 1 - (Math.abs(index - midpoint) / Math.max(midpoint, 1));
                const pattern = voiceWaveState.pattern[index] || mirror;
                const motionA = (Math.sin((seconds * (2.6 + seed.drift)) + seed.phase) + 1) / 2;
                const motionB = (Math.sin((seconds * (6.4 + seed.shimmer)) + (seed.phase * 1.8)) + 1) / 2;
                const chatter = (motionA * 0.45) + (motionB * 0.55);
                const speechLift = (
                    (voiceWaveState.tokenEnergy * tokenDecay) +
                    (voiceWaveState.intensity * (voiceWaveState.active ? 0.8 : 0.22))
                ) * (0.25 + (pattern * 0.95)) * (0.38 + (chatter * 0.85));
                const idleLift = 0.12 + (mirror * 0.08) + (motionA * 0.06);
                const targetScale = clampWave(idleLift + speechLift, 0.12, 1);
                const currentScale = Number(bar.dataset.scale || 0.16);
                const nextScale = currentScale + ((targetScale - currentScale) * 0.34);

                bar.dataset.scale = nextScale.toFixed(3);
                bar.style.setProperty("--voice-wave-scale", nextScale.toFixed(3));
                bar.style.opacity = String(clampWave(0.35 + (nextScale * 0.7), 0.35, 1));
            });

            if (avatarOrb) {
                avatarOrb.style.setProperty(
                    "--voice-wave-glow",
                    String(clampWave(0.24 + (voiceWaveState.intensity * 0.95), 0.24, 1))
                );
            }

            voiceWaveState.animationFrameId = requestAnimationFrame(animateVoiceWave);
        }

        function findTokenAtCharIndex(tokens, charIndex) {
            if (!tokens.length) return null;

            return (
                tokens.find((token) => charIndex >= token.start && charIndex < token.end) ||
                tokens.find((token) => token.start >= charIndex) ||
                tokens[tokens.length - 1]
            );
        }

        function estimateTokenDuration(token) {
            const punctuationPause = /[!?]/.test(token.trailingPunctuation)
                ? 130
                : /[,.;:]/.test(token.trailingPunctuation)
                    ? 90
                    : 0;

            return 130 + (token.syllables * 70) + (token.text.length * 9) + punctuationPause;
        }

        function applyWaveToken(token) {
            if (!token) return;

            voiceWaveState.active = true;
            voiceWaveState.tokenEnergy = token.energy;
            voiceWaveState.targetIntensity = clampWave(0.32 + (token.energy * 0.75), 0.32, 0.95);
            voiceWaveState.tokenStartedAt = performance.now();
            voiceWaveState.pattern = buildWavePattern(token);
            setWaveWordLabel(`Speaking: ${token.displayText}`);
        }

        function settleVoiceWave(label = "Waiting for the next spoken question") {
            voiceWaveState.active = false;
            voiceWaveState.tokenEnergy = 0;
            voiceWaveState.targetIntensity = 0.16;
            voiceWaveState.pattern = buildWavePattern(null);
            setWaveWordLabel(label);
        }

        function scheduleFallbackWave(tokens, cycleId) {
            clearWaveTimers();

            if (!tokens.length) return;

            voiceWaveState.fallbackStartTimerId = setTimeout(() => {
                if (voiceWaveState.cycleId !== cycleId || voiceWaveState.boundaryDetected) return;

                let elapsed = estimateTokenDuration(tokens[0]);

                tokens.slice(1).forEach((token) => {
                    const timerId = setTimeout(() => {
                        if (voiceWaveState.cycleId !== cycleId || voiceWaveState.boundaryDetected) return;
                        applyWaveToken(token);
                    }, elapsed);

                    voiceWaveState.fallbackWordTimerIds.push(timerId);
                    elapsed += estimateTokenDuration(token);
                });
            }, 220);
        }

        if (voiceWaveBars.length) {
            voiceWaveState.animationFrameId = requestAnimationFrame(animateVoiceWave);
        }

        function buildLiveAlgorithm(name, score, high, medium, low) {
            return {
                name,
                score: roundScore(score),
                detail: summarizeScoreBand(score, high, medium, low),
                status: "Live",
                available: true
            };
        }

        function getBlendshapeValue(blendshapes, name) {
            const categories = Array.isArray(blendshapes) ? blendshapes : [];
            const match = categories.find((item) => String(item?.categoryName || "") === name);

            return Number(match?.score) || 0;
        }

        function getAveragePoint(landmarks, indexes) {
            const points = indexes
                .map((index) => landmarks[index])
                .filter((point) => point && Number.isFinite(point.x) && Number.isFinite(point.y));

            if (!points.length) {
                return { x: 0.5, y: 0.5 };
            }

            return {
                x: points.reduce((sum, point) => sum + point.x, 0) / points.length,
                y: points.reduce((sum, point) => sum + point.y, 0) / points.length
            };
        }

        function getBoundingBox(landmarks) {
            return landmarks.reduce((box, point) => ({
                minX: Math.min(box.minX, point.x),
                maxX: Math.max(box.maxX, point.x),
                minY: Math.min(box.minY, point.y),
                maxY: Math.max(box.maxY, point.y)
            }), {
                minX: 1,
                maxX: 0,
                minY: 1,
                maxY: 0
            });
        }

        function buildCameraVisualSnapshot(landmarks, blendshapes) {
            const boundingBox = getBoundingBox(landmarks);
            const centerX = (boundingBox.minX + boundingBox.maxX) / 2;
            const centerY = (boundingBox.minY + boundingBox.maxY) / 2;
            const faceArea = Math.max(0, (boundingBox.maxX - boundingBox.minX) * (boundingBox.maxY - boundingBox.minY));
            const distanceFromCenter = Math.hypot(centerX - 0.5, centerY - 0.45);
            const leftEye = getAveragePoint(landmarks, [33, 133, 159, 145]);
            const rightEye = getAveragePoint(landmarks, [263, 362, 386, 374]);
            const headTilt = Math.abs(leftEye.y - rightEye.y);

            faceCenterHistory.push({ x: centerX, y: centerY });
            if (faceCenterHistory.length > 12) {
                faceCenterHistory.shift();
            }

            const motionDeltas = faceCenterHistory.slice(1).map((point, index) => {
                const previous = faceCenterHistory[index];
                return Math.hypot(point.x - previous.x, point.y - previous.y);
            });
            const averageMotion = motionDeltas.length
                ? motionDeltas.reduce((sum, value) => sum + value, 0) / motionDeltas.length
                : 0;

            const frameCenteringScore = roundScore(10 - Math.min(8, distanceFromCenter * 18));
            const headBalanceScore = roundScore(10 - Math.min(8, headTilt * 160));
            const movementStabilityScore = roundScore(10 - Math.min(8, averageMotion * 180));
            const presenceFramingScore = roundScore(10 - Math.min(8, Math.abs(faceArea - 0.16) * 55));

            const smileWarmth = (getBlendshapeValue(blendshapes, "mouthSmileLeft") + getBlendshapeValue(blendshapes, "mouthSmileRight")) / 2;
            const eyeBlink = (getBlendshapeValue(blendshapes, "eyeBlinkLeft") + getBlendshapeValue(blendshapes, "eyeBlinkRight")) / 2;
            const eyeSquint = (getBlendshapeValue(blendshapes, "eyeSquintLeft") + getBlendshapeValue(blendshapes, "eyeSquintRight")) / 2;
            const eyeWide = (getBlendshapeValue(blendshapes, "eyeWideLeft") + getBlendshapeValue(blendshapes, "eyeWideRight")) / 2;
            const jawOpen = getBlendshapeValue(blendshapes, "jawOpen");
            const mouthPucker = getBlendshapeValue(blendshapes, "mouthPucker");
            const mouthFrown = (getBlendshapeValue(blendshapes, "mouthFrownLeft") + getBlendshapeValue(blendshapes, "mouthFrownRight")) / 2;
            const browDown = (getBlendshapeValue(blendshapes, "browDownLeft") + getBlendshapeValue(blendshapes, "browDownRight")) / 2;
            const browInnerUp = getBlendshapeValue(blendshapes, "browInnerUp");

            const smileWarmthScore = roundScore(10 - Math.min(7, Math.abs(smileWarmth - 0.3) * 20));
            const eyeEngagementScore = roundScore(Math.max(3, Math.min(10, 8 - ((eyeBlink + eyeSquint) * 5) + (eyeWide * 2.5))));
            const jawRelaxationScore = roundScore(Math.max(3, Math.min(10, 10 - (Math.max(0, jawOpen - 0.35) * 10) - (mouthPucker * 5) - (mouthFrown * 4))));
            const browCalmnessScore = roundScore(Math.max(3, Math.min(10, 10 - (browDown * 6) - (Math.max(0, browInnerUp - 0.35) * 4))));
            const eyeContactScore = averageScore([frameCenteringScore, eyeEngagementScore]);
            const postureScore = averageScore([headBalanceScore, presenceFramingScore]);
            const headMovementScore = movementStabilityScore;
            const facialComposureScore = averageScore([smileWarmthScore, jawRelaxationScore, browCalmnessScore]);

            const bodyAlgorithms = [
                buildLiveAlgorithm(
                    "Eye Contact Orientation",
                    eyeContactScore,
                    "Your face stays centered and your gaze reads as attentive to the interviewer.",
                    "Your gaze is mostly usable, but a steadier camera-facing orientation would help.",
                    "Recenter your face and reconnect your gaze toward the camera before the next answer."
                ),
                buildLiveAlgorithm(
                    "Posture Stability",
                    postureScore,
                    "Your head position and framing look upright and interview-ready.",
                    "Your posture is mostly workable, but a straighter reset would help.",
                    "Sit taller, level your head, and rebalance your framing for stronger posture."
                ),
                buildLiveAlgorithm(
                    "Head Movement Control",
                    headMovementScore,
                    "Your movement is calm and steady, which helps the answer feel controlled.",
                    "You move a little while answering. Try settling before the next response.",
                    "Reduce extra movement so the camera reads you as more confident and settled."
                ),
                buildLiveAlgorithm(
                    "Camera Framing Support",
                    presenceFramingScore,
                    "Your distance from the camera looks comfortable and interview-ready.",
                    "Your framing is usable, but moving slightly closer or farther would help.",
                    "Adjust your distance from the camera so your face fills the frame more naturally."
                )
            ];

            const facialAlgorithms = [
                buildLiveAlgorithm(
                    "Facial Composure",
                    facialComposureScore,
                    "Your expression looks calm, steady, and professional on camera.",
                    "Your expression is mostly usable, but relaxing the face a little more would help.",
                    "Release visible facial tension so you look calmer and more composed."
                ),
                buildLiveAlgorithm(
                    "Eye Engagement",
                    eyeEngagementScore,
                    "Your eyes look open and engaged with the interviewer.",
                    "Your eyes are mostly engaged, but a steadier gaze would help.",
                    "Lift your gaze toward the camera and reduce eye tension for stronger engagement."
                ),
                buildLiveAlgorithm(
                    "Jaw Relaxation",
                    jawRelaxationScore,
                    "Your jaw looks relaxed, which helps your delivery feel calm.",
                    "There is a little jaw tension. Slow your breathing before the next answer.",
                    "Release jaw tension so your face looks calmer and your words sound more natural."
                ),
                buildLiveAlgorithm(
                    "Brow Calmness",
                    browCalmnessScore,
                    "Your brows look calm and composed on camera.",
                    "There is slight brow tension. Soften your expression between sentences.",
                    "Relax your brow area to reduce stress signals on camera."
                )
            ];

            const bodyAverage = averageScore(bodyAlgorithms.map((algorithm) => algorithm.score));
            const facialAverage = averageScore(facialAlgorithms.map((algorithm) => algorithm.score));
            const combinedAverage = averageScore([bodyAverage, facialAverage]);
            const lowestAlgorithm = [...bodyAlgorithms, ...facialAlgorithms]
                .slice()
                .sort((left, right) => clampScore(left.score) - clampScore(right.score))[0];

            return {
                headline: summarizeScoreBand(
                    combinedAverage,
                    "Presence looks confident and interview-ready",
                    "Presence is steady, with a few adjustments available",
                    "Presence coaching suggests a quick reset"
                ),
                tip: lowestAlgorithm
                    ? `${lowestAlgorithm.name}: ${lowestAlgorithm.detail}`
                    : "Live coaching is running.",
                tag: `${combinedAverage.toFixed(1)} / 10`,
                tagTone: getScoreTone(combinedAverage),
                criteria: {
                    eyeContactScore,
                    postureScore,
                    headMovementScore,
                    facialComposureScore,
                    eyeContactLabel: summarizeScoreBand(
                        eyeContactScore,
                        "Eye contact orientation looks engaged and camera-aware.",
                        "Eye contact is mostly usable, but a steadier camera-facing gaze would help.",
                        "Reconnect your gaze with the camera so the answer feels more engaged."
                    ),
                    postureLabel: summarizeScoreBand(
                        postureScore,
                        "Posture looks upright, centered, and interview-ready.",
                        "Posture is usable, but a quick reset would make you look more grounded.",
                        "Straighten your posture and rebalance your framing before the next answer."
                    ),
                    headMovementLabel: summarizeScoreBand(
                        headMovementScore,
                        "Head movement is calm and controlled.",
                        "Movement is manageable, but settling more would help.",
                        "Reduce head movement so the camera reads you as steadier and more confident."
                    ),
                    facialComposureLabel: summarizeScoreBand(
                        facialComposureScore,
                        "Facial composure looks calm and professional.",
                        "Facial composure is acceptable, but softening tension would help.",
                        "Relax your face and jaw before the next answer to look more composed."
                    )
                },
                bodyLanguage: {
                    label: "Body Language",
                    average: bodyAverage,
                    summary: summarizeScoreBand(
                        bodyAverage,
                        "Your upper-body camera presence looks grounded and centered.",
                        "Your body language is usable, but a quick posture reset would sharpen it.",
                        "Recenter posture, reduce movement, and re-balance your framing before answering again."
                    ),
                    status: "Live",
                    available: true,
                    algorithms: bodyAlgorithms
                },
                facialExpressions: {
                    label: "Facial Expressions",
                    average: facialAverage,
                    summary: summarizeScoreBand(
                        facialAverage,
                        "Your facial expressions look warm, calm, and professional.",
                        "Your expression is mostly neutral; a little more warmth would help.",
                        "Relax your facial tension and reconnect with the camera before the next answer."
                    ),
                    status: "Live",
                    available: true,
                    algorithms: facialAlgorithms
                }
            };
        }

        function setAvatarSpeaking(isSpeaking) {
            if (isSpeaking) {
                if (avatarOrb) {
                    avatarOrb.dataset.speaking = "true";
                }

                avatarVoiceValue.textContent = "Live";
                avatarSpeechStatus.textContent = "Voice wave active";
            } else {
                if (avatarOrb) {
                    avatarOrb.dataset.speaking = "false";
                }

                avatarVoiceValue.textContent = "Ready";
                avatarSpeechStatus.textContent = "Waveform ready";
                settleVoiceWave();
            }
        }

        function clearHostedAudioPlayback() {
            if (hostedAudioAbortController) {
                hostedAudioAbortController.abort();
                hostedAudioAbortController = null;
            }

            if (hostedAudio) {
                hostedAudio.pause();
                hostedAudio.src = "";
                hostedAudio = null;
            }

            if (hostedAudioUrl) {
                URL.revokeObjectURL(hostedAudioUrl);
                hostedAudioUrl = "";
            }
        }

        function clearSpeechPlaybackState() {
            clearWaveTimers();
            clearHostedAudioPlayback();

            if ("speechSynthesis" in window) {
                window.speechSynthesis.cancel();
            }
        }

        function stopSpeaking() {
            const stoppedCycleId = voiceWaveState.cycleId;

            voiceWaveState.cycleId += 1;
            clearSpeechPlaybackState();
            setAvatarSpeaking(false);
            settleActiveSpeech(stoppedCycleId, "stopped");
        }

        function getCurrentQuestion() {
            const text = currentQuestionText?.textContent?.trim() || "";
            const blockedPhrases = [
                "Choose a category",
                "Choose a track from the sidebar",
                "AI question generator is preparing",
                "AI question generator is temporarily unavailable"
            ];

            if (!text || blockedPhrases.some((phrase) => text.includes(phrase))) return "";
            return text;
        }

        function getPreferredVoice() {
            const voices = window.speechSynthesis.getVoices();
            return (
                voices.find((voice) => /female|samantha|zira|google us english|english/i.test(voice.name)) ||
                voices[0] ||
                null
            );
        }

        function startWavePlayback(text, speechCycleId, tokens, {
            voiceValue = null,
            speechStatus = null,
            wordLabel = "Listening to cadence..."
        } = {}) {
            if (speechCycleId !== voiceWaveState.cycleId) return;

            setAvatarSpeaking(true);
            avatarLineText.textContent = text;

            if (voiceValue) {
                avatarVoiceValue.textContent = voiceValue;
            }

            if (speechStatus) {
                avatarSpeechStatus.textContent = speechStatus;
            }

            setWaveWordLabel(wordLabel);

            if (tokens[0]) {
                applyWaveToken(tokens[0]);
            }

            scheduleFallbackWave(tokens, speechCycleId);
        }

        function speakWithBrowserSynthesis(text, speechCycleId, tokens) {
            if (!("speechSynthesis" in window)) {
                settleVoiceWave("Speech playback unavailable");
                avatarVoiceValue.textContent = "Unsupported";
                avatarSpeechStatus.textContent = "Speech unavailable";
                avatarLineText.textContent = "Speech synthesis is not supported in this browser.";
                return false;
            }

            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = document.documentElement.lang || "en-US";
            utterance.rate = state.interviewerSpeechRate;
            utterance.pitch = 1;

            const preferredVoice = getPreferredVoice();
            if (preferredVoice) {
                utterance.voice = preferredVoice;
            }

            utterance.onstart = () => {
                if (speechCycleId !== voiceWaveState.cycleId) return;

                startWavePlayback(text, speechCycleId, tokens, {
                    voiceValue: "Browser",
                    speechStatus: "Browser voice live"
                });
            };

            utterance.onboundary = (event) => {
                if (speechCycleId !== voiceWaveState.cycleId) return;

                if (event.name && event.name !== "word" && event.name !== "sentence") {
                    return;
                }

                voiceWaveState.boundaryDetected = true;
                clearWaveTimers();

                const token = findTokenAtCharIndex(tokens, event.charIndex);
                applyWaveToken(token);
            };

            utterance.onend = () => {
                if (speechCycleId !== voiceWaveState.cycleId) return;

                clearWaveTimers();
                setAvatarSpeaking(false);
                settleVoiceWave("Prompt finished. Ready for the next question");
                settleActiveSpeech(speechCycleId, "ended");
            };

            utterance.onerror = () => {
                if (speechCycleId !== voiceWaveState.cycleId) return;

                clearWaveTimers();
                setAvatarSpeaking(false);
                avatarVoiceValue.textContent = "Error";
                avatarSpeechStatus.textContent = "Playback error";
                setWaveWordLabel("Speech playback failed");
                settleActiveSpeech(speechCycleId, "error");
            };

            window.speechSynthesis.speak(utterance);
            return true;
        }

        async function speakWithCartesia(text, speechCycleId, tokens) {
            if (!interviewerAudio.configured) {
                return false;
            }

            const abortController = new AbortController();
            hostedAudioAbortController = abortController;
            avatarVoiceValue.textContent = "Cartesia";
            avatarSpeechStatus.textContent = "Generating voice";
            avatarLineText.textContent = text;
            setWaveWordLabel("Generating AI voice...");

            try {
                const { blob, contentType } = await requestWorkspaceBlob("interviewerSpeak", {
                    method: "POST",
                    body: { text },
                    signal: abortController.signal
                });

                if (abortController.signal.aborted || speechCycleId !== voiceWaveState.cycleId) {
                    return true;
                }

                const playableBlob = contentType && blob.type !== contentType
                    ? new Blob([blob], { type: contentType })
                    : blob;
                const objectUrl = URL.createObjectURL(playableBlob);
                const audio = new Audio(objectUrl);
                const releaseHostedAudio = () => {
                    if (hostedAudio === audio) {
                        hostedAudio = null;
                    }

                    if (hostedAudioUrl === objectUrl) {
                        URL.revokeObjectURL(objectUrl);
                        hostedAudioUrl = "";
                    }

                    if (hostedAudioAbortController === abortController) {
                        hostedAudioAbortController = null;
                    }
                };

                hostedAudio = audio;
                hostedAudioUrl = objectUrl;
                audio.preload = "auto";

                audio.onplay = () => {
                    if (speechCycleId !== voiceWaveState.cycleId) {
                        releaseHostedAudio();
                        return;
                    }

                    startWavePlayback(text, speechCycleId, tokens, {
                        voiceValue: "Cartesia",
                        speechStatus: `${interviewerAudio.providerLabel} live`
                    });
                };

                audio.onended = () => {
                    if (speechCycleId !== voiceWaveState.cycleId) {
                        releaseHostedAudio();
                        return;
                    }

                    clearWaveTimers();
                    setAvatarSpeaking(false);
                    settleVoiceWave("Prompt finished. Ready for the next question");
                    settleActiveSpeech(speechCycleId, "ended");
                    releaseHostedAudio();
                };

                audio.onerror = () => {
                    if (speechCycleId !== voiceWaveState.cycleId) {
                        releaseHostedAudio();
                        return;
                    }

                    clearWaveTimers();
                    setAvatarSpeaking(false);
                    avatarVoiceValue.textContent = "Error";
                    avatarSpeechStatus.textContent = "Playback error";
                    setWaveWordLabel("Speech playback failed");
                    settleActiveSpeech(speechCycleId, "error");
                    releaseHostedAudio();
                };

                await audio.play();

                return true;
            } catch (error) {
                if (abortController.signal.aborted || error?.name === "AbortError") {
                    return true;
                }

                clearHostedAudioPlayback();
                setAvatarSpeaking(false);
                console.error("Hosted interviewer voice failed", error);

                return false;
            }
        }

        async function speakText(text, { onSettled = null } = {}) {
            if (!text) {
                settleVoiceWave("Waiting for an interview question");
                avatarLineText.textContent = "No interview question is active yet. Choose a track first.";
                onSettled?.({ status: "empty" });
                return false;
            }

            const previousSpeechCycleId = voiceWaveState.cycleId;
            const speechCycleId = previousSpeechCycleId + 1;
            const tokens = tokenizeSpeech(text);

            settleActiveSpeech(previousSpeechCycleId, "superseded");
            clearSpeechPlaybackState();
            voiceWaveState.cycleId = speechCycleId;
            voiceWaveState.boundaryDetected = false;
            activeSpeechCompletion = typeof onSettled === "function"
                ? { cycleId: speechCycleId, onSettled }
                : null;

            const hostedPlaybackStarted = await speakWithCartesia(text, speechCycleId, tokens);

            if (speechCycleId !== voiceWaveState.cycleId || hostedPlaybackStarted) {
                return hostedPlaybackStarted;
            }

            const browserPlaybackStarted = speakWithBrowserSynthesis(text, speechCycleId, tokens);

            if (!browserPlaybackStarted) {
                settleActiveSpeech(speechCycleId, "unavailable");
            }

            return browserPlaybackStarted;
        }

        async function initFaceLandmarker() {
            if (faceLandmarker) return faceLandmarker;

            if (!FaceLandmarker || !FilesetResolver) {
                throw new Error("Face Landmarker dependencies are unavailable.");
            }

            const vision = await FilesetResolver.forVisionTasks(
                "https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@latest/wasm"
            );

            faceLandmarker = await FaceLandmarker.createFromOptions(vision, {
                baseOptions: {
                    modelAssetPath: faceLandmarkerModelPath
                },
                runningMode: "VIDEO",
                numFaces: 1,
                minFaceDetectionConfidence: 0.5,
                minFacePresenceConfidence: 0.5,
                minTrackingConfidence: 0.5,
                outputFaceBlendshapes: true
            });

            return faceLandmarker;
        }

        function detectFaceLoop() {
            if (!faceLandmarker || !faceCameraVideo || !cameraStream) return;

            if (faceCameraVideo.readyState >= 2 && faceCameraVideo.currentTime !== lastVideoTime) {
                try {
                    const result = faceLandmarker.detectForVideo(faceCameraVideo, performance.now());
                    const facesDetected = result.faceLandmarks?.length || 0;

                    if (facesDetected > 0) {
                        const liveSnapshot = buildCameraVisualSnapshot(
                            result.faceLandmarks[0],
                            result.faceBlendshapes?.[0]?.categories || []
                        );

                        faceStateValue.textContent = "Detected";
                        setStatusTag("Live coaching active", "success");
                        publishVisualSnapshot(liveSnapshot);
                    } else {
                        faceStateValue.textContent = "Not detected";
                        setStatusTag("Recenter face", "warning");
                        showWaitingVisualSnapshot(
                            "No face is centered yet. Sit upright, move slightly closer, and keep your face visible on camera.",
                            {
                                headline: "Face not detected",
                                tag: "Recenter",
                                tone: "warning"
                            }
                        );
                    }
                } catch (error) {
                    console.error(error);
                    faceStateValue.textContent = "Unavailable";
                    setStatusTag("Visual check error", "warning");
                    showWaitingVisualSnapshot(
                        "The live visual model ran into a temporary issue. You can keep practicing while the camera stays on.",
                        {
                            headline: "Visual coaching paused",
                            tag: "Paused",
                            tone: "warning"
                        },
                        true
                    );
                }

                lastVideoTime = faceCameraVideo.currentTime;
            }

            animationFrameId = requestAnimationFrame(detectFaceLoop);
        }

        async function startCamera() {
            try {
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    setStatusTag("Camera unsupported", "error");
                    cameraStateValue.textContent = "Unsupported";
                    avatarLineText.textContent = "Camera access is not supported in this browser.";
                    return;
                }

                if (cameraStream) {
                    setStatusTag("Camera live", "success");
                    return;
                }

                // Check if we're on a mobile device
                const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
                
                cameraStream = await navigator.mediaDevices.getUserMedia({
                    video: { 
                        facingMode: "user",
                        width: { ideal: isMobile ? 640 : 1280 },
                        height: { ideal: isMobile ? 480 : 720 }
                    },
                    audio: false
                });

                faceCameraVideo.srcObject = cameraStream;
                await faceCameraVideo.play();

                cameraStateValue.textContent = "Live";
                setStatusTag("Camera live", "success");
                avatarLineText.textContent = "Camera is live. I will monitor selected non-verbal cues while you practice.";
                showWaitingVisualSnapshot(
                    "Camera is live. Center your face in the frame to unlock selected non-verbal coaching.",
                    {
                        headline: "Camera ready for coaching",
                        tag: "Checking",
                        tone: "neutral"
                    },
                    true
                );

                try {
                    await initFaceLandmarker();
                    faceStateValue.textContent = "Checking...";

                    if (animationFrameId) cancelAnimationFrame(animationFrameId);
                    detectFaceLoop();
                } catch (error) {
                    console.error(error);
                    faceStateValue.textContent = "Unavailable";
                    setStatusTag("Camera live, no face check", "warning");
                    avatarLineText.textContent = "Camera is live, but visual coaching is unavailable. Voice practice still works.";
                    showWaitingVisualSnapshot(
                        "The camera is running, but the visual coaching model is unavailable right now.",
                        {
                            headline: "Visual coaching unavailable",
                            tag: "Unavailable",
                            tone: "warning"
                        },
                        true
                    );
                }
            } catch (error) {
                console.error(error);
                let errorMessage = "Camera access failed.";
                let snapshotMessage = "Camera access was blocked.";
                
                if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
                    errorMessage = "Camera permission denied. Please allow camera access and try again.";
                    snapshotMessage = "Camera permission was denied. Allow camera permissions in your browser settings to unlock selected non-verbal coaching.";
                } else if (error.name === 'NotFoundError' || error.name === 'DevicesNotFoundError') {
                    errorMessage = "No camera found. Please connect a camera and try again.";
                    snapshotMessage = "No camera device was found. Connect a camera to unlock selected non-verbal coaching.";
                } else if (error.name === 'NotReadableError' || error.name === 'TrackStartError') {
                    errorMessage = "Camera is already in use by another application.";
                    snapshotMessage = "Camera is in use by another app. Close other camera-using apps to unlock selected non-verbal coaching.";
                } else {
                    errorMessage = "Camera access failed. Use localhost or HTTPS. For mobile devices, access via your computer's LAN IP address.";
                    snapshotMessage = "Camera access failed. Ensure you're using localhost or HTTPS, and for mobile devices, access via LAN IP.";
                }
                
                setStatusTag("Camera blocked", "error");
                cameraStateValue.textContent = "Blocked";
                faceStateValue.textContent = "Unavailable";
                avatarLineText.textContent = errorMessage;
                showWaitingVisualSnapshot(
                    snapshotMessage,
                    {
                        headline: "Camera blocked",
                        tag: "Blocked",
                        tone: "error"
                    },
                    true
                );
            }
        }

        function stopCamera() {
            if (animationFrameId) {
                cancelAnimationFrame(animationFrameId);
                animationFrameId = null;
            }

            if (cameraStream) {
                cameraStream.getTracks().forEach((track) => track.stop());
                cameraStream = null;
            }

            faceCameraVideo.srcObject = null;
            cameraStateValue.textContent = "Off";
            faceStateValue.textContent = "Not detected";
            setStatusTag("Camera Off", "neutral");
            faceCenterHistory.length = 0;
            showWaitingVisualSnapshot(
                "Turn on the camera to unlock selected non-verbal coaching.",
                {
                    headline: "Camera coaching is waiting",
                    tag: "Standby",
                    tone: "neutral"
                },
                true
            );
        }

        bindInterviewerControls();

        startCameraBtn?.addEventListener("click", startCamera);
        stopCameraBtn?.addEventListener("click", stopCamera);
        askQuestionAloudBtn?.addEventListener("click", () => {
            askQuestionLikeConversation();
        });

        if ("speechSynthesis" in window) {
            window.speechSynthesis.onvoiceschanged = () => {
                getPreferredVoice();
            };
        }

        if (currentQuestionText) {
            const observer = new MutationObserver(() => {
                const question = getCurrentQuestion();
                if (!question) {
                    lastSpokenQuestion = "";
                    settleVoiceWave("Waiting for spoken question");
                    return;
                }

                if (question === lastSpokenQuestion) return;

                lastSpokenQuestion = question;
                stopSpeaking();
                avatarLineText.textContent = question;
                settleVoiceWave("Interviewer will ask automatically");
            });

            observer.observe(currentQuestionText, {
                childList: true,
                subtree: true,
                characterData: true
            });
        }

        window.addEventListener("beforeunload", () => {
            stopCamera();
            stopSpeaking();

            if (voiceWaveState.animationFrameId) {
                cancelAnimationFrame(voiceWaveState.animationFrameId);
                voiceWaveState.animationFrameId = null;
            }
        });
    }

    initInterviewer();

    window.addEventListener("beforeunload", () => {
        cancelPendingQuestionSpeech();
        stopVoiceInput({ commitTranscript: false });
    });
}
