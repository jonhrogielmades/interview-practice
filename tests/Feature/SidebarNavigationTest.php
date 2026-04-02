<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated sidebar shows fifteen feature destinations', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSeeText('Dashboard')
        ->assertSeeText('Session Setup')
        ->assertSeeText('Practice')
        ->assertSeeText('Learning Lab')
        ->assertSee('href="/learning-lab"', false)
        ->assertDontSee('href="/voice-practice"', false)
        ->assertSeeText('Camera Readiness')
        ->assertSeeText('Field Builder')
        ->assertSeeText('Question Generator')
        ->assertSeeText('Interview Chatbot')
        ->assertSeeText('Provider Health')
        ->assertSeeText('Job Interview')
        ->assertSeeText('Scholarship Interview')
        ->assertSeeText('College Admission')
        ->assertSeeText('IT / Programming')
        ->assertSeeText('Progress')
        ->assertSeeText('Session Review')
        ->assertSeeText('Feedback Center')
        ->assertSeeText('Category Insights')
        ->assertSeeText('Mobile LAN')
        ->assertSeeText('User Profile');
});
