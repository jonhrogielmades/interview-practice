<?php

namespace App\Support;

class InterviewPracticeCatalog
{
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

