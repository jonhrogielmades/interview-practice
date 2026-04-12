<?php

namespace App\Support\Notifications;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class NotificationCenterService
{
    public function unreadCountFor(?User $user): int
    {
        if (! $user) {
            return 0;
        }

        return $user->unreadNotifications()->count();
    }

    public function latestFor(?User $user, int $limit = 6): array
    {
        if (! $user) {
            return [];
        }

        return $user->notifications()
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (DatabaseNotification $notification) => $this->present($notification))
            ->all();
    }

    public function paginatedFor(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return $user->notifications()
            ->latest()
            ->paginate($perPage)
            ->through(fn (DatabaseNotification $notification) => $this->present($notification));
    }

    public function present(DatabaseNotification $notification): array
    {
        $data = is_array($notification->data) ? $notification->data : [];

        return [
            'id' => $notification->getKey(),
            'title' => (string) ($data['title'] ?? 'Notification'),
            'body' => (string) ($data['body'] ?? ''),
            'icon' => (string) ($data['icon'] ?? 'bell'),
            'tone' => (string) ($data['tone'] ?? 'brand'),
            'actionUrl' => $this->safeActionUrl($data['action_url'] ?? null),
            'actionLabel' => (string) ($data['action_label'] ?? 'Open'),
            'meta' => is_array($data['meta'] ?? null) ? $data['meta'] : [],
            'isRead' => $notification->read_at !== null,
            'time' => $notification->created_at?->diffForHumans() ?? 'Just now',
            'createdAt' => $notification->created_at?->format('M j, Y g:i A') ?? 'Unknown',
        ];
    }

    protected function safeActionUrl(mixed $value): string
    {
        if (! is_string($value) || trim($value) === '') {
            return route('notifications.index');
        }

        $host = parse_url($value, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return $value;
        }

        $requestHost = request()?->getHost() ?: parse_url((string) config('app.url'), PHP_URL_HOST);

        return is_string($requestHost) && Str::lower($host) === Str::lower($requestHost)
            ? $value
            : route('notifications.index');
    }
}
