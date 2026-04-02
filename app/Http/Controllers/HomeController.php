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

    protected function platformFeatures(): array
    {
        return [
            [
                'title' => 'Category-based mock interview practice',
                'body' => 'Launch targeted practice flows instead of using one generic interview template.',
                'tone' => 'brand',
            ],
            [
                'title' => 'Multi-provider interview chatbot',
                'body' => 'Use Gemini, Groq, OpenRouter, Hugging Face-compatible routing, Cohere, or the local fallback coach.',
                'tone' => 'blue',
            ],
            [
                'title' => 'Session setup defaults',
                'body' => 'Keep question count, coach focus, pacing, and preferred category ready for the next session.',
                'tone' => 'success',
            ],
            [
                'title' => 'Progress tracking and category insights',
                'body' => 'Review saved performance signals and category-specific practice patterns over time.',
                'tone' => 'warning',
            ],
            [
                'title' => 'Responsive dashboard UI with dark mode support',
                'body' => 'Use the dashboard comfortably on desktop or mobile, with theme switching built in.',
                'tone' => 'brand',
            ],
            [
                'title' => 'Voice-ready practice workflow',
                'body' => 'Move from typed answers to browser voice input without leaving the practice workspace.',
                'tone' => 'blue',
            ],
            [
                'title' => 'Text, voice, and hybrid response modes',
                'body' => 'Choose whether a session leans on typing, speaking, or a mix of both.',
                'tone' => 'success',
            ],
            [
                'title' => 'Job interview track',
                'body' => 'Practice hiring conversations for fresh graduates, remote roles, and career shifts.',
                'tone' => 'warning',
            ],
            [
                'title' => 'Scholarship interview track',
                'body' => 'Rehearse academic, service, and financial-need questions in one focused path.',
                'tone' => 'brand',
            ],
            [
                'title' => 'College admission track',
                'body' => 'Prepare for university interviews with motivation, readiness, and course-fit prompts.',
                'tone' => 'blue',
            ],
            [
                'title' => 'IT and programming track',
                'body' => 'Handle capstone, debugging, teamwork, and entry-level technical interview questions.',
                'tone' => 'success',
            ],
            [
                'title' => 'AI-generated question sets',
                'body' => 'Generate fresh questions for the active category before the interview begins.',
                'tone' => 'warning',
            ],
            [
                'title' => 'Field builder before practice',
                'body' => 'Refine the role, course, or specialization first so the session stays specific.',
                'tone' => 'brand',
            ],
            [
                'title' => 'AI answer review summaries',
                'body' => 'Receive strengths, improvements, an overall summary, and a next-step recommendation.',
                'tone' => 'blue',
            ],
            [
                'title' => 'Criteria-based scoring',
                'body' => 'Break feedback into clarity, relevance, grammar, and professionalism.',
                'tone' => 'success',
            ],
            [
                'title' => 'Printable feedback summary',
                'body' => 'Print the current evaluation view directly from the practice workspace.',
                'tone' => 'warning',
            ],
            [
                'title' => 'Saved setup persistence',
                'body' => 'Keep the latest session preferences stored for the next time you return.',
                'tone' => 'brand',
            ],
            [
                'title' => 'Saved session history',
                'body' => 'Store question-by-question answers together with timing, mode, and scoring data.',
                'tone' => 'blue',
            ],
            [
                'title' => 'Session cleanup controls',
                'body' => 'Delete one saved session or clear the full saved practice history when needed.',
                'tone' => 'success',
            ],
            [
                'title' => 'Quick prompts per category',
                'body' => 'Start the chatbot faster with built-in prompts tuned to the selected interview path.',
                'tone' => 'warning',
            ],
            [
                'title' => 'Live provider health checks',
                'body' => 'Run availability checks so configured AI providers can be verified from the UI.',
                'tone' => 'brand',
            ],
            [
                'title' => 'Auto routing with graceful fallback',
                'body' => 'Let the chatbot move through provider priority automatically when one API is unavailable.',
                'tone' => 'blue',
            ],
            [
                'title' => 'Local PH coach fallback',
                'body' => 'Keep interview guidance available even when external AI keys are missing.',
                'tone' => 'success',
            ],
            [
                'title' => 'AI interviewer voice playback',
                'body' => 'Have the active question read aloud through the browser speech synthesis API.',
                'tone' => 'warning',
            ],
            [
                'title' => 'Camera and face visibility checks',
                'body' => 'Use the built-in camera preview and face status indicators during mock interviews.',
                'tone' => 'brand',
            ],
            [
                'title' => 'Google and email authentication',
                'body' => 'Offer homepage sign-in and sign-up flows with Google OAuth and email support.',
                'tone' => 'blue',
            ],
            [
                'title' => 'Profile management and avatars',
                'body' => 'Update personal details, address information, role, location, and profile photo.',
                'tone' => 'success',
            ],
            [
                'title' => 'Weekly signals dashboard',
                'body' => 'Track readiness cues, latest evaluations, and momentum from saved practice data.',
                'tone' => 'warning',
            ],
            [
                'title' => 'Feedback center and session review pages',
                'body' => 'Navigate dedicated areas for reviewing saved results beyond the live workspace.',
                'tone' => 'brand',
            ],
            [
                'title' => 'Mobile LAN testing support',
                'body' => 'Run the app on a local network so it can be opened on a phone during development.',
                'tone' => 'blue',
            ],
        ];
    }
}
