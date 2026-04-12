<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WorkspaceChatbotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.gemini.api_key', null);
        config()->set('services.groq.api_key', null);
        config()->set('services.openrouter.api_key', null);
        config()->set('services.claude.api_key', null);
        config()->set('services.wisdomgate.api_key', null);
        config()->set('services.cohere.api_key', null);
    }

    public function test_chatbot_endpoint_returns_local_fallback_reply(): void
    {
        config()->set('services.gemini.api_key', null);
        config()->set('services.groq.api_key', null);
        config()->set('services.openrouter.api_key', null);
        config()->set('services.wisdomgate.api_key', null);
        config()->set('services.cohere.api_key', null);

        $response = $this->actingAs(User::factory()->create())->postJson(route('workspace.chatbot'), [
            'message' => 'Give me 3 Philippine-style follow-up questions for a job interview.',
            'providerId' => 'auto',
            'categoryId' => 'job',
            'currentQuestion' => 'Tell me about yourself and how your background in the Philippines prepared you for this role.',
            'history' => [],
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'reply',
                'provider',
                'providerId',
                'requestedProviderId',
                'usedFallback',
                'suggestions',
                'availableProviders',
            ])
            ->assertJson([
                'provider' => 'Local PH coach',
                'providerId' => 'local',
                'requestedProviderId' => 'auto',
                'usedFallback' => true,
            ]);
    }

    public function test_chatbot_endpoint_returns_local_generated_question_set(): void
    {
        config()->set('services.gemini.api_key', null);
        config()->set('services.groq.api_key', null);
        config()->set('services.openrouter.api_key', null);
        config()->set('services.wisdomgate.api_key', null);
        config()->set('services.cohere.api_key', null);

        $response = $this->actingAs(User::factory()->create())->postJson(route('workspace.chatbot'), [
            'message' => 'Generate harder questions for a fresh graduate applying for a remote role.',
            'mode' => 'question_set',
            'questionCount' => 10,
            'providerId' => 'auto',
            'categoryId' => 'job',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'provider' => 'Local PH coach',
                'providerId' => 'local',
                'requestedProviderId' => 'auto',
                'usedFallback' => true,
            ])
            ->assertJsonCount(10, 'generatedQuestions');
    }

    public function test_local_generated_question_set_uses_the_selected_field_focus(): void
    {
        config()->set('services.gemini.api_key', null);
        config()->set('services.groq.api_key', null);
        config()->set('services.openrouter.api_key', null);
        config()->set('services.wisdomgate.api_key', null);
        config()->set('services.cohere.api_key', null);

        $response = $this->actingAs(User::factory()->create())->postJson(route('workspace.chatbot'), [
            'message' => 'Generate Philippine IT interview questions focused on Junior Laravel Developer. Field: Junior Laravel Developer.',
            'mode' => 'question_set',
            'questionCount' => 5,
            'providerId' => 'auto',
            'categoryId' => 'it',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'provider' => 'Local PH coach',
                'providerId' => 'local',
                'requestedProviderId' => 'auto',
                'usedFallback' => true,
            ]);

        $this->assertTrue(
            collect($response->json('generatedQuestions'))
                ->contains(fn ($question) => is_string($question) && str_contains($question, 'Junior Laravel Developer'))
        );
    }

    public function test_chatbot_endpoint_returns_local_feedback_review_summary(): void
    {
        config()->set('services.gemini.api_key', null);
        config()->set('services.groq.api_key', null);
        config()->set('services.openrouter.api_key', null);
        config()->set('services.wisdomgate.api_key', null);
        config()->set('services.cohere.api_key', null);

        $response = $this->actingAs(User::factory()->create())->postJson(route('workspace.chatbot'), [
            'message' => 'Create automated interview feedback based on the criteria for this answer.',
            'mode' => 'feedback_review',
            'providerId' => 'auto',
            'categoryId' => 'job',
            'currentQuestion' => 'Tell me about yourself.',
            'answerDraft' => 'I am a fresh graduate with internship experience and I learn quickly.',
            'criteriaScores' => [
                'clarity' => 7.5,
                'relevance' => 8.0,
                'grammar' => 7.0,
                'professionalism' => 8.5,
                'average' => 7.8,
                'matchedKeywords' => 3,
            ],
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'provider' => 'Local PH coach',
                'providerId' => 'local',
                'requestedProviderId' => 'auto',
                'usedFallback' => true,
            ])
            ->assertJsonStructure([
                'feedbackSummary' => [
                    'overall',
                    'strengths',
                    'improvements',
                    'criteria' => ['clarity', 'relevance', 'grammar', 'professionalism'],
                    'nextStep',
                    'provider',
                ],
            ]);
    }

    public function test_chatbot_endpoint_returns_local_field_builder_plan(): void
    {
        config()->set('services.gemini.api_key', null);
        config()->set('services.groq.api_key', null);
        config()->set('services.openrouter.api_key', null);
        config()->set('services.wisdomgate.api_key', null);
        config()->set('services.cohere.api_key', null);

        $response = $this->actingAs(User::factory()->create())->postJson(route('workspace.chatbot'), [
            'message' => 'Remote customer service role in a BPO company.',
            'mode' => 'field_builder',
            'providerId' => 'auto',
            'categoryId' => 'job',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'provider' => 'Local PH coach',
                'providerId' => 'local',
                'requestedProviderId' => 'auto',
                'usedFallback' => true,
            ])
            ->assertJsonStructure([
                'fieldPlan' => [
                    'title',
                    'summary',
                    'instruction',
                    'suggestions',
                ],
            ]);
    }

    public function test_chatbot_endpoint_returns_provider_generated_question_set_when_configured(): void
    {
        config()->set('services.groq.api_key', 'groq-test-key');
        config()->set('services.groq.model', 'openai/gpt-oss-20b');
        config()->set('services.gemini.api_key', null);
        config()->set('services.openrouter.api_key', null);
        config()->set('services.wisdomgate.api_key', null);
        config()->set('services.cohere.api_key', null);

        Http::fake([
            'https://api.groq.com/openai/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'Tell me about a project where you fixed a production bug under pressure.',
                                'How would you explain your capstone role to a non-technical interviewer?',
                                'What would your teammates say about your communication during development work?',
                            ]),
                        ],
                    ],
                ],
                'model' => 'openai/gpt-oss-20b',
            ]),
        ]);

        $response = $this->actingAs(User::factory()->create())->postJson(route('workspace.chatbot'), [
            'message' => 'Generate IT interview questions for a junior developer role.',
            'mode' => 'question_set',
            'questionCount' => 3,
            'providerId' => 'groq',
            'categoryId' => 'it',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'provider' => 'Groq API (openai/gpt-oss-20b)',
                'providerId' => 'groq',
                'requestedProviderId' => 'groq',
                'usedFallback' => false,
            ])
            ->assertJsonCount(3, 'generatedQuestions');
    }

    public function test_chatbot_endpoint_returns_provider_feedback_review_summary_when_configured(): void
    {
        config()->set('services.groq.api_key', 'groq-test-key');
        config()->set('services.groq.model', 'openai/gpt-oss-20b');
        config()->set('services.gemini.api_key', null);
        config()->set('services.openrouter.api_key', null);
        config()->set('services.wisdomgate.api_key', null);
        config()->set('services.cohere.api_key', null);

        Http::fake([
            'https://api.groq.com/openai/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'overall' => 'This answer is solid overall, but it needs a more specific result to feel stronger.',
                                'strengths' => [
                                    'You answer the question directly.',
                                    'Your tone is professional and positive.',
                                ],
                                'improvements' => [
                                    'Add one measurable result from your example.',
                                    'Make the link to the role more explicit.',
                                ],
                                'criteria' => [
                                    'clarity' => 'Your structure is mostly clear, though the ending could be sharper.',
                                    'relevance' => 'The example fits the question, but the role connection should be stronger.',
                                    'grammar' => 'Grammar is generally clean and easy to understand.',
                                    'professionalism' => 'Your tone sounds respectful and interview-ready.',
                                ],
                                'nextStep' => 'Revise the answer so the final sentence explains the value you would bring.',
                            ]),
                        ],
                    ],
                ],
                'model' => 'openai/gpt-oss-20b',
            ]),
        ]);

        $response = $this->actingAs(User::factory()->create())->postJson(route('workspace.chatbot'), [
            'message' => 'Create automated interview feedback based on the criteria for this answer.',
            'mode' => 'feedback_review',
            'providerId' => 'groq',
            'categoryId' => 'it',
            'currentQuestion' => 'Tell me about a project you are proud of.',
            'answerDraft' => 'I built a Laravel dashboard during my internship and worked on the backend APIs.',
            'criteriaScores' => [
                'clarity' => 8.0,
                'relevance' => 7.5,
                'grammar' => 8.0,
                'professionalism' => 8.5,
                'average' => 8.0,
                'matchedKeywords' => 4,
            ],
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'provider' => 'Groq API (openai/gpt-oss-20b)',
                'providerId' => 'groq',
                'requestedProviderId' => 'groq',
                'usedFallback' => false,
            ])
            ->assertJsonPath('feedbackSummary.criteria.clarity', 'Your structure is mostly clear, though the ending could be sharper.');
    }

    public function test_chatbot_endpoint_returns_provider_field_builder_plan_when_configured(): void
    {
        config()->set('services.groq.api_key', 'groq-test-key');
        config()->set('services.groq.model', 'openai/gpt-oss-20b');
        config()->set('services.gemini.api_key', null);
        config()->set('services.openrouter.api_key', null);
        config()->set('services.wisdomgate.api_key', null);
        config()->set('services.cohere.api_key', null);

        Http::fake([
            'https://api.groq.com/openai/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'title' => 'Junior Laravel Developer',
                                'summary' => 'Practice around entry-level backend web development, API work, debugging, and teamwork in the Philippine hiring market.',
                                'instruction' => 'Generate Philippine IT interview questions focused on a Junior Laravel Developer role. Include backend APIs, debugging, teamwork, and project ownership.',
                                'suggestions' => [
                                    'Junior Laravel Developer',
                                    'Junior Full-Stack Developer',
                                    'Backend PHP Developer',
                                    'Web Application Developer',
                                ],
                            ]),
                        ],
                    ],
                ],
                'model' => 'openai/gpt-oss-20b',
            ]),
        ]);

        $response = $this->actingAs(User::factory()->create())->postJson(route('workspace.chatbot'), [
            'message' => 'I want an entry-level Laravel backend role.',
            'mode' => 'field_builder',
            'providerId' => 'groq',
            'categoryId' => 'it',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'provider' => 'Groq API (openai/gpt-oss-20b)',
                'providerId' => 'groq',
                'requestedProviderId' => 'groq',
                'usedFallback' => false,
            ])
            ->assertJsonPath('fieldPlan.title', 'Junior Laravel Developer');
    }

    public function test_chatbot_endpoint_uses_selected_groq_provider_when_configured(): void
    {
        config()->set('services.groq.api_key', 'groq-test-key');
        config()->set('services.groq.model', 'openai/gpt-oss-20b');
        config()->set('services.gemini.api_key', null);
        config()->set('services.openrouter.api_key', null);
        config()->set('services.wisdomgate.api_key', null);
        config()->set('services.cohere.api_key', null);

        Http::fake([
            'https://api.groq.com/openai/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'Groq provider reply',
                        ],
                    ],
                ],
                'model' => 'openai/gpt-oss-20b',
            ]),
        ]);

        $response = $this->actingAs(User::factory()->create())->postJson(route('workspace.chatbot'), [
            'message' => 'Give me a sample answer for a Philippine IT interview.',
            'providerId' => 'groq',
            'categoryId' => 'it',
            'currentQuestion' => 'Tell me about a project you are proud of.',
            'history' => [],
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'reply' => 'Groq provider reply',
                'provider' => 'Groq API (openai/gpt-oss-20b)',
                'providerId' => 'groq',
                'requestedProviderId' => 'groq',
                'usedFallback' => false,
            ]);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.groq.com/openai/v1/chat/completions'
                && $request['model'] === 'openai/gpt-oss-20b'
                && $request['messages'][0]['role'] === 'system';
        });
    }

    public function test_chatbot_endpoint_auto_falls_through_to_next_configured_provider(): void
    {
        config()->set('services.gemini.api_key', 'gemini-test-key');
        config()->set('services.gemini.model', 'gemini-2.5-flash');
        config()->set('services.groq.api_key', 'groq-test-key');
        config()->set('services.groq.model', 'openai/gpt-oss-20b');
        config()->set('services.interview_chatbot.provider_priority', 'gemini,groq,openrouter,claude,wisdomgate,cohere');
        config()->set('services.openrouter.api_key', null);
        config()->set('services.wisdomgate.api_key', null);
        config()->set('services.cohere.api_key', null);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'error' => [
                    'message' => 'Gemini unavailable',
                ],
            ], 500),
            'https://api.groq.com/openai/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'Groq auto fallback reply',
                        ],
                    ],
                ],
                'model' => 'openai/gpt-oss-20b',
            ]),
        ]);

        $response = $this->actingAs(User::factory()->create())->postJson(route('workspace.chatbot'), [
            'message' => 'Help me improve this scholarship answer.',
            'providerId' => 'auto',
            'categoryId' => 'scholarship',
            'currentQuestion' => 'Why do you deserve this scholarship?',
            'answerDraft' => 'I study hard and I want to help my family.',
            'history' => [],
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'reply' => 'Groq auto fallback reply',
                'provider' => 'Groq API (openai/gpt-oss-20b)',
                'providerId' => 'groq',
                'requestedProviderId' => 'auto',
                'usedFallback' => false,
            ]);

        Http::assertSentCount(2);
    }

    public function test_chatbot_endpoint_uses_selected_gemini_provider_when_configured(): void
    {
        config()->set('services.gemini.api_key', 'gemini-test-key');
        config()->set('services.gemini.model', 'gemini-2.5-flash');
        config()->set('services.groq.api_key', null);
        config()->set('services.openrouter.api_key', null);
        config()->set('services.wisdomgate.api_key', null);
        config()->set('services.cohere.api_key', null);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Gemini provider reply'],
                            ],
                        ],
                    ],
                ],
                'modelVersion' => 'gemini-2.5-flash',
            ]),
        ]);

        $response = $this->actingAs(User::factory()->create())->postJson(route('workspace.chatbot'), [
            'message' => 'Give me a sample answer for a Philippine scholarship interview.',
            'providerId' => 'gemini',
            'categoryId' => 'scholarship',
            'history' => [],
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'reply' => 'Gemini provider reply',
                'provider' => 'Gemini API (gemini-2.5-flash)',
                'providerId' => 'gemini',
                'requestedProviderId' => 'gemini',
                'usedFallback' => false,
            ]);

        Http::assertSent(function ($request) {
            return str_starts_with($request->url(), 'https://generativelanguage.googleapis.com/')
                && data_get($request->data(), 'system_instruction.parts.0.text') !== null
                && data_get($request->data(), 'contents.0.role') === 'user';
        });
    }

    public function test_chatbot_endpoint_uses_selected_openrouter_provider_when_configured(): void
    {
        config()->set('services.openrouter.api_key', 'openrouter-test-key');
        config()->set('services.openrouter.model', 'openrouter/free');
        config()->set('services.gemini.api_key', null);
        config()->set('services.groq.api_key', null);
        config()->set('services.wisdomgate.api_key', null);
        config()->set('services.cohere.api_key', null);

        Http::fake([
            'https://openrouter.ai/api/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'OpenRouter provider reply',
                        ],
                    ],
                ],
                'model' => 'openrouter/free',
            ]),
        ]);

        $response = $this->actingAs(User::factory()->create())->postJson(route('workspace.chatbot'), [
            'message' => 'Give me a follow-up question for a BPO interview.',
            'providerId' => 'openrouter',
            'categoryId' => 'job',
            'history' => [],
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'reply' => 'OpenRouter provider reply',
                'provider' => 'OpenRouter API (openrouter/free)',
                'providerId' => 'openrouter',
                'requestedProviderId' => 'openrouter',
                'usedFallback' => false,
            ]);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://openrouter.ai/api/v1/chat/completions'
                && $request->hasHeader('HTTP-Referer')
                && $request->hasHeader('X-Title')
                && $request['model'] === 'openrouter/free';
        });
    }

    public function test_chatbot_endpoint_uses_selected_claude_provider_when_configured(): void
    {
        config()->set('services.claude.api_key', 'claude-test-key');
        config()->set('services.claude.model', 'claude-haiku-4-5-20251001');
        config()->set('services.claude.version', '2023-06-01');
        config()->set('services.gemini.api_key', null);
        config()->set('services.groq.api_key', null);
        config()->set('services.openrouter.api_key', null);
        config()->set('services.wisdomgate.api_key', null);
        config()->set('services.cohere.api_key', null);

        Http::fake([
            'https://api.anthropic.com/v1/messages' => Http::response([
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'Claude provider reply',
                    ],
                ],
                'model' => 'claude-haiku-4-5-20251001',
            ]),
        ]);

        $response = $this->actingAs(User::factory()->create())->postJson(route('workspace.chatbot'), [
            'message' => 'Give me a model answer for a Philippine job interview.',
            'providerId' => 'claude',
            'categoryId' => 'job',
            'history' => [],
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'reply' => 'Claude provider reply',
                'provider' => 'Claude API (claude-haiku-4-5-20251001)',
                'providerId' => 'claude',
                'requestedProviderId' => 'claude',
                'usedFallback' => false,
            ]);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.anthropic.com/v1/messages'
                && $request->hasHeader('x-api-key')
                && $request->hasHeader('anthropic-version')
                && $request['model'] === 'claude-haiku-4-5-20251001'
                && $request['system'] !== null
                && $request['messages'][0]['role'] === 'user';
        });
    }

    public function test_chatbot_endpoint_uses_selected_wisdomgate_provider_when_configured(): void
    {
        config()->set('services.wisdomgate.api_key', 'wisdomgate-test-key');
        config()->set('services.wisdomgate.model', 'gpt-5');
        config()->set('services.wisdomgate.base_url', null);
        config()->set('services.gemini.api_key', null);
        config()->set('services.groq.api_key', null);
        config()->set('services.openrouter.api_key', null);
        config()->set('services.cohere.api_key', null);

        Http::fake([
            'https://wisgate.ai/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'Wisdom Gate provider reply',
                        ],
                    ],
                ],
                'model' => 'gpt-5',
            ]),
        ]);

        $response = $this->actingAs(User::factory()->create())->postJson(route('workspace.chatbot'), [
            'message' => 'Help me improve this answer for a Philippine job interview.',
            'providerId' => 'wisdomgate',
            'categoryId' => 'job',
            'history' => [],
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'reply' => 'Wisdom Gate provider reply',
                'provider' => 'Wisdom Gate API (gpt-5)',
                'providerId' => 'wisdomgate',
                'requestedProviderId' => 'wisdomgate',
                'usedFallback' => false,
            ]);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://wisgate.ai/v1/chat/completions'
                && $request['model'] === 'gpt-5';
        });
    }

    public function test_chatbot_endpoint_uses_selected_cohere_provider_when_configured(): void
    {
        config()->set('services.cohere.api_key', 'cohere-test-key');
        config()->set('services.cohere.model', 'command-r7b-12-2024');
        config()->set('services.gemini.api_key', null);
        config()->set('services.groq.api_key', null);
        config()->set('services.openrouter.api_key', null);
        config()->set('services.wisdomgate.api_key', null);

        Http::fake([
            'https://api.cohere.com/v2/chat' => Http::response([
                'message' => [
                    'content' => [
                        ['type' => 'text', 'text' => 'Cohere provider reply'],
                    ],
                ],
                'model' => 'command-r7b-12-2024',
            ]),
        ]);

        $response = $this->actingAs(User::factory()->create())->postJson(route('workspace.chatbot'), [
            'message' => 'Give me a model answer for a college admission interview.',
            'providerId' => 'cohere',
            'categoryId' => 'admission',
            'history' => [],
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'reply' => 'Cohere provider reply',
                'provider' => 'Cohere API (command-r7b-12-2024)',
                'providerId' => 'cohere',
                'requestedProviderId' => 'cohere',
                'usedFallback' => false,
            ]);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.cohere.com/v2/chat'
                && $request->hasHeader('X-Client-Name')
                && $request['model'] === 'command-r7b-12-2024';
        });
    }

    public function test_provider_status_endpoint_reports_all_six_api_providers_as_working_when_responses_are_healthy(): void
    {
        config()->set('services.gemini.api_key', 'gemini-test-key');
        config()->set('services.gemini.model', 'gemini-2.5-flash');
        config()->set('services.groq.api_key', 'groq-test-key');
        config()->set('services.groq.model', 'openai/gpt-oss-20b');
        config()->set('services.openrouter.api_key', 'openrouter-test-key');
        config()->set('services.openrouter.model', 'openrouter/free');
        config()->set('services.claude.api_key', 'claude-test-key');
        config()->set('services.claude.model', 'claude-haiku-4-5-20251001');
        config()->set('services.claude.version', '2023-06-01');
        config()->set('services.wisdomgate.api_key', 'wisdomgate-test-key');
        config()->set('services.wisdomgate.model', 'gpt-5');
        config()->set('services.wisdomgate.base_url', null);
        config()->set('services.cohere.api_key', 'cohere-test-key');
        config()->set('services.cohere.model', 'command-r7b-12-2024');

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'READY'],
                            ],
                        ],
                    ],
                ],
                'modelVersion' => 'gemini-2.5-flash',
            ]),
            'https://api.groq.com/openai/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'READY',
                        ],
                    ],
                ],
                'model' => 'openai/gpt-oss-20b',
            ]),
            'https://openrouter.ai/api/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'READY',
                        ],
                    ],
                ],
                'model' => 'openrouter/free',
            ]),
            'https://api.anthropic.com/v1/messages' => Http::response([
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'READY',
                    ],
                ],
                'model' => 'claude-haiku-4-5-20251001',
            ]),
            'https://wisgate.ai/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'READY',
                        ],
                    ],
                ],
                'model' => 'gpt-5',
            ]),
            'https://api.cohere.com/v2/chat' => Http::response([
                'message' => [
                    'content' => [
                        ['type' => 'text', 'text' => 'READY'],
                    ],
                ],
                'model' => 'command-r7b-12-2024',
            ]),
        ]);

        $response = $this->actingAs(User::factory()->create())->postJson(route('workspace.chatbot.providers.status'), [
            'providers' => ['gemini', 'groq', 'openrouter', 'claude', 'wisdomgate', 'cohere'],
        ]);

        $response
            ->assertOk()
            ->assertJsonCount(6, 'providers')
            ->assertJsonPath('providers.0.id', 'gemini')
            ->assertJsonPath('providers.0.state', 'working')
            ->assertJsonPath('providers.1.id', 'groq')
            ->assertJsonPath('providers.1.state', 'working')
            ->assertJsonPath('providers.2.id', 'openrouter')
            ->assertJsonPath('providers.2.state', 'working')
            ->assertJsonPath('providers.3.id', 'claude')
            ->assertJsonPath('providers.3.state', 'working')
            ->assertJsonPath('providers.4.id', 'wisdomgate')
            ->assertJsonPath('providers.4.state', 'working')
            ->assertJsonPath('providers.5.id', 'cohere')
            ->assertJsonPath('providers.5.state', 'working');
    }

    public function test_chatbot_endpoint_retries_wisdomgate_fallback_model_after_a_timeout(): void
    {
        config()->set('services.wisdomgate.api_key', 'wisdomgate-test-key');
        config()->set('services.wisdomgate.model', 'wisdom-ai-dsv3');
        config()->set('services.wisdomgate.base_url', null);
        config()->set('services.gemini.api_key', null);
        config()->set('services.groq.api_key', null);
        config()->set('services.openrouter.api_key', null);
        config()->set('services.cohere.api_key', null);

        $attempts = 0;

        Http::fake(function ($request) use (&$attempts) {
            if ($request->url() !== 'https://wisgate.ai/v1/chat/completions') {
                return Http::response([], 500);
            }

            $attempts++;

            if ($attempts === 1) {
                throw new ConnectionException('Operation timed out');
            }

            return Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'Wisdom Gate fallback model reply',
                        ],
                    ],
                ],
                'model' => 'gpt-5',
            ]);
        });

        $response = $this->actingAs(User::factory()->create())->postJson(route('workspace.chatbot'), [
            'message' => 'Coach me for a Philippine job interview.',
            'providerId' => 'wisdomgate',
            'categoryId' => 'job',
            'history' => [],
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'reply' => 'Wisdom Gate fallback model reply',
                'provider' => 'Wisdom Gate API (gpt-5)',
                'providerId' => 'wisdomgate',
                'requestedProviderId' => 'wisdomgate',
                'usedFallback' => false,
            ]);

        $this->assertSame(2, $attempts);
    }
}
