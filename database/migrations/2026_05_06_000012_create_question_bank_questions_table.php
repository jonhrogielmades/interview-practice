<?php

use App\Support\InterviewPracticeCatalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('question_bank_questions', function (Blueprint $table) {
            $table->id();
            $table->string('category_id', 50)->index();
            $table->string('provider_id', 50)->default('local')->index();
            $table->string('provider_label')->default('Local PH coach');
            $table->string('source_type', 30)->default('local')->index();
            $table->text('question');
            $table->text('guidance')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        $now = now();
        $rows = [];

        foreach (InterviewPracticeCatalog::practiceQuestionBank() as $categoryId => $category) {
            foreach (($category['questions'] ?? []) as $index => $question) {
                $rows[] = [
                    'category_id' => $categoryId,
                    'provider_id' => 'local',
                    'provider_label' => 'Local PH coach',
                    'source_type' => 'local',
                    'question' => $question,
                    'guidance' => null,
                    'is_active' => true,
                    'sort_order' => $index + 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if ($rows !== []) {
            DB::table('question_bank_questions')->insert($rows);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_bank_questions');
    }
};
