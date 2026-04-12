<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Admin\AdminPanelService;
use Illuminate\Contracts\View\View;

class AdminMonitoringPageController extends Controller
{
    public function __invoke(AdminPanelService $panel): View
    {
        return view('pages.admin.monitoring', [
            'title' => 'Monitoring Records',
            ...$panel->monitoringRecords(),
        ]);
    }
}
