<?php

namespace App\Helpers;

use App\Models\InterviewSession;
use App\Models\InterviewSessionSetup;
use App\Support\InterviewPracticeCatalog;
use App\Support\InterviewWorkspaceResolver;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class InterviewWorkspaceService
{
    public function __construct(
        private readonly InterviewWorkspaceResolver $resolver,
    ) {
    }

    public function bootstrap(): array
    {
        if (! $this->schemaAvailable()) {
            return $this->emptyWorkspace();
        }

        $workspaceToken = $this->resolver->currentToken();

        return [
            'setup' => $this->loadSetup($workspaceToken),
            'sessions' => $this->loadSessions($workspaceToken),
        ];
    }

    public function saveSetup(array $input): array
    {
        $normalized = InterviewPracticeCatalog::normalizeSessionSetup($input);

        if (! $this->schemaAvailable()) {
            $normalized['savedAt'] = now()->toISOString();

            return $normalized;
        }

        $values = [
            'question_count' => $normalized['questionCount'],
            'focus_mode_index' => $normalized['focusModeIndex'],
            'pacing_mode_index' => $normalized['pacingModeIndex'],
            'preferred_category_id' => $normalized['preferredCategoryId'],
            'voice_mode' => $normalized['voiceMode'],
            'notes' => $normalized['notes'],
            'saved_at' => now(),
        ];

        if (Schema::hasColumn('interview_session_setups', 'advanced_options')) {
            $values['advanced_options'] = $this->sessionSetupAdvancedOptions($normalized);
        }

        $setup = InterviewSessionSetup::query()->updateOrCreate(
            ['workspace_token' => $this->resolver->currentToken()],
            $values,
        );

        return $this->mapSetup($setup);
    }

    public function clearSetup(): array
    {
        if ($this->schemaAvailable()) {
            InterviewSessionSetup::query()
                ->where('workspace_token', $this->resolver->currentToken())
                ->delete();
        }

        return InterviewPracticeCatalog::defaultSessionSetup();
    }

    public function saveSession(array $input): array
    {
        $normalized = $this->normalizeSessionPayload($input);

        if (! $this->schemaAvailable()) {
            return $normalized;
        }

        $workspaceToken = $this->resolver->currentToken();

        $session = DB::transaction(function () use ($workspaceToken, $normalized) {
            $session = InterviewSession::query()->updateOrCreate(
                [
                    'workspace_token' => $workspaceToken,
                    'public_id' => $normalized['id'],
                ],
                [
                    'started_at' => $normalized['startedAt'],
                    'saved_at' => $normalized['savedAt'],
                    'category_id' => $normalized['categoryId'],
                    'category_name' => $normalized['categoryName'],
                    'category_description' => $normalized['categoryDescription'],
                    'question_count' => $normalized['questionCount'],
                    'answered_count' => $normalized['answeredCount'],
                    'focus_mode' => $normalized['focusMode'],
                    'pacing_mode' => $normalized['pacingMode'],
                    'timer_target_seconds' => $normalized['timerTargetSeconds'],
                    'average_score' => $normalized['averageScore'],
                    'criteria_averages' => $normalized['criteriaAverages'],
                    'completed' => $normalized['completed'],
                ],
            );

            $session->answers()->delete();

            foreach ($normalized['answers'] as $answer) {
                $session->answers()->create([
                    'question_index' => $answer['questionIndex'],
                    'question_number' => $answer['questionNumber'],
                    'question' => $answer['question'],
                    'answer' => $answer['answer'],
                    'average_score' => $answer['average'],
                    'clarity' => $answer['clarity'],
                    'relevance' => $answer['relevance'],
                    'grammar' => $answer['grammar'],
                    'professionalism' => $answer['professionalism'],
                    'matched_keywords' => $answer['matchedKeywords'],
                    'elapsed_seconds' => $answer['elapsedSeconds'],
                    'input_mode' => $answer['inputMode'],
                    'feedback_summary' => $answer['feedbackSummary'],
                ]);
            }

            return $session->fresh('answers');
        });

        $this->trimSavedSessions($workspaceToken);

        return $this->mapSession($session);
    }

    public function clearSessions(): array
    {
        if ($this->schemaAvailable()) {
            InterviewSession::query()
                ->where('workspace_token', $this->resolver->currentToken())
                ->delete();
        }

        return [];
    }

    protected function emptyWorkspace(): array
    {
        return [
            'setup' => InterviewPracticeCatalog::defaultSessionSetup(),
            'sessions' => [],
        ];
    }

    protected function schemaAvailable(): bool
    {
        try {
            return Schema::hasTable('interview_session_setups')
                && Schema::hasTable('interview_sessions')
                && Schema::hasTable('interview_session_answers');
        } catch (Throwable) {
            return false;
        }
    }

    protected function loadSetup(string $workspaceToken): array
    {
        $setup = InterviewSessionSetup::query()
            ->where('workspace_token', $workspaceToken)
            ->first();

        return $setup ? $this->mapSetup($setup) : InterviewPracticeCatalog::defaultSessionSetup();
    }

    protected function loadSessions(string $workspaceToken): array
    {
        return InterviewSession::query()
            ->with('answers')
            ->where('workspace_token', $workspaceToken)
            ->orderByDesc('saved_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (InterviewSession $session) => $this->mapSession($session))
            ->all();
    }

    protected function mapSetup(InterviewSessionSetup $setup): array
    {
        return InterviewPracticeCatalog::normalizeSessionSetup([
            'questionCount' => $setup->question_count,
            'focusModeIndex' => $setup->focus_mode_index,
            'pacingModeIndex' => $setup->pacing_mode_index,
            'preferredCategoryId' => $setup->preferred_category_id,
            'voiceMode' => $setup->voice_mode,
            'notes' => $setup->notes,
            'advancedOptions' => (array) ($setup->advanced_options ?? []),
            'savedAt' => $setup->saved_at?->toISOString(),
        ]);
    }

    protected function sessionSetupAdvancedOptions(array $setup): array
    {
        return [
            'targetField' => $this->sanitizeText($setup['targetField'] ?? null, 120) ?? '',
            'panelMode' => in_array(($setup['panelMode'] ?? 'single'), ['single', 'panel', 'hr', 'technical', 'scholarship', 'admission'], true)
                ? $setup['panelMode']
                : 'single',
            'difficultMode' => (bool) ($setup['difficultMode'] ?? false),
            'fillerTracking' => (bool) ($setup['fillerTracking'] ?? true),
            'adviserReviewMode' => (bool) ($setup['adviserReviewMode'] ?? false),
            'weeklyGoal' => max(1, min(7, (int) ($setup['weeklyGoal'] ?? 3))),
            'reminderDays' => array_values(array_filter(
                (array) ($setup['reminderDays'] ?? []),
                fn ($day) => is_string($day) && $day !== ''
            )),
        ];
    }

    protected function mapSession(InterviewSession $session): array
    {
        $criteria = Arr::only((array) ($session->criteria_averages ?? []), [
            'clarity',
            'relevance',
            'grammar',
            'professionalism',
            'eyeContact',
            'posture',
            'headMovement',
            'facialComposure',
            'manuscriptVerbal',
            'manuscriptNonVerbal',
            'manuscriptOverall',
        ]);

        return [
            'id' => $session->public_id,
            'startedAt' => $session->started_at?->toISOString(),
            'savedAt' => $session->saved_at?->toISOString(),
            'categoryId' => $session->category_id,
            'categoryName' => $session->category_name,
            'categoryDescription' => $session->category_description,
            'questionCount' => (int) $session->question_count,
            'answeredCount' => (int) $session->answered_count,
            'focusMode' => $session->focus_mode,
            'pacingMode' => $session->pacing_mode,
            'timerTargetSeconds' => (int) $session->timer_target_seconds,
            'averageScore' => round((float) $session->average_score, 1),
            'criteriaAverages' => [
                'clarity' => round((float) ($criteria['clarity'] ?? 0), 1),
                'relevance' => round((float) ($criteria['relevance'] ?? 0), 1),
                'grammar' => round((float) ($criteria['grammar'] ?? 0), 1),
                'professionalism' => round((float) ($criteria['professionalism'] ?? 0), 1),
                'eyeContact' => round((float) ($criteria['eyeContact'] ?? 0), 1),
                'posture' => round((float) ($criteria['posture'] ?? 0), 1),
                'headMovement' => round((float) ($criteria['headMovement'] ?? 0), 1),
                'facialComposure' => round((float) ($criteria['facialComposure'] ?? 0), 1),
                'manuscriptVerbal' => round((float) ($criteria['manuscriptVerbal'] ?? 0), 2),
                'manuscriptNonVerbal' => round((float) ($criteria['manuscriptNonVerbal'] ?? 0), 2),
                'manuscriptOverall' => round((float) ($criteria['manuscriptOverall'] ?? 0), 2),
            ],
            'completed' => (bool) $session->completed,
            'answers' => $session->answers->map(function ($answer) {
                $feedbackSummary = (array) ($answer->feedback_summary ?? []);

                return [
                    'questionIndex' => (int) $answer->question_index,
                    'questionNumber' => (int) $answer->question_number,
                    'question' => $answer->question,
                    'answer' => $answer->answer,
                    'average' => round((float) $answer->average_score, 1),
                    'clarity' => round((float) $answer->clarity, 1),
                    'relevance' => round((float) $answer->relevance, 1),
                    'grammar' => round((float) $answer->grammar, 1),
                    'professionalism' => round((float) $answer->professionalism, 1),
                    'matchedKeywords' => (int) $answer->matched_keywords,
                    'elapsedSeconds' => (int) $answer->elapsed_seconds,
                    'inputMode' => $answer->input_mode,
                    'feedbackSummary' => [
                        'strengths' => array_values(array_filter(array_map(
                            fn ($item) => $this->sanitizeText($item, 255),
                            Arr::wrap($feedbackSummary['strengths'] ?? [])
                        ))),
                        'improvements' => array_values(array_filter(array_map(
                            fn ($item) => $this->sanitizeText($item, 255),
                            Arr::wrap($feedbackSummary['improvements'] ?? [])
                        ))),
                        'overall' => $this->sanitizeText($feedbackSummary['overall'] ?? null, 1200),
                        'nextStep' => $this->sanitizeText($feedbackSummary['nextStep'] ?? null, 500),
                        'provider' => $this->sanitizeText($feedbackSummary['provider'] ?? null, 255),
                        'criteria' => [
                            'clarity' => $this->sanitizeText(data_get($feedbackSummary, 'criteria.clarity'), 500),
                            'relevance' => $this->sanitizeText(data_get($feedbackSummary, 'criteria.relevance'), 500),
                            'grammar' => $this->sanitizeText(data_get($feedbackSummary, 'criteria.grammar'), 500),
                            'professionalism' => $this->sanitizeText(data_get($feedbackSummary, 'criteria.professionalism'), 500),
                        ],
                        'manuscriptRubric' => $this->normalizeManuscriptRubric(
                            (array) ($feedbackSummary['manuscriptRubric'] ?? [])
                        ),
                        'processEvaluations' => $this->normalizeProcessEvaluations(
                            (array) ($feedbackSummary['processEvaluations'] ?? [])
                        ),
                        'visualSnapshot' => $this->normalizeVisualSnapshot(
                            (array) ($feedbackSummary['visualSnapshot'] ?? [])
                        ),
                        'answerVersions' => $this->normalizeAnswerVersions(
                            (array) ($feedbackSummary['answerVersions'] ?? [])
                        ),
                        'speakingHabits' => $this->normalizeSpeakingHabits(
                            (array) ($feedbackSummary['speakingHabits'] ?? [])
                        ),
                        'panel' => $this->normalizePanelContext(
                            (array) ($feedbackSummary['panel'] ?? [])
                        ),
                        'documentAnalysis' => $this->normalizeDocumentAnalysis(
                            (array) ($feedbackSummary['documentAnalysis'] ?? [])
                        ),
                        'adviserReview' => $this->normalizeAdviserReview(
                            (array) ($feedbackSummary['adviserReview'] ?? [])
                        ),
                    ],
                ];
            })->all(),
        ];
    }

    protected function normalizeSessionPayload(array $input): array
    {
        $criteria = $this->normalizeCriteria($input['criteriaAverages'] ?? []);
        $answers = collect(Arr::wrap($input['answers'] ?? []))
            ->filter(fn ($answer) => is_array($answer))
            ->map(fn ($answer) => $this->normalizeAnswerPayload($answer))
            ->sortBy('questionIndex')
            ->values()
            ->all();
        $answeredCount = max((int) ($input['answeredCount'] ?? count($answers)), count($answers));
        $questionCount = max((int) ($input['questionCount'] ?? $answeredCount), $answeredCount);

        return [
            'id' => $this->sanitizeIdentifier($input['id'] ?? null) ?? ('session-'.Str::lower((string) Str::uuid())),
            'startedAt' => $this->normalizeDateTimeString($input['startedAt'] ?? null) ?? now()->toISOString(),
            'savedAt' => $this->normalizeDateTimeString($input['savedAt'] ?? null) ?? now()->toISOString(),
            'categoryId' => $this->sanitizeText($input['categoryId'] ?? null, 50),
            'categoryName' => $this->sanitizeText($input['categoryName'] ?? null, 255) ?? 'Unknown Category',
            'categoryDescription' => $this->sanitizeText($input['categoryDescription'] ?? null, 4000),
            'questionCount' => min(50, $questionCount),
            'answeredCount' => min(50, $answeredCount),
            'focusMode' => $this->sanitizeText($input['focusMode'] ?? null, 120) ?? 'Balanced Coach',
            'pacingMode' => $this->sanitizeText($input['pacingMode'] ?? null, 120) ?? 'Standard',
            'timerTargetSeconds' => max(0, min(3600, (int) ($input['timerTargetSeconds'] ?? 0))),
            'averageScore' => $this->normalizeScore($input['averageScore'] ?? 0),
            'criteriaAverages' => $criteria,
            'completed' => (bool) ($input['completed'] ?? false),
            'answers' => $answers,
        ];
    }

    protected function normalizeAnswerPayload(array $input): array
    {
        return [
            'questionIndex' => max(0, min(50, (int) ($input['questionIndex'] ?? 0))),
            'questionNumber' => max(0, min(50, (int) ($input['questionNumber'] ?? 0))),
            'question' => $this->sanitizeText($input['question'] ?? null, 4000) ?? 'Untitled question',
            'answer' => $this->sanitizeText($input['answer'] ?? null, 20000) ?? '',
            'average' => $this->normalizeScore($input['average'] ?? 0),
            'clarity' => $this->normalizeScore($input['clarity'] ?? 0),
            'relevance' => $this->normalizeScore($input['relevance'] ?? 0),
            'grammar' => $this->normalizeScore($input['grammar'] ?? 0),
            'professionalism' => $this->normalizeScore($input['professionalism'] ?? 0),
            'matchedKeywords' => max(0, min(999, (int) ($input['matchedKeywords'] ?? 0))),
            'elapsedSeconds' => max(0, min(7200, (int) ($input['elapsedSeconds'] ?? 0))),
            'inputMode' => $this->sanitizeText($input['inputMode'] ?? null, 20) ?? 'Text',
            'feedbackSummary' => [
                'strengths' => array_values(array_filter(array_map(
                    fn ($item) => $this->sanitizeText($item, 255),
                    Arr::wrap(data_get($input, 'feedbackSummary.strengths', []))
                ))),
                'improvements' => array_values(array_filter(array_map(
                    fn ($item) => $this->sanitizeText($item, 255),
                    Arr::wrap(data_get($input, 'feedbackSummary.improvements', []))
                ))),
                'overall' => $this->sanitizeText(data_get($input, 'feedbackSummary.overall'), 1200),
                'nextStep' => $this->sanitizeText(data_get($input, 'feedbackSummary.nextStep'), 500),
                'provider' => $this->sanitizeText(data_get($input, 'feedbackSummary.provider'), 255),
                'criteria' => [
                    'clarity' => $this->sanitizeText(data_get($input, 'feedbackSummary.criteria.clarity'), 500),
                    'relevance' => $this->sanitizeText(data_get($input, 'feedbackSummary.criteria.relevance'), 500),
                    'grammar' => $this->sanitizeText(data_get($input, 'feedbackSummary.criteria.grammar'), 500),
                    'professionalism' => $this->sanitizeText(data_get($input, 'feedbackSummary.criteria.professionalism'), 500),
                ],
                'manuscriptRubric' => $this->normalizeManuscriptRubric(
                    (array) data_get($input, 'feedbackSummary.manuscriptRubric', [])
                ),
                'processEvaluations' => $this->normalizeProcessEvaluations(
                    (array) data_get($input, 'feedbackSummary.processEvaluations', [])
                ),
                'visualSnapshot' => $this->normalizeVisualSnapshot(
                    (array) data_get($input, 'feedbackSummary.visualSnapshot', [])
                ),
                'answerVersions' => $this->normalizeAnswerVersions(
                    (array) data_get($input, 'feedbackSummary.answerVersions', [])
                ),
                'speakingHabits' => $this->normalizeSpeakingHabits(
                    (array) data_get($input, 'feedbackSummary.speakingHabits', [])
                ),
                'panel' => $this->normalizePanelContext(
                    (array) data_get($input, 'feedbackSummary.panel', [])
                ),
                'documentAnalysis' => $this->normalizeDocumentAnalysis(
                    (array) data_get($input, 'feedbackSummary.documentAnalysis', [])
                ),
                'adviserReview' => $this->normalizeAdviserReview(
                    (array) data_get($input, 'feedbackSummary.adviserReview', [])
                ),
            ],
        ];
    }

    protected function normalizeAnswerVersions(array $input): array
    {
        return collect(Arr::wrap($input))
            ->filter(fn ($version) => is_array($version))
            ->take(8)
            ->map(function (array $version, int $index) {
                $answer = $this->sanitizeText($version['answer'] ?? null, 20000);

                if ($answer === null) {
                    return null;
                }

                return [
                    'label' => $this->sanitizeText($version['label'] ?? null, 80) ?? ($index === 0 ? 'First Answer' : 'Improved Answer'),
                    'answer' => $answer,
                    'savedAt' => $this->normalizeDateTimeString($version['savedAt'] ?? null) ?? now()->toISOString(),
                    'source' => $this->sanitizeText($version['source'] ?? null, 120) ?? 'Practice workspace',
                    'question' => $this->sanitizeText($version['question'] ?? null, 4000),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    protected function normalizeSpeakingHabits(array $input): array
    {
        if ($input === []) {
            return [];
        }

        $fillerCounts = collect((array) ($input['fillerCounts'] ?? []))
            ->mapWithKeys(function (mixed $count, mixed $key) {
                $cleanKey = $this->sanitizeIdentifier((string) $key);

                if ($cleanKey === null) {
                    return [];
                }

                return [$cleanKey => max(0, min(999, (int) $count))];
            })
            ->take(12)
            ->all();

        $dominantFillers = collect(Arr::wrap($input['dominantFillers'] ?? []))
            ->filter(fn ($item) => is_array($item))
            ->take(5)
            ->map(function (array $item) {
                return [
                    'key' => $this->sanitizeIdentifier($item['key'] ?? null) ?? 'filler',
                    'label' => $this->sanitizeText($item['label'] ?? null, 80) ?? 'Filler',
                    'count' => max(0, min(999, (int) ($item['count'] ?? 0))),
                ];
            })
            ->all();

        $normalized = [
            'fillerCounts' => $fillerCounts,
            'totalFillers' => max(0, min(999, (int) ($input['totalFillers'] ?? 0))),
            'totalWords' => max(0, min(5000, (int) ($input['totalWords'] ?? 0))),
            'fillerRate' => round(max(0, min(100, (float) ($input['fillerRate'] ?? 0))), 1),
            'repeatedPhraseCount' => max(0, min(999, (int) ($input['repeatedPhraseCount'] ?? 0))),
            'longPauseCount' => max(0, min(999, (int) ($input['longPauseCount'] ?? 0))),
            'pacePerMinute' => max(0, min(500, (int) ($input['pacePerMinute'] ?? 0))),
            'dominantFillers' => $dominantFillers,
            'summary' => $this->sanitizeText($input['summary'] ?? null, 500),
        ];

        return array_filter($normalized, fn ($value) => $value !== null && $value !== []);
    }

    protected function normalizePanelContext(array $input): array
    {
        if ($input === []) {
            return [];
        }

        $normalized = [
            'mode' => $this->sanitizeText($input['mode'] ?? null, 50),
            'role' => $this->sanitizeText($input['role'] ?? null, 120),
        ];

        return array_filter($normalized, fn ($value) => $value !== null);
    }

    protected function normalizeDocumentAnalysis(array $input): array
    {
        if ($input === []) {
            return [];
        }

        $normalized = [
            'categoryName' => $this->sanitizeText($input['categoryName'] ?? null, 120),
            'analyzedAt' => $this->normalizeDateTimeString($input['analyzedAt'] ?? null),
            'signalCount' => max(0, min(999, (int) ($input['signalCount'] ?? 0))),
        ];

        return array_filter($normalized, fn ($value) => $value !== null);
    }

    protected function normalizeAdviserReview(array $input): array
    {
        if ($input === []) {
            return [];
        }

        $normalized = [
            'reviewer' => $this->sanitizeText($input['reviewer'] ?? null, 120) ?? 'Teacher / Adviser',
            'sessionComment' => $this->sanitizeText($input['sessionComment'] ?? null, 2000),
            'updatedAt' => $this->normalizeDateTimeString($input['updatedAt'] ?? null) ?? now()->toISOString(),
            'status' => $this->sanitizeText($input['status'] ?? null, 80) ?? 'Reviewed',
        ];

        return array_filter($normalized, fn ($value) => $value !== null);
    }

    protected function normalizeProcessEvaluations(array $input): array
    {
        $normalized = collect(['response', 'bodyLanguage', 'facialExpressions'])
            ->mapWithKeys(function (string $key) use ($input) {
                $process = $this->normalizeProcessEvaluation(
                    is_array($input[$key] ?? null) ? $input[$key] : []
                );

                return $process === [] ? [] : [$key => $process];
            })
            ->all();

        return $normalized;
    }

    protected function normalizeProcessEvaluation(array $input): array
    {
        if ($input === []) {
            return [];
        }

        $algorithms = collect(Arr::wrap($input['algorithms'] ?? []))
            ->filter(fn ($algorithm) => is_array($algorithm))
            ->take(8)
            ->map(function (array $algorithm) {
                $normalized = [
                    'name' => $this->sanitizeText($algorithm['name'] ?? null, 120),
                    'score' => array_key_exists('score', $algorithm) && $algorithm['score'] !== null
                        ? $this->normalizeScore($algorithm['score'])
                        : null,
                    'detail' => $this->sanitizeText($algorithm['detail'] ?? null, 500),
                    'status' => $this->sanitizeText($algorithm['status'] ?? null, 50),
                    'available' => (bool) ($algorithm['available'] ?? true),
                ];

                return array_filter($normalized, fn ($value) => $value !== null);
            })
            ->filter(fn (array $algorithm) => $algorithm !== [])
            ->values()
            ->all();

        $normalized = [
            'label' => $this->sanitizeText($input['label'] ?? null, 120),
            'average' => array_key_exists('average', $input) && $input['average'] !== null
                ? $this->normalizeScore($input['average'])
                : null,
            'summary' => $this->sanitizeText($input['summary'] ?? null, 500),
            'status' => $this->sanitizeText($input['status'] ?? null, 50),
            'available' => (bool) ($input['available'] ?? true),
            'algorithms' => $algorithms,
        ];

        $filtered = array_filter($normalized, fn ($value) => $value !== null && $value !== []);

        return $filtered === [] ? [] : $filtered;
    }

    protected function normalizeVisualSnapshot(array $input): array
    {
        if ($input === []) {
            return [];
        }

        $normalized = [
            'bodyLanguageScore' => array_key_exists('bodyLanguageScore', $input) && $input['bodyLanguageScore'] !== null
                ? $this->normalizeScore($input['bodyLanguageScore'])
                : null,
            'facialExpressionScore' => array_key_exists('facialExpressionScore', $input) && $input['facialExpressionScore'] !== null
                ? $this->normalizeScore($input['facialExpressionScore'])
                : null,
            'eyeContactScore' => array_key_exists('eyeContactScore', $input) && $input['eyeContactScore'] !== null
                ? $this->normalizeScore($input['eyeContactScore'])
                : null,
            'postureScore' => array_key_exists('postureScore', $input) && $input['postureScore'] !== null
                ? $this->normalizeScore($input['postureScore'])
                : null,
            'headMovementScore' => array_key_exists('headMovementScore', $input) && $input['headMovementScore'] !== null
                ? $this->normalizeScore($input['headMovementScore'])
                : null,
            'facialComposureScore' => array_key_exists('facialComposureScore', $input) && $input['facialComposureScore'] !== null
                ? $this->normalizeScore($input['facialComposureScore'])
                : null,
            'bodyLanguageLabel' => $this->sanitizeText($input['bodyLanguageLabel'] ?? null, 255),
            'facialExpressionLabel' => $this->sanitizeText($input['facialExpressionLabel'] ?? null, 255),
            'eyeContactLabel' => $this->sanitizeText($input['eyeContactLabel'] ?? null, 255),
            'postureLabel' => $this->sanitizeText($input['postureLabel'] ?? null, 255),
            'headMovementLabel' => $this->sanitizeText($input['headMovementLabel'] ?? null, 255),
            'facialComposureLabel' => $this->sanitizeText($input['facialComposureLabel'] ?? null, 255),
            'tip' => $this->sanitizeText($input['tip'] ?? null, 500),
        ];

        $filtered = array_filter($normalized, fn ($value) => $value !== null);

        return $filtered === [] ? [] : $filtered;
    }

    protected function normalizeCriteria(array $criteria): array
    {
        return [
            'clarity' => $this->normalizeScore($criteria['clarity'] ?? 0),
            'relevance' => $this->normalizeScore($criteria['relevance'] ?? 0),
            'grammar' => $this->normalizeScore($criteria['grammar'] ?? 0),
            'professionalism' => $this->normalizeScore($criteria['professionalism'] ?? 0),
            'eyeContact' => $this->normalizeScore($criteria['eyeContact'] ?? 0),
            'posture' => $this->normalizeScore($criteria['posture'] ?? 0),
            'headMovement' => $this->normalizeScore($criteria['headMovement'] ?? 0),
            'facialComposure' => $this->normalizeScore($criteria['facialComposure'] ?? 0),
            'manuscriptVerbal' => $this->normalizeRubricScore($criteria['manuscriptVerbal'] ?? 0),
            'manuscriptNonVerbal' => $this->normalizeRubricScore($criteria['manuscriptNonVerbal'] ?? 0),
            'manuscriptOverall' => $this->normalizeRubricScore($criteria['manuscriptOverall'] ?? 0),
        ];
    }

    protected function normalizeScore(mixed $value): float
    {
        return round(max(0, min(10, (float) $value)), 1);
    }

    protected function normalizeRubricScore(mixed $value): float
    {
        return round(max(0, min(5, (float) $value)), 2);
    }

    protected function normalizeManuscriptRubric(array $input): array
    {
        if ($input === []) {
            return [];
        }

        $normalized = [
            'verbal' => array_key_exists('verbal', $input) && $input['verbal'] !== null
                ? $this->normalizeRubricScore($input['verbal'])
                : null,
            'nonVerbal' => array_key_exists('nonVerbal', $input) && $input['nonVerbal'] !== null
                ? $this->normalizeRubricScore($input['nonVerbal'])
                : null,
            'overall' => array_key_exists('overall', $input) && $input['overall'] !== null
                ? $this->normalizeRubricScore($input['overall'])
                : null,
            'hasNonVerbal' => array_key_exists('hasNonVerbal', $input)
                ? (bool) $input['hasNonVerbal']
                : null,
            'readinessLabel' => $this->sanitizeText($input['readinessLabel'] ?? null, 120),
            'criteria' => [
                'clarity' => $this->normalizeRubricScore(data_get($input, 'criteria.clarity', 0)),
                'relevance' => $this->normalizeRubricScore(data_get($input, 'criteria.relevance', 0)),
                'grammar' => $this->normalizeRubricScore(data_get($input, 'criteria.grammar', 0)),
                'professionalism' => $this->normalizeRubricScore(data_get($input, 'criteria.professionalism', 0)),
                'eyeContact' => $this->normalizeRubricScore(data_get($input, 'criteria.eyeContact', 0)),
                'posture' => $this->normalizeRubricScore(data_get($input, 'criteria.posture', 0)),
                'headMovement' => $this->normalizeRubricScore(data_get($input, 'criteria.headMovement', 0)),
                'facialComposure' => $this->normalizeRubricScore(data_get($input, 'criteria.facialComposure', 0)),
            ],
        ];

        $filtered = array_filter($normalized, fn ($value) => $value !== null && $value !== []);

        return $filtered === [] ? [] : $filtered;
    }

    protected function sanitizeIdentifier(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $cleaned = preg_replace('/[^A-Za-z0-9\-_]/', '-', trim($value)) ?: '';

        return $cleaned !== '' ? Str::limit($cleaned, 120, '') : null;
    }

    protected function sanitizeText(mixed $value, int $limit): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $normalized = trim(preg_replace('/\s+/u', ' ', (string) $value) ?? '');

        return $normalized !== '' ? Str::limit($normalized, $limit, '') : null;
    }

    protected function normalizeDateTimeString(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toISOString();
        } catch (Throwable) {
            return null;
        }
    }

    protected function trimSavedSessions(string $workspaceToken): void
    {
        $staleIds = InterviewSession::query()
            ->where('workspace_token', $workspaceToken)
            ->orderByDesc('saved_at')
            ->orderByDesc('id')
            ->pluck('id')
            ->slice(100);

        if ($staleIds->isNotEmpty()) {
            InterviewSession::query()->whereIn('id', $staleIds)->delete();
        }
    }
}
