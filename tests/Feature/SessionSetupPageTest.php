<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('session setup exposes advanced interview defaults', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('session-setup'))
        ->assertOk()
        ->assertSeeText('Session Setup')
        ->assertSeeText('Interview Enhancements')
        ->assertSeeText('Target Field / Role / Course')
        ->assertSeeText('Mock Panel Mode')
        ->assertSeeText('Difficult Interview Mode')
        ->assertSeeText('Filler Word Tracker')
        ->assertSeeText('Teacher / Adviser Review')
        ->assertSeeText('Practice Reminder Days')
        ->assertSee('id="setupPanelMode"', false)
        ->assertSee('id="setupReminderDays"', false)
        ->assertSee('id="summaryPanelMode"', false)
        ->assertSee('id="summaryReminderDays"', false);
});
