<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('returns interviewer audio when cartesia is configured', function () {
    config()->set('services.cartesia.api_key', 'test-cartesia-key');
    config()->set('services.cartesia.version', '2026-03-01');
    config()->set('services.cartesia.tts_model_id', 'sonic-3');
    config()->set('services.cartesia.tts_voice_id', 'f786b574-daa5-4673-aa0c-cbe3e8534c02');
    config()->set('services.cartesia.tts_language', 'en');
    config()->set('services.cartesia.tts_speed', 1.0);
    config()->set('services.cartesia.tts_emotion', 'neutral');
    config()->set('services.cartesia.tts_sample_rate', 44100);

    Http::fake([
        'https://api.cartesia.ai/tts/bytes' => Http::response('fake-wav-audio', 200, [
            'Content-Type' => 'audio/wav',
        ]),
    ]);

    $this->actingAs(User::factory()->create());

    $response = $this->post(route('workspace.interviewer.speak'), [
        'text' => 'Tell me about yourself.',
    ]);

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toStartWith('audio/wav');
    expect($response->getContent())->toBe('fake-wav-audio');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.cartesia.ai/tts/bytes'
            && ($request->header('Authorization')[0] ?? null) === 'Bearer test-cartesia-key'
            && ($request->header('Cartesia-Version')[0] ?? null) === '2026-03-01'
            && $request['model_id'] === 'sonic-3'
            && $request['transcript'] === 'Tell me about yourself.'
            && ($request['voice']['id'] ?? null) === 'f786b574-daa5-4673-aa0c-cbe3e8534c02'
            && ($request['output_format']['container'] ?? null) === 'wav';
    });
});

it('returns a configuration error when cartesia is unavailable', function () {
    config()->set('services.cartesia.api_key', null);
    Http::fake();

    $this->actingAs(User::factory()->create());

    $this->postJson(route('workspace.interviewer.speak'), [
        'text' => 'Tell me about yourself.',
    ])
        ->assertStatus(503)
        ->assertJsonPath('message', 'AI voice is not configured on the server.');

    Http::assertNothingSent();
});
