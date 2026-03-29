<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('an authenticated user can update profile information without first and last name fields', function () {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
    ]);

    $response = $this->actingAs($user)->patchJson(route('profile.info.update'), [
        'full_name' => 'Jane Example',
        'email' => 'JANE@EXAMPLE.COM',
        'phone' => '09171234567',
        'role' => 'Frontend Candidate',
        'location' => 'Singapore',
        'bio' => 'Focused on interviews and steady improvement.',
        'facebook_url' => 'https://facebook.com/jane',
        'x_url' => 'https://x.com/jane',
        'linkedin_url' => 'https://linkedin.com/in/jane',
        'instagram_url' => 'https://instagram.com/jane',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('message', 'Personal information updated successfully.')
        ->assertJsonPath('profile.fullName', 'Jane Example')
        ->assertJsonPath('profile.email', 'jane@example.com')
        ->assertJsonPath('socials.linkedin', 'https://linkedin.com/in/jane');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Jane Example',
        'email' => 'jane@example.com',
        'phone' => '09171234567',
        'profile_role' => 'Frontend Candidate',
        'profile_location' => 'Singapore',
        'bio' => 'Focused on interviews and steady improvement.',
        'facebook_url' => 'https://facebook.com/jane',
        'x_url' => 'https://x.com/jane',
        'linkedin_url' => 'https://linkedin.com/in/jane',
        'instagram_url' => 'https://instagram.com/jane',
    ]);
});

test('an authenticated user can update profile address', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patchJson(route('profile.address.update'), [
        'country' => 'Philippines',
        'city_state' => 'Manila, NCR',
        'postal_code' => '1000',
        'tax_id' => 'TIN-12345',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('message', 'Address information updated successfully.')
        ->assertJsonPath('address.country', 'Philippines')
        ->assertJsonPath('address.cityState', 'Manila, NCR');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'country' => 'Philippines',
        'city_state' => 'Manila, NCR',
        'postal_code' => '1000',
        'tax_id' => 'TIN-12345',
    ]);
});
