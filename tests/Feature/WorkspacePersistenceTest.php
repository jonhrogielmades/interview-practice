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
            'eyeContact' => 7.9,
            'posture' => 7.8,
            'headMovement' => 8.1,
            'facialComposure' => 7.4,
            'manuscriptVerbal' => 4.38,
            'manuscriptNonVerbal' => 3.87,
            'manuscriptOverall' => 4.23,
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
                    'manuscriptRubric' => [
                        'verbal' => 4.38,
                        'nonVerbal' => 3.87,
                        'overall' => 4.23,
                        'hasNonVerbal' => true,
                        'readinessLabel' => 'Highly Acceptable',
                        'criteria' => [
                            'clarity' => 4.5,
                            'relevance' => 4.3,
                            'grammar' => 4.3,
                            'professionalism' => 4.5,
                            'eyeContact' => 4.0,
                            'posture' => 3.9,
                            'headMovement' => 4.1,
                            'facialComposure' => 3.7,
                        ],
                    ],
                    'processEvaluations' => [
                        'response' => [
                            'label' => 'Answer Process',
                            'average' => 8.6,
                            'summary' => 'The answer process is structured and well supported.',
                            'status' => 'Ready',
                            'available' => true,
                            'algorithms' => [
                                [
                                    'name' => 'Keyword Coverage',
                                    'score' => 8.2,
                                    'detail' => 'The answer matches the active category language.',
                                    'status' => 'Ready',
                                    'available' => true,
                                ],
                                [
                                    'name' => 'STAR Structure',
                                    'score' => 8.8,
                                    'detail' => 'The answer follows a clear situation-action-result flow.',
                                    'status' => 'Ready',
                                    'available' => true,
                                ],
                                [
                                    'name' => 'Outcome Evidence',
                                    'score' => 8.7,
                                    'detail' => 'The answer includes visible project impact.',
                                    'status' => 'Ready',
                                    'available' => true,
                                ],
                            ],
                        ],
                        'bodyLanguage' => [
                            'label' => 'Body Language',
                            'average' => 7.9,
                            'summary' => 'Camera presence is steady overall.',
                            'status' => 'Live',
                            'available' => true,
                            'algorithms' => [
                                [
                                    'name' => 'Frame Centering',
                                    'score' => 8.0,
                                    'detail' => 'The face stays close to the center of the frame.',
                                    'status' => 'Live',
                                    'available' => true,
                                ],
                                [
                                    'name' => 'Head Balance',
                                    'score' => 7.6,
                                    'detail' => 'Head position is mostly level on camera.',
                                    'status' => 'Live',
                                    'available' => true,
                                ],
                                [
                                    'name' => 'Movement Stability',
                                    'score' => 8.1,
                                    'detail' => 'Movement is controlled during the answer.',
                                    'status' => 'Live',
                                    'available' => true,
                                ],
                            ],
                        ],
                        'facialExpressions' => [
                            'label' => 'Facial Expressions',
                            'average' => 7.4,
                            'summary' => 'Expression is professional with room for more warmth.',
                            'status' => 'Live',
                            'available' => true,
                            'algorithms' => [
                                [
                                    'name' => 'Smile Warmth',
                                    'score' => 7.2,
                                    'detail' => 'The expression is welcoming but could be warmer.',
                                    'status' => 'Live',
                                    'available' => true,
                                ],
                                [
                                    'name' => 'Eye Engagement',
                                    'score' => 7.8,
                                    'detail' => 'Eye focus is mostly steady.',
                                    'status' => 'Live',
                                    'available' => true,
                                ],
                                [
                                    'name' => 'Jaw Relaxation',
                                    'score' => 7.3,
                                    'detail' => 'The jaw is mostly relaxed during delivery.',
                                    'status' => 'Live',
                                    'available' => true,
                                ],
                            ],
                        ],
                    ],
                    'visualSnapshot' => [
                        'bodyLanguageScore' => 7.9,
                        'facialExpressionScore' => 7.4,
                        'eyeContactScore' => 7.9,
                        'postureScore' => 7.8,
                        'headMovementScore' => 8.1,
                        'facialComposureScore' => 7.4,
                        'bodyLanguageLabel' => 'Camera presence is steady overall.',
                        'facialExpressionLabel' => 'Expression is professional with room for more warmth.',
                        'eyeContactLabel' => 'Eye contact orientation looks engaged and camera-aware.',
                        'postureLabel' => 'Posture looks upright, centered, and interview-ready.',
                        'headMovementLabel' => 'Head movement is calm and controlled.',
                        'facialComposureLabel' => 'Facial composure looks calm and professional.',
                        'tip' => 'Relax the face slightly more before the next answer.',
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
        ->assertJsonPath('workspace.sessions.0.answers.0.feedbackSummary.provider', 'Groq API (openai/gpt-oss-20b)')
        ->assertJsonPath('workspace.sessions.0.answers.0.feedbackSummary.processEvaluations.response.label', 'Answer Process')
        ->assertJsonPath('workspace.sessions.0.answers.0.feedbackSummary.processEvaluations.bodyLanguage.algorithms.1.name', 'Head Balance')
        ->assertJsonPath('workspace.sessions.0.answers.0.feedbackSummary.manuscriptRubric.overall', 4.23)
        ->assertJsonPath('workspace.sessions.0.answers.0.feedbackSummary.visualSnapshot.eyeContactScore', 7.9)
        ->assertJsonPath('workspace.sessions.0.criteriaAverages.manuscriptOverall', 4.23)
        ->assertJsonPath('workspace.sessions.0.answers.0.feedbackSummary.visualSnapshot.tip', 'Relax the face slightly more before the next answer.');
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
