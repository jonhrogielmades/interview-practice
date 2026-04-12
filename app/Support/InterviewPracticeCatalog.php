<?php

namespace App\Support;

class InterviewPracticeCatalog
{
    public static function manuscriptOverview(): array
    {
        return [
            'title' => 'AI-Based Interview Practice System',
            'subtitle' => 'Capstone-aligned interview simulation for students, applicants, and job seekers.',
            'purpose' => 'Provide an accessible web-based platform for repeated interview simulation, automated feedback, guided learning, and progress monitoring.',
            'scope' => [
                'Category-based interview practice for job, scholarship, admission, and Information Technology interviews.',
                'AI avatar interviewer support with text, voice, and camera-assisted practice modes.',
                'Guided feedback, learning recommendations, report exports, and administrative monitoring.',
            ],
        ];
    }

    public static function practiceQuestionBank(): array
    {
        return [
            'job' => [
                'name' => 'Job Interview',
                'description' => 'Philippine hiring questions for fresh graduates, career shifters, and office or remote roles.',
                'questions' => [
                    'Tell me about yourself and how your background in the Philippines prepared you for this role.',
                    'What skills from your OJT, internship, or part-time work can you bring to our company?',
                    'Describe a challenge you handled in school, work, or your community and how you solved it.',
                    'Why do you want to work for our company in the Philippines?',
                    'How do you see your career growing in the Philippine job market over the next five years?',
                ],
                'localFocus' => [
                    'Use examples from OJT, internships, student leadership, freelance work, barangay activities, or family responsibilities when relevant.',
                    'Keep the tone respectful, direct, and professional.',
                ],
                'quickPrompts' => [
                    'Give me 3 Philippine-style follow-up questions for this category.',
                    'Show me a strong sample answer for a Philippine interviewer.',
                    'What mistakes should Filipino applicants avoid in this category?',
                ],
            ],
            'scholarship' => [
                'name' => 'Scholarship Interview',
                'description' => 'Philippine scholarship questions about academic goals, financial need, service, and future contribution.',
                'questions' => [
                    'Why should you be chosen for this scholarship in the Philippines?',
                    'How will this scholarship help your studies, family, and long-term goals?',
                    'What achievement best shows your discipline and leadership?',
                    'How do you balance academics, home responsibilities, and community involvement?',
                    'How will you use your education to help your family or community in the future?',
                ],
                'localFocus' => [
                    'Highlight academic discipline, service, leadership, and practical impact on your family or community.',
                    'Use specific examples from school organizations, outreach, peer tutoring, or local projects.',
                ],
                'quickPrompts' => [
                    'Give me 3 scholarship interview questions used in the Philippines.',
                    'How can I sound sincere without sounding rehearsed?',
                    'What should I mention about family and financial need professionally?',
                ],
            ],
            'admission' => [
                'name' => 'College Admission',
                'description' => 'Philippine college and university admission questions focused on motivation, readiness, and program fit.',
                'questions' => [
                    'Why do you want to take this program at a Philippine college or university?',
                    'What experiences in senior high school or your community prepared you for this course?',
                    'How do you handle academic pressure, deadlines, and responsibilities at home?',
                    'What makes you a strong candidate for this program?',
                    'What goals do you want to achieve after graduating in the Philippines?',
                ],
                'localFocus' => [
                    'Connect your course choice to realistic goals, family support, and contribution to your community or industry.',
                    'Show readiness for college-level work with concrete habits and examples.',
                ],
                'quickPrompts' => [
                    'Give me 3 Philippine college admission follow-up questions.',
                    'How should I explain why I chose this course?',
                    'What answer structure works best for admission interviews?',
                ],
            ],
            'it' => [
                'name' => 'IT / Programming',
                'description' => 'Philippine tech interview questions about coding, capstone work, debugging, and teamwork.',
                'questions' => [
                    'Tell me about a capstone, freelance, or school project you built and your role in it.',
                    'How do you troubleshoot programming bugs when your deadline is near?',
                    'Which programming languages, frameworks, or tools are you most comfortable using, and why?',
                    'How do you work with a team during software development or group projects?',
                    'Why do you want to build your career in the IT industry in the Philippines?',
                ],
                'localFocus' => [
                    'Use examples from capstone projects, thesis work, internships, bootcamps, client work, or hackathons.',
                    'Emphasize problem-solving, documentation, teamwork, and adaptability.',
                ],
                'quickPrompts' => [
                    'Give me 3 Philippine IT interview follow-up questions.',
                    'How can I explain my capstone project better?',
                    'What technical mistakes should I avoid in an entry-level IT interview?',
                ],
            ],
        ];
    }

    public static function focusModes(): array
    {
        return [
            ['label' => 'Balanced Coach'],
            ['label' => 'Confidence Coach'],
            ['label' => 'Clarity Coach'],
            ['label' => 'Professional Coach'],
        ];
    }

    public static function pacingModes(): array
    {
        return [
            ['label' => 'Standard', 'seconds' => 180],
            ['label' => 'Quick', 'seconds' => 90],
            ['label' => 'Extended', 'seconds' => 240],
        ];
    }

    public static function categories(): array
    {
        return collect(self::practiceQuestionBank())
            ->mapWithKeys(fn (array $category, string $key) => [$key => ['name' => $category['name']]])
            ->all();
    }

    public static function learningActivityCatalog(): array
    {
        return [
            'quick-drill' => [
                'title' => 'Quick Drill',
                'sidebarLabel' => 'Quick Drill',
                'tag' => 'Warm-up',
                'module' => 'answer-blueprint',
                'tone' => 'brand',
                'icon' => 'task',
                'summary' => 'Start a short category-based run using the saved question count, coach focus, and pacing.',
                'actionLabel' => 'Launch Quick Drill',
                'levels' => [
                    ['level' => 1, 'label' => 'Level 1', 'targetScore' => 7.0, 'questionFocus' => 'Answer one warm-up question with a direct point and one example.'],
                    ['level' => 2, 'label' => 'Level 2', 'targetScore' => 8.0, 'questionFocus' => 'Answer a stronger follow-up question with clearer detail and outcome.'],
                    ['level' => 3, 'label' => 'Level 3', 'targetScore' => 8.5, 'questionFocus' => 'Answer a challenge question with polished confidence and concise evidence.'],
                ],
            ],
            'star-response' => [
                'title' => 'STAR Response Drill',
                'sidebarLabel' => 'STAR Drill',
                'tag' => 'Structure',
                'module' => 'answer-blueprint',
                'tone' => 'blue',
                'icon' => 'book-open',
                'summary' => 'Practice one answer with a clear situation, task, action, and result before submitting.',
                'actionLabel' => 'Run STAR Drill',
                'levels' => [
                    ['level' => 1, 'label' => 'Level 1', 'targetScore' => 7.0, 'questionFocus' => 'Build the answer around situation, task, action, and result.'],
                    ['level' => 2, 'label' => 'Level 2', 'targetScore' => 8.0, 'questionFocus' => 'Add measurable results and connect the example to the interview goal.'],
                    ['level' => 3, 'label' => 'Level 3', 'targetScore' => 8.5, 'questionFocus' => 'Handle a follow-up question while keeping the STAR answer concise.'],
                ],
            ],
            'voice-rehearsal' => [
                'title' => 'Voice Rehearsal Sprint',
                'sidebarLabel' => 'Voice Sprint',
                'tag' => 'Delivery',
                'module' => 'delivery-rehearsal',
                'tone' => 'success',
                'icon' => 'microphone',
                'summary' => 'Rehearse the next answer aloud with the saved response preference and pacing target.',
                'actionLabel' => 'Start Voice Sprint',
                'levels' => [
                    ['level' => 1, 'label' => 'Level 1', 'targetScore' => 7.0, 'questionFocus' => 'Deliver one answer clearly within the saved pacing target.'],
                    ['level' => 2, 'label' => 'Level 2', 'targetScore' => 8.0, 'questionFocus' => 'Improve pacing, transitions, and confidence in a follow-up answer.'],
                    ['level' => 3, 'label' => 'Level 3', 'targetScore' => 8.5, 'questionFocus' => 'Give a polished spoken answer with minimal filler and strong closing.'],
                ],
            ],
            'camera-check' => [
                'title' => 'Camera Presence Check',
                'sidebarLabel' => 'Camera Check',
                'tag' => 'Presence',
                'module' => 'visual-presence',
                'tone' => 'success',
                'icon' => 'camera',
                'summary' => 'Check centering, posture, head movement, and facial composure before the mock interview.',
                'actionLabel' => 'Start Camera Check',
                'levels' => [
                    ['level' => 1, 'label' => 'Level 1', 'targetScore' => 7.0, 'questionFocus' => 'Answer while keeping posture and camera framing steady.'],
                    ['level' => 2, 'label' => 'Level 2', 'targetScore' => 8.0, 'questionFocus' => 'Maintain eye contact orientation and calm head movement through a follow-up.'],
                    ['level' => 3, 'label' => 'Level 3', 'targetScore' => 8.5, 'questionFocus' => 'Deliver a harder answer with composed facial presence and steady pacing.'],
                ],
            ],
            'follow-up-sprint' => [
                'title' => 'Follow-up Sprint',
                'sidebarLabel' => 'Follow-up Sprint',
                'tag' => 'Review',
                'module' => 'reflection-review',
                'tone' => 'warning',
                'icon' => 'review',
                'summary' => 'Use the latest saved feedback to answer a tighter follow-up question in the same track.',
                'actionLabel' => 'Launch Follow-up',
                'levels' => [
                    ['level' => 1, 'label' => 'Level 1', 'targetScore' => 7.0, 'questionFocus' => 'Answer one follow-up question using the last feedback area.'],
                    ['level' => 2, 'label' => 'Level 2', 'targetScore' => 8.0, 'questionFocus' => 'Give a clearer follow-up answer with stronger evidence and reflection.'],
                    ['level' => 3, 'label' => 'Level 3', 'targetScore' => 8.5, 'questionFocus' => 'Handle a deeper follow-up with a polished, interview-ready response.'],
                ],
            ],
        ];
    }

    public static function chatbotCategoryContext(?string $categoryId = null): array
    {
        $bank = self::practiceQuestionBank();

        if ($categoryId && isset($bank[$categoryId])) {
            return ['id' => $categoryId] + $bank[$categoryId];
        }

        return [
            'id' => null,
            'name' => 'Philippine Interview Practice',
            'description' => 'General Philippine interview practice guidance across the supported categories.',
            'questions' => collect($bank)
                ->flatMap(fn (array $category) => $category['questions'])
                ->take(6)
                ->values()
                ->all(),
            'localFocus' => [
                'Keep the advice grounded in Philippine interview practice for students, fresh graduates, and early-career applicants.',
                'Stay within job, scholarship, college admission, and IT or programming interview scenarios.',
            ],
            'quickPrompts' => [
                'Give me 3 Philippine interview questions for beginners.',
                'How should I answer Tell me about yourself in the Philippines?',
                'What common interview mistakes should I avoid locally?',
            ],
        ];
    }

    public static function chatbotQuickPrompts(?string $categoryId = null, ?string $currentQuestion = null): array
    {
        $context = self::chatbotCategoryContext($categoryId);
        $prompts = $context['quickPrompts'] ?? [];

        if (is_string($currentQuestion) && trim($currentQuestion) !== '') {
            $prompts[] = 'Give me a stronger answer for this question.';
            $prompts[] = 'What follow-up questions can a Philippine interviewer ask next?';
        }

        return collect($prompts)
            ->filter(fn ($prompt) => is_string($prompt) && trim($prompt) !== '')
            ->unique()
            ->take(4)
            ->values()
            ->all();
    }

    public static function chatbotSystemInstruction(): string
    {
        $lines = [
            'You are InterviewPilot PH Coach, a chatbot for interview practice in the Philippines only.',
            'You may only answer questions related to interview preparation, interview answers, sample interview questions, follow-up questions, or coaching for the supported Philippine categories.',
            'Supported categories: Job Interview, Scholarship Interview, College Admission, and IT / Programming.',
            'Do not answer unrelated topics like general trivia, recipes, politics, health advice, or coding help unrelated to interviews.',
            'If the user goes out of scope, politely say you can only help with Philippine interview practice and offer a relevant interview prompt instead.',
            'When generating interview questions or sample answers, stay grounded in the local question bank and Philippine context below.',
            'Prefer concise, practical coaching in clear English suitable for Filipino students, fresh graduates, and early-career applicants.',
            'Question bank and local focus:',
        ];

        foreach (self::practiceQuestionBank() as $key => $category) {
            $lines[] = sprintf('%s (%s): %s', $category['name'], $key, $category['description']);

            foreach ($category['questions'] as $question) {
                $lines[] = '- Question: '.$question;
            }

            foreach ($category['localFocus'] as $focus) {
                $lines[] = '- Local focus: '.$focus;
            }
        }

        return implode("\n", $lines);
    }

    public static function responsePreferences(): array
    {
        return ['text', 'voice', 'hybrid'];
    }

    public static function questionCountOptions(): array
    {
        return [3, 5, 10, 15, 20];
    }

    public static function manuscriptArchitectureLayers(): array
    {
        return [
            [
                'title' => 'Client Layer',
                'body' => 'Browser-based interview workspace for registration, category selection, mock interviews, reports, and progress review.',
            ],
            [
                'title' => 'Application Layer',
                'body' => 'Laravel coordinates authentication, authorization, session logic, validation, question retrieval, and admin controls.',
            ],
            [
                'title' => 'AI Processing Layer',
                'body' => 'Speech, verbal-evaluation, and selected non-verbal analysis can be routed through AI-supported services and browser capabilities.',
            ],
            [
                'title' => 'Data Layer',
                'body' => 'Persistent storage keeps user accounts, question banks, sessions, feedback, reports, notifications, and monitoring records.',
            ],
        ];
    }

    public static function manuscriptAdminAreas(): array
    {
        return [
            [
                'title' => 'User Management',
                'body' => 'Control access, roles, and profile visibility for standard users and administrators.',
            ],
            [
                'title' => 'Interview Categories and Question Banks',
                'body' => 'Review the interview catalog, starter prompts, and category-backed practice coverage.',
            ],
            [
                'title' => 'Announcements and Notifications',
                'body' => 'Prepare reminders, feedback notices, and practice nudges that keep users engaged.',
            ],
            [
                'title' => 'Monitoring and Reports',
                'body' => 'Track saved sessions, feedback records, notifications, and export-ready monitoring summaries.',
            ],
        ];
    }

    public static function defaultAnnouncementTemplates(): array
    {
        return [
            [
                'title' => 'Practice Reminder',
                'body' => 'Invite users to complete another mock interview session and keep their weekly practice streak active.',
                'audience' => 'All users',
            ],
            [
                'title' => 'Feedback Ready',
                'body' => 'Notify users when an interview session has been saved and the latest feedback summary is ready for review.',
                'audience' => 'Recent participants',
            ],
            [
                'title' => 'Category Focus Week',
                'body' => 'Highlight one interview category and recommend a matching learning activity or question-bank review.',
                'audience' => 'Users with active practice history',
            ],
        ];
    }

    public static function scoringScale(): array
    {
        return [
            'verbal' => [
                'clarity' => 0.25,
                'relevance' => 0.35,
                'grammar' => 0.20,
                'professionalism' => 0.20,
            ],
            'nonVerbal' => [
                'eyeContact' => 0.30,
                'posture' => 0.25,
                'headMovement' => 0.20,
                'facialComposure' => 0.25,
            ],
            'overall' => [
                'verbal' => 0.70,
                'nonVerbal' => 0.30,
            ],
            'bands' => [
                ['min' => 4.0, 'label' => 'Highly Acceptable'],
                ['min' => 3.0, 'label' => 'Acceptable'],
                ['min' => 2.0, 'label' => 'Needs Improvement'],
                ['min' => 1.0, 'label' => 'Poor'],
            ],
        ];
    }

    public static function internalScoreToRubric(mixed $score): float
    {
        $normalized = round(max(0, min(10, (float) $score)) / 2, 2);

        if ($normalized === 0.0) {
            return 0.0;
        }

        return max(1.0, min(5.0, round($normalized, 1)));
    }

    public static function readinessLabel(mixed $score): string
    {
        $normalized = round(max(0, min(5, (float) $score)), 2);

        if ($normalized <= 0) {
            return 'No data yet';
        }

        foreach (self::scoringScale()['bands'] as $band) {
            if ($normalized >= $band['min']) {
                return $band['label'];
            }
        }

        return 'Poor';
    }

    public static function buildRubricSummary(array $verbalCriteria, array $nonVerbalCriteria = []): array
    {
        $weights = self::scoringScale();

        $verbal = collect($weights['verbal'])->reduce(
            fn (float $carry, float $weight, string $key) => $carry + (self::internalScoreToRubric($verbalCriteria[$key] ?? 0) * $weight),
            0.0
        );

        $nonVerbalAvailable = collect($weights['nonVerbal'])
            ->keys()
            ->contains(fn (string $key) => (float) ($nonVerbalCriteria[$key] ?? 0) > 0);

        $nonVerbal = $nonVerbalAvailable
            ? collect($weights['nonVerbal'])->reduce(
                fn (float $carry, float $weight, string $key) => $carry + (self::internalScoreToRubric($nonVerbalCriteria[$key] ?? 0) * $weight),
                0.0
            )
            : 0.0;

        $overall = $nonVerbalAvailable
            ? ($verbal * $weights['overall']['verbal']) + ($nonVerbal * $weights['overall']['nonVerbal'])
            : $verbal;

        return [
            'verbal' => round($verbal, 2),
            'nonVerbal' => round($nonVerbal, 2),
            'overall' => round($overall, 2),
            'readinessLabel' => self::readinessLabel($overall),
        ];
    }

    public static function defaultSessionSetup(): array
    {
        return [
            'questionCount' => 3,
            'focusModeIndex' => 0,
            'pacingModeIndex' => 0,
            'preferredCategoryId' => 'job',
            'voiceMode' => 'text',
            'notes' => '',
            'savedAt' => null,
        ];
    }

    public static function normalizeSessionSetup(array $input = []): array
    {
        $normalized = self::defaultSessionSetup();
        $questionCount = (int) ($input['questionCount'] ?? $input['question_count'] ?? $normalized['questionCount']);
        $focusModeIndex = (int) ($input['focusModeIndex'] ?? $input['focus_mode_index'] ?? $normalized['focusModeIndex']);
        $pacingModeIndex = (int) ($input['pacingModeIndex'] ?? $input['pacing_mode_index'] ?? $normalized['pacingModeIndex']);
        $preferredCategoryId = (string) ($input['preferredCategoryId'] ?? $input['preferred_category_id'] ?? $normalized['preferredCategoryId']);
        $voiceMode = (string) ($input['voiceMode'] ?? $input['voice_mode'] ?? $normalized['voiceMode']);
        $notes = is_string($input['notes'] ?? null) ? trim((string) $input['notes']) : '';
        $savedAt = $input['savedAt'] ?? $input['saved_at'] ?? null;

        if (in_array($questionCount, self::questionCountOptions(), true)) {
            $normalized['questionCount'] = $questionCount;
        }

        if ($focusModeIndex >= 0 && $focusModeIndex < count(self::focusModes())) {
            $normalized['focusModeIndex'] = $focusModeIndex;
        }

        if ($pacingModeIndex >= 0 && $pacingModeIndex < count(self::pacingModes())) {
            $normalized['pacingModeIndex'] = $pacingModeIndex;
        }

        if (array_key_exists($preferredCategoryId, self::categories())) {
            $normalized['preferredCategoryId'] = $preferredCategoryId;
        }

        if (in_array($voiceMode, self::responsePreferences(), true)) {
            $normalized['voiceMode'] = $voiceMode;
        }

        $normalized['notes'] = mb_substr($notes, 0, 500);
        $normalized['savedAt'] = is_string($savedAt) && $savedAt !== '' ? $savedAt : null;

        return $normalized;
    }
}

