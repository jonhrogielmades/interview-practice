<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('chatbot page shows the five supported ai providers for interview coaching', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('chatbot'))
        ->assertOk()
        ->assertSeeText('Interview Chatbot')
        ->assertSeeText('Gemini API')
        ->assertSeeText('Groq API')
        ->assertSeeText('OpenRouter API')
        ->assertSeeText('Wisdom Gate API')
        ->assertSeeText('Cohere API')
        ->assertSeeText('Philippines-only interview guidance');
});
