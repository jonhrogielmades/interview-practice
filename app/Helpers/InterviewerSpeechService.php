<?php

namespace App\Helpers;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class InterviewerSpeechService
{
    protected const DEFAULT_VOICE_ID = 'f786b574-daa5-4673-aa0c-cbe3e8534c02';

    public function frontendBootstrap(): array
    {
        return [
            'configured' => $this->isConfigured(),
            'provider' => 'cartesia',
            'providerLabel' => 'Cartesia Sonic 3',
            'modelId' => $this->modelId(),
            'voiceId' => $this->voiceId(),
            'language' => $this->language(),
        ];
    }

    public function isConfigured(): bool
    {
        return filled(config('services.cartesia.api_key'));
    }

    public function synthesize(string $text): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Cartesia is not configured.');
        }

        $response = Http::withToken((string) config('services.cartesia.api_key'))
            ->withHeaders([
                'Cartesia-Version' => (string) config('services.cartesia.version', '2026-03-01'),
                'Accept' => 'audio/wav',
            ])
            ->timeout(20)
            ->post('https://api.cartesia.ai/tts/bytes', $this->buildPayload($text));

        if (! $response->successful()) {
            throw new RuntimeException($this->errorMessageFromResponse($response));
        }

        return [
            'audio' => $response->body(),
            'contentType' => $response->header('Content-Type') ?: 'audio/wav',
        ];
    }

    protected function buildPayload(string $text): array
    {
        return [
            'model_id' => $this->modelId(),
            'transcript' => trim($text),
            'voice' => [
                'mode' => 'id',
                'id' => $this->voiceId(),
            ],
            'output_format' => [
                'container' => 'wav',
                'encoding' => 'pcm_f32le',
                'sample_rate' => (int) config('services.cartesia.tts_sample_rate', 44100),
            ],
            'language' => $this->language(),
            'generation_config' => [
                'speed' => (float) config('services.cartesia.tts_speed', 1.0),
                'emotion' => (string) config('services.cartesia.tts_emotion', 'neutral'),
            ],
        ];
    }

    protected function modelId(): string
    {
        return (string) config('services.cartesia.tts_model_id', 'sonic-3');
    }

    protected function voiceId(): string
    {
        return (string) config('services.cartesia.tts_voice_id', self::DEFAULT_VOICE_ID);
    }

    protected function language(): string
    {
        return (string) config('services.cartesia.tts_language', 'en');
    }

    protected function errorMessageFromResponse(Response $response): string
    {
        $payload = $response->json();
        $payload = is_array($payload) ? $payload : [];
        $error = $payload['error'] ?? null;
        $errorMessage = is_array($error) ? ($error['message'] ?? null) : $error;
        $message = $payload['message'] ?? $errorMessage;

        return filled($message)
            ? (string) $message
            : (trim($response->body()) ?: 'Cartesia TTS request failed.');
    }
}
