<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows dashboard metrics and latest AI evaluation from saved practice sessions', function () {
    $this->actingAs(User::factory()->create());

    $this->postJson(route('workspace.sessions.store'), [
        'id' => 'dashboard-session-001',
        'startedAt' => now()->subMinutes(10)->toISOString(),
        'savedAt' => now()->toISOString(),
        'categoryId' => 'it',
        'categoryName' => 'IT / Programming',
        'categoryDescription' => 'Philippine tech interview questions about coding, capstone work, debugging, and teamwork.',
        'questionCount' => 3,
        'answeredCount' => 2,
        'focusMode' => 'Clarity Coach',
        'pacingMode' => 'Standard',
        'timerTargetSeconds' => 180,
        'averageScore' => 8.3,
        'criteriaAverages' => [
            'clarity' => 8.5,
            'relevance' => 8.0,
            'grammar' => 8.0,
            'professionalism' => 8.7,
        ],
        'completed' => false,
        'answers' => [
            [
                'questionIndex' => 0,
                'questionNumber' => 1,
                'question' => 'Tell me about a project you are proud of.',
                'answer' => 'I built a Laravel dashboard and handled the API integration work.',
                'average' => 8.4,
                'clarity' => 8.5,
                'relevance' => 8.0,
                'grammar' => 8.0,
                'professionalism' => 8.8,
                'matchedKeywords' => 4,
                'elapsedSeconds' => 95,
                'inputMode' => 'Text',
                'feedbackSummary' => [
                    'strengths' => ['You answered directly and kept the example relevant.'],
                    'improvements' => ['Add one measurable result from the project.'],
                    'overall' => 'AI says this answer is strong overall and needs one clearer result.',
                    'nextStep' => 'Revise the final sentence so it shows business impact.',
                    'provider' => 'Groq API (openai/gpt-oss-20b)',
                    'criteria' => [
                        'clarity' => 'Clarity note from AI.',
                        'relevance' => 'Relevance note from AI.',
                        'grammar' => 'Grammar note from AI.',
                        'professionalism' => 'Professionalism note from AI.',
                    ],
                ],
            ],
            [
                'questionIndex' => 1,
                'questionNumber' => 2,
                'question' => 'How do you debug issues under pressure?',
                'answer' => 'I isolate the issue, verify logs, and test the fix before deployment.',
                'average' => 8.2,
                'clarity' => 8.5,
                'relevance' => 8.0,
                'grammar' => 8.0,
                'professionalism' => 8.6,
                'matchedKeywords' => 3,
                'elapsedSeconds' => 88,
                'inputMode' => 'Text',
                'feedbackSummary' => [
                    'strengths' => ['Your answer is concise and relevant.'],
                    'improvements' => ['Mention how you communicate updates to teammates.'],
                    'overall' => 'This answer is solid and can be even stronger with one teamwork detail.',
                    'nextStep' => 'Add one sentence about how you coordinate the fix with the team.',
                    'provider' => 'Groq API (openai/gpt-oss-20b)',
                    'criteria' => [
                        'clarity' => 'Clear process and sequencing.',
                        'relevance' => 'Relevant to the debugging prompt.',
                        'grammar' => 'Mostly polished wording.',
                        'professionalism' => 'Professional and composed tone.',
                    ],
                ],
            ],
        ],
    ])->assertCreated();

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSeeText('1 saved AI-reviewed session')
        ->assertSeeText('2 scored answers from AI practice')
        ->assertSeeText('8.3 / 10')
        ->assertSeeText('IT / Programming')
        ->assertSeeText('Groq API (openai/gpt-oss-20b)')
        ->assertSeeText('AI says this answer is strong overall and needs one clearer result.')
        ->assertSeeText('Revise the final sentence so it shows business impact.')
        ->assertSeeText('Clarity note from AI.');
});
