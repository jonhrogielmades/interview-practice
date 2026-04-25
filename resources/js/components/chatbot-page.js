import { getChatbotQuickPrompts } from "./practice-config";
import { requestWorkspace } from "./workspace-api";

const MODE_CONFIG = {
    chat: {
        label: "Coach Chat",
        composerLabel: "Ask the interview coach",
        composerPlaceholder: "Ask for interview coaching, sample questions, model answers, or follow-up questions.",
        help: "The assistant will refuse unrelated topics and stay inside Philippine interview coaching.",
        buttonText: "Send To Interview Chatbot"
    },
    question_set: {
        label: "Question Builder",
        composerLabel: "Question set instructions",
        composerPlaceholder: "Example: Generate tougher questions for a fresh graduate applying to a BPO support role.",
        help: "If you leave this blank, the chatbot will generate a fresh question set from the selected interview scope.",
        buttonText: "Generate Interview Questions"
    },
    feedback_review: {
        label: "Answer Review",
        composerLabel: "Review instructions",
        composerPlaceholder: "Example: Review my answer and make it sound more confident and professional.",
        help: "Add the question and your draft answer above, then ask for a detailed review.",
        buttonText: "Review Interview Answer"
    }
};

const REMOTE_PROVIDER_IDS = ["gemini", "groq", "openrouter", "claude", "wisdomgate", "cohere"];

export function initChatbotPage() {
    const app = document.getElementById("chatbotApp");

    if (!app) {
        return;
    }

    const elements = {
        modeButtons: Array.from(app.querySelectorAll("[data-chat-mode]")),
        categoryButtons: Array.from(app.querySelectorAll("[data-category-id]")),
        providerSelect: document.getElementById("chatbotProviderSelect"),
        questionCountWrap: document.getElementById("chatbotQuestionCountWrap"),
        questionCount: document.getElementById("chatbotQuestionCount"),
        reviewFields: document.getElementById("chatbotReviewFields"),
        currentQuestionInput: document.getElementById("chatbotCurrentQuestionInput"),
        answerDraftInput: document.getElementById("chatbotAnswerDraftInput"),
        status: document.getElementById("chatbotStatus"),
        conversation: document.getElementById("chatbotConversation"),
        composerForm: document.getElementById("chatbotComposerForm"),
        composerLabel: document.getElementById("chatbotComposerLabel"),
        composerInput: document.getElementById("chatbotComposerInput"),
        composerHelp: document.getElementById("chatbotComposerHelp"),
        clearButton: document.getElementById("chatbotClearButton"),
        sendButton: document.getElementById("chatbotSendButton"),
        scopeTitle: document.getElementById("chatbotScopeTitle"),
        scopeDescription: document.getElementById("chatbotScopeDescription"),
        providerSummary: document.getElementById("chatbotProviderSummary"),
        quickPrompts: document.getElementById("chatbotQuickPrompts"),
        resultMeta: document.getElementById("chatbotResultMeta"),
        generatedQuestions: document.getElementById("chatbotGeneratedQuestions"),
        feedbackPanel: document.getElementById("chatbotFeedbackPanel"),
        providerHealthButton: document.getElementById("chatbotProviderHealthButton"),
        providerHealthNote: document.getElementById("chatbotProviderHealthNote"),
        providerCards: Array.from(document.querySelectorAll("[data-provider-card]"))
    };

    const profileConfig = {
        userName: String(app.dataset.userName || "You"),
        userAvatar: String(app.dataset.userAvatar || ""),
        assistantName: String(app.dataset.assistantName || "Interview Chatbot")
    };

    const state = {
        mode: "chat",
        categoryId: "all",
        providerId: normalizeProviderSelection(elements.providerSelect.value),
        sending: false,
        messages: [],
        generatedQuestions: [],
        feedbackSummary: null,
        lastProviderLabel: null,
        providerHealthRunning: false,
        providerHealth: buildInitialProviderHealth()
    };

    ensureProviderSelectionIsUsable();
    resetConversation();
    renderAll();

    elements.modeButtons.forEach((button) => {
        button.addEventListener("click", () => {
            const nextMode = String(button.dataset.chatMode || "chat");

            if (!MODE_CONFIG[nextMode] || state.mode === nextMode) {
                return;
            }

            state.mode = nextMode;
            hideStatus();
            resetConversation();
            renderAll();
        });
    });

    elements.categoryButtons.forEach((button) => {
        button.addEventListener("click", () => {
            const nextCategoryId = String(button.dataset.categoryId || "all");

            if (state.categoryId === nextCategoryId) {
                return;
            }

            state.categoryId = nextCategoryId;
            hideStatus();
            renderScope();
            renderCategoryButtons();
            renderQuickPrompts();
        });
    });

    elements.providerSelect.addEventListener("change", () => {
        state.providerId = normalizeProviderSelection(elements.providerSelect.value);
        ensureProviderSelectionIsUsable();
        hideStatus();
        renderScope();
    });

    elements.questionCount.addEventListener("change", () => {
        renderQuickPrompts();
    });

    elements.currentQuestionInput.addEventListener("input", () => {
        if (state.mode === "feedback_review") {
            renderQuickPrompts();
        }
    });

    elements.answerDraftInput.addEventListener("input", () => {
        if (state.mode === "feedback_review") {
            renderQuickPrompts();
        }
    });

    elements.composerForm.addEventListener("submit", async (event) => {
        event.preventDefault();
        await submitPrompt();
    });

    elements.clearButton.addEventListener("click", () => {
        hideStatus();
        resetConversation();
        renderAll();
    });

    elements.providerHealthButton?.addEventListener("click", async () => {
        await runProviderHealthCheck();
    });

    elements.quickPrompts.addEventListener("click", async (event) => {
        const button = event.target.closest("[data-prompt]");

        if (!button || state.sending) {
            return;
        }

        const prompt = String(button.dataset.prompt || "").trim();

        if (!prompt) {
            return;
        }

        if (state.mode === "feedback_review") {
            elements.composerInput.value = prompt;
            elements.composerInput.focus();
            return;
        }

        elements.composerInput.value = prompt;
        await submitPrompt();
    });

    function resetConversation() {
        state.messages = [
            {
                role: "assistant",
                text: buildGreetingMessage(),
                provider: "InterviewPilot PH Coach",
                usedFallback: false
            }
        ];
        state.generatedQuestions = [];
        state.feedbackSummary = null;
        state.lastProviderLabel = null;
        elements.composerInput.value = "";
        renderConversation();
        renderResults();
        scrollConversationToBottom();
    }

    function buildGreetingMessage() {
        const category = getSelectedCategory();

        return `I can help with ${category.name.toLowerCase()} in the Philippines only. Ask me for sample questions, follow-up questions, model answers, or coaching on how to improve your response.`;
    }

    function buildInitialProviderHealth() {
        const providers = Array.isArray(window.__INTERVIEW_CHATBOT__?.providers)
            ? window.__INTERVIEW_CHATBOT__.providers
            : [];

        return Object.fromEntries(
            providers
                .filter((provider) => REMOTE_PROVIDER_IDS.includes(String(provider?.id || "")))
                .map((provider) => {
                    const providerId = String(provider.id || "");
                    const configured = Boolean(provider.configured);

                    return [providerId, {
                        configured,
                        state: configured ? "configured" : "needs_key",
                        message: configured
                            ? "Configured in .env. Run a live check to confirm a real provider response."
                            : "Add the provider API key in .env to enable live checks and chatbot replies.",
                        provider: null
                    }];
                })
        );
    }

    function getSelectedCategory() {
        const selectedButton = elements.categoryButtons.find(
            (button) => String(button.dataset.categoryId || "") === state.categoryId
        );

        if (selectedButton) {
            return {
                id: String(selectedButton.dataset.categoryId || "all"),
                name: String(selectedButton.dataset.categoryName || "All Philippine Interviews"),
                description: String(
                    selectedButton.dataset.categoryDescription
                        || "General interview practice across the supported Philippine interview tracks in this project."
                )
            };
        }

        return {
            id: "all",
            name: "All Philippine Interviews",
            description: "General interview practice across the supported Philippine interview tracks in this project."
        };
    }

    function normalizeProviderSelection(value) {
        return String(value || "auto").trim() || "auto";
    }

    function ensureProviderSelectionIsUsable() {
        const option = elements.providerSelect.selectedOptions[0];

        if (option && option.disabled) {
            elements.providerSelect.value = "auto";
        }

        state.providerId = normalizeProviderSelection(elements.providerSelect.value);
    }

    function getProviderLabel(providerId = state.providerId) {
        if (providerId === "auto") {
            return "Auto choose the best available API";
        }

        const option = Array.from(elements.providerSelect.options).find((item) => item.value === providerId);
        return option ? option.textContent.trim() : "Auto choose the best available API";
    }

    function getConfiguredProviderCount() {
        return Array.from(elements.providerSelect.options)
            .filter((option) => option.value !== "auto" && !option.disabled)
            .length;
    }

    function showStatus(type, message) {
        const styles = {
            success: "border-success-200 bg-success-50 text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-300",
            info: "border-gray-200 bg-gray-50 text-gray-700 dark:border-gray-700 dark:bg-gray-900/70 dark:text-gray-300",
            warning: "border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300"
        };

        elements.status.className = `mt-5 rounded-xl border px-4 py-3 text-sm ${styles[type] || styles.info}`;
        elements.status.textContent = message;
        elements.status.classList.remove("hidden");
    }

    function hideStatus() {
        elements.status.classList.add("hidden");
        elements.status.textContent = "";
    }

    function setSending(nextValue) {
        state.sending = nextValue;
        elements.sendButton.disabled = nextValue;
        elements.clearButton.disabled = nextValue;
        elements.providerSelect.disabled = nextValue;
        elements.questionCount.disabled = nextValue;
        elements.composerInput.disabled = nextValue;
        elements.currentQuestionInput.disabled = nextValue;
        elements.answerDraftInput.disabled = nextValue;

        [
            elements.sendButton,
            elements.clearButton
        ].forEach((button) => {
            button.classList.toggle("cursor-not-allowed", nextValue);
            button.classList.toggle("opacity-60", nextValue);
        });
    }

    function renderAll() {
        renderModeButtons();
        renderCategoryButtons();
        renderComposerMeta();
        renderScope();
        renderProviderHealth();
        renderQuickPrompts();
        renderConversation();
        renderResults();

        if (getConfiguredProviderCount() === 0) {
            showStatus(
                "info",
                "No external API keys are configured yet, so the page will answer with the built-in Philippine interview coach until you add provider keys."
            );
        }
    }

    function renderModeButtons() {
        elements.modeButtons.forEach((button) => {
            const isActive = String(button.dataset.chatMode || "") === state.mode;

            button.className = isActive
                ? "rounded-full bg-brand-500 px-4 py-2 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
                : "rounded-full border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]";
        });
    }

    function renderCategoryButtons() {
        elements.categoryButtons.forEach((button) => {
            const isActive = String(button.dataset.categoryId || "") === state.categoryId;

            button.className = isActive
                ? "rounded-full bg-gray-900 px-4 py-2 text-sm font-medium text-white shadow-theme-xs transition dark:bg-white dark:text-gray-900"
                : "rounded-full border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]";
        });
    }

    function renderComposerMeta() {
        const config = MODE_CONFIG[state.mode];

        elements.composerLabel.textContent = config.label === "Coach Chat"
            ? "Ask the interview coach"
            : config.composerLabel;
        elements.composerInput.placeholder = config.composerPlaceholder;
        elements.composerHelp.textContent = config.help;
        elements.sendButton.textContent = config.buttonText;

        const isQuestionMode = state.mode === "question_set";
        const isReviewMode = state.mode === "feedback_review";

        elements.questionCountWrap.classList.toggle("hidden", !isQuestionMode);
        elements.reviewFields.classList.toggle("hidden", !isReviewMode);
    }

    function renderScope() {
        const category = getSelectedCategory();

        elements.scopeTitle.textContent = category.name;
        elements.scopeDescription.textContent = category.description;
        elements.providerSummary.textContent = state.lastProviderLabel || getProviderLabel();
    }

    function renderProviderHealth() {
        const stateLabels = {
            configured: "API key detected",
            needs_key: "Needs API key",
            working: "Live check passed",
            unavailable: "Unavailable right now"
        };
        const indicatorClasses = {
            configured: "bg-brand-500",
            needs_key: "bg-amber-400",
            working: "bg-success-500",
            unavailable: "bg-error-500"
        };

        elements.providerCards.forEach((card) => {
            const providerId = String(card.dataset.providerCard || "");
            const providerHealth = state.providerHealth[providerId];

            if (!providerHealth) {
                return;
            }

            const stateElement = card.querySelector("[data-provider-state]");
            const indicatorElement = card.querySelector("[data-provider-indicator]");
            const noteElement = card.querySelector("[data-provider-note]");

            if (stateElement) {
                stateElement.textContent = stateLabels[providerHealth.state] || stateLabels.configured;
            }

            if (noteElement) {
                noteElement.textContent = providerHealth.provider
                    ? `${providerHealth.message} Provider reply: ${providerHealth.provider}.`
                    : providerHealth.message;
            }

            if (indicatorElement) {
                indicatorElement.className = `inline-flex h-3 w-3 rounded-full ${indicatorClasses[providerHealth.state] || indicatorClasses.configured}`;
            }
        });

        if (elements.providerHealthButton) {
            elements.providerHealthButton.disabled = state.providerHealthRunning;
            elements.providerHealthButton.classList.toggle("cursor-not-allowed", state.providerHealthRunning);
            elements.providerHealthButton.classList.toggle("opacity-60", state.providerHealthRunning);
            elements.providerHealthButton.textContent = state.providerHealthRunning
                ? "Checking APIs..."
                : "Run Live API Check";
        }
    }

    function renderQuickPrompts() {
        const categoryId = state.categoryId === "all" ? null : state.categoryId;
        const currentQuestion = elements.currentQuestionInput.value.trim();
        let prompts = getChatbotQuickPrompts(categoryId, currentQuestion);

        if (state.mode === "question_set") {
            prompts = [
                `Generate ${elements.questionCount.value} interview questions for ${getSelectedCategory().name}.`,
                `Make the questions harder for ${getSelectedCategory().name}.`,
                ...prompts
            ];
        }

        if (state.mode === "feedback_review") {
            prompts = [
                "Review my answer for clarity, relevance, grammar, and professionalism.",
                "Rewrite this answer so it sounds more confident and concise.",
                "What follow-up question would a Philippine interviewer ask next?",
                "Point out the weakest part of my answer first."
            ];
        }

        const uniquePrompts = Array.from(new Set(prompts.filter((prompt) => String(prompt || "").trim())));

        elements.quickPrompts.innerHTML = uniquePrompts
            .slice(0, 4)
            .map((prompt) => `
                <button
                    type="button"
                    data-prompt="${escapeHtml(prompt)}"
                    class="content-break max-w-full rounded-full border border-gray-300 px-3 py-2 text-left text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    ${escapeHtml(prompt)}
                </button>
            `)
            .join("");
    }

    function renderConversation() {
        elements.conversation.innerHTML = state.messages
            .map((message) => {
                const isAssistant = message.role === "assistant";
                const bubbleClass = isAssistant
                    ? "rounded-3xl rounded-bl-md border border-gray-200 bg-gray-50 text-gray-700 dark:border-gray-800 dark:bg-gray-900/70 dark:text-gray-200"
                    : "rounded-3xl rounded-br-md bg-brand-500 text-white";
                const alignClass = isAssistant ? "justify-start" : "justify-end";
                const rowClass = isAssistant ? "flex-row" : "flex-row-reverse";
                const providerChip = isAssistant && message.provider
                    ? `<span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-[11px] font-medium text-gray-700 shadow-theme-xs dark:bg-gray-800 dark:text-gray-300">${escapeHtml(message.provider)}</span>`
                    : "";
                const fallbackChip = isAssistant && message.usedFallback
                    ? `<span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-[11px] font-medium text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">Fallback reply</span>`
                    : "";

                return `
                    <div class="flex ${alignClass}">
                        <div class="flex ${rowClass} w-full min-w-0 max-w-4xl items-start gap-2 sm:gap-3">
                            ${renderConversationAvatar(isAssistant, profileConfig)}
                            <article class="chatbot-message-bubble content-break w-full min-w-0 px-4 py-4 shadow-theme-xs ${bubbleClass}">
                                <div class="mb-3 flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-semibold uppercase tracking-wide ${isAssistant ? "text-gray-500 dark:text-gray-400" : "text-white/80"}">
                                        ${escapeHtml(isAssistant ? profileConfig.assistantName : profileConfig.userName)}
                                    </span>
                                    ${providerChip}
                                    ${fallbackChip}
                                </div>
                                <div class="chatbot-message-text ${isAssistant ? "chatbot-message-text-ai" : ""} text-sm leading-7">${formatMessage(message.text, { isAssistant })}</div>
                            </article>
                        </div>
                    </div>
                `;
            })
            .join("");

        scrollConversationToBottom();
    }

    function renderConversationAvatar(isAssistant, profile) {
        if (isAssistant) {
            return `
                <div class="relative h-10 w-10 shrink-0">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 to-blue-light-500 text-xs font-semibold uppercase tracking-wide text-white shadow-theme-xs">
                        AI
                    </div>
                    ${renderOnlineBadge()}
                </div>
            `;
        }

        if (profile.userAvatar) {
            return `
                <div class="relative h-10 w-10 shrink-0">
                    <div class="h-10 w-10 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-700 dark:bg-gray-800">
                        <img src="${escapeHtml(profile.userAvatar)}" alt="${escapeHtml(profile.userName)}" class="h-full w-full object-cover object-top" />
                    </div>
                    ${renderOnlineBadge()}
                </div>
            `;
        }

        return `
            <div class="relative h-10 w-10 shrink-0">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gray-900 text-xs font-semibold uppercase tracking-wide text-white shadow-theme-xs dark:bg-white dark:text-gray-900">
                    ${escapeHtml(getInitials(profile.userName))}
                </div>
                ${renderOnlineBadge()}
            </div>
        `;
    }

    function renderOnlineBadge() {
        return `
            <span class="absolute -bottom-0.5 -right-0.5 flex h-3.5 w-3.5 rounded-full border-2 border-white bg-success-500 dark:border-gray-900" title="Online" aria-label="Online"></span>
        `;
    }

    function getInitials(name) {
        const parts = String(name || "")
            .trim()
            .split(/\s+/)
            .filter(Boolean)
            .slice(0, 2);

        if (parts.length === 0) {
            return "U";
        }

        return parts.map((part) => part.charAt(0)).join("");
    }

    function renderResults() {
        if (state.feedbackSummary) {
            const strengths = Array.isArray(state.feedbackSummary.strengths)
                ? state.feedbackSummary.strengths
                : [];
            const improvements = Array.isArray(state.feedbackSummary.improvements)
                ? state.feedbackSummary.improvements
                : [];
            const criteria = state.feedbackSummary.criteria || {};

            elements.resultMeta.textContent = state.feedbackSummary.provider
                ? `Latest review returned by ${state.feedbackSummary.provider}.`
                : "Latest interview answer review.";

            elements.generatedQuestions.innerHTML = "";
            elements.feedbackPanel.innerHTML = `
                <div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-4 dark:border-gray-800 dark:bg-gray-900/70">
                    <p class="chatbot-ai-copy content-break text-sm leading-6 text-gray-700 dark:text-gray-300">${escapeHtml(state.feedbackSummary.overall || "No review summary was returned.")}</p>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950/40">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white/90">Strengths</h4>
                        <ul class="mt-3 space-y-2">
                            ${strengths.map((item) => `<li class="chatbot-ai-copy content-break rounded-xl bg-success-50 px-3 py-2 text-sm leading-6 text-success-700 dark:bg-success-500/10 dark:text-success-300">${escapeHtml(item)}</li>`).join("") || `<li class="content-break rounded-xl bg-gray-50 px-3 py-2 text-sm text-gray-600 dark:bg-gray-900/70 dark:text-gray-400">No strengths returned yet.</li>`}
                        </ul>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950/40">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white/90">Improvements</h4>
                        <ul class="mt-3 space-y-2">
                            ${improvements.map((item) => `<li class="chatbot-ai-copy content-break rounded-xl bg-amber-50 px-3 py-2 text-sm leading-6 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">${escapeHtml(item)}</li>`).join("") || `<li class="content-break rounded-xl bg-gray-50 px-3 py-2 text-sm text-gray-600 dark:bg-gray-900/70 dark:text-gray-400">No improvement items returned yet.</li>`}
                        </ul>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950/40">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white/90">Criteria Notes</h4>
                    <div class="mt-3 space-y-3">
                        ${renderCriterionNote("Clarity", criteria.clarity)}
                        ${renderCriterionNote("Relevance", criteria.relevance)}
                        ${renderCriterionNote("Grammar", criteria.grammar)}
                        ${renderCriterionNote("Professionalism", criteria.professionalism)}
                    </div>
                    <div class="chatbot-ai-copy content-break mt-4 rounded-xl bg-brand-50 px-3 py-3 text-sm leading-6 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                        <strong class="font-semibold">Next step:</strong> ${escapeHtml(state.feedbackSummary.nextStep || "Keep refining your answer with a more specific example and stronger closing statement.")}
                    </div>
                </div>
            `;

            return;
        }

        if (state.generatedQuestions.length > 0) {
            elements.resultMeta.textContent = state.lastProviderLabel
                ? `Latest question set generated by ${state.lastProviderLabel}.`
                : "Latest generated interview question set.";

            elements.generatedQuestions.innerHTML = state.generatedQuestions
                .map((question, index) => `
                    <article class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-4 dark:border-gray-800 dark:bg-gray-900/70">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Question ${index + 1}</p>
                        <p class="chatbot-ai-copy content-break mt-2 text-sm leading-7 text-gray-700 dark:text-gray-300">${escapeHtml(question)}</p>
                    </article>
                `)
                .join("");
            elements.feedbackPanel.innerHTML = "";
            return;
        }

        elements.resultMeta.textContent = "Start a conversation to generate interview help from the chatbot.";
        elements.generatedQuestions.innerHTML = "";
        elements.feedbackPanel.innerHTML = "";
    }

    function renderCriterionNote(label, value) {
        return `
            <div class="chatbot-ai-copy content-break rounded-xl bg-gray-50 px-3 py-3 text-sm leading-6 text-gray-700 dark:bg-gray-900/70 dark:text-gray-300">
                <strong class="font-semibold">${escapeHtml(label)}:</strong> ${escapeHtml(value || "No note returned yet.")}
            </div>
        `;
    }

    async function runProviderHealthCheck() {
        if (state.providerHealthRunning) {
            return;
        }

        const providerIds = elements.providerCards
            .map((card) => String(card.dataset.providerCard || ""))
            .filter(Boolean);

        if (providerIds.length === 0) {
            return;
        }

        state.providerHealthRunning = true;

        if (elements.providerHealthNote) {
            elements.providerHealthNote.textContent = "Running a quick backend check against each configured provider. This can take a few seconds.";
        }

        renderProviderHealth();

        try {
            const payload = await requestWorkspace("chatbotProvidersStatus", {
                method: "POST",
                body: {
                    providers: providerIds
                }
            });
            const statuses = Array.isArray(payload.providers) ? payload.providers : [];

            statuses.forEach((item) => {
                const providerId = String(item?.id || "");

                if (!providerId) {
                    return;
                }

                state.providerHealth[providerId] = {
                    configured: Boolean(item.configured),
                    state: String(item.state || (item.configured ? "configured" : "needs_key")),
                    message: String(item.message || ""),
                    provider: item.provider ? String(item.provider) : null
                };
            });

            const configuredCount = statuses.filter((item) => Boolean(item.configured)).length;
            const workingCount = statuses.filter((item) => String(item.state || "") === "working").length;

            if (elements.providerHealthNote) {
                elements.providerHealthNote.textContent = configuredCount === 0
                    ? "No provider API keys are configured yet. Add the keys in .env first."
                    : `${workingCount} of ${configuredCount} configured APIs responded during the latest live check.`;
            }

            if (configuredCount === 0) {
                showStatus("info", "No provider API keys are configured yet. The chatbot will keep using the local PH coach fallback.");
            } else if (workingCount > 0) {
                showStatus("success", `${workingCount} configured API${workingCount === 1 ? "" : "s"} passed the live chatbot check.`);
            } else {
                showStatus("warning", "The configured APIs did not return a usable live response right now. You can still use the local PH coach fallback.");
            }
        } catch (error) {
            console.error(error);

            if (elements.providerHealthNote) {
                elements.providerHealthNote.textContent = "The live provider check could not be completed right now.";
            }

            showStatus(
                "warning",
                error instanceof Error && error.message
                    ? error.message
                    : "The live provider check could not be completed."
            );
        } finally {
            state.providerHealthRunning = false;
            renderProviderHealth();
        }
    }

    async function submitPrompt() {
        if (state.sending) {
            return;
        }

        const request = buildRequest();

        if (!request) {
            return;
        }

        hideStatus();
        state.generatedQuestions = [];
        state.feedbackSummary = null;
        state.messages.push({
            role: "user",
            text: request.displayMessage
        });
        renderConversation();
        renderResults();
        setSending(true);

        try {
            const payload = await requestWorkspace("chatbot", {
                method: "POST",
                body: request.payload
            });

            state.lastProviderLabel = String(payload.provider || "") || getProviderLabel();
            state.generatedQuestions = Array.isArray(payload.generatedQuestions)
                ? payload.generatedQuestions.filter((item) => String(item || "").trim())
                : [];
            state.feedbackSummary = payload.feedbackSummary && typeof payload.feedbackSummary === "object"
                ? payload.feedbackSummary
                : null;

            state.messages.push({
                role: "assistant",
                text: String(payload.reply || "The interview chatbot returned an empty response."),
                provider: state.lastProviderLabel,
                usedFallback: Boolean(payload.usedFallback)
            });

            elements.composerInput.value = "";
            renderScope();
            renderConversation();
            renderResults();

            if (payload.usedFallback) {
                const requestedProviderId = String(payload.requestedProviderId || state.providerId || "auto");
                const requestedProviderLabel = getProviderLabel(requestedProviderId);
                const resolvedProviderLabel = String(payload.provider || "Local PH coach");

                showStatus(
                    "info",
                    requestedProviderId !== "auto" && requestedProviderId !== "local"
                        ? `${requestedProviderLabel} is unavailable right now, so ${resolvedProviderLabel} replied instead.`
                        : `${resolvedProviderLabel} answered using the Philippine fallback coach because the requested API was unavailable or not configured.`
                );
            }
        } catch (error) {
            console.error(error);
            state.messages.push({
                role: "assistant",
                text: "The chatbot could not complete that request right now. Please try again in a moment.",
                provider: "InterviewPilot PH Coach",
                usedFallback: true
            });
            renderConversation();
            showStatus(
                "warning",
                error instanceof Error && error.message
                    ? error.message
                    : "The chatbot request could not be completed."
            );
        } finally {
            setSending(false);
        }
    }

    function buildRequest() {
        const message = elements.composerInput.value.trim();
        const categoryId = state.categoryId === "all" ? null : state.categoryId;
        const history = serializeHistory();

        if (state.mode === "chat") {
            if (!message) {
                showStatus("warning", "Add an interview question or coaching request first.");
                elements.composerInput.focus();
                return null;
            }

            return {
                displayMessage: message,
                payload: {
                    message,
                    providerId: state.providerId,
                    categoryId,
                    history
                }
            };
        }

        if (state.mode === "question_set") {
            const questionCount = Number(elements.questionCount.value || 5);
            const displayMessage = message || `Generate ${questionCount} interview questions for ${getSelectedCategory().name}.`;

            return {
                displayMessage,
                payload: {
                    message: displayMessage,
                    mode: "question_set",
                    questionCount,
                    providerId: state.providerId,
                    categoryId,
                    history
                }
            };
        }

        const currentQuestion = elements.currentQuestionInput.value.trim();
        const answerDraft = elements.answerDraftInput.value.trim();

        if (!currentQuestion || !answerDraft) {
            showStatus("warning", "Add both the interview question and your draft answer before requesting a review.");

            if (!currentQuestion) {
                elements.currentQuestionInput.focus();
            } else {
                elements.answerDraftInput.focus();
            }

            return null;
        }

        const displayMessage = message || "Review this interview answer for clarity, relevance, grammar, and professionalism.";

        return {
            displayMessage,
            payload: {
                message: displayMessage,
                mode: "feedback_review",
                providerId: state.providerId,
                categoryId,
                currentQuestion,
                answerDraft,
                history
            }
        };
    }

    function serializeHistory() {
        return state.messages
            .slice(-8)
            .map((message) => ({
                role: message.role,
                text: String(message.text || "").slice(0, 2000)
            }));
    }

    function escapeHtml(value) {
        return String(value ?? "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");
    }

    function formatMessage(text, options = {}) {
        const normalizedText = options.isAssistant
            ? normalizeAssistantMessage(text)
            : normalizeMessageText(text);
        const blocks = normalizedText
            .split(/\n{2,}/)
            .map((block) => block.trim())
            .filter(Boolean);

        if (blocks.length === 0) {
            return "";
        }

        return blocks
            .map((block) => {
                const lines = block
                    .split(/\n/)
                    .map((line) => line.trim())
                    .filter(Boolean);

                if (lines.length === 0) {
                    return "";
                }

                return `<p>${lines.map(formatInlineMarkdown).join("<br>")}</p>`;
            })
            .join("");
    }

    function normalizeMessageText(text) {
        return String(text ?? "")
            .replace(/\r\n?/g, "\n")
            .trim();
    }

    function normalizeAssistantMessage(text) {
        return normalizeMessageText(text)
            .replace(/\s+(\d+\.\s+\*\*)/g, "\n\n$1")
            .replace(/(^|\s+)(\d{1,2}\s*[\.)]\s+(?=(?:\*\*)?[A-Z]))/g, "\n\n$2")
            .replace(/\s+([*-]\s*(?:Purpose|Example Answer|Sample Answer|Suggested Answer|Better Answer|Why(?: it[\u2019']s)? asked|Philippine context|How to answer|Follow[- ]?up|Next step|Strengths?|Improvements?|Clarity|Relevance|Grammar|Professionalism|Tip):)/gi, "\n$1")
            .replace(/\s+(\*\*(?:Purpose|Example Answer|Sample Answer|Suggested Answer|Better Answer|Why(?: it[\u2019']s)? asked|Philippine context|How to answer|Follow[- ]?up|Next step|Strengths?|Improvements?|Clarity|Relevance|Grammar|Professionalism|Tip):\*\*)/gi, "\n$1")
            .replace(/\s+(\*\*(?:Why(?: it[\u2019']s)? asked|Philippine context|How to answer|Sample answer|Suggested answer|Better answer|Follow[- ]?up|Next step|Strengths?|Improvements?|Clarity|Relevance|Grammar|Professionalism|Tip):\*\*)/gi, "\n\n$1")
            .replace(/\s+(\*\*(?:Why(?: it['’]s)? asked|Philippine context|How to answer|Sample answer|Suggested answer|Better answer|Follow[- ]?up|Next step|Strengths?|Improvements?|Clarity|Relevance|Grammar|Professionalism|Tip):\*\*)/gi, "\n$1")
            .replace(/\s+(-\s+\*\*)/g, "\n$1")
            .replace(/\n(\*\*(?:Why(?: it[\u2019']s)? asked|Philippine context|How to answer|Sample answer|Suggested answer|Better answer|Follow[- ]?up|Next step|Strengths?|Improvements?|Clarity|Relevance|Grammar|Professionalism|Tip):\*\*)/gi, "\n\n$1")
            .replace(/\n(-\s+\*\*)/g, "\n\n$1")
            .replace(/\n{3,}/g, "\n\n")
            .trim();
    }

    function formatInlineMarkdown(text) {
        const value = String(text ?? "");
        const labelMatch = value.match(/^(?:[*-]\s*)?(Purpose|Example Answer|Sample Answer|Suggested Answer|Better Answer|Why(?: it[\u2019']s)? asked|Philippine context|How to answer|Follow[- ]?up|Next step|Strengths?|Improvements?|Clarity|Relevance|Grammar|Professionalism|Tip):\s*/i);

        if (labelMatch) {
            const rest = value.slice(labelMatch[0].length);

            return `<strong>${escapeHtml(labelMatch[1])}:</strong> ${formatInlineBold(rest)}`;
        }

        return formatInlineBold(value);
    }

    function formatInlineBold(text) {
        return escapeHtml(text)
            .replace(/\*\*([^*]+)\*\*/g, "<strong>$1</strong>");
    }

    function scrollConversationToBottom() {
        elements.conversation.scrollTop = elements.conversation.scrollHeight;
    }
}
