<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterviewSessionAnswer extends Model
{
    protected $fillable = [
        'interview_session_id',
        'question_index',
        'question_number',
        'question',
        'answer',
        'average_score',
        'clarity',
        'relevance',
        'grammar',
        'professionalism',
        'matched_keywords',
        'elapsed_seconds',
        'input_mode',
        'feedback_summary',
    ];

    protected function casts(): array
    {
        return [
            'feedback_summary' => 'array',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(InterviewSession::class, 'interview_session_id');
    }
}
