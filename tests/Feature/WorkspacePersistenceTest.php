<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('stores and returns setup and session data through workspace endpoints', function () {
    $this->actingAs(User::factory()->create());

    $setupPayload = [
        'questionCount' => 5,
        'focusModeIndex' => 2,
        'pacingModeIndex' => 1,
        'preferredCategoryId' => 'it',
        'voiceMode' => 'hybrid',
        'notes' => 'Focus on concise technical examples.',
    ];

    $this->putJson(route('workspace.setup.update'), $setupPayload)
        ->assertOk()
        ->assertJsonPath('setup.questionCount', 5)
        ->assertJsonPath('setup.preferredCategoryId', 'it');

    $sessionPayload = [
        'id' => 'session-test-001',
        'startedAt' => now()->subMinutes(5)->toISOString(),
        'savedAt' => now()->toISOString(),
        'categoryId' => 'it',
        'categoryName' => 'IT / Programming',
        'categoryDescription' => 'Technical questions focused on coding, tools, and project experience.',
        'questionCount' => 3,
        'answeredCount' => 3,
        'focusMode' => 'Clarity Coach',
        'pacingMode' => 'Quick',
        'timerTargetSeconds' => 90,
        'averageScore' => 8.8,
        'criteriaAverages' => [
            'clarity' => 9,
            'relevance' => 8.5,
            'grammar' => 8.5,
            'professionalism' => 9.0,
        ],
        'completed' => true,
        'answers' => [
            [
                'questionIndex' => 0,
                'questionNumber' => 1,
                'question' => 'Tell me about a project you developed and your role in it.',
                'answer' => 'I built a Laravel scheduling dashboard and led the backend integration work.',
                'average' => 8.8,
                'clarity' => 9,
                'relevance' => 8.5,
                'grammar' => 8.5,
                'professionalism' => 9.0,
                'matchedKeywords' => 3,
                'elapsedSeconds' => 74,
                'inputMode' => 'Text',
                'feedbackSummary' => [
                    'strengths' => ['Your answer is clear and easy to follow.'],
                    'improvements' => ['Add one more measurable result to strengthen the example.'],
                    'overall' => 'This answer is strong overall, but it needs one more measurable result.',
                    'nextStep' => 'Add a final sentence that explains the business impact of your work.',
                    'provider' => 'Groq API (openai/gpt-oss-20b)',
                    'criteria' => [
                        'clarity' => 'The structure is easy to follow.',
                        'relevance' => 'The project example fits the question well.',
                        'grammar' => 'The wording is mostly polished.',
                        'professionalism' => 'The tone sounds professional and confident.',
                    ],
                ],
            ],
        ],
    ];

    $this->postJson(route('workspace.sessions.store'), $sessionPayload)
        ->assertCreated()
        ->assertJsonPath('session.id', 'session-test-001')
        ->assertJsonPath('session.answers.0.questionNumber', 1);

    $this->getJson(route('workspace.bootstrap'))
        ->assertOk()
        ->assertJsonPath('workspace.setup.voiceMode', 'hybrid')
        ->assertJsonPath('workspace.sessions.0.id', 'session-test-001')
        ->assertJsonPath('workspace.sessions.0.answers.0.feedbackSummary.strengths.0', 'Your answer is clear and easy to follow.')
        ->assertJsonPath('workspace.sessions.0.answers.0.feedbackSummary.criteria.clarity', 'The structure is easy to follow.')
        ->assertJsonPath('workspace.sessions.0.answers.0.feedbackSummary.provider', 'Groq API (openai/gpt-oss-20b)');
});

it('clears setup and sessions independently', function () {
    $this->actingAs(User::factory()->create());

    $this->putJson(route('workspace.setup.update'), [
        'questionCount' => 3,
        'focusModeIndex' => 0,
        'pacingModeIndex' => 0,
        'preferredCategoryId' => 'job',
        'voiceMode' => 'text',
        'notes' => 'Ready for a general mock interview.',
    ])->assertOk();

    $this->postJson(route('workspace.sessions.store'), [
        'id' => 'session-clear-001',
        'savedAt' => now()->toISOString(),
        'categoryId' => 'job',
        'categoryName' => 'Job Interview',
        'questionCount' => 3,
        'answeredCount' => 1,
        'focusMode' => 'Balanced Coach',
        'pacingMode' => 'Standard',
        'timerTargetSeconds' => 180,
        'averageScore' => 7.5,
        'criteriaAverages' => [
            'clarity' => 7.5,
            'relevance' => 7.5,
            'grammar' => 7.5,
            'professionalism' => 7.5,
        ],
        'completed' => false,
        'answers' => [],
    ])->assertCreated();

    $this->deleteJson(route('workspace.setup.destroy'))
        ->assertOk()
        ->assertJsonPath('setup.savedAt', null);

    $this->deleteJson(route('workspace.sessions.destroy'))
        ->assertOk()
        ->assertJsonCount(0, 'workspace.sessions');
});
