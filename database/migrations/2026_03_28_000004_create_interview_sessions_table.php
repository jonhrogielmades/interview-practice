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
        Schema::create('interview_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('workspace_token')->index();
            $table->string('public_id')->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('saved_at')->nullable();
            $table->string('category_id', 50)->nullable();
            $table->string('category_name')->default('Unknown Category');
            $table->text('category_description')->nullable();
            $table->unsignedTinyInteger('question_count')->default(0);
            $table->unsignedTinyInteger('answered_count')->default(0);
            $table->string('focus_mode')->default('Balanced Coach');
            $table->string('pacing_mode')->default('Standard');
            $table->unsignedInteger('timer_target_seconds')->default(0);
            $table->decimal('average_score', 4, 1)->default(0);
            $table->json('criteria_averages')->nullable();
            $table->boolean('completed')->default(false);
            $table->timestamps();

            $table->unique(['workspace_token', 'public_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_sessions');
    }
};
