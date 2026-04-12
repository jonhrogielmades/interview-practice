<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Admin\AdminPanelService;
use Illuminate\Contracts\View\View;

class AdminContentPageController extends Controller
{
    public function __invoke(AdminPanelService $panel): View
    {
        return view('pages.admin.content', [
            'title' => 'Question Bank & Announcements',
            ...$panel->contentManagement(),
        ]);
    }
}
