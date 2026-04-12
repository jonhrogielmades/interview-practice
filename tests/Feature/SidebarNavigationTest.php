<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated sidebar shows the user workspace destinations', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('user.dashboard'))
        ->assertOk()
        ->assertSeeText('User Dashboard')
        ->assertSeeText('Session Setup')
        ->assertSeeText('Practice')
        ->assertSeeText('Learning Lab')
        ->assertSeeText('Learning Activities')
        ->assertSeeText('Quick Drill')
        ->assertSeeText('STAR Drill')
        ->assertSeeText('Voice Sprint')
        ->assertSeeText('Camera Check')
        ->assertSeeText('Follow-up Sprint')
        ->assertSee('href="/learning-lab"', false)
        ->assertSee('href="/learning-lab/activities"', false)
        ->assertSee('href="/practice?source=learning-lab&amp;module=answer-blueprint&amp;activity=quick-drill&amp;level=1&amp;target=7"', false)
        ->assertDontSee('href="/voice-practice"', false)
        ->assertDontSee('href="/camera-readiness"', false)
        ->assertDontSee('href="/field-builder"', false)
        ->assertDontSee('href="/question-generator"', false)
        ->assertSeeText('Interview Chatbot')
        ->assertSeeText('Job Interview')
        ->assertSeeText('Scholarship Interview')
        ->assertSeeText('College Admission')
        ->assertSeeText('IT / Programming')
        ->assertSeeText('Progress')
        ->assertSeeText('Session Review')
        ->assertSeeText('Feedback Center')
        ->assertSeeText('Category Insights')
        ->assertDontSeeText('Mobile LAN')
        ->assertDontSee('href="/admin/mobile-lan"', false)
        ->assertSeeText('User Profile');
});

test('admin sidebar stays separate from the user workspace menu', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSeeText('Admin Dashboard')
        ->assertSeeText('User Management')
        ->assertSeeText('API Management')
        ->assertSeeText('Question Bank & Announcements')
        ->assertSeeText('Monitoring Records')
        ->assertSeeText('Mobile LAN')
        ->assertSee('href="/admin/mobile-lan"', false)
        ->assertSeeText('Admin Profile')
        ->assertDontSee('href="/session-setup"', false)
        ->assertDontSee('href="/practice"', false)
        ->assertDontSee('href="/chatbot"', false)
        ->assertDontSee('href="/user/dashboard"', false);
});
