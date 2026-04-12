<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Admin\AdminPanelService;
use Illuminate\Contracts\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(AdminPanelService $panel): View
    {
        return view('pages.admin.dashboard', [
            'title' => 'Admin Dashboard',
            ...$panel->overview(),
        ]);
    }
}
