<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('interview_session_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interview_session_id')->constrained('interview_sessions')->cascadeOnDelete();
            $table->unsignedTinyInteger('question_index')->default(0);
            $table->unsignedTinyInteger('question_number')->default(0);
            $table->text('question');
            $table->longText('answer')->nullable();
            $table->decimal('average_score', 4, 1)->default(0);
            $table->decimal('clarity', 4, 1)->default(0);
            $table->decimal('relevance', 4, 1)->default(0);
            $table->decimal('grammar', 4, 1)->default(0);
            $table->decimal('professionalism', 4, 1)->default(0);
            $table->unsignedSmallInteger('matched_keywords')->default(0);
            $table->unsignedInteger('elapsed_seconds')->default(0);
            $table->string('input_mode', 20)->default('Text');
            $table->json('feedback_summary')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_session_answers');
    }
};
