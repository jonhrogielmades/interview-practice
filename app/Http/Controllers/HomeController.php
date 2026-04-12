<?php

namespace App\Http\Controllers;

use App\Support\InterviewPracticeCatalog;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $questionBank = collect(InterviewPracticeCatalog::practiceQuestionBank());

        return view('pages.home', [
            'title' => 'Home',
            'manuscriptOverview' => InterviewPracticeCatalog::manuscriptOverview(),
            'architectureLayers' => InterviewPracticeCatalog::manuscriptArchitectureLayers(),
            'practiceTracks' => $this->practiceTracks($questionBank),
            'workflow' => $this->workflow(),
            'platformFeatures' => $this->platformFeatures(),
            'focusModes' => collect(InterviewPracticeCatalog::focusModes())
                ->pluck('label')
                ->values()
                ->all(),
            'pacingModes' => collect(InterviewPracticeCatalog::pacingModes())
                ->map(fn (array $mode) => [
                    'label' => $mode['label'],
                    'detail' => sprintf('%d seconds per answer', $mode['seconds']),
                ])
                ->values()
                ->all(),
            'responseModes' => collect(InterviewPracticeCatalog::responsePreferences())
                ->map(fn (string $mode) => Str::headline($mode))
                ->values()
                ->all(),
            'questionCountOptions' => InterviewPracticeCatalog::questionCountOptions(),
        ]);
    }

    protected function practiceTracks(Collection $questionBank): array
    {
        return $questionBank
            ->map(fn (array $track, string $id) => [
                'id' => $id,
                'name' => $track['name'],
                'description' => $track['description'],
                'questions' => collect($track['questions'])->take(3)->values()->all(),
                'localFocus' => collect($track['localFocus'])->take(2)->values()->all(),
                'quickPrompts' => collect($track['quickPrompts'])->take(3)->values()->all(),
            ])
            ->values()
            ->all();
    }

    protected function workflow(): array
    {
        return [
            [
                'step' => '01',
                'title' => 'Create an account and choose a category',
                'description' => 'Start from the homepage, register or sign in, then move into a category-based interview workflow.',
            ],
            [
                'step' => '02',
                'title' => 'Run the AI-guided mock interview',
                'description' => 'Set the session defaults, let the AI avatar present the question, and answer through text, voice, or hybrid mode.',
            ],
            [
                'step' => '03',
                'title' => 'Review feedback, learning tasks, and reports',
                'description' => 'Save the interview, review verbal and selected non-verbal feedback, and track progress through the dashboard and exports.',
            ],
        ];
    }

    protected function platformFeatures(): array
    {
        return [
            [
                'title' => 'Category-based interview simulation',
                'body' => 'Keep practice focused on job, scholarship, admission, and Information Technology interview scenarios.',
                'tone' => 'brand',
            ],
            [
                'title' => 'AI avatar interviewer',
                'body' => 'Present the active question with a guided avatar experience that supports a more realistic interview flow.',
                'tone' => 'blue',
            ],
            [
                'title' => 'Text, voice, and hybrid response input',
                'body' => 'Let users answer naturally and keep the interview flow accessible across different practice styles.',
                'tone' => 'success',
            ],
            [
                'title' => 'Speech-to-text support',
                'body' => 'Convert spoken answers into analyzable text so verbal feedback can use the same review pipeline.',
                'tone' => 'warning',
            ],
            [
                'title' => 'Verbal response evaluation',
                'body' => 'Review clarity, relevance, grammar, and professionalism with criterion-based feedback.',
                'tone' => 'brand',
            ],
            [
                'title' => 'Selected non-verbal observation',
                'body' => 'Surface eye-contact orientation, posture, head movement, and facial composure cues during practice.',
                'tone' => 'blue',
            ],
            [
                'title' => 'Weighted capstone rubric',
                'body' => 'Translate runtime feedback into the manuscript’s weighted 1-to-5 verbal, non-verbal, and overall scoring model.',
                'tone' => 'success',
            ],
            [
                'title' => 'Feedback summaries and improvement tips',
                'body' => 'Generate strengths, weak areas, next-step guidance, and recommendation-ready summaries after each answer.',
                'tone' => 'warning',
            ],
            [
                'title' => 'Learning activities and guided drills',
                'body' => 'Map weak areas to answer blueprints, delivery rehearsals, and visual-presence practice modules.',
                'tone' => 'brand',
            ],
            [
                'title' => 'Saved interview history and dashboard tracking',
                'body' => 'Store sessions, compare performance trends, and monitor long-term growth over time.',
                'tone' => 'blue',
            ],
            [
                'title' => 'Report exports for documentation',
                'body' => 'Generate downloadable JSON and CSV reports that support review and capstone-style documentation.',
                'tone' => 'success',
            ],
            [
                'title' => 'Admin visibility and monitoring',
                'body' => 'Keep separate admin views for users, APIs, question-bank planning, announcements, and monitoring records.',
                'tone' => 'warning',
            ],
            [
                'title' => 'Question bank and field builder support',
                'body' => 'Shape the role, course, or specialization before generating the next guided question set.',
                'tone' => 'brand',
            ],
            [
                'title' => 'Progress-ready review pages',
                'body' => 'Use Progress, Session Review, Feedback Center, and Category Insights as the full review cluster.',
                'tone' => 'blue',
            ],
            [
                'title' => 'Responsive web access',
                'body' => 'Keep the prototype reachable on desktop and mobile while preserving the current theme-aware interface.',
                'tone' => 'success',
            ],
        ];
    }
}



