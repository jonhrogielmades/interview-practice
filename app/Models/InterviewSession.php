<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InterviewSession extends Model
{
    protected $fillable = [
        'workspace_token',
        'public_id',
        'started_at',
        'saved_at',
        'category_id',
        'category_name',
        'category_description',
        'question_count',
        'answered_count',
        'focus_mode',
        'pacing_mode',
        'timer_target_seconds',
        'average_score',
        'criteria_averages',
        'completed',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'saved_at' => 'datetime',
            'criteria_averages' => 'array',
            'completed' => 'boolean',
        ];
    }

    public function answers(): HasMany
    {
        return $this->hasMany(InterviewSessionAnswer::class)->orderBy('question_index');
    }
}
