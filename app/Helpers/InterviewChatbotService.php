<?php

namespace App\Helpers;

use App\Support\InterviewPracticeCatalog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class InterviewChatbotService
{
    protected const REMOTE_PROVIDER_IDS = [
        'gemini',
        'groq',
        'openrouter',
        'wisdomgate',
        'cohere',
    ];

    protected array $lastProviderErrors = [];

    public function frontendBootstrap(): array
    {
        $providers = $this->providerDefinitions();

        return [
            'defaultProviderId' => $this->defaultProviderId($providers),
            'providers' => array_values($providers),
        ];
    }

    public function providerStatuses(?array $requestedProviderIds = null): array
    {
        $bootstrap = $this->frontendBootstrap();
        $providers = collect($bootstrap['providers'])->keyBy('id')->all();
        $providerIds = collect($requestedProviderIds ?? self::REMOTE_PROVIDER_IDS)
            ->map(fn (mixed $providerId) => $this->normalizeProviderId($providerId))
            ->filter(fn (?string $providerId) => $providerId !== null && in_array($providerId, self::REMOTE_PROVIDER_IDS, true))
            ->unique()
            ->values()
            ->all();

        $probeContext = InterviewPracticeCatalog::chatbotCategoryContext('job');
        $statuses = [];

        foreach ($providerIds as $providerId) {
            $provider = $providers[$providerId] ?? null;

            if (! is_array($provider)) {
                continue;
            }

            $isConfigured = (bool) ($provider['configured'] ?? false);
            $status = [
                'id' => $providerId,
                'label' => (string) ($provider['label'] ?? $providerId),
                'configured' => $isConfigured,
                'model' => (string) ($provider['model'] ?? ''),
                'state' => $isConfigured ? 'configured' : 'needs_key',
                'message' => $isConfigured
                    ? 'Configured in .env. Run a live check to confirm a real provider response.'
                    : 'Add the API key in .env before using this provider.',
                'provider' => null,
            ];

            if (! $isConfigured) {
                $statuses[] = $status;
                continue;
            }

            $this->forgetProviderError($providerId);

            try {
                $reply = $this->requestProviderReply(
                    $providerId,
                    'Reply with only READY. This is a Philippine job interview chatbot connectivity check.',
                    $probeContext,
                    null,
                    null,
                    []
                );

                if ($reply !== null) {
                    $status['state'] = 'working';
                    $status['message'] = 'Live API check passed and returned a response.';
                    $status['provider'] = $reply['provider'] ?? null;
                } else {
                    $status['state'] = 'unavailable';
                    $status['message'] = $this->providerProbeFailureMessage($providerId);
                }
            } catch (Throwable $error) {
                $this->recordProviderThrowable($providerId, $error);
                $this->safeReport($error);
                $status['state'] = 'unavailable';
                $status['message'] = $this->providerProbeFailureMessage($providerId);
            }

            $statuses[] = $status;
        }

        return $statuses;
    }

    public function reply(array $input): array
    {
        $bootstrap = $this->frontendBootstrap();
        $providers = collect($bootstrap['providers'])->keyBy('id')->all();
        $mode = $this->normalizeMode($input['mode'] ?? null);
        $requestedQuestionCount = $this->normalizeQuestionCount($input['questionCount'] ?? null);

        $message = $this->sanitizeText($input['message'] ?? null, 2000) ?? '';
        $categoryId = $this->normalizeCategoryId($input['categoryId'] ?? null);
        $currentQuestion = $this->sanitizeText($input['currentQuestion'] ?? null, 4000);
        $answerDraft = $this->sanitizeText($input['answerDraft'] ?? null, 8000);
        $criteriaScores = $this->normalizeCriteriaScores($input['criteriaScores'] ?? []);
        $history = $this->normalizeHistory($input['history'] ?? []);
        $requestedProviderId = $this->normalizeRequestedProviderId($input['providerId'] ?? null, $providers, $bootstrap['defaultProviderId']);
        $context = InterviewPracticeCatalog::chatbotCategoryContext($categoryId);
        $suggestions = InterviewPracticeCatalog::chatbotQuickPrompts($categoryId, $currentQuestion);

        if ($mode === 'question_set') {
            return $this->generateQuestionSetReply(
                message: $message,
                requestedQuestionCount: $requestedQuestionCount,
                requestedProviderId: $requestedProviderId,
                providers: $providers,
                context: $context,
                suggestions: $suggestions,
                availableProviders: $bootstrap['providers'],
            );
        }

        if ($mode === 'field_builder') {
            return $this->generateFieldBuilderReply(
                message: $message,
                requestedProviderId: $requestedProviderId,
                providers: $providers,
                context: $context,
                suggestions: $suggestions,
                availableProviders: $bootstrap['providers'],
            );
        }

        if ($mode === 'feedback_review') {
            return $this->generateFeedbackReviewReply(
                message: $message,
                requestedProviderId: $requestedProviderId,
                providers: $providers,
                context: $context,
                suggestions: $suggestions,
                availableProviders: $bootstrap['providers'],
                currentQuestion: $currentQuestion,
                answerDraft: $answerDraft,
                criteriaScores: $criteriaScores,
            );
        }

        if ($message === '') {
            return $this->buildResponsePayload(
                reply: 'Ask me about Philippine interview practice, and I will stay within the local interview categories on this page.',
                providerId: 'local',
                providerLabel: 'Local PH coach',
                requestedProviderId: $requestedProviderId,
                usedFallback: true,
                suggestions: $suggestions,
                availableProviders: $bootstrap['providers'],
            );
        }

        foreach ($this->providerAttemptOrder($requestedProviderId, $providers) as $providerId) {
            if ($providerId === 'local') {
                break;
            }

            try {
                $reply = $this->requestProviderReply($providerId, $message, $context, $currentQuestion, $answerDraft, $history);

                if ($reply !== null) {
                    return $this->buildResponsePayload(
                        reply: $reply['reply'],
                        providerId: $reply['providerId'],
                        providerLabel: $reply['provider'],
                        requestedProviderId: $requestedProviderId,
                        usedFallback: false,
                        suggestions: $suggestions,
                        availableProviders: $bootstrap['providers'],
                    );
                }
            } catch (Throwable $error) {
                $this->recordProviderThrowable($providerId, $error);
                $this->safeReport($error);
            }
        }

        return $this->buildResponsePayload(
            reply: $this->buildLocalReply($message, $context, $currentQuestion, $answerDraft),
            providerId: 'local',
            providerLabel: 'Local PH coach',
            requestedProviderId: $requestedProviderId,
            usedFallback: true,
            suggestions: $suggestions,
            availableProviders: $bootstrap['providers'],
        );
    }

    protected function buildResponsePayload(
        string $reply,
        string $providerId,
        string $providerLabel,
        string $requestedProviderId,
        bool $usedFallback,
        array $suggestions,
        array $availableProviders,
        array $generatedQuestions = [],
        array $feedbackSummary = [],
        array $fieldPlan = []
    ): array {
        return [
            'reply' => $reply,
            'provider' => $providerLabel,
            'providerId' => $providerId,
            'requestedProviderId' => $requestedProviderId,
            'usedFallback' => $usedFallback,
            'suggestions' => $suggestions,
            'availableProviders' => $availableProviders,
            'generatedQuestions' => array_values($generatedQuestions),
            'feedbackSummary' => $feedbackSummary,
            'fieldPlan' => $fieldPlan,
        ];
    }

    protected function normalizeMode(mixed $value): string
    {
        return match ($value) {
            'question_set' => 'question_set',
            'field_builder' => 'field_builder',
            'feedback_review' => 'feedback_review',
            default => 'chat',
        };
    }

    protected function normalizeQuestionCount(mixed $value): int
    {
        $count = (int) $value;

        return min(20, max(1, $count > 0 ? $count : 3));
    }

    protected function normalizeCriteriaScores(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return [
            'clarity' => round(max(0, min(10, (float) ($value['clarity'] ?? 0))), 1),
            'relevance' => round(max(0, min(10, (float) ($value['relevance'] ?? 0))), 1),
            'grammar' => round(max(0, min(10, (float) ($value['grammar'] ?? 0))), 1),
            'professionalism' => round(max(0, min(10, (float) ($value['professionalism'] ?? 0))), 1),
            'average' => round(max(0, min(10, (float) ($value['average'] ?? 0))), 1),
            'matchedKeywords' => max(0, min(999, (int) ($value['matchedKeywords'] ?? 0))),
        ];
    }

    protected function generateQuestionSetReply(
        string $message,
        int $requestedQuestionCount,
        string $requestedProviderId,
        array $providers,
        array $context,
        array $suggestions,
        array $availableProviders
    ): array {
        $generationPrompt = $this->buildQuestionSetPrompt($context, $requestedQuestionCount, $message);

        foreach ($this->providerAttemptOrder($requestedProviderId, $providers) as $providerId) {
            if ($providerId === 'local') {
                break;
            }

            try {
                $reply = $this->requestProviderReply($providerId, $generationPrompt, $context, null, null, []);
                $questions = $this->extractGeneratedQuestions($reply['reply'] ?? null, $requestedQuestionCount);

                if ($reply !== null && count($questions) > 0) {
                    return $this->buildResponsePayload(
                        reply: $this->buildQuestionSetSummary($questions, $context, $reply['provider']),
                        providerId: $reply['providerId'],
                        providerLabel: $reply['provider'],
                        requestedProviderId: $requestedProviderId,
                        usedFallback: false,
                        suggestions: $suggestions,
                        availableProviders: $availableProviders,
                        generatedQuestions: $questions,
                    );
                }
            } catch (Throwable $error) {
                $this->recordProviderThrowable($providerId, $error);
                $this->safeReport($error);
            }
        }

        $questions = $this->buildLocalQuestionSet($context, $requestedQuestionCount, $message);

        return $this->buildResponsePayload(
            reply: $this->buildQuestionSetSummary($questions, $context, 'Local PH coach'),
            providerId: 'local',
            providerLabel: 'Local PH coach',
            requestedProviderId: $requestedProviderId,
            usedFallback: true,
            suggestions: $suggestions,
            availableProviders: $availableProviders,
            generatedQuestions: $questions,
        );
    }

    protected function generateFieldBuilderReply(
        string $message,
        string $requestedProviderId,
        array $providers,
        array $context,
        array $suggestions,
        array $availableProviders
    ): array {
        $fieldBuilderPrompt = $this->buildFieldBuilderPrompt($context, $message);

        foreach ($this->providerAttemptOrder($requestedProviderId, $providers) as $providerId) {
            if ($providerId === 'local') {
                break;
            }

            try {
                $reply = $this->requestProviderReply($providerId, $fieldBuilderPrompt, $context, null, null, []);
                $fieldPlan = $this->extractFieldPlan($reply['reply'] ?? null, $context);

                if ($reply !== null && $fieldPlan !== []) {
                    return $this->buildResponsePayload(
                        reply: $this->buildFieldPlanSummary($fieldPlan, $context, $reply['provider']),
                        providerId: $reply['providerId'],
                        providerLabel: $reply['provider'],
                        requestedProviderId: $requestedProviderId,
                        usedFallback: false,
                        suggestions: $suggestions,
                        availableProviders: $availableProviders,
                        fieldPlan: $fieldPlan,
                    );
                }
            } catch (Throwable $error) {
                $this->recordProviderThrowable($providerId, $error);
                $this->safeReport($error);
            }
        }

        $fieldPlan = $this->buildLocalFieldPlan($context, $message);

        return $this->buildResponsePayload(
            reply: $this->buildFieldPlanSummary($fieldPlan, $context, 'Local PH coach'),
            providerId: 'local',
            providerLabel: 'Local PH coach',
            requestedProviderId: $requestedProviderId,
            usedFallback: true,
            suggestions: $suggestions,
            availableProviders: $availableProviders,
            fieldPlan: $fieldPlan,
        );
    }

    protected function generateFeedbackReviewReply(
        string $message,
        string $requestedProviderId,
        array $providers,
        array $context,
        array $suggestions,
        array $availableProviders,
        ?string $currentQuestion,
        ?string $answerDraft,
        array $criteriaScores
    ): array {
        $feedbackPrompt = $this->buildFeedbackReviewPrompt($context, $message, $currentQuestion, $answerDraft, $criteriaScores);

        foreach ($this->providerAttemptOrder($requestedProviderId, $providers) as $providerId) {
            if ($providerId === 'local') {
                break;
            }

            try {
                $reply = $this->requestProviderReply($providerId, $feedbackPrompt, $context, $currentQuestion, $answerDraft, []);
                $feedbackSummary = $this->extractFeedbackSummary($reply['reply'] ?? null, $criteriaScores);

                if ($reply !== null && $feedbackSummary !== []) {
                    $feedbackSummary['provider'] = $reply['provider'];

                    return $this->buildResponsePayload(
                        reply: $feedbackSummary['overall'],
                        providerId: $reply['providerId'],
                        providerLabel: $reply['provider'],
                        requestedProviderId: $requestedProviderId,
                        usedFallback: false,
                        suggestions: $suggestions,
                        availableProviders: $availableProviders,
                        feedbackSummary: $feedbackSummary,
                    );
                }
            } catch (Throwable $error) {
                $this->recordProviderThrowable($providerId, $error);
                $this->safeReport($error);
            }
        }

        $feedbackSummary = $this->buildLocalFeedbackSummary($context, $currentQuestion, $answerDraft, $criteriaScores);
        $feedbackSummary['provider'] = 'Local PH coach';

        return $this->buildResponsePayload(
            reply: $feedbackSummary['overall'],
            providerId: 'local',
            providerLabel: 'Local PH coach',
            requestedProviderId: $requestedProviderId,
            usedFallback: true,
            suggestions: $suggestions,
            availableProviders: $availableProviders,
            feedbackSummary: $feedbackSummary,
        );
    }

    protected function providerDefinitions(): array
    {
        return [
            'auto' => [
                'id' => 'auto',
                'label' => 'Auto',
                'description' => 'Try configured AI APIs in priority order, then fall back to the local PH coach.',
                'configured' => true,
                'type' => 'router',
                'model' => null,
            ],
            'gemini' => [
                'id' => 'gemini',
                'label' => 'Gemini API',
                'description' => 'Google Gemini through the Gemini API.',
                'configured' => $this->hasApiKey(config('services.gemini.api_key')),
                'type' => 'remote',
                'model' => (string) config('services.gemini.model', 'gemini-2.5-flash'),
            ],
            'groq' => [
                'id' => 'groq',
                'label' => 'Groq API',
                'description' => 'Groq OpenAI-compatible chat completions.',
                'configured' => $this->hasApiKey(config('services.groq.api_key')),
                'type' => 'remote',
                'model' => (string) config('services.groq.model', 'openai/gpt-oss-20b'),
            ],
            'openrouter' => [
                'id' => 'openrouter',
                'label' => 'OpenRouter API',
                'description' => 'OpenRouter unified chat completions with support for free models.',
                'configured' => $this->hasApiKey(config('services.openrouter.api_key')),
                'type' => 'remote',
                'model' => (string) config('services.openrouter.model', 'openrouter/free'),
            ],
            'wisdomgate' => [
                'id' => 'wisdomgate',
                'label' => 'Wisdom Gate API',
                'description' => 'Wisdom Gate chat completions through a configurable OpenAI-compatible endpoint.',
                'configured' => $this->hasApiKey(config('services.wisdomgate.api_key')),
                'type' => 'remote',
                'model' => (string) config('services.wisdomgate.model', 'wisdom-ai-dsv3'),
            ],
            'cohere' => [
                'id' => 'cohere',
                'label' => 'Cohere API',
                'description' => 'Cohere chat completions for conversational coaching.',
                'configured' => $this->hasApiKey(config('services.cohere.api_key')),
                'type' => 'remote',
                'model' => (string) config('services.cohere.model', 'command-r7b-12-2024'),
            ],
            'local' => [
                'id' => 'local',
                'label' => 'Local PH coach',
                'description' => 'Built-in fallback that stays on Philippine interview coaching without external APIs.',
                'configured' => true,
                'type' => 'fallback',
                'model' => null,
            ],
        ];
    }

    protected function defaultProviderId(array $providers): string
    {
        $candidate = $this->normalizeProviderId(config('services.interview_chatbot.default_provider'));

        if ($candidate === null || ! isset($providers[$candidate])) {
            return 'auto';
        }

        if (($providers[$candidate]['type'] ?? null) === 'remote' && ! ($providers[$candidate]['configured'] ?? false)) {
            return 'auto';
        }

        return $candidate;
    }

    protected function normalizeRequestedProviderId(mixed $value, array $providers, string $defaultProviderId): string
    {
        $candidate = $this->normalizeProviderId($value);

        if ($candidate === null || ! isset($providers[$candidate])) {
            return $defaultProviderId;
        }

        return $candidate;
    }

    protected function normalizeProviderId(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $providerId = Str::of($value)->trim()->lower()->replace([' ', '-'], '_')->toString();
        $providerId = $providerId !== '' ? str_replace('_', '', $providerId) : '';

        if ($providerId === 'huggingface') {
            return 'wisdomgate';
        }

        return $providerId !== '' ? $providerId : null;
    }

    protected function providerAttemptOrder(string $requestedProviderId, array $providers): array
    {
        $configuredRemoteProviders = $this->configuredRemoteProviderIds($providers);

        if ($requestedProviderId === 'local') {
            return ['local'];
        }

        if ($requestedProviderId !== 'auto' && in_array($requestedProviderId, self::REMOTE_PROVIDER_IDS, true)) {
            if (($providers[$requestedProviderId]['configured'] ?? false) === true) {
                return [$requestedProviderId, 'local'];
            }

            return ['local'];
        }

        return [...$configuredRemoteProviders, 'local'];
    }

    protected function configuredRemoteProviderIds(array $providers): array
    {
        $priority = $this->configuredPriorityOrder();
        $configured = collect(self::REMOTE_PROVIDER_IDS)
            ->filter(fn (string $providerId) => ($providers[$providerId]['configured'] ?? false) === true)
            ->values()
            ->all();

        $ordered = [];

        foreach ($priority as $providerId) {
            if (in_array($providerId, $configured, true)) {
                $ordered[] = $providerId;
            }
        }

        foreach ($configured as $providerId) {
            if (! in_array($providerId, $ordered, true)) {
                $ordered[] = $providerId;
            }
        }

        return $ordered;
    }

    protected function configuredPriorityOrder(): array
    {
        $raw = (string) config('services.interview_chatbot.provider_priority', 'gemini,groq,openrouter,wisdomgate,cohere');
        $priority = collect(explode(',', $raw))
            ->map(fn (string $item) => $this->normalizeProviderId($item))
            ->filter(fn (?string $providerId) => $providerId !== null && in_array($providerId, self::REMOTE_PROVIDER_IDS, true))
            ->values()
            ->all();

        return $priority !== [] ? $priority : self::REMOTE_PROVIDER_IDS;
    }

    protected function requestProviderReply(
        string $providerId,
        string $message,
        array $context,
        ?string $currentQuestion,
        ?string $answerDraft,
        array $history
    ): ?array {
        $this->forgetProviderError($providerId);

        return match ($providerId) {
            'gemini' => $this->requestGeminiReply($message, $context, $currentQuestion, $answerDraft, $history),
            'groq' => $this->requestGroqReply($message, $context, $currentQuestion, $answerDraft, $history),
            'openrouter' => $this->requestOpenRouterReply($message, $context, $currentQuestion, $answerDraft, $history),
            'wisdomgate' => $this->requestWisdomGateReply($message, $context, $currentQuestion, $answerDraft, $history),
            'cohere' => $this->requestCohereReply($message, $context, $currentQuestion, $answerDraft, $history),
            default => null,
        };
    }

    protected function requestGeminiReply(
        string $message,
        array $context,
        ?string $currentQuestion,
        ?string $answerDraft,
        array $history
    ): ?array {
        if (! $this->hasApiKey(config('services.gemini.api_key'))) {
            return null;
        }

        $model = (string) config('services.gemini.model', 'gemini-2.5-flash');
        $apiKey = (string) config('services.gemini.api_key');
        $url = sprintf('https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent', $model);
        $contents = [];

        foreach ($history as $item) {
            $contents[] = [
                'role' => $item['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [
                    ['text' => $item['text']],
                ],
            ];
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [
                [
                    'text' => $this->buildProviderUserPrompt($message, $context, $currentQuestion, $answerDraft),
                ],
            ],
        ];

        $response = Http::timeout(20)
            ->acceptJson()
            ->withHeaders([
                'x-goog-api-key' => $apiKey,
            ])
            ->post($url, [
                'system_instruction' => [
                    'parts' => [
                        ['text' => InterviewPracticeCatalog::chatbotSystemInstruction()],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.4,
                    'topP' => 0.85,
                    'maxOutputTokens' => 600,
                ],
                'contents' => $contents,
            ]);

        if (! $response->successful()) {
            $this->recordProviderError(
                'gemini',
                data_get($response->json(), 'error.message') ?: $response->body(),
                $response->status()
            );
            return null;
        }

        $reply = $this->sanitizeReply(
            $this->collectTextFromItems(data_get($response->json(), 'candidates.0.content.parts', []), 'text')
        );

        if ($reply === null) {
            $this->recordProviderError('gemini', 'The provider returned an empty response.', $response->status());
            return null;
        }

        $responseModel = $this->sanitizeText(data_get($response->json(), 'modelVersion'), 120) ?? $model;

        return [
            'reply' => $reply,
            'provider' => sprintf('Gemini API (%s)', $responseModel),
            'providerId' => 'gemini',
        ];
    }

    protected function requestGroqReply(
        string $message,
        array $context,
        ?string $currentQuestion,
        ?string $answerDraft,
        array $history
    ): ?array {
        return $this->requestOpenAiCompatibleReply(
            providerId: 'groq',
            providerLabel: 'Groq API',
            url: 'https://api.groq.com/openai/v1/chat/completions',
            apiKey: (string) config('services.groq.api_key'),
            model: (string) config('services.groq.model', 'openai/gpt-oss-20b'),
            message: $message,
            context: $context,
            currentQuestion: $currentQuestion,
            answerDraft: $answerDraft,
            history: $history,
        );
    }

    protected function requestOpenRouterReply(
        string $message,
        array $context,
        ?string $currentQuestion,
        ?string $answerDraft,
        array $history
    ): ?array {
        return $this->requestOpenAiCompatibleReply(
            providerId: 'openrouter',
            providerLabel: 'OpenRouter API',
            url: 'https://openrouter.ai/api/v1/chat/completions',
            apiKey: (string) config('services.openrouter.api_key'),
            model: (string) config('services.openrouter.model', 'openrouter/free'),
            message: $message,
            context: $context,
            currentQuestion: $currentQuestion,
            answerDraft: $answerDraft,
            history: $history,
            headers: [
                'HTTP-Referer' => rtrim((string) config('app.url', 'http://localhost'), '/'),
                'X-Title' => (string) config('app.name', 'InterviewPilot'),
            ],
        );
    }

    protected function requestWisdomGateReply(
        string $message,
        array $context,
        ?string $currentQuestion,
        ?string $answerDraft,
        array $history
    ): ?array {
        $apiKey = (string) config('services.wisdomgate.api_key');

        if (! $this->hasApiKey($apiKey)) {
            return null;
        }

        $configuredModel = $this->sanitizeText((string) config('services.wisdomgate.model', 'wisdom-ai-dsv3'), 120);
        $configuredUrl = $this->sanitizeText((string) config('services.wisdomgate.base_url', 'https://wisgate.ai/v1/chat/completions'), 255);
        $candidateUrls = array_values(array_unique(array_filter([
            $configuredUrl,
            'https://wisgate.ai/v1/chat/completions',
            'https://api.wisgate.ai/v1/chat/completions',
            'https://wisdom-gate.juheapi.com/v1/chat/completions',
        ])));
        $candidateModels = array_values(array_unique(array_filter([
            $configuredModel,
            'gpt-5',
            'wisdom-ai-dsv3',
            'gemini-2.5-flash',
            'deepseek-r1',
        ])));

        foreach ($candidateUrls as $url) {
            foreach ($candidateModels as $model) {
                try {
                    $reply = $this->requestOpenAiCompatibleReply(
                        providerId: 'wisdomgate',
                        providerLabel: 'Wisdom Gate API',
                        url: $url,
                        apiKey: $apiKey,
                        model: $model,
                        message: $message,
                        context: $context,
                        currentQuestion: $currentQuestion,
                        answerDraft: $answerDraft,
                        history: $history,
                    );

                    if ($reply !== null) {
                        return $reply;
                    }
                } catch (Throwable $error) {
                    $this->recordProviderThrowable('wisdomgate', $error);
                }
            }
        }

        return null;
    }

    protected function requestOpenAiCompatibleReply(
        string $providerId,
        string $providerLabel,
        string $url,
        string $apiKey,
        string $model,
        string $message,
        array $context,
        ?string $currentQuestion,
        ?string $answerDraft,
        array $history,
        array $headers = []
    ): ?array {
        if (! $this->hasApiKey($apiKey)) {
            return null;
        }

        $response = Http::timeout(20)
            ->acceptJson()
            ->withToken($apiKey)
            ->withHeaders($headers)
            ->post($url, [
                'model' => $model,
                'messages' => $this->buildOpenAiCompatibleMessages($message, $context, $currentQuestion, $answerDraft, $history),
                'temperature' => 0.4,
                'max_tokens' => 600,
            ]);

        if (! $response->successful()) {
            $this->recordProviderError(
                $providerId,
                data_get($response->json(), 'error.message')
                    ?: data_get($response->json(), 'message')
                    ?: $response->body(),
                $response->status()
            );
            return null;
        }

        $reply = $this->sanitizeReply($this->extractOpenAiCompatibleReply($response->json()));

        if ($reply === null) {
            $this->recordProviderError($providerId, 'The provider returned an empty response.', $response->status());
            return null;
        }

        $responseModel = $this->sanitizeText(data_get($response->json(), 'model'), 120) ?? $model;

        return [
            'reply' => $reply,
            'provider' => sprintf('%s (%s)', $providerLabel, $responseModel),
            'providerId' => $providerId,
        ];
    }

    protected function requestCohereReply(
        string $message,
        array $context,
        ?string $currentQuestion,
        ?string $answerDraft,
        array $history
    ): ?array {
        $apiKey = (string) config('services.cohere.api_key');

        if (! $this->hasApiKey($apiKey)) {
            return null;
        }

        $model = (string) config('services.cohere.model', 'command-r7b-12-2024');

        $response = Http::timeout(20)
            ->acceptJson()
            ->withToken($apiKey)
            ->withHeaders([
                'X-Client-Name' => (string) config('app.name', 'InterviewPilot'),
            ])
            ->post('https://api.cohere.com/v2/chat', [
                'model' => $model,
                'stream' => false,
                'messages' => $this->buildOpenAiCompatibleMessages($message, $context, $currentQuestion, $answerDraft, $history),
                'temperature' => 0.4,
                'max_tokens' => 600,
            ]);

        if (! $response->successful()) {
            $this->recordProviderError(
                'cohere',
                data_get($response->json(), 'message') ?: $response->body(),
                $response->status()
            );
            return null;
        }

        $reply = $this->sanitizeReply(
            $this->collectTextFromItems(data_get($response->json(), 'message.content', []), 'text')
        );

        if ($reply === null) {
            $this->recordProviderError('cohere', 'The provider returned an empty response.', $response->status());
            return null;
        }

        $responseModel = $this->sanitizeText(data_get($response->json(), 'model'), 120) ?? $model;

        return [
            'reply' => $reply,
            'provider' => sprintf('Cohere API (%s)', $responseModel),
            'providerId' => 'cohere',
        ];
    }

    protected function buildOpenAiCompatibleMessages(
        string $message,
        array $context,
        ?string $currentQuestion,
        ?string $answerDraft,
        array $history
    ): array {
        $messages = [[
            'role' => 'system',
            'content' => InterviewPracticeCatalog::chatbotSystemInstruction(),
        ]];

        foreach ($history as $item) {
            $messages[] = [
                'role' => $item['role'],
                'content' => $item['text'],
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $this->buildProviderUserPrompt($message, $context, $currentQuestion, $answerDraft),
        ];

        return $messages;
    }

    protected function extractOpenAiCompatibleReply(array $payload): ?string
    {
        $content = data_get($payload, 'choices.0.message.content');

        if (is_string($content)) {
            return $content;
        }

        if (! is_array($content)) {
            return null;
        }

        return $this->collectTextFromItems($content, 'text');
    }

    protected function collectTextFromItems(mixed $items, string $key = 'text'): ?string
    {
        if (! is_array($items)) {
            return null;
        }

        $text = collect($items)
            ->map(function ($item) use ($key) {
                if (is_string($item)) {
                    return $item;
                }

                if (is_array($item)) {
                    return data_get($item, $key);
                }

                return null;
            })
            ->filter(fn ($item) => is_string($item) && trim($item) !== '')
            ->implode("\n\n");

        return $text !== '' ? $text : null;
    }

    protected function buildProviderUserPrompt(
        string $message,
        array $context,
        ?string $currentQuestion,
        ?string $answerDraft
    ): string {
        $lines = [
            'Stay within Philippine interview practice only.',
            'Selected category: '.$context['name'],
            'Category description: '.$context['description'],
        ];

        if ($currentQuestion) {
            $lines[] = 'Current interview question: '.$currentQuestion;
        }

        if ($answerDraft) {
            $lines[] = 'Current answer draft from the user: '.$answerDraft;
        }

        foreach ($context['localFocus'] ?? [] as $focus) {
            $lines[] = 'Local focus: '.$focus;
        }

        $lines[] = 'User request: '.$message;

        return implode("\n", $lines);
    }

    protected function buildQuestionSetPrompt(array $context, int $questionCount, string $message = ''): string
    {
        $baseInstruction = $message !== ''
            ? $message
            : sprintf('Generate %d interview questions for this category.', $questionCount);

        return implode("\n", [
            'Generate a fresh question set for the active Philippine interview category.',
            'Return ONLY a valid JSON array of strings.',
            sprintf('The array must contain exactly %d interview questions.', $questionCount),
            'Each question must be distinct, realistic, and specific to the selected category in the Philippines.',
            'Do not include numbering, labels, markdown, explanations, or extra text outside the JSON array.',
            'Use clear English suitable for Filipino students, fresh graduates, and early-career applicants.',
            'Instruction: '.$baseInstruction,
        ]);
    }

    protected function buildFieldBuilderPrompt(array $context, string $message = ''): string
    {
        $baseInstruction = $message !== ''
            ? $message
            : 'Create a focused practice field for this interview category.';

        return implode("\n", [
            'Create a focused practice field for the active Philippine interview category.',
            'Return ONLY a valid JSON object.',
            'Use this schema exactly: {"title":"string","summary":"string","instruction":"string","suggestions":["string"]}',
            'The "title" must be a short field, role, course, or specialization name.',
            'The "summary" must explain what the practice should focus on in 1 to 2 sentences.',
            'The "instruction" must be a direct instruction that another AI can use to generate interview questions for that field.',
            'The "suggestions" array must contain 3 to 4 short alternatives related to the same category.',
            'Keep the result grounded in the Philippines and the selected interview category only.',
            'Use clear English suitable for Filipino students, fresh graduates, and early-career applicants.',
            'Instruction: '.$baseInstruction,
        ]);
    }

    protected function extractGeneratedQuestions(?string $reply, int $questionCount): array
    {
        if (! is_string($reply) || trim($reply) === '') {
            return [];
        }

        $trimmed = trim($reply);
        $decoded = $this->decodeQuestionSetJson($trimmed);

        if ($decoded !== []) {
            return array_slice($decoded, 0, $questionCount);
        }

        $cleanedLines = collect(preg_split('/\R+/', $trimmed) ?: [])
            ->map(function (string $line) {
                $cleaned = preg_replace('/^\s*(?:[-*]|\d+[.)])\s*/', '', trim($line)) ?? '';
                return trim($cleaned, "\"' \t\n\r\0\x0B");
            })
            ->filter(fn (string $line) => $line !== '')
            ->unique()
            ->take($questionCount)
            ->values()
            ->all();

        return $cleanedLines;
    }

    protected function decodeQuestionSetJson(string $reply): array
    {
        $candidates = [$reply];

        if (preg_match('/```(?:json)?\s*(.*?)```/si', $reply, $matches) === 1) {
            $candidates[] = trim((string) ($matches[1] ?? ''));
        }

        foreach ($candidates as $candidate) {
            $decoded = json_decode($candidate, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                continue;
            }

            if (! is_array($decoded)) {
                continue;
            }

            if (array_is_list($decoded)) {
                $questions = collect($decoded)
                    ->map(fn ($item) => $this->sanitizeText($item, 300))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                if ($questions !== []) {
                    return $questions;
                }
            }

            if (isset($decoded['questions']) && is_array($decoded['questions'])) {
                $questions = collect($decoded['questions'])
                    ->map(fn ($item) => $this->sanitizeText($item, 300))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                if ($questions !== []) {
                    return $questions;
                }
            }
        }

        return [];
    }

    protected function extractFieldPlan(?string $reply, array $context): array
    {
        if (! is_string($reply) || trim($reply) === '') {
            return [];
        }

        $decoded = $this->decodeFieldPlanJson(trim($reply));

        if ($decoded === []) {
            return [];
        }

        $title = $this->sanitizeText($decoded['title'] ?? $decoded['field'] ?? $decoded['name'] ?? null, 120);
        $summary = $this->sanitizeText($decoded['summary'] ?? $decoded['description'] ?? null, 500);
        $instruction = $this->sanitizeText($decoded['instruction'] ?? $decoded['prompt'] ?? null, 700);
        $suggestions = collect($decoded['suggestions'] ?? $decoded['alternatives'] ?? [])
            ->map(fn ($item) => $this->sanitizeText($item, 120))
            ->filter()
            ->unique()
            ->take(4)
            ->values()
            ->all();

        if (! $title) {
            return [];
        }

        $fallback = $this->buildLocalFieldPlan($context, $title);

        return [
            'title' => $title,
            'summary' => $summary ?? $fallback['summary'],
            'instruction' => $instruction ?? $fallback['instruction'],
            'suggestions' => $suggestions !== [] ? $suggestions : $fallback['suggestions'],
        ];
    }

    protected function decodeFieldPlanJson(string $reply): array
    {
        $candidates = [$reply];

        if (preg_match('/```(?:json)?\s*(.*?)```/si', $reply, $matches) === 1) {
            $candidates[] = trim((string) ($matches[1] ?? ''));
        }

        foreach ($candidates as $candidate) {
            $decoded = json_decode($candidate, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    protected function buildLocalQuestionSet(array $context, int $questionCount, string $message = ''): array
    {
        $seedQuestions = collect($context['questions'] ?? [])
            ->map(fn ($question) => $this->sanitizeText($question, 300))
            ->filter()
            ->values();
        $fieldFocus = $this->extractFieldFocusLabel($message);
        $instruction = Str::lower($message);
        $questions = [];

        foreach (range(0, $questionCount - 1) as $index) {
            $baseQuestion = $seedQuestions->get($index % max($seedQuestions->count(), 1), 'Tell me about yourself.');
            $questions[] = $this->adaptLocalQuestion(
                $baseQuestion,
                $context,
                $instruction,
                $index,
                (int) floor($index / 5),
                $fieldFocus
            );
        }

        return collect($questions)
            ->map(fn ($question) => $this->sanitizeText($question, 300))
            ->filter()
            ->unique()
            ->take($questionCount)
            ->values()
            ->all();
    }

    protected function buildLocalFieldPlan(array $context, string $message = ''): array
    {
        $categoryId = $context['id'] ?? null;
        $suggestions = $this->defaultFieldSuggestions($categoryId);
        $requestedTitle = $this->sanitizeText($message, 120);
        $title = $this->selectLocalFieldTitle($requestedTitle, $suggestions);
        $summary = match ($categoryId) {
            'job' => "Practice as a candidate applying for {$title} in the Philippines, with questions about fit, experience, strengths, and day-to-day responsibilities.",
            'scholarship' => "Practice scholarship answers around {$title} so the coaching stays aligned with your study goals, motivation, financial need, and future contribution.",
            'admission' => "Practice admission answers for {$title}, focusing on course fit, readiness, personal motivation, and long-term goals in the Philippines.",
            'it' => "Practice IT interview questions focused on {$title}, including projects, tools, debugging, teamwork, and the kind of junior role you want to pursue.",
            default => "Practice interview questions focused on {$title} in the Philippine setting.",
        };
        $instruction = match ($categoryId) {
            'job' => "Generate interview questions for a Philippine job interview focused on the {$title} field. Match the responsibilities, strengths, and hiring expectations of that role.",
            'scholarship' => "Generate scholarship interview questions for a student pursuing {$title} in the Philippines. Focus on motivation, need, discipline, service, and future contribution.",
            'admission' => "Generate college admission interview questions for a student applying to {$title} in the Philippines. Focus on course fit, readiness, values, and future plans.",
            'it' => "Generate Philippine IT interview questions focused on {$title}. Include projects, technical problem-solving, communication, teamwork, and role fit.",
            default => "Generate interview questions focused on {$title} in the Philippine setting.",
        };

        return [
            'title' => $title,
            'summary' => $summary,
            'instruction' => $instruction,
            'suggestions' => $suggestions,
        ];
    }

    protected function defaultFieldSuggestions(?string $categoryId): array
    {
        return match ($categoryId) {
            'job' => [
                'Customer Service Representative',
                'Administrative Assistant',
                'Virtual Assistant',
                'Sales Associate',
            ],
            'scholarship' => [
                'Nursing',
                'Education',
                'Information Technology',
                'Accountancy',
            ],
            'admission' => [
                'BS Information Technology',
                'BS Nursing',
                'BS Accountancy',
                'BS Education',
            ],
            'it' => [
                'Junior Web Developer',
                'QA Tester',
                'Technical Support Specialist',
                'Junior Data Analyst',
            ],
            default => [
                'General Interview Practice',
                'Entry-Level Role',
                'Student Leadership',
                'Community Service',
            ],
        };
    }

    protected function selectLocalFieldTitle(?string $requestedTitle, array $suggestions): string
    {
        $cleaned = trim((string) $requestedTitle);

        if ($cleaned !== '' && str_word_count($cleaned) <= 10 && mb_strlen($cleaned) <= 80) {
            return $cleaned;
        }

        if ($cleaned !== '') {
            foreach ($suggestions as $suggestion) {
                if (Str::contains(Str::lower($cleaned), Str::lower($suggestion))) {
                    return $suggestion;
                }
            }
        }

        return $suggestions[0] ?? 'General Interview Practice';
    }

    protected function extractFieldFocusLabel(?string $message): ?string
    {
        $text = trim((string) $message);

        if ($text === '') {
            return null;
        }

        $patterns = [
            '/Field:\s*([^\.]+)\./i',
            '/for a student pursuing\s+([^\.]+?)\s+in the philippines/i',
            '/student applying to\s+([^\.]+?)\s+in the philippines/i',
            '/focused on\s+(?:the\s+)?([^\.]+?)\./i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches) !== 1) {
                continue;
            }

            $focus = $this->sanitizeText($matches[1] ?? null, 120);

            if ($focus === null) {
                continue;
            }

            $focus = preg_replace('/^(?:the|a|an)\s+/i', '', $focus) ?? $focus;
            $focus = preg_replace('/\s+(?:role|field|course|program)\s*$/i', '', $focus) ?? $focus;
            $focus = trim($focus);

            if ($focus !== '') {
                return $focus;
            }
        }

        return null;
    }

    protected function adaptLocalQuestion(
        string $baseQuestion,
        array $context,
        string $instruction,
        int $index,
        int $cycle = 0,
        ?string $fieldFocus = null
    ): string
    {
        $categoryId = $context['id'] ?? null;
        $remoteHint = Str::contains($instruction, ['remote', 'online', 'virtual', 'home-based'])
            ? ' for a remote or hybrid role'
            : '';
        $freshGraduateHint = Str::contains($instruction, ['fresh graduate', 'entry-level', 'entry level', 'beginner'])
            ? ' as a fresh graduate'
            : '';
        $harderHint = Str::contains($instruction, ['hard', 'harder', 'challenging', 'advanced'])
            ? ' Give a specific and detailed example.'
            : '';
        $variationHint = match ($cycle % 4) {
            1 => ' Focus on one specific example and its result.',
            2 => ' Highlight communication, professionalism, or teamwork in your answer.',
            3 => ' Connect your answer to Philippine workplace, school, family, or community realities.',
            default => '',
        };

        $question = match ($categoryId) {
            'job' => match ($index % 5) {
                0 => "Walk me through the background and experience that make you a fit".($fieldFocus ? " for the {$fieldFocus} role" : '')."{$remoteHint}{$freshGraduateHint} in the Philippine job market.",
                1 => "What strengths from your OJT, internship, student leadership, or part-time work would you bring to ".($fieldFocus ? "a {$fieldFocus} role" : 'this employer')."{$remoteHint}?",
                2 => "Describe a real challenge you handled in school, work, or your community and explain your actions and the result.{$harderHint}",
                3 => "Why do you want ".($fieldFocus ? "the {$fieldFocus} role" : 'this role').", and how does it match the kind of career you want to build in the Philippines{$remoteHint}?",
                default => "If we hire you today".($fieldFocus ? " as a {$fieldFocus}" : '').", what value would you bring to our team during your first few months{$remoteHint}{$freshGraduateHint}?",
            },
            'scholarship' => match ($index % 5) {
                0 => "Why are you a strong candidate for ".($fieldFocus ? "scholarship support in {$fieldFocus}" : 'this scholarship').", and what makes your situation and goals worth supporting in the Philippines?",
                1 => "How would this scholarship change ".($fieldFocus ? "your path in {$fieldFocus}" : 'your studies').", responsibilities at home, and long-term plans?",
                2 => "Tell me about a time you showed discipline, leadership, or service even without many resources.",
                3 => "How do you stay focused on academics while handling family or community responsibilities?",
                default => "If we invest in your education now, how do you plan to give back in the future?",
            },
            'admission' => match ($index % 5) {
                0 => "What made you choose ".($fieldFocus ?: 'this course').", and why do you believe ".($fieldFocus ? 'that program' : 'this program')." fits your strengths and goals?",
                1 => "Which senior high school, family, or community experiences prepared you for ".($fieldFocus ?: 'this course')."?",
                2 => "How do you handle pressure, deadlines, and competing responsibilities at home and in school?",
                3 => "What kind of student and classmate do you think you will be if you are admitted?",
                default => "What do you hope to achieve in college, and how does that connect to the future you want in the Philippines?",
            },
            'it' => match ($index % 5) {
                0 => "Tell me about a project, system, or capstone that prepared you for ".($fieldFocus ?: 'the type of IT role you want')." and the part of the work you personally owned.",
                1 => "How do you debug a technical problem when time is limited and the issue is affecting a deadline?",
                2 => "Which languages, tools, or frameworks do you use most confidently".($fieldFocus ? " for {$fieldFocus}" : '').", and where have you applied them?",
                3 => "Describe how you collaborate with teammates when building software or finishing technical requirements.",
                default => "Why do you want to grow your IT career in the Philippines".($fieldFocus ? " as a {$fieldFocus}" : '').", and what role do you want to prepare for next?",
            },
            default => $baseQuestion,
        };

        return trim($question.$variationHint);
    }

    protected function buildQuestionSetSummary(array $questions, array $context, string $providerLabel): string
    {
        $categoryName = $context['name'] ?? 'your selected category';
        $count = count($questions);

        return sprintf(
            '%s prepared %d interview question%s for %s. The first question is now active for the interviewer.',
            $providerLabel,
            $count,
            $count === 1 ? '' : 's',
            $categoryName
        );
    }

    protected function buildFieldPlanSummary(array $fieldPlan, array $context, string $providerLabel): string
    {
        $categoryName = $context['name'] ?? 'your selected category';
        $title = $fieldPlan['title'] ?? 'your chosen field';
        $summary = $fieldPlan['summary'] ?? 'Your practice field is ready.';

        return sprintf(
            '%s prepared the %s field for %s. %s',
            $providerLabel,
            $title,
            $categoryName,
            $summary
        );
    }

    protected function buildFeedbackReviewPrompt(
        array $context,
        string $message,
        ?string $currentQuestion,
        ?string $answerDraft,
        array $criteriaScores
    ): string {
        $instruction = $message !== ''
            ? $message
            : 'Create automated interview feedback based on the criteria scores.';

        return implode("\n", [
            'Review the user interview answer and generate automated coaching feedback.',
            'Return ONLY a valid JSON object.',
            'Use this schema exactly: {"overall":"string","strengths":["string"],"improvements":["string"],"criteria":{"clarity":"string","relevance":"string","grammar":"string","professionalism":"string"},"nextStep":"string"}',
            'Strengths and improvements should each contain 2 to 3 short items.',
            'Each criterion note should explain what is working or what should improve based on the score.',
            'Use concise English suitable for Philippine interview practice.',
            'Do not include markdown, labels, or extra text outside the JSON object.',
            'Selected category: '.($context['name'] ?? 'Philippine Interview Practice'),
            'Question: '.($currentQuestion ?? 'No question provided'),
            'Answer: '.($answerDraft ?? 'No answer provided'),
            sprintf('Clarity score: %.1f/10', (float) ($criteriaScores['clarity'] ?? 0)),
            sprintf('Relevance score: %.1f/10', (float) ($criteriaScores['relevance'] ?? 0)),
            sprintf('Grammar score: %.1f/10', (float) ($criteriaScores['grammar'] ?? 0)),
            sprintf('Professionalism score: %.1f/10', (float) ($criteriaScores['professionalism'] ?? 0)),
            sprintf('Overall score: %.1f/10', (float) ($criteriaScores['average'] ?? 0)),
            'Matched keywords: '.(int) ($criteriaScores['matchedKeywords'] ?? 0),
            'Instruction: '.$instruction,
        ]);
    }

    protected function extractFeedbackSummary(?string $reply, array $criteriaScores): array
    {
        if (! is_string($reply) || trim($reply) === '') {
            return [];
        }

        $decoded = $this->decodeFeedbackJson(trim($reply));

        if ($decoded === []) {
            return [];
        }

        $fallback = $this->buildLocalFeedbackSummary([], null, null, $criteriaScores);
        $strengths = $this->extractFeedbackItems($decoded['strengths'] ?? []);
        $improvements = $this->extractFeedbackItems($decoded['improvements'] ?? []);

        return [
            'overall' => $this->sanitizeText($decoded['overall'] ?? $decoded['summary'] ?? null, 1200) ?? $fallback['overall'],
            'strengths' => $strengths !== [] ? $strengths : $fallback['strengths'],
            'improvements' => $improvements !== [] ? $improvements : $fallback['improvements'],
            'criteria' => [
                'clarity' => $this->sanitizeText(data_get($decoded, 'criteria.clarity'), 500) ?? $fallback['criteria']['clarity'],
                'relevance' => $this->sanitizeText(data_get($decoded, 'criteria.relevance'), 500) ?? $fallback['criteria']['relevance'],
                'grammar' => $this->sanitizeText(data_get($decoded, 'criteria.grammar'), 500) ?? $fallback['criteria']['grammar'],
                'professionalism' => $this->sanitizeText(data_get($decoded, 'criteria.professionalism'), 500) ?? $fallback['criteria']['professionalism'],
            ],
            'nextStep' => $this->sanitizeText($decoded['nextStep'] ?? $decoded['next_step'] ?? null, 500) ?? $fallback['nextStep'],
        ];
    }

    protected function decodeFeedbackJson(string $reply): array
    {
        $candidates = [$reply];

        if (preg_match('/```(?:json)?\s*(.*?)```/si', $reply, $matches) === 1) {
            $candidates[] = trim((string) ($matches[1] ?? ''));
        }

        foreach ($candidates as $candidate) {
            $decoded = json_decode($candidate, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    protected function extractFeedbackItems(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->map(fn ($item) => $this->sanitizeText($item, 255))
            ->filter()
            ->unique()
            ->take(5)
            ->values()
            ->all();
    }

    protected function buildLocalFeedbackSummary(
        array $context,
        ?string $currentQuestion,
        ?string $answerDraft,
        array $criteriaScores
    ): array {
        $answer = trim((string) ($answerDraft ?? ''));
        $wordCount = $answer === '' ? 0 : count(array_filter(preg_split('/\s+/', $answer) ?: []));
        $average = (float) ($criteriaScores['average'] ?? 0);
        $categoryName = $context['name'] ?? 'this interview';
        $strengths = [];
        $improvements = [];

        if ((float) ($criteriaScores['clarity'] ?? 0) >= 8) {
            $strengths[] = 'Your answer is clear and easy to follow.';
        } else {
            $improvements[] = 'Organize your answer into main point, example, and closing.';
        }

        if ((float) ($criteriaScores['relevance'] ?? 0) >= 8) {
            $strengths[] = 'Your response stays relevant to the question.';
        } else {
            $improvements[] = 'Connect your example more directly to what the interviewer asked.';
        }

        if ((float) ($criteriaScores['grammar'] ?? 0) >= 8) {
            $strengths[] = 'Your wording is mostly polished and professional.';
        } else {
            $improvements[] = 'Tighten sentence flow and complete each thought more cleanly.';
        }

        if ((float) ($criteriaScores['professionalism'] ?? 0) >= 8) {
            $strengths[] = 'Your tone sounds professional and confident.';
        } else {
            $improvements[] = 'Use stronger, more professional wording so the answer sounds more confident.';
        }

        if ($wordCount < 25) {
            $improvements[] = 'Add one specific example and a short result to strengthen your answer.';
        }

        $strengths = array_values(array_unique($strengths));
        $improvements = array_values(array_unique($improvements));

        return [
            'overall' => match (true) {
                $average >= 8 => "This is a strong answer for {$categoryName}. Keep the structure and make the example as concrete as possible.",
                $average >= 6 => "This answer has a solid base for {$categoryName}, but it needs tighter structure and more precise support to sound more convincing.",
                default => "This answer needs more structure and stronger support before it will feel interview-ready for {$categoryName}.",
            },
            'strengths' => $strengths !== [] ? $strengths : ['Your answer has a good starting point.'],
            'improvements' => $improvements !== [] ? $improvements : ['Keep practicing to improve consistency.'],
            'criteria' => [
                'clarity' => $this->buildLocalCriterionFeedback('clarity', (float) ($criteriaScores['clarity'] ?? 0)),
                'relevance' => $this->buildLocalCriterionFeedback('relevance', (float) ($criteriaScores['relevance'] ?? 0)),
                'grammar' => $this->buildLocalCriterionFeedback('grammar', (float) ($criteriaScores['grammar'] ?? 0)),
                'professionalism' => $this->buildLocalCriterionFeedback('professionalism', (float) ($criteriaScores['professionalism'] ?? 0)),
            ],
            'nextStep' => $wordCount < 25
                ? 'Expand your answer with one concrete example and a short result.'
                : 'Refine the example you gave so the impact and relevance are more obvious.',
        ];
    }

    protected function buildLocalCriterionFeedback(string $criterion, float $score): string
    {
        return match ($criterion) {
            'clarity' => match (true) {
                $score >= 8 => 'Your ideas are organized well, so the answer is easy to follow from start to finish.',
                $score >= 6 => 'Your main point is visible, but the answer would land better with clearer structure and transitions.',
                default => 'Your answer needs a clearer flow. Lead with the main point, add one example, then close with the result.',
            },
            'relevance' => match (true) {
                $score >= 8 => 'You stay close to the question and connect your answer to what the interviewer is asking.',
                $score >= 6 => 'Parts of the answer are relevant, but you can tie your example back to the exact question more directly.',
                default => 'Your answer drifts away from the prompt. Mirror the question language and explain why your example fits.',
            },
            'grammar' => match (true) {
                $score >= 8 => 'Your wording is mostly polished and professional, which makes the answer sound interview-ready.',
                $score >= 6 => 'The answer is understandable, but tighter sentence flow would make it sound cleaner.',
                default => 'Grammar and sentence flow are weakening the message. Use shorter complete sentences and cleaner endings.',
            },
            default => match (true) {
                $score >= 8 => 'Your tone sounds confident, respectful, and appropriate for an interview setting.',
                $score >= 6 => 'Your tone is generally professional, but stronger wording would make you sound more confident.',
                default => 'Your tone feels too casual or uncertain. Use more direct, professional language and sound deliberate.',
            },
        };
    }

    protected function buildLocalReply(
        string $message,
        array $context,
        ?string $currentQuestion,
        ?string $answerDraft
    ): string {
        $normalized = Str::lower($message);

        if ($this->isOutOfScope($normalized)) {
            return 'I can only help with Philippine interview practice. Ask me for sample questions, follow-up questions, answer feedback, or interview tips for job, scholarship, college admission, or IT interviews in the Philippines.';
        }

        if ($answerDraft && $this->wantsAnswerImprovement($normalized)) {
            return $this->buildDraftCoachingReply($context, $currentQuestion, $answerDraft);
        }

        if ($this->wantsSampleAnswer($normalized)) {
            return $this->buildSampleAnswerReply($context, $currentQuestion);
        }

        if ($this->wantsQuestionList($normalized)) {
            return $this->buildQuestionListReply($context);
        }

        return $this->buildGeneralCoachingReply($context, $currentQuestion);
    }

    protected function buildQuestionListReply(array $context): string
    {
        $questions = array_slice($context['questions'] ?? [], 0, 3);
        $formatted = collect($questions)
            ->values()
            ->map(fn ($question, $index) => sprintf('%d. %s', $index + 1, $question))
            ->implode("\n");

        return sprintf(
            "Here are Philippine-focused %s practice questions you can use right now:\n\n%s\n\nKeep your answer direct, give one concrete example, and end with the result or lesson learned.",
            Str::lower($context['name'] ?? 'interview'),
            $formatted
        );
    }

    protected function buildSampleAnswerReply(array $context, ?string $currentQuestion): string
    {
        $question = $currentQuestion ?: ($context['questions'][0] ?? 'Tell me about yourself.');

        $sample = match ($context['id'] ?? null) {
            'job' => "Good day. I am a recent graduate from the Philippines with hands-on experience from my OJT and school projects. In those roles, I developed discipline, communication, and teamwork. I believe I fit this role because I learn quickly, follow through on responsibilities, and stay professional when working with different people. I am now looking for a company where I can contribute, grow, and build a long-term career.",
            'scholarship' => "I believe I deserve this scholarship because I have been consistent in my studies and I take my responsibilities seriously at home and in school. This support would help me stay focused on my education, reduce the financial pressure on my family, and allow me to participate more fully in academic and community activities. In return, I plan to use my education to create practical value for my family and community in the Philippines.",
            'admission' => "I want to take this program because it matches both my strengths and my long-term goals. My experiences in senior high school helped me realize that I enjoy learning, solving problems, and improving my skills in this area. I am applying to this program because I want the right academic training, strong discipline, and real opportunities to prepare for my future career in the Philippines.",
            'it' => "One project I am proud of is our capstone system, where I worked on the development and testing of key features. My role included fixing bugs, coordinating with teammates, and making sure the system met the user requirements. That experience taught me how to solve problems under pressure, communicate clearly with a team, and keep improving until the product became more reliable.",
            default => 'I would answer directly, give one specific example, and end with the result. Keep your tone respectful, clear, and realistic for a Philippine interview setting.',
        };

        return sprintf(
            "For the question \"%s\", here is a Philippine-style sample answer:\n\n%s\n\nYou can personalize it with your own school, OJT, project, family, or community example.",
            $question,
            $sample
        );
    }

    protected function buildDraftCoachingReply(array $context, ?string $currentQuestion, string $answerDraft): string
    {
        $questionLine = $currentQuestion
            ? 'Current question: '.$currentQuestion
            : 'Use your current interview question as the anchor for your answer.';

        return sprintf(
            "%s\n\nYour draft already has useful material. To make it stronger for a Philippine interviewer, tighten it in this order: direct answer first, one concrete example, then the result or lesson.\n\nWhat to improve:\n1. Remove repeated ideas and keep your strongest point in the first two sentences.\n2. Add one specific example from OJT, school, work, project, or community experience.\n3. End with why that experience makes you ready for the role or opportunity.\n\nYour draft to improve:\n\"%s\"\n\nIf you want, ask me to turn this into a cleaner model answer.",
            $questionLine,
            $answerDraft
        );
    }

    protected function buildGeneralCoachingReply(array $context, ?string $currentQuestion): string
    {
        $questionText = $currentQuestion
            ? 'Current question: '.$currentQuestion
            : 'Choose a category and I can tailor the coaching to that interview type.';
        $localFocus = collect($context['localFocus'] ?? [])
            ->take(2)
            ->map(fn ($item) => '- '.$item)
            ->implode("\n");

        return sprintf(
            "I can help you practice %s in a Philippine context.\n\n%s\n\nBest approach:\n%s\n\nAsk me for sample questions, a model answer, follow-up questions, or feedback on your draft answer.",
            $context['name'] ?? 'interviews',
            $questionText,
            $localFocus
        );
    }

    protected function isOutOfScope(string $message): bool
    {
        $blockedKeywords = [
            'recipe',
            'weather',
            'movie',
            'song',
            'politics',
            'medical',
            'diagnosis',
            'crypto',
            'trading',
            'sports score',
        ];

        foreach ($blockedKeywords as $keyword) {
            if (Str::contains($message, $keyword)) {
                return true;
            }
        }

        return false;
    }

    protected function wantsQuestionList(string $message): bool
    {
        return Str::contains($message, ['question', 'follow-up', 'follow up', 'ask me', 'sample question']);
    }

    protected function wantsSampleAnswer(string $message): bool
    {
        return Str::contains($message, ['sample answer', 'model answer', 'example answer', 'best answer']);
    }

    protected function wantsAnswerImprovement(string $message): bool
    {
        return Str::contains($message, ['improve', 'rewrite', 'revise', 'edit my answer', 'fix my answer']);
    }

    protected function normalizeCategoryId(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $categoryId = trim($value);

        return array_key_exists($categoryId, InterviewPracticeCatalog::categories()) ? $categoryId : null;
    }

    protected function normalizeHistory(mixed $history): array
    {
        if (! is_array($history)) {
            return [];
        }

        return collect($history)
            ->filter(fn ($item) => is_array($item))
            ->map(function (array $item) {
                $role = ($item['role'] ?? 'user') === 'assistant' ? 'assistant' : 'user';
                $text = $this->sanitizeText($item['text'] ?? null, 2000);

                return $text ? compact('role', 'text') : null;
            })
            ->filter()
            ->take(-8)
            ->values()
            ->all();
    }

    protected function sanitizeReply(?string $text): ?string
    {
        if (! is_string($text)) {
            return null;
        }

        $cleaned = trim(preg_replace('/\s+/u', ' ', $text) ?? '');

        return $cleaned !== '' ? Str::limit($cleaned, 4000, '') : null;
    }

    protected function forgetProviderError(string $providerId): void
    {
        unset($this->lastProviderErrors[$providerId]);
    }

    protected function recordProviderError(string $providerId, ?string $message = null, ?int $status = null): void
    {
        $this->lastProviderErrors[$providerId] = [
            'status' => $status,
            'message' => $this->sanitizeProviderErrorMessage($message),
        ];
    }

    protected function recordProviderThrowable(string $providerId, Throwable $error): void
    {
        $status = property_exists($error, 'response') && $error->response
            ? $error->response->status()
            : null;

        $this->recordProviderError($providerId, $error->getMessage(), $status);
    }

    protected function sanitizeProviderErrorMessage(?string $message): ?string
    {
        $cleaned = $this->sanitizeText($message, 220);

        if ($cleaned === null) {
            return null;
        }

        return preg_replace('/\s+/', ' ', $cleaned) ?: null;
    }

    protected function providerProbeFailureMessage(string $providerId): string
    {
        $error = $this->lastProviderErrors[$providerId] ?? [];
        $status = $error['status'] ?? null;
        $message = $error['message'] ?? null;

        if (in_array($status, [401, 403], true)) {
            return 'The API key was rejected or the selected model is not available for this account.';
        }

        if ($status === 429) {
            return 'The provider is rate-limited or the account quota is currently exhausted.';
        }

        if (is_int($status) && $status >= 500) {
            return 'The provider returned a server error during the live check.';
        }

        if (is_string($message) && Str::contains(Str::lower($message), ['connect', 'timed out', 'timeout', 'could not resolve', 'dns'])) {
            return 'The app could not reach this provider during the live check.';
        }

        if (is_string($message) && Str::contains(Str::lower($message), ['plan', 'no access', 'model'])) {
            return 'The account, model, or plan does not currently allow this provider request.';
        }

        return 'The provider did not return a usable response right now.';
    }

    protected function safeReport(Throwable $error): void
    {
        try {
            report($error);
        } catch (Throwable) {
            // Ignore logging failures so chatbot fallback behavior still works.
        }
    }

    protected function sanitizeText(mixed $value, int $limit = 255): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $cleaned = trim(preg_replace('/\s+/u', ' ', (string) $value) ?? '');

        return $cleaned !== '' ? Str::limit($cleaned, $limit, '') : null;
    }

    protected function hasApiKey(mixed $value): bool
    {
        return is_string($value) && trim($value) !== '';
    }
}
