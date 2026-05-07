<?php

use App\Models\QuestionBankQuestion;
use App\Support\InterviewPracticeCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('question bank dataset covers every marathon practice category', function () {
    $bank = InterviewPracticeCatalog::practiceQuestionBank();

    expect($bank)->toHaveKeys(['job', 'scholarship', 'admission', 'it']);

    foreach (['job', 'scholarship', 'admission', 'it'] as $categoryId) {
        expect($bank[$categoryId]['questions'])->toHaveCount(20);
    }
});

test('question bank dataset seeds managed questions with guidance', function () {
    expect(QuestionBankQuestion::query()->count())->toBe(80);

    $this->assertDatabaseHas('question_bank_questions', [
        'category_id' => 'it',
        'provider_id' => 'local',
        'question' => 'Tell me about a capstone, freelance, or school project you built and your role in it.',
        'guidance' => 'Listen for project purpose, owned responsibilities, tools used, and measurable result.',
        'sort_order' => 1,
        'is_active' => true,
    ]);
});
