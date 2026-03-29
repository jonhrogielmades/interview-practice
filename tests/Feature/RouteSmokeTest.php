<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

dataset('public_routes', [
    '/',
    '/up',
]);

dataset('public_redirect_routes', [
    ['/signin', 'signin'],
    ['/signup', 'signup'],
]);

dataset('protected_routes', [
    '/dashboard',
    '/session-setup',
    '/practice',
    '/progress',
    '/session-review',
    '/feedback-center',
    '/category-insights',
    '/chatbot',
    '/calendar',
    '/profile',
]);

test('public routes return a successful response', function (string $uri) {
    $this->get($uri)->assertOk();
})->with('public_routes');

test('public auth alias routes redirect to home', function (string $uri, string $authView) {
    $this->get($uri)->assertRedirect(route('home', ['auth' => $authView]));
})->with('public_redirect_routes');

test('protected routes redirect guests to sign in', function (string $uri) {
    $this->get($uri)->assertRedirect(route('signin'));
})->with('protected_routes');

test('protected routes are available to authenticated users', function (string $uri) {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get($uri)
        ->assertOk();
})->with('protected_routes');
