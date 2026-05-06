<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\InterviewChatbotService;
use App\Http\Controllers\Controller;
use App\Support\Admin\AdminPanelService;
use Illuminate\Contracts\View\View;

class AdminQuestionBankPageController extends Controller
{
    public function __invoke(AdminPanelService $panel, InterviewChatbotService $chatbot): View
    {
        return view('pages.admin.question-bank', [
            'title' => 'Question Bank',
            ...$panel->questionBankManagement($chatbot),
        ]);
    }
}
