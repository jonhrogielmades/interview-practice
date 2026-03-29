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
            'practiceTracks' => $this->practiceTracks($questionBank),
            'stats' => $this->stats($questionBank),
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

    protected function stats(Collection $questionBank): array
    {
        return [
            [
                'value' => (string) $questionBank->count(),
                'label' => 'Practice Tracks',
                'detail' => 'Job, scholarship, college, and IT interview scenarios in one place.',
            ],
            [
                'value' => (string) $questionBank->sum(fn (array $track) => count($track['questions'])),
                'label' => 'Starter Questions',
                'detail' => 'Guided prompts grounded in the same Philippine-focused practice catalog used in the app.',
            ],
            [
                'value' => (string) count(InterviewPracticeCatalog::focusModes()),
                'label' => 'Coaching Styles',
                'detail' => 'Switch between balanced, confidence, clarity, and professional coaching angles.',
            ],
            [
                'value' => (string) count(InterviewPracticeCatalog::responsePreferences()),
                'label' => 'Response Modes',
                'detail' => 'Prepare with text, voice, or hybrid practice sessions once you enter the workspace.',
            ],
        ];
    }
}
