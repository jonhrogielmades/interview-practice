import { practiceData, readSessionSetup } from "./practice-config";
import { appendPracticeSession } from "./practice-storage";
import { buildFeedbackSummary, normalizeFeedbackSummary } from "./feedback-utils";
import { requestWorkspace } from "./workspace-api";

export function initPractice() {
    const practiceRoot = document.getElementById("practiceApp");

    if (!practiceRoot) {
        return;
    }

    const rawChatbotBootstrap = window.__INTERVIEW_CHATBOT__ || {};
    const state = {
        selectedCategory: null,
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
        questionAgentRequestId: 0
    };

    const elements = {
        questionCountSelect: document.getElementById("questionCountSelect"),
        focusModeSelect: document.getElementById("focusModeSelect"),
        pacingModeSelect: document.getElementById("pacingModeSelect"),
        practiceCategoryList: document.getElementById("practiceCategoryList"),
        practiceSessionModal: document.getElementById("practiceSessionModal"),
        practiceSessionModalBackdrop: document.getElementById("practiceSessionModalBackdrop"),
        openPracticeModalBtn: document.getElementById("openPracticeModalBtn"),
        closePracticeModalBtn: document.getElementById("closePracticeModalBtn"),
        practiceModalCategoryName: document.getElementById("practiceModalCategoryName"),
        practiceModalSummaryText: document.getElementById("practiceModalSummaryText"),
        practiceModalStateTag: document.getElementById("practiceModalStateTag"),
        practiceModalActiveCategory: document.getElementById("practiceModalActiveCategory"),
        practiceModalAnsweredValue: document.getElementById("practiceModalAnsweredValue"),
        practiceModalWorkspaceValue: document.getElementById("practiceModalWorkspaceValue"),
        selectedCategoryName: document.getElementById("selectedCategoryName"),
        selectedCategoryDescription: document.getElementById("selectedCategoryDescription"),
        questionCounter: document.getElementById("questionCounter"),
        practiceStatusTag: document.getElementById("practiceStatusTag"),
        practiceLabelTag: document.getElementById("practiceLabelTag"),
        coachModeValue: document.getElementById("coachModeValue"),
        timerTargetValue: document.getElementById("timerTargetValue"),
        questionTimerValue: document.getElementById("questionTimerValue"),
        questionProgressFill: document.getElementById("questionProgressFill"),
        currentQuestionText: document.getElementById("currentQuestionText"),
        coachModeTag: document.getElementById("coachModeTag"),
        coachTipText: document.getElementById("coachTipText"),
        questionKeywordTags: document.getElementById("questionKeywordTags"),
        responseInput: document.getElementById("responseInput"),
        startVoiceBtn: document.getElementById("startVoiceBtn"),
        stopVoiceBtn: document.getElementById("stopVoiceBtn"),
        submitAnswerBtn: document.getElementById("submitAnswerBtn"),
        nextQuestionBtn: document.getElementById("nextQuestionBtn"),
        endSessionBtn: document.getElementById("endSessionBtn"),
        voiceStatusText: document.getElementById("voiceStatusText"),
        voiceStatusDot: document.getElementById("voiceStatusDot"),
        practiceMessage: document.getElementById("practiceMessage"),
        feedbackContent: document.getElementById("feedbackContent"),
        printFeedbackBtn: document.getElementById("printFeedbackBtn"),
        inputModeValue: document.getElementById("inputModeValue"),
        answeredCountValue: document.getElementById("answeredCountValue"),
        paceModeValue: document.getElementById("paceModeValue"),
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
        stopCamera: () => {},
        stopSpeaking: () => {}
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

    function isPracticeModalOpen() {
        return Boolean(elements.practiceSessionModal) && !elements.practiceSessionModal.classList.contains("hidden");
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

    function updatePracticeModalSummary() {
        const modalIsOpen = isPracticeModalOpen();
        const questionTotal = state.selectedCategory ? (getActiveQuestionCount() || state.questionCount) : 0;
        const answeredLabel = `${state.answeredCount} / ${questionTotal}`;

        elements.practiceModalAnsweredValue.textContent = answeredLabel;
        elements.practiceModalWorkspaceValue.textContent = modalIsOpen ? "Open" : "Closed";

        if (!state.selectedCategory) {
            elements.practiceModalCategoryName.textContent = "Select a category to launch";
            elements.practiceModalSummaryText.textContent = "Choose a category from the left panel. The interview flow and AI interviewer will open in a modal.";
            elements.practiceModalActiveCategory.textContent = "None selected";
            elements.openPracticeModalBtn.textContent = "Open Interview Modal";
            elements.openPracticeModalBtn.disabled = true;
            setPracticeModalStateTag("Waiting", "neutral");
            return;
        }

        elements.practiceModalCategoryName.textContent = `${state.selectedCategory.name} workspace`;
        elements.practiceModalActiveCategory.textContent = state.selectedCategory.name;

        if (state.questionAgentLoading) {
            elements.practiceModalSummaryText.textContent = `Generating a fresh ${state.questionCount}-question set for ${state.selectedCategory.name} inside the Interview Workspace modal.`;
            elements.openPracticeModalBtn.textContent = modalIsOpen ? "Interview Modal Open" : "Continue Interview Modal";
            elements.openPracticeModalBtn.disabled = modalIsOpen;
            setPracticeModalStateTag("Preparing", "warning");
            return;
        }

        if (modalIsOpen) {
            elements.practiceModalSummaryText.textContent = getActiveQuestionCount() > 0
                ? `${state.questionSetSummary} Continue your session in the modal.`
                : `${state.selectedCategory.name} is open in the modal. Generate a fresh question set to begin.`;
            elements.openPracticeModalBtn.textContent = "Interview Modal Open";
            elements.openPracticeModalBtn.disabled = true;
            setPracticeModalStateTag("Live", "success");
            return;
        }

        elements.openPracticeModalBtn.disabled = false;
        elements.openPracticeModalBtn.textContent = "Continue Interview Modal";

        if (state.timerPaused) {
            elements.practiceModalSummaryText.textContent = `${state.selectedCategory.name} is paused. Reopen the modal to continue from the current question.`;
            setPracticeModalStateTag("Paused", "warning");
            return;
        }

        elements.practiceModalSummaryText.textContent = getActiveQuestionCount() > 0
            ? `${state.questionSetSummary} Reopen the modal to continue the interview and AI interviewer tools.`
            : `${state.selectedCategory.name} is ready. Reopen the modal to generate your next AI question set.`;
        setPracticeModalStateTag("Ready", "neutral");
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

    function openPracticeModal({ focusResponse = false } = {}) {
        if (!elements.practiceSessionModal || !state.selectedCategory) {
            return;
        }

        if (!isPracticeModalOpen()) {
            elements.practiceSessionModal.classList.remove("hidden");
            elements.practiceSessionModal.classList.add("flex");
            elements.practiceSessionModal.setAttribute("aria-hidden", "false");
            lockBodyScroll();
        }

        resumeTimerIfNeeded();
        updatePracticeModalSummary();

        if (focusResponse) {
            window.setTimeout(() => {
                elements.responseInput.focus();
            }, 0);
        }
    }

    function closePracticeModal({ returnFocus = true } = {}) {
        if (!elements.practiceSessionModal || !isPracticeModalOpen()) {
            return;
        }

        pauseTimer();
        stopVoiceInput({ commitTranscript: false });
        interviewerControls.stopSpeaking();
        interviewerControls.stopCamera();

        elements.practiceSessionModal.classList.add("hidden");
        elements.practiceSessionModal.classList.remove("flex");
        elements.practiceSessionModal.setAttribute("aria-hidden", "true");
        unlockBodyScroll();

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
            return "category-btn flex h-full min-h-[140px] w-full flex-col rounded-xl border border-brand-300 bg-brand-50 px-4 py-4 text-left transition dark:border-brand-500/30 dark:bg-brand-500/10";
        }

        return "category-btn flex h-full min-h-[140px] w-full flex-col rounded-xl border border-gray-200 bg-gray-50 px-4 py-4 text-left transition hover:border-brand-300 hover:bg-brand-50 dark:border-gray-800 dark:bg-gray-900/60 dark:hover:border-brand-500/30 dark:hover:bg-brand-500/10";
    }

    function renderCategories() {
        elements.practiceCategoryList.innerHTML = practiceData.categories
            .map((category) => `
                <button
                    type="button"
                    data-category-id="${category.id}"
                    class="${getCategoryButtonClass()}"
                >
                    <strong class="block text-sm font-semibold text-gray-900 dark:text-white/90">${category.name}</strong>
                    <span class="mt-2 block text-sm leading-6 text-gray-600 dark:text-gray-400">${category.description}</span>
                </button>
            `)
            .join("");

        document.querySelectorAll(".category-btn").forEach((button) => {
            button.addEventListener("click", () => {
                const category = practiceData.categories.find((item) => item.id === button.dataset.categoryId);
                if (category) {
                    selectCategory(category);
                }
            });
        });
    }

    function updateTipsPanel() {
        elements.inputModeValue.textContent = state.currentMode;
        elements.answeredCountValue.textContent = String(state.answeredCount);

        const pacing = getSelectedPacingMode();
        elements.paceModeValue.textContent = pacing.label;
        updatePracticeModalSummary();
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

    function updateSessionActionButtons() {
        const hasActiveSession = Boolean(state.selectedCategory) && getActiveQuestionCount() > 0;
        const speechSupported = Boolean(state.speechRecognition);

        elements.startVoiceBtn.disabled = !speechSupported || !hasActiveSession || state.recognitionActive || state.savingSession || state.questionAgentLoading || state.feedbackLoading;
        elements.stopVoiceBtn.disabled = !speechSupported || !state.recognitionActive;
        elements.submitAnswerBtn.disabled = !hasActiveSession || state.savingSession || state.questionAgentLoading || state.feedbackLoading;
        elements.nextQuestionBtn.disabled = !hasActiveSession || state.savingSession || state.questionAgentLoading || state.feedbackLoading;
        elements.endSessionBtn.disabled = !state.selectedCategory || state.savingSession || state.feedbackLoading;
        elements.printFeedbackBtn.disabled = state.feedbackHistory.length === 0;
        syncQuestionAgentControls();
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

    function stopVoiceInput({ commitTranscript = true } = {}) {
        cancelRecognitionRestart();
        state.recognitionShouldRestart = false;

        if (commitTranscript) {
            commitRecognitionInterimText();
        } else {
            state.recognitionInterimText = "";
            applyRecognitionDraft();
        }

        if (state.speechRecognition && state.recognitionActive) {
            setVoiceStatus("Stopping voice input...", "processing");
            updateSessionActionButtons();
            try {
                state.speechRecognition.stop();
            } catch (error) {
                console.error(error);
            }
        } else {
            setVoiceStatus(
                state.speechRecognition
                    ? "Voice input is idle."
                    : "Voice input is not supported in this browser. Use Chrome or Edge on localhost/HTTPS.",
                state.speechRecognition ? "idle" : "warning"
            );
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

    function startVoiceInput() {
        if (!state.selectedCategory) {
            showMessage("warning", "Choose a category before starting voice input.");
            return;
        }

        if (getActiveQuestionCount() === 0) {
            showMessage("warning", "Generate a question set before starting voice input.");
            return;
        }

        if (!state.speechRecognition) {
            showMessage("warning", "Voice input is not supported in this browser.");
            return;
        }

        if (state.recognitionActive) {
            showMessage("info", "Voice input is already active.");
            return;
        }

        cancelRecognitionRestart();
        state.recognitionShouldRestart = true;
        state.recognitionBaseText = elements.responseInput.value.trim();
        state.recognitionInterimText = "";
        state.manualInputUsed = Boolean(state.recognitionBaseText);
        setVoiceStatus("Requesting microphone access...", "processing");
        updateSessionActionButtons();
        clearMessage();

        try {
            state.speechRecognition.start();
        } catch (error) {
            state.recognitionShouldRestart = false;
            updateSessionActionButtons();

            if (error?.name === "InvalidStateError") {
                setVoiceStatus("Voice input is already starting...", "processing");
                return;
            }

            console.error(error);
            setVoiceStatus("Voice input could not start.", "error");
            showMessage(
                "error",
                "Voice input could not start. Use Chrome or Edge on localhost/HTTPS and allow microphone access."
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

        return messages[errorCode] || "Voice input encountered an error. Please try again.";
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
    }

    function renderQuestionAgentProviderOptions() {
        if (!elements.practiceQuestionAgentProviderSelect) {
            return;
        }

        elements.practiceQuestionAgentProviderSelect.innerHTML = state.questionAgentProviderCatalog.map((provider) => {
            const isAvailable = provider.configured || provider.id === "auto" || provider.id === "local";
            const selected = provider.id === state.questionAgentSelectedProviderId ? " selected" : "";
            const disabled = isAvailable ? "" : " disabled";
            const optionLabel = provider.configured || provider.id === "auto" || provider.id === "local"
                ? provider.label
                : `${provider.label} (Add API key)`;

            return `<option value="${escapeHtml(provider.id)}"${selected}${disabled}>${escapeHtml(optionLabel)}</option>`;
        }).join("");
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

        return `I am ready to build ${state.questionCount} fresh ${category.name} questions for this workspace. Add an instruction or use a quick action to tailor the set.`;
    }

    function buildDefaultQuestionAgentInstruction(category) {
        return `Generate ${state.questionCount} fresh interview questions for ${category.name} in the Philippine setting.`;
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
                    class="inline-flex items-center rounded-full border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700 dark:border-gray-700 dark:bg-gray-900/70 dark:text-gray-300 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-200"
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
                <div class="rounded-2xl border border-dashed border-gray-300 px-4 py-5 text-sm leading-6 text-gray-500 dark:border-gray-700 dark:text-gray-400">
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
                    <div class="max-w-full rounded-2xl border px-4 py-3 text-sm leading-6 ${bubbleClass}">
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
        state.questionAgentHistory = [{ role: "assistant", text: buildQuestionAgentWelcomeMessage(category) }];
        state.questionAgentResolvedProviderLabel = null;
        state.questionSetSummary = category
            ? `Select or refine an instruction to generate ${state.questionCount} fresh questions for ${category.name}.`
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
        stopTimer();
        stopVoiceInput({ commitTranscript: false });
        state.sessionId = createSessionId();
        state.questionIndex = 0;
        state.activeQuestions = [];
        state.answeredCount = 0;
        state.feedbackHistory = [];
        state.sessionStartedAt = new Date().toISOString();
        state.sessionSaved = false;
        state.feedbackLoading = false;
        elements.responseInput.value = "";
        elements.questionTimerValue.textContent = "00:00";
        resetRecognitionDraft();
        resetFeedbackPlaceholder();
    }

    function showQuestionSetLoadingState(category) {
        elements.selectedCategoryName.textContent = category.name;
        elements.selectedCategoryDescription.textContent = `Generating ${state.questionCount} fresh AI interview questions for ${category.name}.`;
        elements.currentQuestionText.textContent = "The AI question generator is preparing your first interview question.";
        elements.practiceStatusTag.textContent = "Generating questions";
        elements.practiceStatusTag.className = "inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-700 dark:bg-amber-500/10 dark:text-amber-300";
        elements.practiceLabelTag.textContent = "AI question set loading";
        renderKeywords(category.keywords);
        updateFocusModeDisplay();
        updateQuestionProgress();
    }

    function applyGeneratedQuestionSet(questions, payload, category) {
        state.activeQuestions = questions;
        state.questionIndex = 0;
        state.answeredCount = 0;
        state.feedbackHistory = [];
        state.sessionId = createSessionId();
        state.sessionStartedAt = new Date().toISOString();
        state.sessionSaved = false;
        state.questionSourceLabel = payload?.usedFallback ? "Local generated set" : "AI-generated set";
        state.questionSetSummary = String(payload?.reply || `${questions.length} AI-generated interview questions are ready.`);
        state.questionAgentResolvedProviderLabel = String(payload?.provider || "");
        loadCurrentQuestion();
    }

    async function generateQuestionSet(instruction = "") {
        if (!state.selectedCategory || state.questionAgentLoading) {
            return;
        }

        const category = state.selectedCategory;
        const hasExistingProgress = state.feedbackHistory.length > 0;
        const message = String(instruction || elements.practiceQuestionAgentInput.value || "").trim() || buildDefaultQuestionAgentInstruction(category);
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
            openPracticeModal({ focusResponse: true });
            showMessage(
                hasExistingProgress ? "info" : "success",
                hasExistingProgress
                    ? "A new AI question set is ready. The previous question progress was reset for this fresh run."
                    : "Your AI-generated question set is ready."
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
            : `${selectedFocus.tip} Choose a category to begin practicing.`;
    }

    function updateSelectedCategoryButtons() {
        document.querySelectorAll(".category-btn").forEach((button) => {
            const isActive = button.dataset.categoryId === state.selectedCategory?.id;
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

        stopVoiceInput({ commitTranscript: false });
        resetRecognitionDraft();

        elements.currentQuestionText.textContent = question;
        updateFocusModeDisplay();
        elements.selectedCategoryName.textContent = state.selectedCategory.name;
        elements.selectedCategoryDescription.textContent = state.questionSetSummary;
        elements.practiceStatusTag.textContent = "Session active";
        elements.practiceStatusTag.className = "inline-flex items-center rounded-full bg-success-100 px-3 py-1 text-xs font-medium text-success-700 dark:bg-success-500/10 dark:text-success-300";
        elements.practiceLabelTag.textContent = "Ready for answer";

        renderKeywords(state.selectedCategory.keywords);
        updateQuestionProgress();
        elements.responseInput.value = "";
        clearMessage();
        startTimer();
        updateSessionActionButtons();
    }

    async function selectCategory(category) {
        stopVoiceInput({ commitTranscript: false });
        state.selectedCategory = category;
        state.questionCount = Number(elements.questionCountSelect.value);

        const selectedPacing = getSelectedPacingMode();

        state.timerTarget = selectedPacing.seconds;
        elements.timerTargetValue.textContent = formatTime(state.timerTarget);

        resetSessionProgress();
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
                <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-400">${description}</p>
            </div>
        `;
    }

    function resetFeedbackPlaceholder() {
        elements.feedbackContent.innerHTML = `
            <div class="rounded-2xl border border-dashed border-gray-300 px-5 py-10 text-center dark:border-gray-700">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">No answer evaluated yet</h3>
                <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">
                    Once you submit a response, the system will generate detailed scores, strengths, improvement areas, and suggestions.
                </p>
            </div>
        `;
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
            categoryDescription: state.questionSetSummary,
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
                professionalism: Number((criteriaTotals.professionalism / answerCount).toFixed(1))
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
        const providerMeta = normalizedSummary.provider
            ? `<span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-gray-700 shadow-theme-xs dark:bg-gray-800 dark:text-gray-300">${normalizedSummary.provider}</span>`
            : "";

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

                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/70">
                    <strong class="block text-sm font-semibold text-gray-900 dark:text-white/90">Strengths</strong>
                    <ul class="mt-3 list-disc space-y-2 pl-5 text-sm leading-6 text-gray-600 dark:text-gray-400">
                        ${normalizedSummary.strengths.length ? normalizedSummary.strengths.map((item) => `<li>${item}</li>`).join("") : "<li>Your answer has a good starting point.</li>"}
                    </ul>
                </div>

                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/70">
                    <strong class="block text-sm font-semibold text-gray-900 dark:text-white/90">Improvement Areas</strong>
                    <ul class="mt-3 list-disc space-y-2 pl-5 text-sm leading-6 text-gray-600 dark:text-gray-400">
                        ${normalizedSummary.improvements.length ? normalizedSummary.improvements.map((item) => `<li>${item}</li>`).join("") : "<li>Keep practicing to improve consistency.</li>"}
                    </ul>
                </div>

                <div class="rounded-xl border border-brand-100 bg-brand-50 p-4 dark:border-brand-500/20 dark:bg-brand-500/10">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <strong class="block text-sm font-semibold text-gray-900 dark:text-white/90">Feedback Summary</strong>
                        ${providerMeta}
                    </div>
                    <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">
                        ${normalizedSummary.overall}
                    </p>
                    <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-400">
                        <strong>Next step:</strong> ${normalizedSummary.nextStep}
                    </p>
                </div>
            </div>
        `;
    }

    async function submitAnswer() {
        if (!state.selectedCategory) {
            showMessage("warning", "Choose a category before submitting an answer.");
            return;
        }

        if (getActiveQuestionCount() === 0) {
            showMessage("warning", "Generate a question set first before submitting an answer.");
            return;
        }

        if (state.recognitionActive) {
            stopVoiceInput();
        }

        const answer = elements.responseInput.value.trim();
        const activeQuestion = getActiveQuestion();

        if (!activeQuestion) {
            showMessage("warning", "There is no active interview question yet.");
            return;
        }

        if (!answer) {
            showMessage("error", "Blank answers are not accepted. Please type or record your response.");
            return;
        }

        stopTimer();

        const scoreData = calculateScore(answer, state.selectedCategory.keywords);
        const fallbackSummary = normalizeFeedbackSummary(answer, scoreData, buildFeedbackSummary(answer, scoreData));

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
            finalSummary = normalizeFeedbackSummary(answer, scoreData, {
                ...(payload.feedbackSummary || {}),
                provider: payload.feedbackSummary?.provider || payload.provider || null
            });
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

        try {
            await persistSessionIfNeeded();
            showMessage("success", feedbackMessage);
        } catch (error) {
            console.error(error);
            showMessage("warning", "Your answer was evaluated, but it could not be saved to the database.");
        }
    }

    function nextQuestion() {
        if (!state.selectedCategory) {
            showMessage("warning", "Please choose a category first.");
            return;
        }

        if (getActiveQuestionCount() === 0) {
            showMessage("warning", "Generate a question set first before moving to the next question.");
            return;
        }

        stopVoiceInput({ commitTranscript: false });

        if (state.questionIndex + 1 >= getActiveQuestionCount()) {
            showMessage("info", "You have reached the end of this session. Click End Session to save it to Progress.");
            elements.practiceLabelTag.textContent = "Session complete";
            stopTimer();
            updateSessionActionButtons();
            return;
        }

        state.questionIndex += 1;
        loadCurrentQuestion();
    }

    async function endSession() {
        state.questionAgentRequestId += 1;
        state.questionAgentLoading = false;
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

        elements.selectedCategoryName.textContent = "Select a category to start";
        elements.selectedCategoryDescription.textContent = "Your chosen interview type will load a fresh AI-generated question set.";
        elements.questionCounter.textContent = "Question 0 of 0";
        elements.practiceStatusTag.textContent = "Session ended";
        elements.practiceStatusTag.className = "inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300";
        elements.practiceLabelTag.textContent = "No active session";
        elements.currentQuestionText.textContent = "Choose a category from the left panel to begin your interview simulation.";
        elements.coachTipText.textContent = "Choose a category to load your coach guidance and answer keywords.";
        elements.questionKeywordTags.innerHTML = "";
        elements.questionProgressFill.style.width = "0%";
        elements.responseInput.value = "";
        elements.questionTimerValue.textContent = "00:00";
        resetFeedbackPlaceholder();

        state.selectedCategory = null;
        state.sessionId = null;
        state.questionIndex = 0;
        state.activeQuestions = [];
        state.answeredCount = 0;
        state.currentMode = "Text";
        state.sessionStartedAt = null;
        state.sessionSaved = false;
        state.feedbackHistory = [];
        state.feedbackLoading = false;
        state.questionSourceLabel = "Awaiting category";
        state.questionSetSummary = "Select a category and the chatbot will build a fresh question set for the workspace.";
        state.questionAgentResolvedProviderLabel = null;

        resetRecognitionDraft();
        resetQuestionAgentConversation();
        setVoiceStatus(
            state.speechRecognition
                ? "Voice input is idle."
                : "Voice input is not supported in this browser. Use Chrome or Edge on localhost/HTTPS.",
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
                "Voice input is not supported in this browser. Use Chrome or Edge on localhost/HTTPS.",
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
            setVoiceStatus("Voice input is listening...", "listening");
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

            setVoiceStatus(
                interimTranscript ? "Transcribing your answer in real time..." : "Voice input is listening...",
                interimTranscript ? "processing" : "listening"
            );
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
            updateSessionActionButtons();

            if (state.recognitionShouldRestart && state.selectedCategory && !state.savingSession) {
                cancelRecognitionRestart();
                setVoiceStatus("Voice input paused. Reconnecting...", "processing");
                state.restartVoiceTimeout = window.setTimeout(() => {
                    state.restartVoiceTimeout = null;

                    if (!state.recognitionShouldRestart || !state.selectedCategory || state.recognitionActive) {
                        updateSessionActionButtons();
                        return;
                    }

                    try {
                        state.speechRecognition.start();
                    } catch (error) {
                        console.error(error);
                        state.recognitionShouldRestart = false;
                        setVoiceStatus("Voice input could not restart.", "error");
                        updateSessionActionButtons();
                    }
                }, 250);
                return;
            }

            commitRecognitionInterimText();
            setVoiceStatus("Voice input is idle.", "idle");
            updateSessionActionButtons();
        };

        state.speechRecognition = recognition;
        setVoiceStatus("Voice input is ready when your session starts.", "idle");
        updateSessionActionButtons();
    }

    function launchSavedSetup(savedSetup) {
        const params = new URLSearchParams(window.location.search);

        if (params.get("launch") !== "setup" || !savedSetup?.savedAt) {
            return;
        }

        const preferredCategory = practiceData.categories.find((item) => item.id === savedSetup.preferredCategoryId);

        if (preferredCategory) {
            selectCategory(preferredCategory);
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

        if (savedSetup.voiceMode === "voice") {
            setVoiceStatus(
                state.speechRecognition
                    ? "Voice First is selected. Click Start Voice Input when you are ready."
                    : "Voice First is selected, but this browser does not support speech recognition.",
                state.speechRecognition ? "idle" : "warning"
            );
            elements.startVoiceBtn.focus();
        } else if (savedSetup.voiceMode === "hybrid") {
            setVoiceStatus("Hybrid mode is ready. Type or start voice input anytime.", "idle");
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
            selectCategory(state.selectedCategory);
        }
    });

    elements.focusModeSelect.addEventListener("change", () => {
        if (state.selectedCategory) {
            updateFocusModeDisplay();
            showMessage("info", `Coach focus updated to ${getSelectedFocusMode().label}.`);
            return;
        }

        updateFocusModeDisplay();
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

    elements.openPracticeModalBtn.addEventListener("click", () => {
        openPracticeModal({ focusResponse: Boolean(state.selectedCategory && getActiveQuestionCount() > 0) });
    });

    elements.closePracticeModalBtn.addEventListener("click", () => {
        closePracticeModal();
    });

    elements.practiceSessionModalBackdrop.addEventListener("click", () => {
        closePracticeModal({ returnFocus: false });
    });

    window.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
            closePracticeModal();
        }
    });

    elements.startVoiceBtn.addEventListener("click", startVoiceInput);
    elements.stopVoiceBtn.addEventListener("click", () => stopVoiceInput());

    elements.submitAnswerBtn.addEventListener("click", submitAnswer);
    elements.nextQuestionBtn.addEventListener("click", nextQuestion);
    elements.endSessionBtn.addEventListener("click", endSession);
    elements.printFeedbackBtn.addEventListener("click", () => window.print());
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
    updateSessionActionButtons();
    launchSavedSetup(savedSetup);

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
        const avatarLineText = document.getElementById("avatarLineText");
        const avatarOrb = document.getElementById("avatarOrb");
        const currentQuestionText = document.getElementById("currentQuestionText");
        const faceLandmarkerModelPath = "https://storage.googleapis.com/mediapipe-models/face_landmarker/face_landmarker/float16/1/face_landmarker.task";

        let FaceLandmarker = null;
        let FilesetResolver = null;

        try {
            ({ FaceLandmarker, FilesetResolver } = await import("https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@latest/vision_bundle.mjs"));
        } catch (error) {
            console.error(error);
            faceStateValue.textContent = "Unavailable";
            avatarLineText.textContent = "Camera and spoken questions are available, but live face detection could not be loaded.";
        }

        let faceLandmarker = null;
        let cameraStream = null;
        let animationFrameId = null;
        let lastVideoTime = -1;
        let lastSpokenQuestion = "";

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

        function setAvatarSpeaking(isSpeaking) {
            if (isSpeaking) {
                avatarOrb.classList.add("animate-pulse", "ring-4", "ring-brand-500/20");
                avatarVoiceValue.textContent = "Speaking";
                avatarSpeechStatus.textContent = "Avatar speaking";
            } else {
                avatarOrb.classList.remove("animate-pulse", "ring-4", "ring-brand-500/20");
                avatarVoiceValue.textContent = "Ready";
                avatarSpeechStatus.textContent = "Avatar idle";
            }
        }

        function stopSpeaking() {
            if ("speechSynthesis" in window) {
                window.speechSynthesis.cancel();
            }

            setAvatarSpeaking(false);
        }

        function getCurrentQuestion() {
            const text = currentQuestionText?.textContent?.trim() || "";
            const blockedPhrases = [
                "Choose a category",
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

        function speakText(text) {
            if (!("speechSynthesis" in window)) {
                avatarVoiceValue.textContent = "Unsupported";
                avatarLineText.textContent = "Speech synthesis is not supported in this browser.";
                return;
            }

            if (!text) {
                avatarLineText.textContent = "No interview question is active yet. Choose a category first.";
                return;
            }

            window.speechSynthesis.cancel();

            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = document.documentElement.lang || "en-US";
            utterance.rate = 1;
            utterance.pitch = 1;

            const preferredVoice = getPreferredVoice();
            if (preferredVoice) {
                utterance.voice = preferredVoice;
            }

            utterance.onstart = () => {
                setAvatarSpeaking(true);
                avatarLineText.textContent = text;
            };

            utterance.onend = () => {
                setAvatarSpeaking(false);
            };

            utterance.onerror = () => {
                setAvatarSpeaking(false);
                avatarVoiceValue.textContent = "Error";
            };

            window.speechSynthesis.speak(utterance);
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
                outputFaceBlendshapes: false
            });

            return faceLandmarker;
        }

        function detectFaceLoop() {
            if (!faceLandmarker || !faceCameraVideo || !cameraStream) return;

            if (faceCameraVideo.readyState >= 2 && faceCameraVideo.currentTime !== lastVideoTime) {
                const result = faceLandmarker.detectForVideo(faceCameraVideo, performance.now());
                const facesDetected = result.faceLandmarks?.length || 0;

                if (facesDetected > 0) {
                    faceStateValue.textContent = "Detected";
                    setStatusTag("Face detected", "success");
                } else {
                    faceStateValue.textContent = "Not detected";
                    setStatusTag("Face not detected", "warning");
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

                cameraStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: "user" },
                    audio: false
                });

                faceCameraVideo.srcObject = cameraStream;
                await faceCameraVideo.play();

                cameraStateValue.textContent = "Live";
                setStatusTag("Camera live", "success");
                avatarLineText.textContent = "Camera is live. I will monitor face visibility while you practice.";

                try {
                    await initFaceLandmarker();
                    faceStateValue.textContent = "Checking...";

                    if (animationFrameId) cancelAnimationFrame(animationFrameId);
                    detectFaceLoop();
                } catch (error) {
                    console.error(error);
                    faceStateValue.textContent = "Unavailable";
                    setStatusTag("Camera live, no face check", "warning");
                    avatarLineText.textContent = "Camera is live, but face detection is unavailable. Voice practice still works.";
                }
            } catch (error) {
                console.error(error);
                setStatusTag("Camera blocked", "error");
                cameraStateValue.textContent = "Blocked";
                faceStateValue.textContent = "Unavailable";
                avatarLineText.textContent = "Camera access or the face model failed. Use localhost or HTTPS and ensure the model file exists.";
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
        }

        interviewerControls.stopCamera = stopCamera;
        interviewerControls.stopSpeaking = stopSpeaking;

        startCameraBtn?.addEventListener("click", startCamera);
        stopCameraBtn?.addEventListener("click", stopCamera);
        askQuestionAloudBtn?.addEventListener("click", () => {
            const question = getCurrentQuestion();
            speakText(question || "Please choose a category to begin the interview.");
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
                    return;
                }

                if (question === lastSpokenQuestion) return;

                lastSpokenQuestion = question;
                avatarLineText.textContent = question;
                speakText(question);
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
        });
    }

    initInterviewer();

    window.addEventListener("beforeunload", () => {
        stopVoiceInput({ commitTranscript: false });
    });
}
