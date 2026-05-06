<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InterviewSessionSetup extends Model
{
    protected $fillable = [
        'workspace_token',
        'question_count',
        'focus_mode_index',
        'pacing_mode_index',
        'preferred_category_id',
        'voice_mode',
        'notes',
        'advanced_options',
        'saved_at',
    ];

    protected function casts(): array
    {
        return [
            'advanced_options' => 'array',
            'saved_at' => 'datetime',
        ];
    }
}
