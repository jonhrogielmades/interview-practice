<?php

use App\Models\InterviewSession;
use App\Models\InterviewSessionAnswer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('admin.name', 'System Administrator');
    config()->set('admin.email', 'admin@example.com');
});

test('database seeder creates the fixed admin account', function () {
    $this->seed();

    $this->assertDatabaseHas('users', [
        'email' => 'admin@example.com',
        'account_role' => User::ROLE_ADMIN,
    ]);
});

test('admins are redirected from the standard dashboard to the admin dashboard', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@example.com',
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertRedirect(route('admin.dashboard'));
});

test('admins are redirected away from the user dashboard', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@example.com',
    ]);

    $this->actingAs($admin)
        ->get(route('user.dashboard'))
        ->assertRedirect(route('admin.dashboard'));
});

test('admins are redirected away from user workspace routes', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@example.com',
    ]);

    $this->actingAs($admin)
        ->get(route('practice'))
        ->assertRedirect(route('admin.dashboard'));
});

test('admins can open the admin dashboard and review system metrics', function () {
    $admin = User::factory()->admin()->create([
        'name' => 'System Administrator',
        'email' => 'admin@example.com',
    ]);

    User::factory()->create([
        'name' => 'Jane Candidate',
        'email' => 'jane@example.com',
    ]);

    User::factory()->create([
        'name' => 'Google Member',
        'email' => 'google@example.com',
        'password' => null,
        'google_id' => 'google-123',
    ]);

    $session = InterviewSession::query()->create([
        'workspace_token' => 'workspace-admin-001',
        'public_id' => 'session-admin-001',
        'saved_at' => now(),
        'category_id' => 'it',
        'category_name' => 'IT / Programming',
        'question_count' => 3,
        'answered_count' => 2,
        'average_score' => 8.4,
        'criteria_averages' => [
            'clarity' => 8.5,
            'relevance' => 8.2,
            'grammar' => 8.1,
            'professionalism' => 8.7,
        ],
        'completed' => true,
    ]);

    InterviewSessionAnswer::query()->create([
        'interview_session_id' => $session->id,
        'question_index' => 0,
        'question_number' => 1,
        'question' => 'Tell me about a project you are proud of.',
        'answer' => 'I built a Laravel admin dashboard.',
        'average_score' => 8.4,
        'clarity' => 8.5,
        'relevance' => 8.2,
        'grammar' => 8.1,
        'professionalism' => 8.7,
        'matched_keywords' => 4,
        'elapsed_seconds' => 75,
        'input_mode' => 'Text',
        'feedback_summary' => [],
    ]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSeeText('Admin Dashboard')
        ->assertSeeText('Registered Users')
        ->assertSeeText('IT / Programming')
        ->assertSeeText('User Management')
        ->assertSeeText('API Management')
        ->assertSeeText('Question Bank & Announcements')
        ->assertSeeText('Monitoring Records');
});

test('standard users cannot open the admin dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('admin sidebar shows the admin dashboard link', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@example.com',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSeeText('Admin Dashboard')
        ->assertSee('href="/admin/dashboard"', false)
        ->assertSee('href="/admin/users"', false)
        ->assertSee('href="/admin/apis"', false)
        ->assertSee('href="/admin/content"', false)
        ->assertSee('href="/admin/monitoring"', false);
});

test('admins can open the dedicated user management page', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@example.com',
    ]);
    User::factory()->create([
        'name' => 'Jane Candidate',
        'email' => 'jane@example.com',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.users'))
        ->assertOk()
        ->assertSeeText('User Management')
        ->assertSeeText('Jane Candidate')
        ->assertSeeText('Make admin');
});

test('admins can open the dedicated api management page', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@example.com',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.apis'))
        ->assertOk()
        ->assertSeeText('API Management')
        ->assertSeeText('Gemini API')
        ->assertSeeText('Environment Keys')
        ->assertSeeText('Run Live API Check');
});

test('admins can open the content management page', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@example.com',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.content'))
        ->assertOk()
        ->assertSeeText('Question Bank & Announcements')
        ->assertSeeText('Category Question Banks')
        ->assertSeeText('Announcement Templates');
});

test('admins can open the monitoring records page', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@example.com',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.monitoring'))
        ->assertOk()
        ->assertSeeText('Monitoring Records')
        ->assertSeeText('Recent Practice Records')
        ->assertSeeText('Report Notes');
});

test('provider health alias redirects admins to the api management page', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@example.com',
    ]);

    $this->actingAs($admin)
        ->get(route('provider-health'))
        ->assertRedirect(route('admin.apis'));
});

test('provider health alias is forbidden for standard users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('provider-health'))
        ->assertForbidden();
});

test('admins can promote a standard user to admin', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@example.com',
    ]);

    $user = User::factory()->create([
        'account_role' => User::ROLE_USER,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.users.role.update', $user), [
            'account_role' => User::ROLE_ADMIN,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'account_role' => User::ROLE_ADMIN,
    ]);
});

test('the fixed admin account cannot be demoted', function () {
    $admin = User::factory()->admin()->create([
        'name' => 'System Administrator',
        'email' => 'admin@example.com',
    ]);

    $this->actingAs($admin)
        ->from(route('admin.dashboard'))
        ->patch(route('admin.users.role.update', $admin), [
            'account_role' => User::ROLE_USER,
        ])
        ->assertRedirect(route('admin.dashboard'))
        ->assertSessionHas('error', 'The fixed admin account must remain an administrator.');

    $this->assertDatabaseHas('users', [
        'id' => $admin->id,
        'account_role' => User::ROLE_ADMIN,
    ]);
});

test('admins can create a user from the user management page', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@example.com',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => 'Maria Santos',
            'email' => 'maria@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'account_role' => User::ROLE_USER,
            'phone' => '+63 912 345 6789',
            'profile_role' => 'HR Manager',
            'profile_location' => 'Quezon City',
            'bio' => 'Helps oversee hiring operations.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('users', [
        'email' => 'maria@example.com',
        'name' => 'Maria Santos',
        'account_role' => User::ROLE_USER,
        'profile_role' => 'HR Manager',
    ]);
});

test('admins can update a user profile and access level', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@example.com',
    ]);

    $user = User::factory()->create([
        'name' => 'Jane Candidate',
        'email' => 'jane@example.com',
        'account_role' => User::ROLE_USER,
    ]);

    $this->actingAs($admin)
        ->put(route('admin.users.update', $user), [
            'name' => 'Jane Manager',
            'email' => 'jane.manager@example.com',
            'password' => '',
            'password_confirmation' => '',
            'account_role' => User::ROLE_ADMIN,
            'phone' => '555-0101',
            'profile_role' => 'Operations Lead',
            'profile_location' => 'Makati',
            'bio' => 'Promoted by the admin team.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Jane Manager',
        'email' => 'jane.manager@example.com',
        'account_role' => User::ROLE_ADMIN,
        'phone' => '555-0101',
        'profile_role' => 'Operations Lead',
        'profile_location' => 'Makati',
    ]);
});

test('admins can delete a standard user and clear their active sessions', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@example.com',
    ]);

    $user = User::factory()->create();

    \Illuminate\Support\Facades\DB::table('sessions')->insert([
        'id' => 'delete-user-session',
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Pest',
        'payload' => 'payload',
        'last_activity' => now()->timestamp,
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.users.destroy', $user))
        ->assertRedirect();

    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
    ]);

    $this->assertDatabaseMissing('sessions', [
        'id' => 'delete-user-session',
    ]);
});

test('the fixed admin account cannot be deleted', function () {
    $admin = User::factory()->admin()->create([
        'name' => 'System Administrator',
        'email' => 'admin@example.com',
    ]);

    $this->actingAs($admin)
        ->from(route('admin.users'))
        ->delete(route('admin.users.destroy', $admin))
        ->assertRedirect(route('admin.users'))
        ->assertSessionHas('error', 'The fixed admin account cannot be deleted.');

    $this->assertDatabaseHas('users', [
        'id' => $admin->id,
        'account_role' => User::ROLE_ADMIN,
    ]);
});

test('admins cannot delete their own account from the admin dashboard', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'owner@example.com',
    ]);

    $this->actingAs($admin)
        ->from(route('admin.users'))
        ->delete(route('admin.users.destroy', $admin))
        ->assertRedirect(route('admin.users'))
        ->assertSessionHas('error', 'You cannot delete your own account from this dashboard.');

    $this->assertDatabaseHas('users', [
        'id' => $admin->id,
        'account_role' => User::ROLE_ADMIN,
    ]);
});

test('updating core admin fields preserves profile data that is not exposed in the form', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@example.com',
    ]);

    $user = User::factory()->create([
        'email' => 'hidden-fields@example.com',
        'country' => 'Philippines',
        'city_state' => 'Pasig',
        'facebook_url' => 'https://facebook.com/hidden.fields',
    ]);

    $this->actingAs($admin)
        ->put(route('admin.users.update', $user), [
            'name' => 'Hidden Fields User',
            'email' => 'hidden-fields@example.com',
            'password' => '',
            'password_confirmation' => '',
            'account_role' => User::ROLE_USER,
            'phone' => '555-1111',
            'profile_role' => 'Coach',
            'profile_location' => 'Taguig',
            'bio' => 'Updated from admin panel.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'country' => 'Philippines',
        'city_state' => 'Pasig',
        'facebook_url' => 'https://facebook.com/hidden.fields',
    ]);
});
