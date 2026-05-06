<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\InterviewChatbotService;
use App\Http\Controllers\Controller;
use App\Models\QuestionBankQuestion;
use App\Support\InterviewPracticeCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminQuestionBankController extends Controller
{
    public function store(Request $request, InterviewChatbotService $chatbot): RedirectResponse
    {
        $validated = $this->validateQuestion($request, $chatbot);
        $provider = $this->providerMap($chatbot)[$validated['provider_id']];

        $question = QuestionBankQuestion::query()->create([
            ...$validated,
            'provider_label' => $provider['label'],
            'source_type' => $provider['type'] === 'remote' ? 'ai_provider' : 'local',
            'is_active' => $request->boolean('is_active'),
            'created_by' => $request->user()?->getKey(),
            'updated_by' => $request->user()?->getKey(),
        ]);

        return back()->with('status', sprintf('Question #%d was added to the bank.', $question->id));
    }

    public function update(Request $request, QuestionBankQuestion $question, InterviewChatbotService $chatbot): RedirectResponse
    {
        $validated = $this->validateQuestion($request, $chatbot);
        $provider = $this->providerMap($chatbot)[$validated['provider_id']];

        $question->forceFill([
            ...$validated,
            'provider_label' => $provider['label'],
            'source_type' => $provider['type'] === 'remote' ? 'ai_provider' : 'local',
            'is_active' => $request->boolean('is_active'),
            'updated_by' => $request->user()?->getKey(),
        ])->save();

        return back()->with('status', sprintf('Question #%d was updated.', $question->id));
    }

    public function destroy(QuestionBankQuestion $question): RedirectResponse
    {
        $questionId = $question->id;
        $question->delete();

        return back()->with('status', sprintf('Question #%d was removed from the bank.', $questionId));
    }

    protected function validateQuestion(Request $request, InterviewChatbotService $chatbot): array
    {
        return $request->validate([
            'category_id' => ['required', 'string', Rule::in(array_keys(InterviewPracticeCatalog::categories()))],
            'provider_id' => ['required', 'string', Rule::in(array_keys($this->providerMap($chatbot)))],
            'question' => ['required', 'string', 'max:1000'],
            'guidance' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    protected function providerMap(InterviewChatbotService $chatbot): array
    {
        $providers = collect($chatbot->frontendBootstrap()['providers'] ?? [])
            ->filter(fn (array $provider) => ($provider['id'] ?? null) !== 'auto')
            ->mapWithKeys(function (array $provider) {
                $providerId = (string) ($provider['id'] ?? '');

                if ($providerId === '') {
                    return [];
                }

                return [
                    $providerId => [
                        'id' => $providerId,
                        'label' => (string) ($provider['label'] ?? $providerId),
                        'type' => (string) ($provider['type'] ?? 'local'),
                    ],
                ];
            })
            ->all();

        return $providers + [
            'local' => [
                'id' => 'local',
                'label' => 'Local PH coach',
                'type' => 'local',
            ],
        ];
    }
}
