<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest homepage promotes account creation and sign in', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSeeText('Create Free Account')
        ->assertSeeText('Sign In')
        ->assertSeeText('Platform features')
        ->assertSeeText('Category-based interview simulation')
        ->assertSeeText('AI avatar interviewer')
        ->assertSeeText('Job Interview')
        ->assertSeeText('IT / Programming');
});

test('authenticated homepage points users back into the workspace', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSeeText('View Dashboard')
        ->assertSeeText('Continue Practice')
        ->assertSeeText('Session Setup');
});

test('authenticated homepage ignores legacy tutorial session data', function () {
    $user = User::factory()->create([
        'name' => 'Jane Example',
    ]);

    $this->actingAs($user)
        ->withSession([
            'post_auth_tutorial' => [
                'redirect_to' => route('dashboard'),
                'destination_label' => 'Continue to dashboard',
            ],
        ])
        ->get(route('home'))
        ->assertOk()
        ->assertSeeText('View Dashboard')
        ->assertSeeText('Continue Practice')
        ->assertDontSeeText('Skip tutorial');
});
