<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('learning lab overview has its own standalone function', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('learning-lab'))
        ->assertOk()
        ->assertSeeText('Learning Lab Overview')
        ->assertSeeText('Learning Modules')
        ->assertSeeText('Learning Activities')
        ->assertSeeText('Practice Track Connections')
        ->assertSeeText('Recent Learning Signals')
        ->assertSeeText('Open In Practice')
        ->assertSeeText('Open Practice')
        ->assertSee(e(route('learning-lab.activities')), false)
        ->assertSee(e(route('practice', ['category' => 'job', 'source' => 'learning-lab', 'module' => 'answer-blueprint'])), false)
        ->assertDontSeeText('Camera Presence Check')
        ->assertDontSee(e(route('practice', ['category' => 'job', 'source' => 'learning-lab', 'module' => 'answer-blueprint', 'activity' => 'quick-drill', 'level' => 1, 'target' => 7.0])), false);
});

test('learning activities has a separate activity function', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('learning-lab.activities'))
        ->assertOk()
        ->assertSeeText('Learning Activities')
        ->assertSeeText('Recommended Activity')
        ->assertSeeText('Recommended')
        ->assertSeeText('Quick Drill')
        ->assertSeeText('STAR Response Drill')
        ->assertSeeText('Voice Rehearsal Sprint')
        ->assertSeeText('Camera Presence Check')
        ->assertSeeText('Follow-up Sprint')
        ->assertSeeText('Target Score')
        ->assertSeeText('7.0 / 10')
        ->assertSeeText('Below target: try the same level again. Passed: proceed to the next level of questions.')
        ->assertSeeText('Level 2')
        ->assertSeeText('8.0 / 10')
        ->assertSeeText('Open Learning Lab Overview')
        ->assertSeeText('Choose Practice Category')
        ->assertDontSeeText('Practice Track Connections')
        ->assertSee(e(route('practice', ['source' => 'learning-lab', 'module' => 'answer-blueprint', 'activity' => 'quick-drill', 'level' => 1, 'target' => 7.0])), false)
        ->assertDontSee(e(route('practice', ['category' => 'job', 'source' => 'learning-lab', 'module' => 'answer-blueprint', 'activity' => 'quick-drill', 'level' => 1, 'target' => 7.0])), false);
});
