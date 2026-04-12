<?php

namespace App\View\Components\header;

use App\Support\Notifications\NotificationCenterService;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class NotificationDropdown extends Component
{
    public array $notifications;

    public int $unreadCount;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $service = app(NotificationCenterService::class);
        $user = auth()->user();

        $this->notifications = $service->latestFor($user);
        $this->unreadCount = $service->unreadCountFor($user);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.header.notification-dropdown');
    }
}
