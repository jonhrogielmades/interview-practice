<?php

namespace App\Http\Controllers;

use App\Support\Notifications\NotificationCenterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request, NotificationCenterService $notifications): View
    {
        $user = $request->user();

        return view('pages.notifications.index', [
            'title' => 'Notifications',
            'notifications' => $notifications->paginatedFor($user),
            'unreadCount' => $notifications->unreadCountFor($user),
        ]);
    }

    public function markRead(Request $request, string $notification): RedirectResponse
    {
        /** @var DatabaseNotification $notificationModel */
        $notificationModel = $request->user()
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail();

        if ($notificationModel->read_at === null) {
            $notificationModel->markAsRead();
        }

        $redirectTo = $this->safeRedirect(
            $request->input('redirect_to')
                ?: data_get($notificationModel->data, 'action_url')
        );

        return redirect()->to($redirectTo);
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('status', 'Notifications marked as read.');
    }

    public function destroy(Request $request, string $notification): RedirectResponse
    {
        /** @var DatabaseNotification $notificationModel */
        $notificationModel = $request->user()
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail();

        $notificationModel->delete();

        return back()->with('status', 'Notification deleted.');
    }

    public function clear(Request $request): RedirectResponse
    {
        $deletedCount = $request->user()
            ->notifications()
            ->delete();

        return back()->with(
            'status',
            $deletedCount > 0 ? 'Notifications cleared.' : 'No notifications to clear.'
        );
    }

    protected function safeRedirect(mixed $value): string
    {
        if (! is_string($value) || trim($value) === '') {
            return route('notifications.index');
        }

        $host = parse_url($value, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return $value;
        }

        $requestHost = request()?->getHost() ?: parse_url((string) config('app.url'), PHP_URL_HOST);

        return is_string($requestHost) && strcasecmp($host, $requestHost) === 0
            ? $value
            : route('notifications.index');
    }
}
