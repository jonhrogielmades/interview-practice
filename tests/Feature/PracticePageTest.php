<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('practice page uses sidebar launch guidance and keeps live visual coaching', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('practice'))
        ->assertOk()
        ->assertSeeText('Choose Practice Category')
        ->assertSeeText('Choose the interview category you want')
        ->assertSee('id="openPracticeCategoryModalBtn"', false)
        ->assertSee('id="practiceCategoryModal"', false)
        ->assertDontSeeText('Automated Feedback')
        ->assertSeeText('Body Language Algorithms')
        ->assertSeeText('Facial Presence Algorithms')
        ->assertSee('id="practiceCategoryList"', false)
        ->assertDontSee('id="feedbackContent"', false)
        ->assertDontSee('id="learningLabSection"', false)
        ->assertDontSee('id="learningModulesList"', false)
        ->assertSee('id="livePresenceSummary"', false);
});
