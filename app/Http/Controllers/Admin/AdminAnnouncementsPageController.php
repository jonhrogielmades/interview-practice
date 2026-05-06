<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Admin\AdminPanelService;
use Illuminate\Contracts\View\View;

class AdminAnnouncementsPageController extends Controller
{
    public function __invoke(AdminPanelService $panel): View
    {
        return view('pages.admin.announcements', [
            'title' => 'Announcements',
            ...$panel->announcementManagement(),
        ]);
    }
}
