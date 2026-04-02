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
        ->assertSeeText('Category-based mock interview practice')
        ->assertSeeText('Multi-provider interview chatbot')
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
