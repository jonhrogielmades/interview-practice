<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class AITranslationService
{
    /**
     * Translate an array of text strings to a target language.
     *
     * @param array $texts
     * @param string $targetLanguage
     * @return array
     */
    public function translateBatch(array $texts, string $targetLanguage): array
    {
        if (empty($texts)) {
            return [];
        }

        $systemPrompt = "You are a highly accurate translation API. " .
            "You will receive a JSON array of strings. " .
            "Your task is to translate ALL the strings into {$targetLanguage}. " .
            "Maintain the original tone, context, and any HTML entities or placeholders. " .
            "Return strictly a valid JSON array of strings in the exact same order and length as the input. " .
            "Do not include any markdown formatting, explanations, or additional text.";

        if (strtolower($targetLanguage) === 'bisaya') {
            $systemPrompt .= " IMPORTANT: Use strictly Bisaya (Cebuano). Do NOT use Tagalog. For example, use 'Ang imong pinakabag-o nga track sa pagbansay makita dinhi' instead of Tagalog.";
        }

        $providers = explode(',', config('services.interview_chatbot.provider_priority', 'gemini,groq,openrouter,claude,wisdomgate,cohere'));

        foreach ($providers as $provider) {
            $provider = trim($provider);
            
            try {
                $resultContent = $this->attemptTranslation($provider, $texts, $systemPrompt);
                
                if ($resultContent) {
                    // Clean up any potential markdown code blocks returned by AI
                    $resultContent = preg_replace('/^```json\s*/i', '', trim($resultContent));
                    $resultContent = preg_replace('/```$/', '', $resultContent);
                    
                    $translatedArray = json_decode($resultContent, true);

                    if (is_array($translatedArray) && count($translatedArray) === count($texts)) {
                        return $translatedArray;
                    } else {
                        Log::warning("AITranslationService: $provider returned invalid JSON or mismatched array length", [
                            'response' => $resultContent
                        ]);
                    }
                }
            } catch (Throwable $e) {
                Log::error("AITranslationService: $provider Exception occurred", [
                    'message' => $e->getMessage(),
                ]);
            }
        }

        // Fallback to original if all providers fail
        return $texts;
    }

    protected function attemptTranslation(string $provider, array $texts, string $systemPrompt): ?string
    {
        return match ($provider) {
            'gemini' => $this->translateWithGemini($texts, $systemPrompt),
            'groq' => $this->translateWithGroq($texts, $systemPrompt),
            'openrouter' => $this->translateWithOpenRouter($texts, $systemPrompt),
            'claude' => $this->translateWithClaude($texts, $systemPrompt),
            'wisdomgate' => $this->translateWithWisdomGate($texts, $systemPrompt),
            'cohere' => $this->translateWithCohere($texts, $systemPrompt),
            default => null,
        };
    }

    protected function translateWithGemini(array $texts, string $systemPrompt): ?string
    {
        $apiKey = config('services.gemini.api_key');
        if (empty($apiKey)) return null;

        $model = config('services.gemini.model', 'gemini-2.5-flash');
        $url = sprintf('https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent', $model);

        $response = Http::timeout(5)
            ->acceptJson()
            ->withHeaders(['x-goog-api-key' => $apiKey])
            ->post($url, [
                'system_instruction' => [
                    'parts' => [['text' => $systemPrompt]],
                ],
                'generationConfig' => [
                    'temperature' => 0.1,
                    'responseMimeType' => 'application/json',
                ],
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [['text' => json_encode($texts, JSON_UNESCAPED_UNICODE)]],
                    ],
                ],
            ]);

        if ($response->successful()) {
            return data_get($response->json(), 'candidates.0.content.parts.0.text');
        }

        return null;
    }

    protected function translateWithGroq(array $texts, string $systemPrompt): ?string
    {
        $apiKey = config('services.groq.api_key');
        if (empty($apiKey)) return null;

        $model = config('services.groq.model', 'openai/gpt-oss-20b');
        $url = 'https://api.groq.com/openai/v1/chat/completions';

        return $this->requestOpenAiCompatible($url, $apiKey, $model, $texts, $systemPrompt);
    }

    protected function translateWithOpenRouter(array $texts, string $systemPrompt): ?string
    {
        $apiKey = config('services.openrouter.api_key');
        if (empty($apiKey)) return null;

        $model = config('services.openrouter.model', 'openrouter/free');
        $url = 'https://openrouter.ai/api/v1/chat/completions';

        return $this->requestOpenAiCompatible($url, $apiKey, $model, $texts, $systemPrompt);
    }

    protected function translateWithWisdomGate(array $texts, string $systemPrompt): ?string
    {
        $apiKey = config('services.wisdomgate.api_key');
        if (empty($apiKey)) return null;

        $model = config('services.wisdomgate.model', 'wisdom-ai-dsv3');
        $url = config('services.wisdomgate.base_url', 'https://wisgate.ai/v1/chat/completions');

        return $this->requestOpenAiCompatible($url, $apiKey, $model, $texts, $systemPrompt);
    }

    protected function translateWithClaude(array $texts, string $systemPrompt): ?string
    {
        $apiKey = config('services.claude.api_key');
        if (empty($apiKey)) return null;

        $model = config('services.claude.model', 'claude-haiku-4-5-20251001');

        $response = Http::timeout(5)
            ->withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => config('services.claude.version', '2023-06-01'),
                'content-type' => 'application/json',
            ])
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => $model,
                'system' => $systemPrompt,
                'messages' => [
                    ['role' => 'user', 'content' => json_encode($texts, JSON_UNESCAPED_UNICODE)],
                ],
                'max_tokens' => 2000,
                'temperature' => 0.1,
            ]);

        if ($response->successful()) {
            return data_get($response->json(), 'content.0.text');
        }

        return null;
    }

    protected function translateWithCohere(array $texts, string $systemPrompt): ?string
    {
        $apiKey = config('services.cohere.api_key');
        if (empty($apiKey)) return null;

        $model = config('services.cohere.model', 'command-r7b-12-2024');

        $response = Http::timeout(5)
            ->withToken($apiKey)
            ->post('https://api.cohere.ai/v1/chat', [
                'model' => $model,
                'message' => json_encode($texts, JSON_UNESCAPED_UNICODE),
                'preamble' => $systemPrompt,
                'temperature' => 0.1,
            ]);

        if ($response->successful()) {
            return data_get($response->json(), 'text');
        }

        return null;
    }

    protected function requestOpenAiCompatible(string $url, string $apiKey, string $model, array $texts, string $systemPrompt): ?string
    {
        $response = Http::timeout(5)
            ->withToken($apiKey)
            ->post($url, [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => json_encode($texts, JSON_UNESCAPED_UNICODE)],
                ],
                'temperature' => 0.1,
            ]);

        if ($response->successful()) {
            return data_get($response->json(), 'choices.0.message.content');
        }

        return null;
    }
}
