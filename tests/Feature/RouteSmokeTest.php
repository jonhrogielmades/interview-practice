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

dataset('protected_user_routes', [
    '/user/dashboard',
    '/session-setup',
    '/practice',
    '/progress',
    '/session-review',
    '/feedback-center',
    '/category-insights',
    '/question-generator',
    '/field-builder',
    '/learning-lab',
    '/learning-lab/activities',
    '/voice-practice',
    '/camera-readiness',
    '/chatbot',
    '/calendar',
    '/profile',
]);

dataset('protected_admin_routes', [
    '/admin/dashboard',
    '/admin/users',
    '/admin/apis',
    '/admin/content',
    '/admin/monitoring',
    '/admin/mobile-lan',
]);

test('public routes return a successful response', function (string $uri) {
    $this->get($uri)->assertOk();
})->with('public_routes');

test('public auth alias routes redirect to home', function (string $uri, string $authView) {
    $this->get($uri)->assertRedirect(route('home', ['auth' => $authView]));
})->with('public_redirect_routes');

test('dashboard entrypoint redirects guests to sign in', function () {
    $this->get('/dashboard')->assertRedirect(route('signin'));
});

test('protected user routes redirect guests to sign in', function (string $uri) {
    $this->get($uri)->assertRedirect(route('signin'));
})->with('protected_user_routes');

test('protected user routes are available to standard authenticated users', function (string $uri) {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get($uri)
        ->assertOk();
})->with('protected_user_routes');

test('dashboard entrypoint redirects standard users to the user dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect(route('user.dashboard'));
});

test('mobile lan alias is forbidden for standard users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/mobile-lan')
        ->assertForbidden();
});

test('mobile lan alias redirects admins to the admin page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/mobile-lan')
        ->assertRedirect(route('admin.mobile-lan'));
});

test('protected admin routes redirect guests to sign in', function (string $uri) {
    $this->get($uri)->assertRedirect(route('signin'));
})->with('protected_admin_routes');

test('protected admin routes are forbidden for standard users', function (string $uri) {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get($uri)
        ->assertForbidden();
})->with('protected_admin_routes');

test('protected admin routes are available to admin users', function (string $uri) {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get($uri)
        ->assertOk();
})->with('protected_admin_routes');
