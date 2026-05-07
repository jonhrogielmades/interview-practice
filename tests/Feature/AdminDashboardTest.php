<?php

use App\Models\InterviewSession;
use App\Models\InterviewSessionAnswer;
use App\Models\QuestionBankQuestion;
use App\Models\User;
use App\Support\InterviewPracticeCatalog;
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
        ->assertSeeText('Question Bank')
        ->assertSeeText('Announcements')
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
        ->assertSee('href="/admin/question-bank?category=job"', false)
        ->assertSee('href="/admin/question-bank?category=scholarship"', false)
        ->assertSee('href="/admin/question-bank?category=admission"', false)
        ->assertSee('href="/admin/question-bank?category=it"', false)
        ->assertSee('href="/admin/announcements"', false)
        ->assertSee('href="/admin/apis"', false)
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

test('admins can open the dedicated question bank page', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@example.com',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.question-bank'))
        ->assertOk()
        ->assertSeeText('Question Bank')
        ->assertSeeText('Category Question Banks')
        ->assertSeeText('Add question')
        ->assertSeeText('Local PH coach')
        ->assertSeeText('Announcement Templates');
});

test('admins can filter the question bank by sidebar category links', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@example.com',
    ]);

    QuestionBankQuestion::query()->create([
        'category_id' => 'it',
        'provider_id' => 'local',
        'provider_label' => 'Local PH coach',
        'source_type' => 'local',
        'question' => 'How did you debug your Laravel capstone under deadline pressure?',
        'is_active' => true,
        'sort_order' => 0,
    ]);

    QuestionBankQuestion::query()->create([
        'category_id' => 'scholarship',
        'provider_id' => 'local',
        'provider_label' => 'Local PH coach',
        'source_type' => 'local',
        'question' => 'How will this scholarship help your family and community?',
        'is_active' => true,
        'sort_order' => 20,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.question-bank', ['category' => 'it']))
        ->assertOk()
        ->assertSeeText('IT / Programming Question Bank')
        ->assertSee('href="/admin/question-bank?category=job"', false)
        ->assertSee('href="/admin/question-bank?category=scholarship"', false)
        ->assertSee('href="/admin/question-bank?category=admission"', false)
        ->assertSee('href="/admin/question-bank?category=it"', false)
        ->assertSeeText('How did you debug your Laravel capstone under deadline pressure?')
        ->assertDontSeeText('How will this scholarship help your family and community?');
});

test('admins can search question bank rows and choose the visible list size', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@example.com',
    ]);

    QuestionBankQuestion::query()->create([
        'category_id' => 'it',
        'provider_id' => 'local',
        'provider_label' => 'Local PH coach',
        'source_type' => 'local',
        'question' => 'CodexSearch architecture: how did you choose your Laravel modules?',
        'is_active' => true,
        'sort_order' => 0,
    ]);

    QuestionBankQuestion::query()->create([
        'category_id' => 'it',
        'provider_id' => 'local',
        'provider_label' => 'Local PH coach',
        'source_type' => 'local',
        'question' => 'CodexSearch debugging: how did you resolve your hardest bug?',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    QuestionBankQuestion::query()->create([
        'category_id' => 'it',
        'provider_id' => 'local',
        'provider_label' => 'Local PH coach',
        'source_type' => 'local',
        'question' => 'How do you work with a software team?',
        'is_active' => true,
        'sort_order' => 3,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.question-bank', [
            'category' => 'it',
            'search' => 'codexsearch',
            'per_page' => 1,
        ]))
        ->assertOk()
        ->assertSee('name="search"', false)
        ->assertSee('value="codexsearch"', false)
        ->assertSee('name="per_page"', false)
        ->assertSee('value="1" selected', false)
        ->assertSeeText('Showing 1 of 2 matching questions')
        ->assertSee('x-model.debounce.100ms="questionSearch"', false)
        ->assertSee('data-question-row', false)
        ->assertSeeText('CodexSearch architecture: how did you choose your Laravel modules?');
});

test('admins can open the dedicated announcements page', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@example.com',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.announcements'))
        ->assertOk()
        ->assertSeeText('Announcements')
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

test('admins can create a managed question bank question', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@example.com',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.question-bank.questions.store'), [
            'question_modal' => 'create',
            'category_id' => 'it',
            'provider_id' => 'local',
            'question' => 'How would you explain your Laravel capstone to a non-technical Philippine interviewer?',
            'guidance' => 'Listen for concise project scope, user value, and owned tasks.',
            'sort_order' => 99,
            'is_active' => '1',
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    $this->assertDatabaseHas('question_bank_questions', [
        'category_id' => 'it',
        'provider_id' => 'local',
        'source_type' => 'local',
        'question' => 'How would you explain your Laravel capstone to a non-technical Philippine interviewer?',
        'is_active' => true,
    ]);

    expect(InterviewPracticeCatalog::chatbotCategoryContext('it')['questions'])
        ->toContain('How would you explain your Laravel capstone to a non-technical Philippine interviewer?');
});

test('admins can update a managed question bank question from an ai provider source', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@example.com',
    ]);

    $question = QuestionBankQuestion::query()->create([
        'category_id' => 'job',
        'provider_id' => 'local',
        'provider_label' => 'Local PH coach',
        'source_type' => 'local',
        'question' => 'Old interview prompt?',
        'guidance' => null,
        'is_active' => true,
        'sort_order' => 10,
    ]);

    $this->actingAs($admin)
        ->put(route('admin.question-bank.questions.update', $question), [
            'question_modal' => 'edit-'.$question->id,
            'category_id' => 'job',
            'provider_id' => 'groq',
            'question' => 'What strengths from your OJT match this Philippine job opening?',
            'guidance' => 'Expect a specific strength, brief evidence, and role fit.',
            'sort_order' => 4,
            'is_active' => '0',
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    $this->assertDatabaseHas('question_bank_questions', [
        'id' => $question->id,
        'provider_id' => 'groq',
        'source_type' => 'ai_provider',
        'question' => 'What strengths from your OJT match this Philippine job opening?',
        'is_active' => false,
        'sort_order' => 4,
    ]);
});

test('admins can delete a managed question bank question', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@example.com',
    ]);

    $question = QuestionBankQuestion::query()->create([
        'category_id' => 'scholarship',
        'provider_id' => 'local',
        'provider_label' => 'Local PH coach',
        'source_type' => 'local',
        'question' => 'Temporary scholarship prompt?',
        'is_active' => true,
        'sort_order' => 20,
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.question-bank.questions.destroy', $question))
        ->assertRedirect()
        ->assertSessionHas('status');

    $this->assertDatabaseMissing('question_bank_questions', [
        'id' => $question->id,
    ]);
});
