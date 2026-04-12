<?php

namespace App\Support\Notifications;

use App\Models\User;
use App\Notifications\SystemDatabaseNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SystemNotificationService
{
    public function sendWelcomeNotification(User $user, string $source): void
    {
        $user->notify(new SystemDatabaseNotification([
            'title' => 'Welcome to Interview Practice',
            'body' => sprintf(
                'Your account is ready through %s. Start with session setup, then save your first AI-reviewed practice session.',
                $source
            ),
            'icon' => 'sparkles',
            'tone' => 'success',
            'action_url' => route('dashboard'),
            'action_label' => 'Open dashboard',
            'meta' => [
                'audience' => 'user',
                'source' => $source,
            ],
        ]));
    }

    public function notifyAdminsAboutRegistration(User $user, string $source): void
    {
        $this->notifyAdmins([
            'title' => 'New user registered',
            'body' => sprintf('%s joined the platform using %s.', $user->name, $source),
            'icon' => 'user',
            'tone' => 'brand',
            'action_url' => route('admin.users'),
            'action_label' => 'Review users',
            'meta' => [
                'audience' => 'admin',
                'event' => 'user_registered',
                'user_id' => $user->getKey(),
                'user_email' => $user->email,
            ],
        ]);
    }

    public function notifyUserAboutAdminCreatedAccount(User $target, User $actor): void
    {
        $target->notify(new SystemDatabaseNotification([
            'title' => 'Your account was created by an administrator',
            'body' => sprintf(
                '%s created your account with %s access. Sign in to review your dashboard and profile.',
                $actor->name,
                $this->roleLabel($target->account_role)
            ),
            'icon' => 'shield',
            'tone' => 'warning',
            'action_url' => route('dashboard'),
            'action_label' => 'Open dashboard',
            'meta' => [
                'audience' => 'user',
                'event' => 'admin_created_account',
                'actor_id' => $actor->getKey(),
            ],
        ]));
    }

    public function notifyAdminsAboutUserCreated(User $target, User $actor): void
    {
        $this->notifyAdmins([
            'title' => 'User account created',
            'body' => sprintf(
                '%s created %s as %s.',
                $actor->name,
                $target->name,
                $this->roleLabel($target->account_role)
            ),
            'icon' => 'user',
            'tone' => 'brand',
            'action_url' => route('admin.users'),
            'action_label' => 'Manage users',
            'meta' => [
                'audience' => 'admin',
                'event' => 'admin_created_user',
                'actor_id' => $actor->getKey(),
                'user_id' => $target->getKey(),
            ],
        ], [$actor->getKey(), $target->getKey()]);
    }

    public function notifyUserAboutAccountUpdated(User $target, User $actor, bool $roleChanged = false): void
    {
        $target->notify(new SystemDatabaseNotification([
            'title' => $roleChanged ? 'Your account access was updated' : 'Your account profile was updated',
            'body' => $roleChanged
                ? sprintf('%s changed your access to %s.', $actor->name, $this->roleLabel($target->account_role))
                : sprintf('%s updated your account details from the admin dashboard.', $actor->name),
            'icon' => $roleChanged ? 'shield' : 'profile',
            'tone' => $roleChanged ? 'warning' : 'blue',
            'action_url' => route('dashboard'),
            'action_label' => 'Review account',
            'meta' => [
                'audience' => 'user',
                'event' => $roleChanged ? 'account_access_updated' : 'account_profile_updated',
                'actor_id' => $actor->getKey(),
            ],
        ]));
    }

    public function notifyAdminsAboutUserUpdated(User $target, User $actor, bool $roleChanged = false): void
    {
        $this->notifyAdmins([
            'title' => $roleChanged ? 'Access level changed' : 'User profile updated',
            'body' => $roleChanged
                ? sprintf('%s changed %s to %s.', $actor->name, $target->name, $this->roleLabel($target->account_role))
                : sprintf('%s updated %s from the admin dashboard.', $actor->name, $target->name),
            'icon' => $roleChanged ? 'shield' : 'profile',
            'tone' => $roleChanged ? 'warning' : 'blue',
            'action_url' => route('admin.users'),
            'action_label' => 'Review users',
            'meta' => [
                'audience' => 'admin',
                'event' => $roleChanged ? 'user_role_updated' : 'user_profile_updated',
                'actor_id' => $actor->getKey(),
                'user_id' => $target->getKey(),
            ],
        ], [$actor->getKey(), $target->getKey()]);
    }

    public function notifyAdminsAboutUserDeleted(array $target, User $actor): void
    {
        $this->notifyAdmins([
            'title' => 'User account removed',
            'body' => sprintf('%s deleted %s (%s).', $actor->name, $target['name'], $target['email']),
            'icon' => 'trash',
            'tone' => 'danger',
            'action_url' => route('admin.users'),
            'action_label' => 'Review users',
            'meta' => [
                'audience' => 'admin',
                'event' => 'user_deleted',
                'actor_id' => $actor->getKey(),
                'deleted_email' => $target['email'],
            ],
        ], [$actor->getKey()]);
    }

    public function notifyUserAboutPracticeSession(User $user, array $session): void
    {
        $completed = (bool) ($session['completed'] ?? false);
        $category = (string) ($session['categoryName'] ?? 'Interview practice');
        $answeredCount = (int) ($session['answeredCount'] ?? 0);
        $averageScore = round((float) ($session['averageScore'] ?? 0), 1);

        $user->notify(new SystemDatabaseNotification([
            'title' => $completed ? 'Practice session completed' : 'Practice session saved',
            'body' => $completed
                ? sprintf('%s was completed with %d answered question%s and an average score of %.1f / 10.', $category, $answeredCount, $answeredCount === 1 ? '' : 's', $averageScore)
                : sprintf('%s was saved with %d answered question%s.', $category, $answeredCount, $answeredCount === 1 ? '' : 's'),
            'icon' => 'practice',
            'tone' => $completed ? 'success' : 'brand',
            'action_url' => $completed ? route('session-review') : route('practice'),
            'action_label' => $completed ? 'Review session' : 'Open practice',
            'meta' => [
                'audience' => 'user',
                'event' => $completed ? 'practice_completed' : 'practice_saved',
                'category' => $category,
                'score' => $averageScore,
            ],
        ]));
    }

    public function notifyAdminsAboutCompletedPracticeSession(User $user, array $session): void
    {
        if (! (bool) ($session['completed'] ?? false)) {
            return;
        }

        $category = (string) ($session['categoryName'] ?? 'Interview practice');
        $answeredCount = (int) ($session['answeredCount'] ?? 0);
        $averageScore = round((float) ($session['averageScore'] ?? 0), 1);

        $this->notifyAdmins([
            'title' => 'Practice session completed',
            'body' => sprintf(
                '%s completed %s with %d answered question%s and an average score of %.1f / 10.',
                $user->name,
                $category,
                $answeredCount,
                $answeredCount === 1 ? '' : 's',
                $averageScore
            ),
            'icon' => 'practice',
            'tone' => 'success',
            'action_url' => route('admin.dashboard'),
            'action_label' => 'Open admin dashboard',
            'meta' => [
                'audience' => 'admin',
                'event' => 'practice_completed',
                'user_id' => $user->getKey(),
                'category' => $category,
                'score' => $averageScore,
            ],
        ]);
    }

    protected function notifyAdmins(array $payload, array $exceptIds = []): void
    {
        $this->adminRecipients($exceptIds)->each(function (User $admin) use ($payload) {
            $admin->notify(new SystemDatabaseNotification($payload));
        });
    }

    protected function adminRecipients(array $exceptIds = []): Collection
    {
        return User::query()
            ->where('account_role', User::ROLE_ADMIN)
            ->when($exceptIds !== [], fn ($query) => $query->whereNotIn('id', array_values(array_unique($exceptIds))))
            ->get();
    }

    protected function roleLabel(?string $role): string
    {
        return Str::headline($role ?: User::ROLE_USER);
    }
}
