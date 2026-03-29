import {
    practiceData,
    questionCountOptions,
    responsePreferenceOptions,
    formatPracticeTime,
    readSessionSetup,
    writeSessionSetup,
    clearSessionSetup,
    getQuestionCountOption,
    getResponsePreferenceOption,
    normalizeSessionSetup,
    SESSION_SETUP_UPDATED_EVENT
} from "./practice-config";

export function initSessionSetup() {
    const app = document.getElementById("sessionSetupApp");

    if (!app) {
        return;
    }

    const elements = {
        questionCount: document.getElementById("setupQuestionCount"),
        coachMode: document.getElementById("setupCoachMode"),
        pacingMode: document.getElementById("setupPacingMode"),
        preferredCategory: document.getElementById("setupPreferredCategory"),
        voiceMode: document.getElementById("setupVoiceMode"),
        notes: document.getElementById("setupNotes"),
        notesCount: document.getElementById("setupNotesCount"),
        summaryQuestionCount: document.getElementById("summaryQuestionCount"),
        summaryCoachMode: document.getElementById("summaryCoachMode"),
        summaryPacingMode: document.getElementById("summaryPacingMode"),
        summaryCategory: document.getElementById("summaryCategory"),
        summaryVoiceMode: document.getElementById("summaryVoiceMode"),
        summaryEstimatedTime: document.getElementById("summaryEstimatedTime"),
        summaryNotes: document.getElementById("summaryNotes"),
        summaryCategoryDescription: document.getElementById("summaryCategoryDescription"),
        summaryCategoryQuestion: document.getElementById("summaryCategoryQuestion"),
        summaryCategoryKeywords: document.getElementById("summaryCategoryKeywords"),
        saveButton: document.getElementById("saveSessionSetupBtn"),
        resetButton: document.getElementById("resetSessionSetupBtn"),
        openPracticeButton: document.getElementById("openPracticeBtn"),
        status: document.getElementById("sessionSetupStatus")
    };

    const state = {
        lastSavedSetup: normalizeSessionSetup(readSessionSetup()),
        isDirty: false,
        isSaving: false,
        isResetting: false
    };

    function formatSavedTime(value) {
        return new Intl.DateTimeFormat("en-US", {
            month: "short",
            day: "numeric",
            year: "numeric",
            hour: "numeric",
            minute: "2-digit"
        }).format(new Date(value));
    }

    function setStatus(type, text) {
        const baseClass = "mt-4 rounded-xl border px-4 py-3 text-sm";
        const map = {
            success: "border-success-200 bg-success-50 text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-300",
            warning: "border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300",
            info: "border-gray-200 bg-gray-50 text-gray-700 dark:border-gray-700 dark:bg-gray-900/70 dark:text-gray-300"
        };

        elements.status.className = `${baseClass} ${map[type] || map.info}`;
        elements.status.textContent = text;
        elements.status.classList.remove("hidden");
    }

    function populateSelects() {
        elements.questionCount.innerHTML = questionCountOptions
            .map((option) => `<option value="${option.value}">${option.label}</option>`)
            .join("");

        elements.coachMode.innerHTML = practiceData.focusModes
            .map((mode, index) => `<option value="${index}">${mode.label}</option>`)
            .join("");

        elements.pacingMode.innerHTML = practiceData.pacingModes
            .map((mode, index) => `<option value="${index}">${mode.label} (${formatPracticeTime(mode.seconds)})</option>`)
            .join("");

        elements.preferredCategory.innerHTML = practiceData.categories
            .map((category) => `<option value="${category.id}">${category.name}</option>`)
            .join("");

        elements.voiceMode.innerHTML = responsePreferenceOptions
            .map((option) => `<option value="${option.value}">${option.label}</option>`)
            .join("");
    }

    function readFormState() {
        return normalizeSessionSetup({
            questionCount: Number(elements.questionCount.value),
            focusModeIndex: Number(elements.coachMode.value),
            pacingModeIndex: Number(elements.pacingMode.value),
            preferredCategoryId: elements.preferredCategory.value,
            voiceMode: elements.voiceMode.value,
            notes: elements.notes.value,
            savedAt: state.lastSavedSetup.savedAt
        });
    }

    function applyFormState(setup) {
        elements.questionCount.value = String(setup.questionCount);
        elements.coachMode.value = String(setup.focusModeIndex);
        elements.pacingMode.value = String(setup.pacingModeIndex);
        elements.preferredCategory.value = setup.preferredCategoryId;
        elements.voiceMode.value = setup.voiceMode;
        elements.notes.value = setup.notes;
    }

    function getComparableSetup(setup) {
        const normalized = normalizeSessionSetup(setup);

        return JSON.stringify({
            questionCount: normalized.questionCount,
            focusModeIndex: normalized.focusModeIndex,
            pacingModeIndex: normalized.pacingModeIndex,
            preferredCategoryId: normalized.preferredCategoryId,
            voiceMode: normalized.voiceMode,
            notes: normalized.notes
        });
    }

    function renderCategoryKeywords(keywords) {
        elements.summaryCategoryKeywords.innerHTML = keywords
            .map((keyword) => `
                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    ${keyword}
                </span>
            `)
            .join("");
    }

    function updateSummary() {
        const current = readFormState();
        const questionCount = getQuestionCountOption(current.questionCount);
        const focusMode = practiceData.focusModes[current.focusModeIndex] || practiceData.focusModes[0];
        const pacingMode = practiceData.pacingModes[current.pacingModeIndex] || practiceData.pacingModes[0];
        const preferredCategory = practiceData.categories.find((category) => category.id === current.preferredCategoryId) || practiceData.categories[0];
        const voiceMode = getResponsePreferenceOption(current.voiceMode);
        const notes = current.notes.trim();
        const estimatedSeconds = current.questionCount * pacingMode.seconds;

        elements.summaryQuestionCount.textContent = questionCount.label;
        elements.summaryCoachMode.textContent = focusMode.label;
        elements.summaryPacingMode.textContent = `${pacingMode.label} (${formatPracticeTime(pacingMode.seconds)})`;
        elements.summaryCategory.textContent = preferredCategory.name;
        elements.summaryVoiceMode.textContent = voiceMode.label;
        elements.summaryEstimatedTime.textContent = `${formatPracticeTime(estimatedSeconds)} total`;
        elements.summaryNotes.textContent = notes || "No notes saved yet.";
        elements.summaryCategoryDescription.textContent = preferredCategory.description;
        elements.summaryCategoryQuestion.textContent = preferredCategory.questions[0] || "No sample question available yet.";
        elements.notesCount.textContent = `${elements.notes.value.length} / 500`;

        renderCategoryKeywords(preferredCategory.keywords);
    }

    function updateActionState() {
        const canSave = state.isDirty || !state.lastSavedSetup.savedAt;

        elements.saveButton.disabled = !canSave || state.isSaving || state.isResetting;
        elements.resetButton.disabled = state.isSaving || state.isResetting;

        elements.saveButton.textContent = state.isSaving
            ? "Saving..."
            : canSave
                ? "Save Defaults"
                : "Defaults Saved";
        elements.resetButton.textContent = state.isResetting ? "Resetting..." : "Reset Defaults";

        elements.openPracticeButton.classList.toggle("pointer-events-none", state.isSaving || state.isResetting);
        elements.openPracticeButton.classList.toggle("opacity-60", state.isSaving || state.isResetting);
    }

    function showLoadedStatus() {
        if (state.lastSavedSetup.savedAt) {
            setStatus("info", `Saved defaults loaded from ${formatSavedTime(state.lastSavedSetup.savedAt)}.`);
            return;
        }

        setStatus("info", "Choose your defaults here, then save them to preload the Practice page.");
    }

    function syncDirtyState({ announce = false } = {}) {
        state.isDirty = getComparableSetup(readFormState()) !== getComparableSetup(state.lastSavedSetup);
        updateActionState();

        if (!announce) {
            return;
        }

        if (state.isDirty) {
            setStatus("warning", "You have unsaved changes.");
            return;
        }

        showLoadedStatus();
    }

    function handleFormChange() {
        if (elements.notes.value.length > 500) {
            elements.notes.value = elements.notes.value.slice(0, 500);
        }

        updateSummary();
        syncDirtyState({ announce: true });
    }

    async function saveCurrentSetup(successMessage = null) {
        if (state.isSaving || state.isResetting) {
            return null;
        }

        state.isSaving = true;
        updateActionState();

        try {
            const saved = await writeSessionSetup(readFormState());
            state.lastSavedSetup = normalizeSessionSetup(saved);
            applyFormState(state.lastSavedSetup);
            updateSummary();
            state.isDirty = false;
            setStatus(
                "success",
                successMessage
                    || `Defaults saved at ${formatSavedTime(saved.savedAt)}. Practice is ready with your selected category, pacing, and response mode.`
            );
            return state.lastSavedSetup;
        } catch (error) {
            console.error(error);
            setStatus("warning", "Defaults could not be saved to the database. Please try again.");
            return null;
        } finally {
            state.isSaving = false;
            updateActionState();
        }
    }

    async function resetCurrentSetup() {
        if (state.isSaving || state.isResetting) {
            return;
        }

        state.isResetting = true;
        updateActionState();

        try {
            const reset = await clearSessionSetup();
            state.lastSavedSetup = normalizeSessionSetup(reset);
            applyFormState(state.lastSavedSetup);
            updateSummary();
            state.isDirty = false;
            setStatus("info", "Defaults reset to the recommended starting setup.");
        } catch (error) {
            console.error(error);
            setStatus("warning", "Defaults could not be reset right now. Please try again.");
        } finally {
            state.isResetting = false;
            updateActionState();
        }
    }

    async function openPracticeWithSetup(event) {
        event.preventDefault();

        if (state.isSaving || state.isResetting) {
            return;
        }

        if (state.isDirty || !state.lastSavedSetup.savedAt) {
            const saved = await saveCurrentSetup(
                state.isDirty
                    ? "Unsaved changes were saved. Opening Practice with your latest defaults..."
                    : "Recommended defaults were saved. Opening Practice..."
            );

            if (!saved) {
                return;
            }
        }

        window.location.href = elements.openPracticeButton.href;
    }

    populateSelects();
    applyFormState(state.lastSavedSetup);
    updateSummary();
    updateActionState();
    showLoadedStatus();

    [
        elements.questionCount,
        elements.coachMode,
        elements.pacingMode,
        elements.preferredCategory,
        elements.voiceMode,
        elements.notes
    ].forEach((element) => {
        element.addEventListener("input", handleFormChange);
        element.addEventListener("change", handleFormChange);
    });

    elements.saveButton.addEventListener("click", () => {
        saveCurrentSetup();
    });

    elements.resetButton.addEventListener("click", () => {
        resetCurrentSetup();
    });

    elements.openPracticeButton.addEventListener("click", openPracticeWithSetup);

    window.addEventListener("beforeunload", (event) => {
        if (!state.isDirty) {
            return;
        }

        event.preventDefault();
        event.returnValue = "";
    });

    document.addEventListener("keydown", (event) => {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === "s") {
            event.preventDefault();
            saveCurrentSetup();
        }
    });

    window.addEventListener(SESSION_SETUP_UPDATED_EVENT, (event) => {
        const incomingSetup = normalizeSessionSetup(event.detail?.setup || readSessionSetup());

        if (state.isSaving || state.isResetting) {
            return;
        }

        if (state.isDirty) {
            setStatus("warning", "Saved defaults changed elsewhere. Save or reset this page to sync them.");
            return;
        }

        state.lastSavedSetup = incomingSetup;
        applyFormState(incomingSetup);
        updateSummary();
        state.isDirty = false;
        updateActionState();
        showLoadedStatus();
    });
}
