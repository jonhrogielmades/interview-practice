<?php

use App\Models\User;
use App\Notifications\SystemDatabaseNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('admin.email', 'admin@example.com');
});

test('registration creates notifications for the new user and admins', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@example.com',
    ]);

    $this->post(route('register'), [
        'email' => 'jane@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])
        ->assertRedirect(route('dashboard'));

    $user = User::query()->where('email', 'jane@example.com')->firstOrFail();

    expect(data_get($user->notifications()->latest()->first()?->data, 'title'))
        ->toBe('Welcome to Interview Practice');

    expect(data_get($admin->fresh()->notifications()->latest()->first()?->data, 'title'))
        ->toBe('New user registered');
});

test('completed practice sessions create notifications for the user and admins', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@example.com',
    ]);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('workspace.sessions.store'), [
            'id' => 'session-notify-1',
            'categoryId' => 'it',
            'categoryName' => 'IT / Programming',
            'questionCount' => 3,
            'answeredCount' => 2,
            'averageScore' => 8.4,
            'criteriaAverages' => [
                'clarity' => 8.5,
                'relevance' => 8.2,
                'grammar' => 8.1,
                'professionalism' => 8.8,
            ],
            'completed' => true,
        ])
        ->assertCreated();

    expect(data_get($user->fresh()->notifications()->latest()->first()?->data, 'title'))
        ->toBe('Practice session completed');

    expect(data_get($admin->fresh()->notifications()->latest()->first()?->data, 'title'))
        ->toBe('Practice session completed');
});

test('admin access changes notify the target user and the other admins', function () {
    $primaryAdmin = User::factory()->admin()->create([
        'email' => 'admin@example.com',
    ]);
    $secondaryAdmin = User::factory()->admin()->create([
        'email' => 'ops@example.com',
    ]);
    $user = User::factory()->create([
        'account_role' => User::ROLE_USER,
    ]);

    $this->actingAs($primaryAdmin)
        ->patch(route('admin.users.role.update', $user), [
            'account_role' => User::ROLE_ADMIN,
        ])
        ->assertRedirect();

    expect(data_get($user->fresh()->notifications()->latest()->first()?->data, 'title'))
        ->toBe('Your account access was updated');

    expect(data_get($secondaryAdmin->fresh()->notifications()->latest()->first()?->data, 'title'))
        ->toBe('Access level changed');

    expect($primaryAdmin->fresh()->notifications()->count())->toBe(0);
});

test('notification center displays stored notifications and can mark them all as read', function () {
    $user = User::factory()->create();

    $user->notify(new SystemDatabaseNotification([
        'title' => 'Profile review reminder',
        'body' => 'Complete your profile details to keep your account ready for interview practice.',
        'icon' => 'profile',
        'tone' => 'blue',
        'action_url' => route('profile'),
        'action_label' => 'Open profile',
    ]));

    $this->actingAs($user)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSeeText('Profile review reminder')
        ->assertSeeText('Open profile');

    $this->actingAs($user)
        ->patch(route('notifications.read-all'))
        ->assertRedirect();

    expect($user->fresh()->unreadNotifications()->count())->toBe(0);
});

test('notification center can delete one owned notification', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $user->notify(new SystemDatabaseNotification([
        'title' => 'Delete this reminder',
        'body' => 'This notification belongs to the signed-in user.',
    ]));
    $otherUser->notify(new SystemDatabaseNotification([
        'title' => 'Keep this reminder',
        'body' => 'This notification belongs to another user.',
    ]));

    $notificationId = $user->notifications()->firstOrFail()->getKey();
    $otherNotificationId = $otherUser->notifications()->firstOrFail()->getKey();

    $this->actingAs($user)
        ->delete(route('notifications.destroy', $notificationId))
        ->assertRedirect()
        ->assertSessionHas('status', 'Notification deleted.');

    expect($user->fresh()->notifications()->count())->toBe(0);
    expect($otherUser->fresh()->notifications()->count())->toBe(1);

    $this->actingAs($user)
        ->delete(route('notifications.destroy', $otherNotificationId))
        ->assertNotFound();

    expect($otherUser->fresh()->notifications()->count())->toBe(1);
});

test('notification center can clear all owned notifications', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $user->notify(new SystemDatabaseNotification([
        'title' => 'First clearable notification',
        'body' => 'One of the signed-in user notifications.',
    ]));
    $user->notify(new SystemDatabaseNotification([
        'title' => 'Second clearable notification',
        'body' => 'Another signed-in user notification.',
    ]));
    $otherUser->notify(new SystemDatabaseNotification([
        'title' => 'Other user notification',
        'body' => 'This one should stay.',
    ]));

    $this->actingAs($user)
        ->delete(route('notifications.clear'))
        ->assertRedirect()
        ->assertSessionHas('status', 'Notifications cleared.');

    expect($user->fresh()->notifications()->count())->toBe(0);
    expect($otherUser->fresh()->notifications()->count())->toBe(1);
});
