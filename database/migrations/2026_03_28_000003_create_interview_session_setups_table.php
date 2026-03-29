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
        Schema::create('interview_session_setups', function (Blueprint $table) {
            $table->id();
            $table->string('workspace_token')->unique();
            $table->unsignedTinyInteger('question_count')->default(3);
            $table->unsignedTinyInteger('focus_mode_index')->default(0);
            $table->unsignedTinyInteger('pacing_mode_index')->default(0);
            $table->string('preferred_category_id', 50)->default('job');
            $table->string('voice_mode', 20)->default('text');
            $table->text('notes')->nullable();
            $table->timestamp('saved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_session_setups');
    }
};
