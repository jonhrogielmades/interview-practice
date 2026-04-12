<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\InterviewChatbotService;
use App\Http\Controllers\Controller;
use App\Support\Admin\AdminPanelService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminApiManagementController extends Controller
{
    public function __invoke(AdminPanelService $panel, InterviewChatbotService $chatbot): View
    {
        return view('pages.admin.apis', [
            'title' => 'API Management',
            ...$panel->apiManagement($chatbot),
        ]);
    }

    public function providerStatuses(Request $request, InterviewChatbotService $chatbot): JsonResponse
    {
        $validated = $request->validate([
            'providers' => ['nullable', 'array', 'max:6'],
            'providers.*' => ['required_with:providers', 'string', 'max:50'],
        ]);

        return response()->json([
            'providers' => $chatbot->providerStatuses($validated['providers'] ?? null),
        ]);
    }
}
