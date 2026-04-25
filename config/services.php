<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
    ],

    'groq' => [
        'api_key' => env('GROQ_API_KEY'),
        'model' => env('GROQ_MODEL', 'openai/gpt-oss-20b'),
    ],

    'openrouter' => [
        'api_key' => env('OPENROUTER_API_KEY'),
        'model' => env('OPENROUTER_MODEL', 'openrouter/free'),
    ],

    'claude' => [
        'api_key' => env('ANTHROPIC_API_KEY') ?: env('CLAUDE_API_KEY'),
        'model' => env('ANTHROPIC_MODEL') ?: env('CLAUDE_MODEL', 'claude-haiku-4-5-20251001'),
        'version' => env('ANTHROPIC_VERSION', '2023-06-01'),
    ],

    'wisdomgate' => [
        'api_key' => env('WISDOMGATE_API_KEY') ?: env('HUGGINGFACE_API_KEY'),
        'model' => env('WISDOMGATE_MODEL') ?: env('HUGGINGFACE_MODEL', 'wisdom-ai-dsv3'),
        'base_url' => env('WISDOMGATE_BASE_URL') ?: env('HUGGINGFACE_BASE_URL') ?: 'https://wisgate.ai/v1/chat/completions',
    ],

    'cohere' => [
        'api_key' => env('COHERE_API_KEY'),
        'model' => env('COHERE_MODEL', 'command-r7b-12-2024'),
    ],

    'cartesia' => [
        'api_key' => env('CARTESIA_API_KEY'),
        'version' => env('CARTESIA_VERSION', '2026-03-01'),
        'tts_model_id' => env('CARTESIA_TTS_MODEL_ID', 'sonic-3'),
        'tts_voice_id' => env('CARTESIA_TTS_VOICE_ID', 'f786b574-daa5-4673-aa0c-cbe3e8534c02'),
        'tts_language' => env('CARTESIA_TTS_LANGUAGE', 'en'),
        'tts_speed' => (float) env('CARTESIA_TTS_SPEED', 1.0),
        'tts_emotion' => env('CARTESIA_TTS_EMOTION', 'neutral'),
        'tts_sample_rate' => (int) env('CARTESIA_TTS_SAMPLE_RATE', 44100),
    ],

    'interview_chatbot' => [
        'default_provider' => env('INTERVIEW_CHATBOT_DEFAULT_PROVIDER', 'auto'),
        'provider_priority' => env('INTERVIEW_CHATBOT_PROVIDER_PRIORITY', 'gemini,groq,openrouter,claude,wisdomgate,cohere'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI')
            ?: rtrim((string) env('APP_URL', 'http://localhost'), '/').'/auth/google/callback',
        'stateless' => env('GOOGLE_STATELESS', false),
    ],

];
