<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

uses(RefreshDatabase::class);

test('a user can register with email and password', function () {
    $response = $this->post(route('register'), [
        'email' => 'jane@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('dashboard'));

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'name' => 'Jane',
        'email' => 'jane@example.com',
    ]);
});

test('a user can sign in with email and password', function () {
    $user = User::factory()->create([
        'email' => 'jane@example.com',
        'password' => 'password123',
    ]);

    $response = $this->post(route('login'), [
        'email' => 'jane@example.com',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
});

test('google-only accounts are prompted to use google sign in', function () {
    User::factory()->create([
        'email' => 'google@example.com',
        'password' => null,
        'google_id' => 'google-123',
    ]);

    $response = $this->from(route('signin'))->post(route('login'), [
        'email' => 'google@example.com',
        'password' => 'password123',
    ]);

    $response
        ->assertRedirect(route('signin'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('an authenticated user can log out', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $response->assertRedirect(route('home'));
    $this->assertGuest();
});

test('google redirect route starts the oauth flow', function () {
    config()->set('services.google.client_id', 'test-client-id');
    config()->set('services.google.client_secret', 'test-client-secret');

    Socialite::shouldReceive('driver')->once()->with('google')->andReturnSelf();
    Socialite::shouldReceive('scopes')->once()->with(['openid', 'profile', 'email'])->andReturnSelf();
    Socialite::shouldReceive('redirect')->once()->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

    $this->get(route('google.redirect'))
        ->assertRedirect('https://accounts.google.com/o/oauth2/auth');
});

test('google redirect route can use stateless mode when configured', function () {
    config()->set('services.google.client_id', 'test-client-id');
    config()->set('services.google.client_secret', 'test-client-secret');
    config()->set('services.google.stateless', true);

    Socialite::shouldReceive('driver')->once()->with('google')->andReturnSelf();
    Socialite::shouldReceive('scopes')->once()->with(['openid', 'profile', 'email'])->andReturnSelf();
    Socialite::shouldReceive('stateless')->once()->andReturnSelf();
    Socialite::shouldReceive('redirect')->once()->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

    $this->get(route('google.redirect'))
        ->assertRedirect('https://accounts.google.com/o/oauth2/auth');
});

test('google callback creates and signs in the user', function () {
    config()->set('services.google.client_id', 'test-client-id');
    config()->set('services.google.client_secret', 'test-client-secret');

    $googleUser = mock(SocialiteUser::class);
    $googleUser->shouldReceive('getId')->andReturn('google-123');
    $googleUser->shouldReceive('getEmail')->andReturn('google@example.com');
    $googleUser->shouldReceive('getName')->andReturn('Google User');
    $googleUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');

    Socialite::shouldReceive('driver')->once()->with('google')->andReturnSelf();
    Socialite::shouldReceive('user')->once()->andReturn($googleUser);

    $response = $this->get(route('google.callback'));

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'email' => 'google@example.com',
        'google_id' => 'google-123',
    ]);
});

test('google callback retries with stateless auth after an invalid state error', function () {
    config()->set('services.google.client_id', 'test-client-id');
    config()->set('services.google.client_secret', 'test-client-secret');

    $googleUser = mock(SocialiteUser::class);
    $googleUser->shouldReceive('getId')->andReturn('google-456');
    $googleUser->shouldReceive('getEmail')->andReturn('retry@example.com');
    $googleUser->shouldReceive('getName')->andReturn('Retry User');
    $googleUser->shouldReceive('getAvatar')->andReturn('https://example.com/retry-avatar.jpg');

    Socialite::shouldReceive('driver')->once()->with('google')->ordered()->andReturnSelf();
    Socialite::shouldReceive('user')->once()->ordered()->andThrow(new InvalidStateException);
    Socialite::shouldReceive('driver')->once()->with('google')->ordered()->andReturnSelf();
    Socialite::shouldReceive('stateless')->once()->ordered()->andReturnSelf();
    Socialite::shouldReceive('user')->once()->ordered()->andReturn($googleUser);

    $response = $this->get(route('google.callback'));

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'email' => 'retry@example.com',
        'google_id' => 'google-456',
    ]);
});

test('authenticated users can still reach dashboard routes with a legacy tutorial session present', function () {
    $user = User::factory()->create();
    $legacyTutorialSession = [
        'post_auth_tutorial' => [
            'redirect_to' => route('dashboard'),
            'destination_label' => 'Continue to dashboard',
        ],
    ];

    $this->actingAs($user)
        ->withSession($legacyTutorialSession)
        ->get(route('dashboard'))
        ->assertRedirect(route('user.dashboard'));

    $this->actingAs($user)
        ->withSession($legacyTutorialSession)
        ->get(route('user.dashboard'))
        ->assertOk();
});

test('an authenticated user can upload a profile avatar', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $avatar = UploadedFile::fake()->image('profile-photo.png');

    $response = $this->actingAs($user)->post(route('profile.avatar.update'), [
        'avatar' => $avatar,
    ]);

    $response
        ->assertRedirect(route('profile'))
        ->assertSessionHas('status', 'Profile photo updated successfully.');

    $user->refresh();

    expect($user->avatar_path)->not->toBeNull();

    Storage::disk('public')->assertExists($user->avatar_path);

    $this->actingAs($user)
        ->get(route('users.avatar', $user))
        ->assertOk();
});
