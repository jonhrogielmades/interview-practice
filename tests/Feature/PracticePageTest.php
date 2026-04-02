<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('practice page uses sidebar launch guidance and keeps live visual coaching', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('practice'))
        ->assertOk()
        ->assertSeeText('Start From Sidebar')
        ->assertSeeText('Choose a track from the sidebar')
        ->assertDontSeeText('Automated Feedback')
        ->assertSeeText('Body Language Algorithms')
        ->assertSeeText('Facial Expression Algorithms')
        ->assertDontSee('id="practiceCategoryList"', false)
        ->assertDontSee('id="feedbackContent"', false)
        ->assertDontSee('id="learningLabSection"', false)
        ->assertDontSee('id="learningModulesList"', false)
        ->assertSee('id="livePresenceSummary"', false);
});
