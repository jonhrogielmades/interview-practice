<?php

namespace App\Http\Controllers;

use App\Helpers\InterviewWorkspaceService;
use App\Support\InterviewPracticeCatalog;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index(InterviewWorkspaceService $workspace): View
    {
        $sessions = collect($workspace->bootstrap()['sessions'] ?? []);
        $evaluations = $this->flattenEvaluations($sessions);
        $latestSession = $sessions->first();
        $latestEvaluation = $this->latestEvaluation($evaluations);
        $latestFeedback = is_array($latestEvaluation['feedbackSummary'] ?? null)
            ? $latestEvaluation['feedbackSummary']
            : [];
        $capstoneRubric = $this->capstoneRubric($latestEvaluation, $latestFeedback);

        return view('pages.dashboard.interview', [
            'title' => 'User Dashboard',
            'summaryCards' => [
                [
                    'label' => 'Total Sessions',
                    'value' => (string) $sessions->count(),
                    'detail' => $sessions->count() > 0
                        ? sprintf('%d saved AI-reviewed session%s', $sessions->count(), $sessions->count() === 1 ? '' : 's')
                        : 'No saved sessions yet',
                    'tone' => 'success',
                ],
                [
                    'label' => 'Questions Answered',
                    'value' => (string) $this->totalAnsweredQuestions($sessions),
                    'detail' => $evaluations->count() > 0
                        ? sprintf('%d scored answer%s from AI practice', $evaluations->count(), $evaluations->count() === 1 ? '' : 's')
                        : 'Answer a question to begin tracking',
                    'tone' => 'blue',
                ],
                [
                    'label' => 'Average Score',
                    'value' => sprintf('%.1f / 10', $this->averageScore($sessions, $evaluations)),
                    'detail' => $evaluations->count() > 0
                        ? 'Based on saved AI evaluations'
                        : 'No scored answers yet',
                    'tone' => 'brand',
                ],
                [
                    'label' => 'Latest Category',
                    'value' => (string) ($latestSession['categoryName'] ?? 'No sessions yet'),
                    'detail' => isset($latestSession['savedAt'])
                        ? 'Last practiced '.$this->formatSavedAt($latestSession['savedAt'])
                        : 'Your newest practice track appears here',
                    'tone' => 'warning',
                ],
            ],
            'categories' => collect(InterviewPracticeCatalog::practiceQuestionBank())
                ->map(fn (array $category) => [
                    'title' => $category['name'],
                    'body' => $category['description'],
                ])
                ->values()
                ->all(),
            'sampleEvaluation' => [
                'category' => $latestEvaluation['categoryName'] ?? 'No category yet',
                'questionLabel' => $latestEvaluation
                    ? 'Question '.((int) ($latestEvaluation['questionNumber'] ?? 0))
                    : 'No saved answer yet',
                'question' => $latestEvaluation['question'] ?? 'Complete a practice session and save an answer to surface your latest AI evaluation here.',
                'provider' => $latestFeedback['provider'] ?? 'Awaiting AI feedback',
                'savedAt' => isset($latestEvaluation['sessionSavedAt'])
                    ? $this->formatSavedAt($latestEvaluation['sessionSavedAt'])
                    : null,
                'summary' => $latestFeedback['overall'] ?? 'No saved AI evaluation summary yet. Once you answer a question in Practice, the dashboard will show criterion-based feedback here.',
                'nextStep' => $latestFeedback['nextStep'] ?? 'Start a practice session and save at least one answer to generate AI evaluation feedback.',
                'items' => $this->buildEvaluationItems($latestEvaluation, $latestFeedback),
                'capstoneRubric' => $capstoneRubric,
            ],
        ]);
    }

    protected function flattenEvaluations(Collection $sessions): Collection
    {
        return $sessions->flatMap(function (array $session) {
            return collect($session['answers'] ?? [])->map(function (array $answer) use ($session) {
                return [
                    ...$answer,
                    'sessionSavedAt' => $session['savedAt'] ?? null,
                    'categoryName' => $session['categoryName'] ?? 'Unknown Category',
                    'feedbackSummary' => is_array($answer['feedbackSummary'] ?? null) ? $answer['feedbackSummary'] : [],
                ];
            });
        })->values();
    }

    protected function latestEvaluation(Collection $evaluations): ?array
    {
        return $evaluations
            ->sortByDesc(fn (array $evaluation) => sprintf(
                '%s|%04d',
                (string) ($evaluation['sessionSavedAt'] ?? ''),
                (int) ($evaluation['questionNumber'] ?? 0)
            ))
            ->first();
    }

    protected function totalAnsweredQuestions(Collection $sessions): int
    {
        return (int) $sessions->sum(function (array $session) {
            return max(
                (int) ($session['answeredCount'] ?? 0),
                count($session['answers'] ?? [])
            );
        });
    }

    protected function averageScore(Collection $sessions, Collection $evaluations): float
    {
        if ($evaluations->isNotEmpty()) {
            return round((float) $evaluations->avg(fn (array $evaluation) => (float) ($evaluation['average'] ?? 0)), 1);
        }

        if ($sessions->isNotEmpty()) {
            return round((float) $sessions->avg(fn (array $session) => (float) ($session['averageScore'] ?? 0)), 1);
        }

        return 0.0;
    }

    protected function buildEvaluationItems(?array $latestEvaluation, array $latestFeedback): array
    {
        $criteria = [
            'Clarity' => [
                'score' => (float) ($latestEvaluation['clarity'] ?? 0),
                'tone' => 'brand',
                'note' => $latestFeedback['criteria']['clarity'] ?? 'AI clarity notes will appear after your first saved answer.',
            ],
            'Relevance' => [
                'score' => (float) ($latestEvaluation['relevance'] ?? 0),
                'tone' => 'success',
                'note' => $latestFeedback['criteria']['relevance'] ?? 'AI relevance notes will appear after your first saved answer.',
            ],
            'Grammar' => [
                'score' => (float) ($latestEvaluation['grammar'] ?? 0),
                'tone' => 'blue',
                'note' => $latestFeedback['criteria']['grammar'] ?? 'AI grammar notes will appear after your first saved answer.',
            ],
            'Professionalism' => [
                'score' => (float) ($latestEvaluation['professionalism'] ?? 0),
                'tone' => 'warning',
                'note' => $latestFeedback['criteria']['professionalism'] ?? 'AI professionalism notes will appear after your first saved answer.',
            ],
        ];

        return collect($criteria)->map(function (array $item, string $label) {
            $score = round($item['score'], 1);

            return [
                'label' => $label,
                'score' => sprintf('%.1f / 10', $score),
                'width' => max(0, min(100, $score * 10)).'%',
                'tone' => $item['tone'],
                'note' => $item['note'],
            ];
        })->values()->all();
    }

    protected function capstoneRubric(?array $latestEvaluation, array $latestFeedback): array
    {
        $visualSnapshot = (array) ($latestFeedback['visualSnapshot'] ?? []);

        return InterviewPracticeCatalog::buildRubricSummary(
            [
                'clarity' => $latestEvaluation['clarity'] ?? 0,
                'relevance' => $latestEvaluation['relevance'] ?? 0,
                'grammar' => $latestEvaluation['grammar'] ?? 0,
                'professionalism' => $latestEvaluation['professionalism'] ?? 0,
            ],
            [
                'eyeContact' => data_get($visualSnapshot, 'eyeContactScore', data_get($visualSnapshot, 'bodyLanguageScore', 0)),
                'posture' => data_get($visualSnapshot, 'postureScore', data_get($visualSnapshot, 'bodyLanguageScore', 0)),
                'headMovement' => data_get($visualSnapshot, 'headMovementScore', data_get($visualSnapshot, 'bodyLanguageScore', 0)),
                'facialComposure' => data_get($visualSnapshot, 'facialComposureScore', data_get($visualSnapshot, 'facialExpressionScore', 0)),
            ],
        );
    }

    protected function formatSavedAt(string $value): string
    {
        return Carbon::parse($value)->format('M j, Y g:i A');
    }
}
