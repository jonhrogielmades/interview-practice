<?php

namespace Database\Seeders;

use App\Models\QuestionBankQuestion;
use App\Support\InterviewQuestionBankDataset;
use Illuminate\Database\Seeder;

class QuestionBankQuestionSeeder extends Seeder
{
    /**
     * Seed the managed question bank with the project starter dataset.
     */
    public function run(): void
    {
        foreach (InterviewQuestionBankDataset::seedRows() as $row) {
            QuestionBankQuestion::query()->updateOrCreate(
                [
                    'category_id' => $row['category_id'],
                    'provider_id' => $row['provider_id'],
                    'question' => $row['question'],
                ],
                [
                    'provider_label' => $row['provider_label'],
                    'source_type' => $row['source_type'],
                    'guidance' => $row['guidance'],
                    'is_active' => $row['is_active'],
                    'sort_order' => $row['sort_order'],
                ],
            );
        }
    }
}
