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

        return view('pages.dashboard.interview', [
            'title' => 'Interview Dashboard',
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
            ],
            'focusItems' => $this->focusItems($evaluations),
            'weeklySignals' => [
                ['label' => 'Practice streak', 'value' => $this->practiceStreakLabel($sessions)],
                ['label' => 'Top category', 'value' => $this->topCategory($sessions)],
                ['label' => 'Best dimension', 'value' => $this->bestDimension($sessions)],
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

    protected function focusItems(Collection $evaluations): array
    {
        $items = $evaluations
            ->flatMap(function (array $evaluation) {
                return collect($evaluation['feedbackSummary']['improvements'] ?? []);
            })
            ->filter(fn ($item) => is_string($item) && trim($item) !== '')
            ->countBy()
            ->sortDesc()
            ->keys()
            ->take(3)
            ->values()
            ->all();

        return $items !== []
            ? $items
            : [
                'Strengthen answer structure with more specific examples.',
                'Keep your wording direct, confident, and professional.',
                'Save at least one evaluated answer to surface AI coaching priorities here.',
            ];
    }

    protected function practiceStreakLabel(Collection $sessions): string
    {
        $dates = $sessions
            ->pluck('savedAt')
            ->filter(fn ($value) => is_string($value) && $value !== '')
            ->map(fn (string $value) => Carbon::parse($value)->toDateString())
            ->unique()
            ->values();

        if ($dates->isEmpty()) {
            return '0 days';
        }

        $streak = 1;
        $previous = Carbon::parse($dates->first());

        foreach ($dates->slice(1) as $date) {
            $current = Carbon::parse($date);

            if (! $current->equalTo($previous->copy()->subDay())) {
                break;
            }

            $streak += 1;
            $previous = $current;
        }

        return sprintf('%d day%s', $streak, $streak === 1 ? '' : 's');
    }

    protected function topCategory(Collection $sessions): string
    {
        $topCategory = $sessions
            ->pluck('categoryName')
            ->filter(fn ($value) => is_string($value) && $value !== '')
            ->countBy()
            ->sortDesc()
            ->keys()
            ->first();

        return $topCategory ?: 'No data yet';
    }

    protected function bestDimension(Collection $sessions): string
    {
        $criteria = [
            'Clarity' => round((float) $sessions->avg(fn (array $session) => (float) data_get($session, 'criteriaAverages.clarity', 0)), 1),
            'Relevance' => round((float) $sessions->avg(fn (array $session) => (float) data_get($session, 'criteriaAverages.relevance', 0)), 1),
            'Grammar' => round((float) $sessions->avg(fn (array $session) => (float) data_get($session, 'criteriaAverages.grammar', 0)), 1),
            'Professionalism' => round((float) $sessions->avg(fn (array $session) => (float) data_get($session, 'criteriaAverages.professionalism', 0)), 1),
        ];

        arsort($criteria);
        $label = array_key_first($criteria);

        return $sessions->isNotEmpty() && $label ? $label : 'No data yet';
    }

    protected function formatSavedAt(string $value): string
    {
        return Carbon::parse($value)->format('M j, Y g:i A');
    }
}
