<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\InterviewChatbotService;
use App\Http\Controllers\Controller;
use App\Support\Admin\AdminPanelService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AdminQuestionBankPageController extends Controller
{
    public function __invoke(Request $request, AdminPanelService $panel, InterviewChatbotService $chatbot): View
    {
        $categoryId = $request->query('category');
        $search = $request->query('search');
        $perPage = $request->query('per_page');

        return view('pages.admin.question-bank', [
            'title' => 'Question Bank',
            ...$panel->questionBankManagement(
                $chatbot,
                is_string($categoryId) ? $categoryId : null,
                is_string($search) ? $search : null,
                is_numeric($perPage) ? (int) $perPage : null,
            ),
        ]);
    }
}
