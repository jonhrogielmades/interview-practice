<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('learning lab has its own standalone page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('learning-lab'))
        ->assertOk()
        ->assertSeeText('Learning Lab')
        ->assertSeeText('Learning Modules')
        ->assertSeeText('Learning Activities')
        ->assertSeeText('Practice Track Connections')
        ->assertSeeText('Recent Learning Signals')
        ->assertSeeText('Launch Job Interview')
        ->assertSeeText('Open Practice')
        ->assertSee(e(route('practice', ['category' => 'job', 'source' => 'learning-lab', 'module' => 'answer-blueprint'])), false)
        ->assertSee(e(route('practice', ['category' => 'job', 'source' => 'learning-lab', 'module' => 'answer-blueprint', 'activity' => 'quick-drill'])), false);
});
