<?php

namespace App\Http\Controllers;

use App\Helpers\InterviewChatbotService;
use App\Helpers\InterviewWorkspaceService;
use App\Support\Notifications\SystemNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkspaceController extends Controller
{
    public function bootstrap(InterviewWorkspaceService $workspace): JsonResponse
    {
        return response()->json([
            'workspace' => $workspace->bootstrap(),
        ]);
    }

    public function updateSetup(Request $request, InterviewWorkspaceService $workspace): JsonResponse
    {
        $validated = $request->validate([
            'questionCount' => ['required', 'integer', 'min:1', 'max:20'],
            'focusModeIndex' => ['required', 'integer', 'min:0', 'max:10'],
            'pacingModeIndex' => ['required', 'integer', 'min:0', 'max:10'],
            'preferredCategoryId' => ['required', 'string', 'max:50'],
            'voiceMode' => ['required', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $setup = $workspace->saveSetup($validated);

        return response()->json([
            'setup' => $setup,
            'workspace' => $workspace->bootstrap(),
        ]);
    }

    public function destroySetup(InterviewWorkspaceService $workspace): JsonResponse
    {
        $setup = $workspace->clearSetup();

        return response()->json([
            'setup' => $setup,
            'workspace' => $workspace->bootstrap(),
        ]);
    }

    public function storeSession(
        Request $request,
        InterviewWorkspaceService $workspace,
        SystemNotificationService $notifications,
    ): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['nullable', 'string', 'max:120'],
            'startedAt' => ['nullable', 'date'],
            'savedAt' => ['nullable', 'date'],
            'categoryId' => ['nullable', 'string', 'max:50'],
            'categoryName' => ['nullable', 'string', 'max:255'],
            'categoryDescription' => ['nullable', 'string', 'max:4000'],
            'questionCount' => ['nullable', 'integer', 'min:0', 'max:50'],
            'answeredCount' => ['nullable', 'integer', 'min:0', 'max:50'],
            'focusMode' => ['nullable', 'string', 'max:120'],
            'pacingMode' => ['nullable', 'string', 'max:120'],
            'timerTargetSeconds' => ['nullable', 'integer', 'min:0', 'max:3600'],
            'averageScore' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'criteriaAverages' => ['nullable', 'array'],
            'criteriaAverages.clarity' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'criteriaAverages.relevance' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'criteriaAverages.grammar' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'criteriaAverages.professionalism' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'criteriaAverages.eyeContact' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'criteriaAverages.posture' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'criteriaAverages.headMovement' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'criteriaAverages.facialComposure' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'criteriaAverages.manuscriptVerbal' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'criteriaAverages.manuscriptNonVerbal' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'criteriaAverages.manuscriptOverall' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'completed' => ['nullable', 'boolean'],
            'answers' => ['nullable', 'array', 'max:50'],
            'answers.*.questionIndex' => ['nullable', 'integer', 'min:0', 'max:50'],
            'answers.*.questionNumber' => ['nullable', 'integer', 'min:0', 'max:50'],
            'answers.*.question' => ['nullable', 'string', 'max:4000'],
            'answers.*.answer' => ['nullable', 'string', 'max:20000'],
            'answers.*.average' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'answers.*.clarity' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'answers.*.relevance' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'answers.*.grammar' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'answers.*.professionalism' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'answers.*.matchedKeywords' => ['nullable', 'integer', 'min:0', 'max:999'],
            'answers.*.elapsedSeconds' => ['nullable', 'integer', 'min:0', 'max:7200'],
            'answers.*.inputMode' => ['nullable', 'string', 'max:20'],
            'answers.*.feedbackSummary' => ['nullable', 'array'],
            'answers.*.feedbackSummary.strengths' => ['nullable', 'array', 'max:10'],
            'answers.*.feedbackSummary.strengths.*' => ['nullable', 'string', 'max:255'],
            'answers.*.feedbackSummary.improvements' => ['nullable', 'array', 'max:10'],
            'answers.*.feedbackSummary.improvements.*' => ['nullable', 'string', 'max:255'],
            'answers.*.feedbackSummary.overall' => ['nullable', 'string', 'max:1200'],
            'answers.*.feedbackSummary.nextStep' => ['nullable', 'string', 'max:500'],
            'answers.*.feedbackSummary.provider' => ['nullable', 'string', 'max:255'],
            'answers.*.feedbackSummary.criteria' => ['nullable', 'array'],
            'answers.*.feedbackSummary.criteria.clarity' => ['nullable', 'string', 'max:500'],
            'answers.*.feedbackSummary.criteria.relevance' => ['nullable', 'string', 'max:500'],
            'answers.*.feedbackSummary.criteria.grammar' => ['nullable', 'string', 'max:500'],
            'answers.*.feedbackSummary.criteria.professionalism' => ['nullable', 'string', 'max:500'],
            'answers.*.feedbackSummary.manuscriptRubric' => ['nullable', 'array'],
            'answers.*.feedbackSummary.manuscriptRubric.verbal' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'answers.*.feedbackSummary.manuscriptRubric.nonVerbal' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'answers.*.feedbackSummary.manuscriptRubric.overall' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'answers.*.feedbackSummary.manuscriptRubric.hasNonVerbal' => ['nullable', 'boolean'],
            'answers.*.feedbackSummary.manuscriptRubric.readinessLabel' => ['nullable', 'string', 'max:120'],
            'answers.*.feedbackSummary.manuscriptRubric.criteria' => ['nullable', 'array'],
            'answers.*.feedbackSummary.manuscriptRubric.criteria.clarity' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'answers.*.feedbackSummary.manuscriptRubric.criteria.relevance' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'answers.*.feedbackSummary.manuscriptRubric.criteria.grammar' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'answers.*.feedbackSummary.manuscriptRubric.criteria.professionalism' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'answers.*.feedbackSummary.manuscriptRubric.criteria.eyeContact' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'answers.*.feedbackSummary.manuscriptRubric.criteria.posture' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'answers.*.feedbackSummary.manuscriptRubric.criteria.headMovement' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'answers.*.feedbackSummary.manuscriptRubric.criteria.facialComposure' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'answers.*.feedbackSummary.processEvaluations' => ['nullable', 'array'],
            'answers.*.feedbackSummary.processEvaluations.*' => ['nullable', 'array'],
            'answers.*.feedbackSummary.processEvaluations.*.label' => ['nullable', 'string', 'max:120'],
            'answers.*.feedbackSummary.processEvaluations.*.average' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'answers.*.feedbackSummary.processEvaluations.*.summary' => ['nullable', 'string', 'max:500'],
            'answers.*.feedbackSummary.processEvaluations.*.status' => ['nullable', 'string', 'max:50'],
            'answers.*.feedbackSummary.processEvaluations.*.available' => ['nullable', 'boolean'],
            'answers.*.feedbackSummary.processEvaluations.*.algorithms' => ['nullable', 'array', 'max:8'],
            'answers.*.feedbackSummary.processEvaluations.*.algorithms.*.name' => ['nullable', 'string', 'max:120'],
            'answers.*.feedbackSummary.processEvaluations.*.algorithms.*.score' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'answers.*.feedbackSummary.processEvaluations.*.algorithms.*.detail' => ['nullable', 'string', 'max:500'],
            'answers.*.feedbackSummary.processEvaluations.*.algorithms.*.status' => ['nullable', 'string', 'max:50'],
            'answers.*.feedbackSummary.processEvaluations.*.algorithms.*.available' => ['nullable', 'boolean'],
            'answers.*.feedbackSummary.visualSnapshot' => ['nullable', 'array'],
            'answers.*.feedbackSummary.visualSnapshot.bodyLanguageScore' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'answers.*.feedbackSummary.visualSnapshot.facialExpressionScore' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'answers.*.feedbackSummary.visualSnapshot.eyeContactScore' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'answers.*.feedbackSummary.visualSnapshot.postureScore' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'answers.*.feedbackSummary.visualSnapshot.headMovementScore' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'answers.*.feedbackSummary.visualSnapshot.facialComposureScore' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'answers.*.feedbackSummary.visualSnapshot.bodyLanguageLabel' => ['nullable', 'string', 'max:255'],
            'answers.*.feedbackSummary.visualSnapshot.facialExpressionLabel' => ['nullable', 'string', 'max:255'],
            'answers.*.feedbackSummary.visualSnapshot.eyeContactLabel' => ['nullable', 'string', 'max:255'],
            'answers.*.feedbackSummary.visualSnapshot.postureLabel' => ['nullable', 'string', 'max:255'],
            'answers.*.feedbackSummary.visualSnapshot.headMovementLabel' => ['nullable', 'string', 'max:255'],
            'answers.*.feedbackSummary.visualSnapshot.facialComposureLabel' => ['nullable', 'string', 'max:255'],
            'answers.*.feedbackSummary.visualSnapshot.tip' => ['nullable', 'string', 'max:500'],
            'notify' => ['nullable', 'boolean'],
        ]);

        $session = $workspace->saveSession($validated);
        $user = $request->user();

        if ($user && ($validated['notify'] ?? true)) {
            $notifications->notifyUserAboutPracticeSession($user, $session);
            $notifications->notifyAdminsAboutCompletedPracticeSession($user, $session);
        }

        return response()->json([
            'session' => $session,
            'workspace' => $workspace->bootstrap(),
        ], 201);
    }

    public function destroySessions(InterviewWorkspaceService $workspace): JsonResponse
    {
        $workspace->clearSessions();

        return response()->json([
            'workspace' => $workspace->bootstrap(),
        ]);
    }

    public function chatbot(Request $request, InterviewChatbotService $chatbot): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'mode' => ['nullable', 'string', 'in:chat,question_set,feedback_review,field_builder'],
            'questionCount' => ['nullable', 'integer', 'min:1', 'max:20'],
            'providerId' => ['nullable', 'string', 'max:50'],
            'categoryId' => ['nullable', 'string', 'max:50'],
            'currentQuestion' => ['nullable', 'string', 'max:4000'],
            'answerDraft' => ['nullable', 'string', 'max:8000'],
            'criteriaScores' => ['nullable', 'array'],
            'criteriaScores.clarity' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'criteriaScores.relevance' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'criteriaScores.grammar' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'criteriaScores.professionalism' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'criteriaScores.average' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'criteriaScores.matchedKeywords' => ['nullable', 'integer', 'min:0', 'max:999'],
            'history' => ['nullable', 'array', 'max:8'],
            'history.*.role' => ['required_with:history', 'string', 'in:user,assistant'],
            'history.*.text' => ['required_with:history', 'string', 'max:2000'],
        ]);

        return response()->json($chatbot->reply($validated));
    }

    public function chatbotProvidersStatus(Request $request, InterviewChatbotService $chatbot): JsonResponse
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
