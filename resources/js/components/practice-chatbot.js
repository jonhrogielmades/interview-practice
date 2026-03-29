import { getChatbotQuickPrompts, practiceData } from "./practice-config";
import { requestWorkspace } from "./workspace-api";

function getChatbotBootstrap() {
    const fallbackProviders = [
        {
            id: "auto",
            label: "Auto",
            description: "Try configured AI APIs in priority order, then use the local PH coach.",
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
    const raw = window.__INTERVIEW_CHATBOT__ || {};
    const providers = Array.isArray(raw.providers) && raw.providers.length > 0
        ? raw.providers
        : fallbackProviders;

    return {
        defaultProviderId: typeof raw.defaultProviderId === "string" ? raw.defaultProviderId : "auto",
        providers
    };
}

export function initPracticeChatbot() {
    const root = document.getElementById("practiceApp");

    if (!root) {
        return;
    }

    const elements = {
        selectedCategoryName: document.getElementById("selectedCategoryName"),
        currentQuestionText: document.getElementById("currentQuestionText"),
        responseInput: document.getElementById("responseInput"),
        chatbotModal: document.getElementById("practiceChatbotModal"),
        chatbotModalBackdrop: document.getElementById("practiceChatbotModalBackdrop"),
        openChatbotModalBtn: document.getElementById("openPracticeChatbotModalBtn"),
        closeChatbotModalBtn: document.getElementById("closePracticeChatbotModalBtn"),
        chatbotModalCategoryName: document.getElementById("practiceChatbotModalCategoryName"),
        chatbotModalSummaryText: document.getElementById("practiceChatbotModalSummaryText"),
        chatbotModalStateTag: document.getElementById("practiceChatbotModalStateTag"),
        chatbotActiveContextValue: document.getElementById("practiceChatbotActiveContextValue"),
        chatbotMessageCountValue: document.getElementById("practiceChatbotMessageCountValue"),
        chatbotWindowValue: document.getElementById("practiceChatbotWindowValue"),
        chatbotStatusTag: document.getElementById("practiceChatbotStatusTag"),
        chatbotModeText: document.getElementById("practiceChatbotModeText"),
        chatbotContextText: document.getElementById("practiceChatbotContextText"),
        chatbotMessages: document.getElementById("practiceChatbotMessages"),
        chatbotPrompts: document.getElementById("practiceChatbotPrompts"),
        chatbotProviderSelect: document.getElementById("practiceChatbotProviderSelect"),
        chatbotProviderHelpText: document.getElementById("practiceChatbotProviderHelpText"),
        chatbotInput: document.getElementById("practiceChatbotInput"),
        chatbotSendBtn: document.getElementById("practiceChatbotSendBtn"),
        chatbotCancelEditBtn: document.getElementById("practiceChatbotCancelEditBtn"),
        chatbotClearBtn: document.getElementById("practiceChatbotClearBtn"),
        chatbotScrollTopBtn: document.getElementById("practiceChatbotScrollTopBtn"),
        chatbotScrollBottomBtn: document.getElementById("practiceChatbotScrollBottomBtn")
    };

    if (Object.values(elements).some((element) => !element)) {
        return;
    }

    const bootstrap = getChatbotBootstrap();
    const fallbackProviderCatalog = bootstrap.providers.map((provider) => ({
        id: String(provider.id || ""),
        label: String(provider.label || provider.id || "Provider"),
        description: String(provider.description || ""),
        configured: provider.configured !== false,
        type: String(provider.type || "remote"),
        model: provider.model ? String(provider.model) : null
    }));
    const defaultProvider = fallbackProviderCatalog.find((provider) => provider.id === bootstrap.defaultProviderId && provider.configured)
        ? bootstrap.defaultProviderId
        : "auto";

    const state = {
        history: [],
        loading: false,
        categoryId: null,
        currentQuestion: "",
        statusText: "Ready",
        statusTone: "neutral",
        editingMessageIndex: null,
        providerCatalog: fallbackProviderCatalog,
        selectedProviderId: defaultProvider,
        lastResolvedProviderLabel: null
    };

    function escapeHtml(value) {
        return String(value ?? "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/\"/g, "&quot;")
            .replace(/'/g, "&#39;");
    }

    function truncate(value, maxLength = 120) {
        const text = String(value || "").trim();
        return text.length > maxLength ? `${text.slice(0, maxLength - 3)}...` : text;
    }

    function getCurrentContext() {
        const categoryName = elements.selectedCategoryName.textContent.trim();
        const currentQuestion = elements.currentQuestionText.textContent.trim();
        const category = practiceData.categories.find((item) => item.name === categoryName) || null;
        const hasQuestion = currentQuestion && !currentQuestion.includes("Choose a category");

        return {
            categoryId: category?.id || null,
            categoryName: category?.name || null,
            currentQuestion: hasQuestion ? currentQuestion : ""
        };
    }

    function getProviderById(providerId = "") {
        return state.providerCatalog.find((provider) => provider.id === providerId) || null;
    }

    function getConfiguredRemoteProviders() {
        return state.providerCatalog.filter((provider) => provider.type === "remote" && provider.configured);
    }

    function getProviderSummaryLabel(provider) {
        if (!provider) {
            return "AI provider";
        }

        return provider.label.replace(/\s+API$/i, "");
    }

    function isChatbotModalOpen() {
        return Boolean(elements.chatbotModal) && !elements.chatbotModal.classList.contains("hidden");
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

    function getTagClasses() {
        return {
            neutral: "inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300",
            success: "inline-flex items-center rounded-full bg-success-100 px-3 py-1 text-xs font-medium text-success-700 dark:bg-success-500/10 dark:text-success-300",
            warning: "inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-700 dark:bg-amber-500/10 dark:text-amber-300",
            error: "inline-flex items-center rounded-full bg-error-100 px-3 py-1 text-xs font-medium text-error-700 dark:bg-error-500/10 dark:text-error-300"
        };
    }

    function applyTag(element, text, tone = "neutral") {
        const classes = getTagClasses();

        element.textContent = text;
        element.className = classes[tone] || classes.neutral;
    }

    function syncProviderPresentation() {
        const selectedProvider = getProviderById(state.selectedProviderId) || getProviderById("auto");
        const configuredRemoteProviders = getConfiguredRemoteProviders();
        const configuredLabels = configuredRemoteProviders.map((provider) => getProviderSummaryLabel(provider));
        const resolvedProviderText = state.lastResolvedProviderLabel ? ` Last reply: ${state.lastResolvedProviderLabel}.` : "";

        if (!selectedProvider || selectedProvider.id === "auto") {
            if (configuredRemoteProviders.length === 0) {
                elements.chatbotModeText.textContent = "No external AI key is configured, so the local PH coach will answer";
                elements.chatbotProviderHelpText.textContent = "Add one or more API keys in .env to enable Gemini, Groq, OpenRouter, Wisdom Gate, or Cohere.";
                return;
            }

            elements.chatbotModeText.textContent = `Auto can route across ${configuredLabels.join(", ")} before the local PH coach fallback${resolvedProviderText}`.trim();
            elements.chatbotProviderHelpText.textContent = "Auto prefers the configured provider priority from your backend settings and will fall back gracefully if one API is unavailable.";
            return;
        }

        if (selectedProvider.id === "local") {
            elements.chatbotModeText.textContent = "Local PH coach only";
            elements.chatbotProviderHelpText.textContent = "This keeps the chatbot fully local without calling external AI APIs.";
            return;
        }

        elements.chatbotModeText.textContent = `${selectedProvider.label}${selectedProvider.configured ? " selected" : " not configured"}${resolvedProviderText}`.trim();
        elements.chatbotProviderHelpText.textContent = selectedProvider.configured
            ? `${selectedProvider.description}${selectedProvider.model ? ` Model: ${selectedProvider.model}.` : ""}`
            : `${selectedProvider.description} Add its API key in .env to enable it.`;
    }

    function syncModalSummary() {
        const context = getCurrentContext();
        const modalIsOpen = isChatbotModalOpen();
        const hasConversation = state.history.length > 1;
        const activeContext = context.categoryName && context.currentQuestion
            ? truncate(`${context.categoryName}: ${context.currentQuestion}`, 84)
            : context.categoryName || "General PH interview coaching";

        elements.chatbotActiveContextValue.textContent = activeContext;
        elements.chatbotMessageCountValue.textContent = String(state.history.length);
        elements.chatbotWindowValue.textContent = modalIsOpen ? "Open" : "Closed";

        if (context.categoryName && context.currentQuestion) {
            elements.chatbotModalCategoryName.textContent = `${context.categoryName} coach`;
            elements.chatbotModalSummaryText.textContent = modalIsOpen
                ? `${context.categoryName} coaching is open in the modal. Continue asking about the current question or improve your draft answer here.`
                : `${context.categoryName} is active. Open the chatbot modal for local follow-up questions, answer improvements, or coaching on the current question.`;
        } else if (context.categoryName) {
            elements.chatbotModalCategoryName.textContent = `${context.categoryName} coach`;
            elements.chatbotModalSummaryText.textContent = modalIsOpen
                ? `${context.categoryName} coaching is open in the modal. Continue your Philippine interview chat here.`
                : `${context.categoryName} is selected. Open the chatbot modal for local follow-up questions, model answers, or practice tips.`;
        } else {
            elements.chatbotModalCategoryName.textContent = "Open the PH interview coach";
            elements.chatbotModalSummaryText.textContent = modalIsOpen
                ? "The PH interview coach is open in the modal. Ask about job, scholarship, admission, or IT interviews in the Philippines."
                : "Ask Philippine interview practice questions in a dedicated modal, with Gemini, Groq, OpenRouter, Wisdom Gate, and Cohere support when configured.";
        }

        elements.openChatbotModalBtn.disabled = modalIsOpen;
        elements.openChatbotModalBtn.textContent = modalIsOpen
            ? "Chatbot Modal Open"
            : hasConversation
                ? "Continue Chatbot Modal"
                : "Open Chatbot Modal";

        if (state.loading) {
            applyTag(elements.chatbotModalStateTag, "Thinking", "warning");
            return;
        }

        if (state.statusTone === "error") {
            applyTag(elements.chatbotModalStateTag, state.statusText, state.statusTone);
            return;
        }

        if (modalIsOpen) {
            applyTag(elements.chatbotModalStateTag, "Live", "success");
            return;
        }

        applyTag(elements.chatbotModalStateTag, "Ready", "neutral");
    }

    function scrollConversation(position = "bottom", behavior = "smooth") {
        if (!elements.chatbotMessages) {
            return;
        }

        const nextTop = position === "top" ? 0 : elements.chatbotMessages.scrollHeight;

        elements.chatbotMessages.scrollTo({
            top: nextTop,
            behavior
        });
    }

    function setStatus(text, tone = "neutral") {
        state.statusText = text;
        state.statusTone = tone;
        applyTag(elements.chatbotStatusTag, text, tone);
        syncModalSummary();
    }

    function syncChatAvailability() {
        syncProviderPresentation();
    }

    function buildWelcomeMessage() {
        const context = getCurrentContext();

        if (context.categoryName && context.currentQuestion) {
            return `I'm your Philippine interview practice coach. You are currently in ${context.categoryName}, and I can help you improve this question, generate local follow-up questions, or refine your draft answer.`;
        }

        if (context.categoryName) {
            return `I'm your Philippine interview practice coach. You are currently in ${context.categoryName}. Ask for sample answers, local follow-up questions, or category-specific interview tips.`;
        }

        return "I'm your Philippine interview practice coach. Ask me for local interview questions, sample answers, or coaching tips for job, scholarship, college admission, or IT interviews in the Philippines.";
    }

    function getEditableMessageIndex(index) {
        const item = state.history[index];
        return item && item.role === "user" ? index : null;
    }

    function applyProviderCatalog(providers = [], requestedProviderId = state.selectedProviderId) {
        if (!Array.isArray(providers) || providers.length === 0) {
            return;
        }

        state.providerCatalog = providers.map((provider) => ({
            id: String(provider.id || ""),
            label: String(provider.label || provider.id || "Provider"),
            description: String(provider.description || ""),
            configured: provider.configured !== false,
            type: String(provider.type || "remote"),
            model: provider.model ? String(provider.model) : null
        }));

        const requestedProvider = getProviderById(requestedProviderId);
        state.selectedProviderId = requestedProvider?.configured || requestedProvider?.id === "auto" || requestedProvider?.id === "local"
            ? requestedProviderId
            : "auto";

        renderProviderOptions();
        syncChatAvailability();
    }

    function renderProviderOptions() {
        elements.chatbotProviderSelect.innerHTML = state.providerCatalog.map((provider) => {
            const isDisabled = provider.id !== "auto" && provider.id !== "local" && !provider.configured;
            const selected = provider.id === state.selectedProviderId ? "selected" : "";
            const disabled = isDisabled ? "disabled" : "";
            const suffix = isDisabled ? " (Add API key)" : "";

            return `<option value="${escapeHtml(provider.id)}" ${selected} ${disabled}>${escapeHtml(provider.label + suffix)}</option>`;
        }).join("");

        elements.chatbotProviderSelect.value = state.selectedProviderId;
    }

    function cancelEditing({ clearInput = true } = {}) {
        state.editingMessageIndex = null;

        if (clearInput) {
            elements.chatbotInput.value = "";
        }

        setStatus("Ready", "neutral");
        syncControls();
        renderMessages();
    }

    function startEditingMessage(index) {
        const editableIndex = getEditableMessageIndex(index);

        if (editableIndex === null || state.loading) {
            return;
        }

        state.editingMessageIndex = editableIndex;
        elements.chatbotInput.value = state.history[editableIndex].text;
        setStatus("Editing draft", "warning");
        syncControls();
        renderMessages({ scrollTarget: editableIndex });
        openChatbotModal({ focusInput: true });
    }

    function renderMessages({ scrollTarget = "bottom" } = {}) {
        elements.chatbotMessages.innerHTML = state.history.map((item, index) => {
            const isAssistant = item.role === "assistant";
            const wrapperClass = isAssistant ? "items-start" : "items-end";
            const bubbleClass = isAssistant
                ? "border-gray-200 bg-gray-50 text-gray-700 dark:border-gray-800 dark:bg-gray-900/70 dark:text-gray-300"
                : "border-brand-200 bg-brand-50 text-brand-700 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-200";
            const label = isAssistant ? "PH Coach" : "You";
            const editAction = !isAssistant
                ? `
                    <button
                        type="button"
                        data-chatbot-edit-index="${index}"
                        class="inline-flex items-center justify-center rounded-full border border-brand-200 bg-white px-2.5 py-1 text-[11px] font-medium text-brand-700 transition hover:border-brand-300 hover:bg-brand-50 dark:border-brand-500/30 dark:bg-gray-900/70 dark:text-brand-200 dark:hover:bg-brand-500/10"
                    >
                        Edit
                    </button>
                `
                : "";
            const editingBadge = state.editingMessageIndex === index
                ? '<span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-medium text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">Editing</span>'
                : "";
            const bubbleAccentClass = state.editingMessageIndex === index
                ? `${bubbleClass} ring-2 ring-amber-200 dark:ring-amber-500/30`
                : bubbleClass;

            return `
                <div class="flex flex-col ${wrapperClass} gap-2" data-chatbot-message-index="${index}">
                    <div class="flex items-center gap-2 ${isAssistant ? "" : "justify-end"}">
                        <span class="text-xs font-medium uppercase tracking-wide text-gray-400">${label}</span>
                        ${editingBadge}
                        ${editAction}
                    </div>
                    <div class="max-w-full rounded-2xl border px-4 py-3 text-sm leading-6 ${bubbleAccentClass}">
                        ${escapeHtml(item.text).replace(/\n/g, "<br>")}
                    </div>
                </div>
            `;
        }).join("");

        elements.chatbotMessages.querySelectorAll("[data-chatbot-edit-index]").forEach((button) => {
            button.addEventListener("click", () => {
                startEditingMessage(Number(button.dataset.chatbotEditIndex));
            });
        });

        if (scrollTarget === "bottom") {
            scrollConversation("bottom", "auto");
        } else if (Number.isInteger(scrollTarget)) {
            const targetMessage = elements.chatbotMessages.querySelector(`[data-chatbot-message-index="${scrollTarget}"]`);
            targetMessage?.scrollIntoView({
                behavior: "smooth",
                block: "nearest"
            });
        }

        syncModalSummary();
    }

    function renderPrompts(prompts = []) {
        elements.chatbotPrompts.innerHTML = prompts.map((prompt) => `
            <button
                type="button"
                data-chatbot-prompt="${escapeHtml(prompt)}"
                title="${escapeHtml(prompt)}"
                class="inline-flex items-center rounded-full border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700 dark:border-gray-700 dark:bg-gray-900/70 dark:text-gray-300 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/10 dark:hover:text-brand-200"
            >
                ${escapeHtml(truncate(prompt, 68))}
            </button>
        `).join("");

        elements.chatbotPrompts.querySelectorAll("[data-chatbot-prompt]").forEach((button) => {
            button.addEventListener("click", () => {
                sendMessage(button.dataset.chatbotPrompt || "");
            });
        });
    }

    function syncContextText() {
        const context = getCurrentContext();

        state.categoryId = context.categoryId;
        state.currentQuestion = context.currentQuestion;

        if (context.categoryName && context.currentQuestion) {
            elements.chatbotContextText.textContent = `${context.categoryName}: ${context.currentQuestion}`;
        } else if (context.categoryName) {
            elements.chatbotContextText.textContent = `${context.categoryName}: ask for local follow-up questions, model answers, or answer improvements.`;
        } else {
            elements.chatbotContextText.textContent = "Choose a category first if you want the chatbot to coach you on the active Philippine interview track.";
        }

        renderPrompts(getChatbotQuickPrompts(context.categoryId, context.currentQuestion));
        syncModalSummary();
    }

    function syncControls() {
        const hasValue = elements.chatbotInput.value.trim().length > 0;

        elements.chatbotSendBtn.disabled = state.loading || !hasValue;
        elements.chatbotSendBtn.textContent = state.editingMessageIndex === null ? "Send" : "Update & Send";
        elements.chatbotCancelEditBtn.disabled = state.loading || state.editingMessageIndex === null;
        elements.chatbotClearBtn.disabled = state.loading || state.history.length <= 1;
        elements.chatbotScrollTopBtn.disabled = state.history.length <= 1;
        elements.chatbotScrollBottomBtn.disabled = state.history.length <= 1;
        elements.chatbotProviderSelect.disabled = state.loading;
    }

    function openChatbotModal({ focusInput = false } = {}) {
        if (!elements.chatbotModal) {
            return;
        }

        if (!isChatbotModalOpen()) {
            elements.chatbotModal.classList.remove("hidden");
            elements.chatbotModal.classList.add("flex");
            elements.chatbotModal.setAttribute("aria-hidden", "false");
            lockBodyScroll();
        }

        syncModalSummary();

        if (focusInput) {
            window.setTimeout(() => {
                elements.chatbotInput.focus();
            }, 0);
        }
    }

    function closeChatbotModal({ returnFocus = true } = {}) {
        if (!elements.chatbotModal || !isChatbotModalOpen()) {
            return;
        }

        elements.chatbotModal.classList.add("hidden");
        elements.chatbotModal.classList.remove("flex");
        elements.chatbotModal.setAttribute("aria-hidden", "true");
        unlockBodyScroll();
        syncModalSummary();

        if (returnFocus) {
            elements.openChatbotModalBtn.focus();
        }
    }

    function resetConversation() {
        state.history = [{ role: "assistant", text: buildWelcomeMessage() }];
        state.editingMessageIndex = null;
        state.lastResolvedProviderLabel = null;
        elements.chatbotInput.value = "";
        renderMessages();
        syncContextText();
        setStatus("Ready", "neutral");
        syncChatAvailability();
        syncControls();
    }

    async function sendMessage(overrideText = "") {
        const message = String(overrideText || elements.chatbotInput.value || "").trim();

        if (!message || state.loading) {
            return;
        }

        const context = getCurrentContext();
        const baseHistory = state.editingMessageIndex === null
            ? state.history
            : state.history.slice(0, state.editingMessageIndex);
        const historyForRequest = baseHistory.slice(-8);

        state.history = [...baseHistory, { role: "user", text: message }];
        state.loading = true;
        state.editingMessageIndex = null;
        elements.chatbotInput.value = "";
        renderMessages();
        syncControls();
        setStatus("Thinking", "warning");

        try {
            const payload = await requestWorkspace("chatbot", {
                method: "POST",
                body: {
                    message,
                    providerId: state.selectedProviderId,
                    categoryId: context.categoryId,
                    currentQuestion: context.currentQuestion || null,
                    answerDraft: elements.responseInput.value.trim() || null,
                    history: historyForRequest
                }
            });

            applyProviderCatalog(payload.availableProviders, payload.requestedProviderId || state.selectedProviderId);

            state.history.push({
                role: "assistant",
                text: String(payload.reply || "The chatbot did not return a reply.")
            });
            state.lastResolvedProviderLabel = String(payload.provider || "");
            renderMessages();
            renderPrompts(Array.isArray(payload.suggestions) ? payload.suggestions : getChatbotQuickPrompts(context.categoryId, context.currentQuestion));
            setStatus(payload.usedFallback ? "Local PH coach" : "AI ready", payload.usedFallback ? "neutral" : "success");
            syncChatAvailability();
        } catch (error) {
            console.error(error);
            state.history.push({
                role: "assistant",
                text: "The chatbot is unavailable right now. You can still ask for Philippine interview practice again in a moment."
            });
            state.lastResolvedProviderLabel = null;
            renderMessages();
            setStatus("Unavailable", "error");
        } finally {
            state.loading = false;
            syncControls();
            syncModalSummary();
            syncChatAvailability();
        }
    }

    const contextObserver = new MutationObserver(() => {
        const previousCategoryId = state.categoryId;

        syncContextText();

        if (previousCategoryId !== state.categoryId) {
            resetConversation();
        }
    });

    contextObserver.observe(elements.selectedCategoryName, {
        childList: true,
        subtree: true,
        characterData: true
    });

    contextObserver.observe(elements.currentQuestionText, {
        childList: true,
        subtree: true,
        characterData: true
    });

    elements.openChatbotModalBtn.addEventListener("click", () => {
        openChatbotModal({ focusInput: true });
    });

    elements.closeChatbotModalBtn.addEventListener("click", () => {
        closeChatbotModal();
    });

    elements.chatbotModalBackdrop.addEventListener("click", () => {
        closeChatbotModal({ returnFocus: false });
    });

    window.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
            closeChatbotModal();
        }
    });

    elements.chatbotProviderSelect.addEventListener("change", () => {
        state.selectedProviderId = elements.chatbotProviderSelect.value || "auto";
        state.lastResolvedProviderLabel = null;
        syncChatAvailability();
        syncControls();
    });

    elements.chatbotInput.addEventListener("input", syncControls);
    elements.chatbotInput.addEventListener("keydown", (event) => {
        if (event.key === "Enter" && !event.shiftKey) {
            event.preventDefault();
            sendMessage();
        }
    });

    elements.chatbotCancelEditBtn.addEventListener("click", () => {
        cancelEditing();
    });

    elements.chatbotSendBtn.addEventListener("click", () => sendMessage());
    elements.chatbotScrollTopBtn.addEventListener("click", () => {
        scrollConversation("top");
    });
    elements.chatbotScrollBottomBtn.addEventListener("click", () => {
        scrollConversation("bottom");
    });
    elements.chatbotClearBtn.addEventListener("click", () => {
        resetConversation();
    });

    renderProviderOptions();
    syncChatAvailability();
    resetConversation();
}
