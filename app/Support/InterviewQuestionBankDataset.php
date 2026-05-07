<?php

namespace App\Support;

class InterviewQuestionBankDataset
{
    public static function catalog(): array
    {
        return collect(self::categories())
            ->map(function (array $category) {
                $category['questions'] = collect($category['questions'] ?? [])
                    ->map(fn (mixed $question) => self::questionText($question))
                    ->filter()
                    ->values()
                    ->all();

                return $category;
            })
            ->all();
    }

    public static function seedRows(): array
    {
        $rows = [];

        foreach (self::categories() as $categoryId => $category) {
            foreach (($category['questions'] ?? []) as $index => $question) {
                $questionText = self::questionText($question);

                if ($questionText === null) {
                    continue;
                }

                $rows[] = [
                    'category_id' => $categoryId,
                    'provider_id' => 'local',
                    'provider_label' => 'Local PH coach',
                    'source_type' => 'local',
                    'question' => $questionText,
                    'guidance' => self::questionGuidance($question),
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ];
            }
        }

        return $rows;
    }

    protected static function categories(): array
    {
        $path = base_path('resources/data/interview-question-bank.json');

        if (! is_file($path)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        if (! is_array($decoded) || ! is_array($decoded['categories'] ?? null)) {
            return [];
        }

        return collect(['job', 'scholarship', 'admission', 'it'])
            ->mapWithKeys(function (string $categoryId) use ($decoded) {
                $category = $decoded['categories'][$categoryId] ?? null;

                return is_array($category) ? [$categoryId => $category] : [];
            })
            ->all();
    }

    protected static function questionText(mixed $question): ?string
    {
        $text = is_array($question) ? ($question['question'] ?? null) : $question;

        if (! is_string($text)) {
            return null;
        }

        $text = trim($text);

        return $text !== '' ? $text : null;
    }

    protected static function questionGuidance(mixed $question): ?string
    {
        if (! is_array($question) || ! is_string($question['guidance'] ?? null)) {
            return null;
        }

        $guidance = trim($question['guidance']);

        return $guidance !== '' ? $guidance : null;
    }
}
