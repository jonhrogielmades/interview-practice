<?php

namespace App\Http\Controllers;

use App\Helpers\InterviewChatbotService;
use App\Support\InterviewPracticeCatalog;
use Illuminate\Contracts\View\View;

class ChatbotPageController extends Controller
{
    public function __invoke(InterviewChatbotService $chatbot): View
    {
        $bootstrap = $chatbot->frontendBootstrap();

        return view('pages.chatbot', [
            'title' => 'Interview Chatbot',
            'chatbotCategories' => collect(InterviewPracticeCatalog::practiceQuestionBank())
                ->map(fn (array $category, string $id) => [
                    'id' => $id,
                    'name' => $category['name'],
                    'description' => $category['description'],
                ])
                ->values()
                ->all(),
            'chatbotProviders' => collect($bootstrap['providers'] ?? [])
                ->filter(fn (array $provider) => in_array($provider['id'] ?? null, ['gemini', 'groq', 'openrouter', 'wisdomgate', 'cohere'], true))
                ->values()
                ->all(),
            'chatbotDefaultProviderId' => (string) ($bootstrap['defaultProviderId'] ?? 'auto'),
        ]);
    }
}
