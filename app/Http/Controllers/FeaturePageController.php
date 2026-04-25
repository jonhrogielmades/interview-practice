<?php

namespace App\Http\Controllers;

use App\Helpers\InterviewChatbotService;
use App\Helpers\InterviewWorkspaceService;
use App\Support\InterviewPracticeCatalog;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FeaturePageController extends Controller
{
    public function show(
        string $page,
        InterviewWorkspaceService $workspace,
        InterviewChatbotService $chatbot
    ): View {
        $context = $this->buildContext($workspace, $chatbot);

        $featurePage = match ($page) {
            'provider-health' => $this->providerHealthPage($context),
            'question-generator' => $this->questionGeneratorPage($context),
            'field-builder' => $this->fieldBuilderPage($context),
            'learning-lab' => $this->learningLabPage($context),
            'learning-lab-activities' => $this->learningLabPage($context, 'activities'),
            'voice-practice' => $this->voicePracticePage($context),
            'camera-readiness' => $this->cameraReadinessPage($context),
            'mobile-lan' => $this->mobileLanPage($context),
            default => abort(404),
        };

        return view($featurePage['view'] ?? "pages.{$page}", [
            'title' => $featurePage['title'],
            'featurePage' => $featurePage,
        ]);
    }

    protected function buildContext(
        InterviewWorkspaceService $workspace,
        InterviewChatbotService $chatbot
    ): array {
        $workspaceBootstrap = $workspace->bootstrap();
        $setup = $workspaceBootstrap['setup'] ?? InterviewPracticeCatalog::defaultSessionSetup();
        $sessions = collect($workspaceBootstrap['sessions'] ?? []);
        $answers = $sessions->flatMap(function (array $session) {
            return collect($session['answers'] ?? [])->map(function (array $answer) use ($session) {
                $answer['sessionSavedAt'] = $session['savedAt'] ?? null;
                $answer['categoryName'] = $session['categoryName'] ?? 'Unknown Category';

                return $answer;
            });
        })->values();

        $chatbotBootstrap = $chatbot->frontendBootstrap();
        $providers = collect($chatbotBootstrap['providers'] ?? []);
        $remoteProviders = $providers
            ->filter(fn (array $provider) => ($provider['type'] ?? null) === 'remote')
            ->values();
        $configuredRemoteProviders = $remoteProviders
            ->filter(fn (array $provider) => (bool) ($provider['configured'] ?? false))
            ->values();

        $categories = collect(InterviewPracticeCatalog::practiceQuestionBank());
        $preferredCategoryId = (string) ($setup['preferredCategoryId'] ?? 'job');
        $preferredCategory = $categories->get($preferredCategoryId) ?? $categories->first() ?? [];
        $providerPriorityIds = collect(explode(',', (string) config('services.interview_chatbot.provider_priority', '')))
            ->map(fn (string $providerId) => trim($providerId))
            ->filter()
            ->values();

        return [
            'setup' => $setup,
            'sessions' => $sessions,
            'answers' => $answers,
            'providers' => $providers,
            'remoteProviders' => $remoteProviders,
            'configuredRemoteProviders' => $configuredRemoteProviders,
            'defaultProviderId' => (string) ($chatbotBootstrap['defaultProviderId'] ?? 'auto'),
            'providerPriorityIds' => $providerPriorityIds,
            'providerPriorityLabels' => $providerPriorityIds
                ->map(fn (string $providerId) => $this->providerLabel($providers, $providerId))
                ->values(),
            'categories' => $categories,
            'preferredCategoryId' => $preferredCategoryId,
            'preferredCategory' => $preferredCategory,
            'focusModes' => collect(InterviewPracticeCatalog::focusModes()),
            'pacingModes' => collect(InterviewPracticeCatalog::pacingModes()),
            'questionCountOptions' => collect(InterviewPracticeCatalog::questionCountOptions()),
            'responsePreferences' => collect(InterviewPracticeCatalog::responsePreferences()),
            'fieldBlueprints' => collect($this->fieldBlueprints()),
            'appUrl' => rtrim((string) config('app.url', 'http://localhost'), '/'),
            'viteDevServerHost' => trim((string) env('VITE_DEV_SERVER_HOST', '')),
        ];
    }

    protected function providerHealthPage(array $context): array
    {
        $providers = $context['providers'];
        $remoteProviders = $context['remoteProviders'];
        $configuredRemoteProviders = $context['configuredRemoteProviders'];
        $priorityLabels = $context['providerPriorityLabels'];

        $providerItems = $providers->map(function (array $provider) {
            $isConfigured = (bool) ($provider['configured'] ?? false);
            $type = (string) ($provider['type'] ?? 'local');
            $value = match ($type) {
                'remote' => $isConfigured ? 'Configured' : 'Needs key',
                'router' => 'Routing enabled',
                default => 'Always ready',
            };

            $list = [];

            if (! empty($provider['model'])) {
                $list[] = 'Model: '.(string) $provider['model'];
            }

            if ($type === 'remote') {
                $list[] = $isConfigured
                    ? 'This provider can be selected from the chatbot and practice workspace.'
                    : 'Add the matching API key in the environment file to enable it.';
            } elseif ($type === 'router') {
                $list[] = 'Auto tries configured providers in priority order before the local fallback.';
            } else {
                $list[] = 'The built-in fallback keeps interview coaching available without external APIs.';
            }

            return [
                'eyebrow' => Str::headline($type),
                'title' => (string) ($provider['label'] ?? 'Unknown provider'),
                'value' => $value,
                'body' => (string) ($provider['description'] ?? 'Interview coaching route'),
                'list' => $list,
                'tone' => match ($type) {
                    'router' => 'brand',
                    'remote' => $isConfigured ? 'success' : 'warning',
                    default => 'blue',
                },
            ];
        })->values()->all();

        $keyStatuses = [
            ['name' => 'GEMINI_API_KEY', 'configured' => (bool) data_get($remoteProviders->firstWhere('id', 'gemini'), 'configured', false)],
            ['name' => 'GROQ_API_KEY', 'configured' => (bool) data_get($remoteProviders->firstWhere('id', 'groq'), 'configured', false)],
            ['name' => 'OPENROUTER_API_KEY', 'configured' => (bool) data_get($remoteProviders->firstWhere('id', 'openrouter'), 'configured', false)],
            ['name' => 'ANTHROPIC_API_KEY / CLAUDE_API_KEY', 'configured' => (bool) data_get($remoteProviders->firstWhere('id', 'claude'), 'configured', false)],
            ['name' => 'HUGGINGFACE_API_KEY / WISDOMGATE_API_KEY', 'configured' => (bool) data_get($remoteProviders->firstWhere('id', 'wisdomgate'), 'configured', false)],
            ['name' => 'COHERE_API_KEY', 'configured' => (bool) data_get($remoteProviders->firstWhere('id', 'cohere'), 'configured', false)],
        ];

        return [
            'eyebrow' => 'Chatbot routing and fallback visibility',
            'title' => 'Provider Health',
            'description' => 'Inspect how the multi-provider interview coach is configured right now, which routes are available, and where the local fallback takes over.',
            'gradient' => 'from-brand-500/10 via-white to-sky-500/10 dark:from-brand-500/5 dark:via-gray-900 dark:to-sky-500/5',
            'summaryCards' => [
                [
                    'label' => 'Configured APIs',
                    'value' => $configuredRemoteProviders->count().' / '.$remoteProviders->count(),
                    'detail' => $configuredRemoteProviders->isNotEmpty()
                        ? 'Remote providers available for chatbot and workspace requests'
                        : 'No remote provider keys are configured yet',
                ],
                [
                    'label' => 'Default Route',
                    'value' => $this->providerLabel($providers, $context['defaultProviderId']),
                    'detail' => 'Current default selection from the interview chatbot bootstrap',
                ],
                [
                    'label' => 'Priority Starts With',
                    'value' => $priorityLabels->first() ?? 'Not set',
                    'detail' => $priorityLabels->isNotEmpty()
                        ? 'Auto routing chain: '.$priorityLabels->implode(' -> ')
                        : 'Provider priority has not been configured',
                ],
                [
                    'label' => 'Fallback Coach',
                    'value' => 'Local Ready',
                    'detail' => 'The local PH coach remains available even without remote keys',
                ],
            ],
            'primarySections' => [
                [
                    'title' => 'Provider Directory',
                    'description' => 'Every route exposed by the current chatbot bootstrap, including Auto and the local fallback.',
                    'columns' => 'md:grid-cols-2',
                    'items' => $providerItems,
                ],
                [
                    'title' => 'Routing Behavior',
                    'description' => 'How requests move through the interview chatbot and practice workspace today.',
                    'columns' => 'md:grid-cols-2',
                    'items' => [
                        [
                            'title' => 'Auto routing',
                            'value' => 'Enabled',
                            'body' => 'Auto can try configured remote providers before handing the request to the local PH coach.',
                            'list' => [
                                'Default provider: '.$this->providerLabel($providers, $context['defaultProviderId']),
                                'Priority order: '.($priorityLabels->isNotEmpty() ? $priorityLabels->implode(' -> ') : 'No priority configured'),
                            ],
                            'tone' => 'brand',
                        ],
                        [
                            'title' => 'Question generation',
                            'value' => 'Selectable',
                            'body' => 'The practice workspace can choose a provider while generating fresh category-based question sets.',
                            'list' => [
                                'Used by the AI question chatbot in Practice',
                                'Falls back locally when a provider is unavailable',
                            ],
                            'tone' => 'blue',
                        ],
                        [
                            'title' => 'Field builder',
                            'value' => 'Selectable',
                            'body' => 'The field builder can route through the selected provider or keep working with the local fallback.',
                            'list' => [
                                'Lets users refine a role, course, or specialization before launching practice',
                                'Keeps the next question set aligned to the selected field',
                            ],
                            'tone' => 'success',
                        ],
                        [
                            'title' => 'Answer review',
                            'value' => 'Selectable',
                            'body' => 'Feedback review requests use the same provider routing rules as normal chatbot conversations.',
                            'list' => [
                                'Scores are still saved even when remote AI is unavailable',
                                'The local coach can generate fallback review summaries',
                            ],
                            'tone' => 'warning',
                        ],
                    ],
                ],
            ],
            'secondarySections' => [
                [
                    'title' => 'Environment Keys',
                    'description' => 'These are the keys the app checks before marking a remote provider as configured.',
                    'items' => collect($keyStatuses)->map(function (array $key) {
                        return [
                            'title' => $key['name'],
                            'value' => $key['configured'] ? 'Configured' : 'Missing',
                            'body' => $key['configured']
                                ? 'This environment value is present and the provider can be selected.'
                                : 'Add this value in the environment file to unlock the provider.',
                            'tone' => $key['configured'] ? 'success' : 'warning',
                        ];
                    })->all(),
                ],
                [
                    'title' => 'Next Step',
                    'description' => 'Use the existing pages that already exercise the routing logic live.',
                    'items' => [
                        [
                            'title' => 'Run a live health check',
                            'value' => 'Chatbot page',
                            'body' => 'The interview chatbot page already includes a live provider check button for configured APIs.',
                            'tone' => 'brand',
                        ],
                        [
                            'title' => 'Try provider selection in practice',
                            'value' => 'Practice page',
                            'body' => 'Question generation, field building, and feedback review all route through the provider catalog in Practice.',
                            'tone' => 'blue',
                        ],
                    ],
                ],
            ],
            'actionsDescription' => 'Move straight into the existing UI that consumes these provider routes.',
            'actions' => [
                ['label' => 'Open Interview Chatbot', 'href' => route('chatbot'), 'style' => 'primary'],
                ['label' => 'Open Practice', 'href' => route('practice'), 'style' => 'secondary'],
                ['label' => 'Open Session Setup', 'href' => route('session-setup'), 'style' => 'ghost'],
            ],
        ];
    }

    protected function questionGeneratorPage(array $context): array
    {
        $categories = $context['categories'];
        $preferredCategory = $context['preferredCategory'];
        $providers = $context['providers'];
        $setup = $context['setup'];
        $questionCount = (int) ($setup['questionCount'] ?? 3);
        $questionCountLabel = $this->questionCountLabel($context['questionCountOptions'], $questionCount);

        return [
            'eyebrow' => 'Fresh question set planning',
            'title' => 'Question Generator',
            'description' => 'Review the category-backed question bank, the current generation defaults, and the prompts that feed the practice workspace before a mock session starts.',
            'gradient' => 'from-blue-light-500/10 via-white to-brand-500/10 dark:from-blue-light-500/5 dark:via-gray-900 dark:to-brand-500/5',
            'summaryCards' => [
                [
                    'label' => 'Practice Tracks',
                    'value' => (string) $categories->count(),
                    'detail' => 'Job, scholarship, admission, and IT interview flows',
                ],
                [
                    'label' => 'Starter Questions',
                    'value' => (string) $categories->sum(fn (array $category) => count($category['questions'] ?? [])),
                    'detail' => 'Seed prompts available before AI generation starts',
                ],
                [
                    'label' => 'Default Set Size',
                    'value' => (string) $questionCount,
                    'detail' => $questionCountLabel,
                ],
                [
                    'label' => 'Prompt Starters',
                    'value' => (string) $categories->sum(fn (array $category) => count($category['quickPrompts'] ?? [])),
                    'detail' => 'Quick prompts available across the category catalog',
                ],
            ],
            'primarySections' => [
                [
                    'title' => 'Category Question Banks',
                    'description' => 'The question generator is anchored to the same interview catalog the rest of the app uses.',
                    'columns' => 'md:grid-cols-2',
                    'items' => $categories->map(function (array $category, string $categoryId) {
                        return [
                            'eyebrow' => strtoupper($categoryId),
                            'title' => (string) ($category['name'] ?? 'Interview Category'),
                            'value' => count($category['questions'] ?? []).' prompts',
                            'body' => (string) ($category['description'] ?? 'Interview practice category'),
                            'list' => collect($category['questions'] ?? [])->take(2)->values()->all(),
                            'meta' => 'Quick prompts: '.count($category['quickPrompts'] ?? []),
                            'tone' => match ($categoryId) {
                                'job' => 'brand',
                                'scholarship' => 'blue',
                                'admission' => 'success',
                                default => 'warning',
                            },
                        ];
                    })->values()->all(),
                ],
                [
                    'title' => 'Generation Workflow',
                    'description' => 'How the workspace shapes a question set before the first answer is recorded.',
                    'columns' => 'md:grid-cols-2',
                    'items' => [
                        [
                            'title' => 'Category-first generation',
                            'value' => (string) ($preferredCategory['name'] ?? 'General interview'),
                            'body' => 'Question sets stay grounded in the selected interview category instead of using a generic prompt.',
                            'list' => [
                                'Preferred category comes from saved session defaults',
                                'The category context also drives quick prompts and chatbot coaching',
                            ],
                            'tone' => 'brand',
                        ],
                        [
                            'title' => 'Question count control',
                            'value' => $questionCountLabel,
                            'body' => 'The selected question count becomes the target size for the next generated interview set.',
                            'list' => [
                                'Available counts: '.$context['questionCountOptions']->implode(', '),
                                'The workspace can still regenerate the set after the first pass',
                            ],
                            'tone' => 'blue',
                        ],
                        [
                            'title' => 'Provider routing',
                            'value' => $this->providerLabel($providers, $context['defaultProviderId']),
                            'body' => 'Question generation can use the selected provider or drop to the local PH coach when needed.',
                            'list' => [
                                'Provider selection is available directly from Practice',
                                'The generated question set remains tied to the active category',
                            ],
                            'tone' => 'success',
                        ],
                        [
                            'title' => 'Interviewer sync',
                            'value' => 'Enabled',
                            'body' => 'Once a set is generated, the active question is kept in sync with the AI interviewer voice inside the modal.',
                            'list' => [
                                'The interviewer can read the current question aloud',
                                'The next question flow stays aligned with the saved set',
                            ],
                            'tone' => 'warning',
                        ],
                    ],
                ],
            ],
            'secondarySections' => [
                [
                    'title' => 'Saved Defaults',
                    'description' => 'The next generated set will inherit these current workspace preferences.',
                    'items' => [
                        [
                            'title' => 'Question Count',
                            'value' => $questionCountLabel,
                            'body' => 'Saved from Session Setup and reused when Practice loads.',
                            'tone' => 'brand',
                        ],
                        [
                            'title' => 'Coach Focus',
                            'value' => $this->focusModeLabel($context['focusModes'], (int) ($setup['focusModeIndex'] ?? 0)),
                            'body' => 'This framing helps shape the coaching voice around the next generated set.',
                            'tone' => 'blue',
                        ],
                        [
                            'title' => 'Pacing',
                            'value' => $this->pacingModeLabel($context['pacingModes'], (int) ($setup['pacingModeIndex'] ?? 0)),
                            'body' => 'Pacing affects how the overall mock session feels once generation is complete.',
                            'tone' => 'success',
                        ],
                        [
                            'title' => 'Response Preference',
                            'value' => $this->responsePreferenceLabel((string) ($setup['voiceMode'] ?? 'text')),
                            'body' => 'The same session defaults carry into the live practice modal after generation.',
                            'tone' => 'warning',
                        ],
                    ],
                ],
                [
                    'title' => 'Preferred Category Prompts',
                    'description' => 'Quick prompt starters currently available for the default category.',
                    'items' => [
                        [
                            'title' => (string) ($preferredCategory['name'] ?? 'Interview Practice'),
                            'value' => count($preferredCategory['quickPrompts'] ?? []).' prompts',
                            'body' => 'Use these starters in the chatbot before regenerating a new question set.',
                            'list' => collect($preferredCategory['quickPrompts'] ?? [])->take(4)->values()->all(),
                            'tone' => 'brand',
                        ],
                    ],
                ],
            ],
            'actionsDescription' => 'Jump into the tools that already generate and refine interview question sets.',
            'actions' => [
                ['label' => 'Open Practice', 'href' => route('practice'), 'style' => 'primary'],
                ['label' => 'Open Interview Chatbot', 'href' => route('chatbot'), 'style' => 'secondary'],
                ['label' => 'Adjust Session Setup', 'href' => route('session-setup'), 'style' => 'ghost'],
            ],
        ];
    }

    protected function fieldBuilderPage(array $context): array
    {
        $setup = $context['setup'];
        $preferredCategoryId = $context['preferredCategoryId'];
        $preferredCategory = $context['preferredCategory'];
        $providers = $context['providers'];
        $fieldBlueprint = $context['fieldBlueprints']->get($preferredCategoryId) ?? $context['fieldBlueprints']->first() ?? [];

        return [
            'eyebrow' => 'Target role and course planning',
            'title' => 'Field Builder',
            'description' => 'Shape the specific role, course, or specialization before practice starts so the next mock interview feels tailored instead of generic.',
            'gradient' => 'from-warning-500/10 via-white to-brand-500/10 dark:from-warning-500/5 dark:via-gray-900 dark:to-brand-500/5',
            'summaryCards' => [
                [
                    'label' => 'Preferred Category',
                    'value' => (string) ($preferredCategory['name'] ?? 'Interview Practice'),
                    'detail' => 'The field builder defaults to this category first',
                ],
                [
                    'label' => 'Suggested Fields',
                    'value' => (string) count($fieldBlueprint['suggestions'] ?? []),
                    'detail' => (string) ($fieldBlueprint['fieldLabel'] ?? 'Target field').' ideas available immediately',
                ],
                [
                    'label' => 'Saved Notes',
                    'value' => trim((string) ($setup['notes'] ?? '')) !== '' ? 'Ready' : 'Empty',
                    'detail' => trim((string) ($setup['notes'] ?? '')) !== ''
                        ? 'Setup notes can guide the next field plan'
                        : 'Add notes in Session Setup to steer the next field plan',
                ],
                [
                    'label' => 'Current Route',
                    'value' => $this->providerLabel($providers, $context['defaultProviderId']),
                    'detail' => 'Field plans can use the selected provider or the local fallback',
                ],
            ],
            'primarySections' => [
                [
                    'title' => 'Builder Workflow',
                    'description' => 'How the field builder turns a broad category into a more targeted interview scenario.',
                    'columns' => 'md:grid-cols-2',
                    'items' => [
                        [
                            'title' => 'Choose a category',
                            'value' => (string) ($preferredCategory['name'] ?? 'Interview category'),
                            'body' => 'The field builder starts with the selected category before you refine a role, study field, or specialization.',
                            'list' => [
                                'Current default: '.(string) ($preferredCategory['name'] ?? 'Interview category'),
                                'Each category keeps its own starter suggestions and quick prompts',
                            ],
                            'tone' => 'brand',
                        ],
                        [
                            'title' => 'Describe the target field',
                            'value' => (string) ($fieldBlueprint['fieldLabel'] ?? 'Target field'),
                            'body' => (string) ($fieldBlueprint['tip'] ?? 'Describe what you want the field builder to emphasize before generating a plan.'),
                            'list' => [
                                'Example placeholder: '.(string) ($fieldBlueprint['placeholder'] ?? 'Add a role, course, or specialization'),
                                'Notes can add extra context before the plan is generated',
                            ],
                            'tone' => 'blue',
                        ],
                        [
                            'title' => 'Generate the plan',
                            'value' => $this->providerLabel($providers, $context['defaultProviderId']),
                            'body' => 'The selected provider can draft a field plan, but the local fallback can still keep the flow moving.',
                            'list' => [
                                'Provider selection is available from the practice workspace',
                                'The plan can be refined again before the interview modal opens',
                            ],
                            'tone' => 'success',
                        ],
                        [
                            'title' => 'Apply and continue',
                            'value' => 'Practice ready',
                            'body' => 'Once the field is confirmed, Practice uses it to generate a more specific question set for the modal.',
                            'list' => [
                                'The selected field becomes part of the next question generation step',
                                'You can still edit the field again before continuing',
                            ],
                            'tone' => 'warning',
                        ],
                    ],
                ],
                [
                    'title' => 'Category Starting Points',
                    'description' => 'Starter field suggestions available across the interview tracks.',
                    'columns' => 'md:grid-cols-2',
                    'items' => $context['fieldBlueprints']->map(function (array $blueprint, string $categoryId) use ($context) {
                        $category = $context['categories']->get($categoryId) ?? [];

                        return [
                            'eyebrow' => strtoupper($categoryId),
                            'title' => (string) ($category['name'] ?? 'Interview category'),
                            'value' => (string) ($blueprint['fieldLabel'] ?? 'Target field'),
                            'body' => (string) ($blueprint['tip'] ?? 'Starter suggestions for field planning.'),
                            'list' => array_values($blueprint['suggestions'] ?? []),
                            'meta' => 'Prompt example: '.(string) ($blueprint['placeholder'] ?? 'Describe the field you want to practice'),
                            'tone' => match ($categoryId) {
                                'job' => 'brand',
                                'scholarship' => 'blue',
                                'admission' => 'success',
                                default => 'warning',
                            },
                        ];
                    })->values()->all(),
                ],
            ],
            'secondarySections' => [
                [
                    'title' => 'Current Defaults',
                    'description' => 'The field builder works alongside the saved session setup, not separately from it.',
                    'items' => [
                        [
                            'title' => 'Coach Focus',
                            'value' => $this->focusModeLabel($context['focusModes'], (int) ($setup['focusModeIndex'] ?? 0)),
                            'body' => 'This affects how follow-up guidance is framed after the field is set.',
                            'tone' => 'brand',
                        ],
                        [
                            'title' => 'Pacing',
                            'value' => $this->pacingModeLabel($context['pacingModes'], (int) ($setup['pacingModeIndex'] ?? 0)),
                            'body' => 'The eventual interview modal still uses the saved pacing target.',
                            'tone' => 'blue',
                        ],
                        [
                            'title' => 'Response Preference',
                            'value' => $this->responsePreferenceLabel((string) ($setup['voiceMode'] ?? 'text')),
                            'body' => 'Voice-ready preferences still carry forward after the field is chosen.',
                            'tone' => 'success',
                        ],
                        [
                            'title' => 'Setup Notes',
                            'value' => trim((string) ($setup['notes'] ?? '')) !== '' ? 'Available' : 'Add notes',
                            'body' => trim((string) ($setup['notes'] ?? '')) !== ''
                                ? Str::limit((string) $setup['notes'], 120, '')
                                : 'Use notes to remind yourself what the next field plan should emphasize.',
                            'tone' => 'warning',
                        ],
                    ],
                ],
                [
                    'title' => 'Quick Prompt Starters',
                    'description' => 'These prompts can help refine the preferred category before applying the field plan.',
                    'items' => [
                        [
                            'title' => (string) ($preferredCategory['name'] ?? 'Interview Practice'),
                            'value' => count($preferredCategory['quickPrompts'] ?? []).' prompts',
                            'body' => 'Use these in the chatbot or the embedded field builder before launching a session.',
                            'list' => collect($preferredCategory['quickPrompts'] ?? [])->take(4)->values()->all(),
                            'tone' => 'brand',
                        ],
                    ],
                ],
            ],
            'actionsDescription' => 'The field builder already lives inside Practice and works with the chatbot routes shown above.',
            'actions' => [
                ['label' => 'Open Practice', 'href' => route('practice'), 'style' => 'primary'],
                ['label' => 'Open Interview Chatbot', 'href' => route('chatbot'), 'style' => 'secondary'],
                ['label' => 'Adjust Session Setup', 'href' => route('session-setup'), 'style' => 'ghost'],
            ],
        ];
    }

    protected function learningLabPage(array $context, string $viewMode = 'overview'): array
    {
        $viewMode = $viewMode === 'activities' ? 'activities' : 'overview';
        $setup = $context['setup'];
        $sessions = $context['sessions'];
        $answers = $context['answers'];
        $preferredCategory = $context['preferredCategory'];
        $preferredCategoryId = (string) ($context['preferredCategoryId'] ?? 'job');
        $preferredCategoryName = (string) ($preferredCategory['name'] ?? 'Interview Practice');
        $focusLabel = $this->focusModeLabel($context['focusModes'], (int) ($setup['focusModeIndex'] ?? 0));
        $pacingLabel = $this->pacingModeLabel($context['pacingModes'], (int) ($setup['pacingModeIndex'] ?? 0));
        $responseLabel = $this->responsePreferenceLabel((string) ($setup['voiceMode'] ?? 'text'));
        $averageScoreValue = $answers->isNotEmpty()
            ? (float) $answers->avg(fn (array $answer) => (float) ($answer['average'] ?? 0))
            : null;
        $averageScore = $averageScoreValue !== null
            ? number_format($averageScoreValue, 1).' / 10'
            : 'No scores yet';
        $latestAnswer = $answers
            ->sortByDesc(fn (array $answer) => (string) ($answer['sessionSavedAt'] ?? ''))
            ->first();
        $practiceLaunch = function (string $categoryId, array $query = []) {
            return route('practice', array_merge([
                'category' => $categoryId,
                'source' => 'learning-lab',
            ], $query));
        };
        $learningActivityLaunch = function (array $query = []) {
            return route('practice', array_merge([
                'source' => 'learning-lab',
            ], $query));
        };
        $learningActivityCatalog = collect(InterviewPracticeCatalog::learningActivityCatalog());
        $recommendedActivityId = match (true) {
            $answers->isEmpty() => 'quick-drill',
            $averageScoreValue !== null && $averageScoreValue < 6.5 => 'star-response',
            in_array((string) ($setup['voiceMode'] ?? 'text'), ['voice', 'hybrid'], true) => 'voice-rehearsal',
            default => 'follow-up-sprint',
        };
        $recommendedActivity = $learningActivityCatalog->get($recommendedActivityId)
            ?? $learningActivityCatalog->first()
            ?? ['title' => 'Quick Drill'];
        $recommendedReason = match ($recommendedActivityId) {
            'quick-drill' => 'No evaluated answer is saved yet, so a short baseline run is the best starting point.',
            'star-response' => 'The saved score shows that answer structure is the best area to strengthen next.',
            'voice-rehearsal' => 'The saved response preference already leans toward spoken practice.',
            default => 'Saved answers are available, so a follow-up round can target the next improvement.',
        };

        $categoryCards = $context['categories']->map(function (array $category, string $categoryId) use ($practiceLaunch) {
            $tone = match ($categoryId) {
                'job' => 'brand',
                'scholarship' => 'blue',
                'admission' => 'success',
                default => 'warning',
            };

            return [
                'eyebrow' => strtoupper($categoryId),
                'title' => (string) ($category['name'] ?? 'Interview Track'),
                'value' => count($category['quickPrompts'] ?? []).' drills',
                'body' => (string) ($category['description'] ?? 'Interview practice track'),
                'list' => collect($category['quickPrompts'] ?? [])->take(3)->values()->all(),
                'meta' => 'Use this track when the next practice round needs category-specific coaching.',
                'tone' => $tone,
                'actionLabel' => 'Open In Practice',
                'actionHref' => $practiceLaunch($categoryId, ['activity' => 'track-launch']),
            ];
        })->values()->all();

        $learningModules = [
            [
                'title' => 'Answer Blueprint',
                'tag' => $focusLabel,
                'summary' => 'Launch Practice with a structure-first drill so the next answer is built around direct response, evidence, and results.',
                'meta' => 'Best before a fresh question set on '.$preferredCategoryName,
                'tone' => 'brand',
                'primaryActionLabel' => 'Launch In Practice',
                'primaryActionHref' => $practiceLaunch($preferredCategoryId, ['module' => 'answer-blueprint']),
                'secondaryActionLabel' => 'Adjust Session Setup',
                'secondaryActionHref' => route('session-setup'),
            ],
            [
                'title' => 'Delivery Rehearsal',
                'tag' => $responseLabel,
                'summary' => 'Carry your saved response preference and pacing into Practice so the next session feels rehearsed instead of improvised.',
                'meta' => 'Current pacing: '.$pacingLabel,
                'tone' => 'blue',
                'primaryActionLabel' => 'Start Rehearsal',
                'primaryActionHref' => $practiceLaunch($preferredCategoryId, [
                    'module' => 'delivery-rehearsal',
                    'activity' => 'voice-rehearsal',
                ]),
                'secondaryActionLabel' => 'Open Practice',
                'secondaryActionHref' => route('practice'),
            ],
            [
                'title' => 'Visual Presence',
                'tag' => 'Camera-ready',
                'summary' => 'Use Camera Readiness and then return to Practice with a presence-focused drill before the next interview run starts.',
                'meta' => 'Ideal before live body-language and facial-expression coaching',
                'tone' => 'success',
                'primaryActionLabel' => 'Open Camera Readiness',
                'primaryActionHref' => route('camera-readiness'),
                'secondaryActionLabel' => 'Launch Presence Drill',
                'secondaryActionHref' => $practiceLaunch($preferredCategoryId, [
                    'module' => 'visual-presence',
                    'activity' => 'camera-check',
                ]),
            ],
            [
                'title' => 'Reflection Review',
                'tag' => $answers->isNotEmpty() ? $averageScore : 'Start practicing',
                'summary' => 'Review saved performance first, then open Practice again with a better idea of what the next question set should improve.',
                'meta' => $answers->isNotEmpty()
                    ? 'Saved answers: '.$answers->count().' across '.$sessions->count().' session(s)'
                    : 'No reviewed answers yet, so the first drill should focus on baseline confidence',
                'tone' => 'warning',
                'primaryActionLabel' => 'Open Progress',
                'primaryActionHref' => route('progress'),
                'secondaryActionLabel' => 'Launch Review Drill',
                'secondaryActionHref' => $practiceLaunch($preferredCategoryId, [
                    'module' => 'reflection-review',
                    'activity' => 'follow-up-sprint',
                ]),
            ],
        ];

        $learningActivities = $learningActivityCatalog
            ->map(function (array $activity, string $activityId) use (
                $context,
                $learningActivityLaunch,
                $preferredCategoryName,
                $recommendedActivityId,
                $responseLabel,
                $setup
            ) {
                $tag = match ($activityId) {
                    'quick-drill' => $this->questionCountLabel($context['questionCountOptions'], (int) ($setup['questionCount'] ?? 3)),
                    'voice-rehearsal' => $responseLabel,
                    'follow-up-sprint' => $preferredCategoryName,
                    default => (string) ($activity['tag'] ?? 'Practice'),
                };
                $levels = collect($activity['levels'] ?? [])
                    ->map(function (array $level) {
                        $targetScore = (float) ($level['targetScore'] ?? 7.0);

                        return [
                            'level' => (int) ($level['level'] ?? 1),
                            'label' => (string) ($level['label'] ?? 'Level 1'),
                            'targetScore' => $targetScore,
                            'targetLabel' => number_format($targetScore, 1).' / 10',
                            'questionFocus' => (string) ($level['questionFocus'] ?? 'Answer the next interview question clearly.'),
                        ];
                    })
                    ->values();
                $launchLevel = $levels->first() ?? [
                    'level' => 1,
                    'label' => 'Level 1',
                    'targetScore' => 7.0,
                    'targetLabel' => '7.0 / 10',
                    'questionFocus' => 'Answer the next interview question clearly.',
                ];

                return [
                    'title' => (string) ($activity['title'] ?? 'Learning Activity'),
                    'tag' => $tag,
                    'summary' => (string) ($activity['summary'] ?? 'Open a focused practice drill for this interview system.'),
                    'tone' => (string) ($activity['tone'] ?? 'neutral'),
                    'recommended' => $activityId === $recommendedActivityId,
                    'levelLabel' => (string) $launchLevel['label'],
                    'targetScoreLabel' => (string) $launchLevel['targetLabel'],
                    'passRule' => 'Pass this level with '.$launchLevel['targetLabel'].' or higher.',
                    'retryRule' => 'Below target: try the same level again. Passed: proceed to the next level of questions.',
                    'levels' => $levels->all(),
                    'actionLabel' => (string) ($activity['actionLabel'] ?? 'Launch Activity'),
                    'actionHref' => $learningActivityLaunch([
                        'module' => (string) ($activity['module'] ?? 'answer-blueprint'),
                        'activity' => $activityId,
                        'level' => (int) $launchLevel['level'],
                        'target' => (float) $launchLevel['targetScore'],
                    ]),
                ];
            })
            ->values()
            ->all();
        $isActivitiesView = $viewMode === 'activities';
        $pageTitle = $isActivitiesView ? 'Learning Activities' : 'Learning Lab Overview';
        $pageDescription = $isActivitiesView
            ? 'Choose a focused activity for this interview system, then choose the practice category you want before the drill starts.'
            : 'Review the Learning Lab modules, practice track connections, and saved learning signals before choosing a focused activity.';
        $actionsDescription = $isActivitiesView
            ? 'Choose a drill, return to the overview, or adjust the setup before launching Practice.'
            : 'Use the overview to understand the next move, then open the activity page when you are ready to drill.';
        $actions = $isActivitiesView
            ? [
                ['label' => 'Choose Practice Category', 'href' => $learningActivityLaunch(), 'style' => 'primary'],
                ['label' => 'Open Learning Lab Overview', 'href' => route('learning-lab'), 'style' => 'secondary'],
                ['label' => 'Adjust Session Setup', 'href' => route('session-setup'), 'style' => 'ghost'],
            ]
            : [
                ['label' => 'Open Learning Activities', 'href' => route('learning-lab.activities'), 'style' => 'primary'],
                ['label' => 'Open Practice', 'href' => route('practice'), 'style' => 'secondary'],
                ['label' => 'Adjust Session Setup', 'href' => route('session-setup'), 'style' => 'ghost'],
            ];

        return [
            'eyebrow' => 'Standalone adaptive coaching hub',
            'title' => $pageTitle,
            'description' => $pageDescription,
            'view' => 'pages.learning-lab',
            'viewMode' => $viewMode,
            'overviewHref' => route('learning-lab'),
            'activitiesHref' => route('learning-lab.activities'),
            'gradient' => 'from-amber-500/10 via-white to-brand-500/10 dark:from-amber-500/5 dark:via-gray-900 dark:to-brand-500/5',
            'summaryCards' => [
                [
                    'label' => 'Preferred Track',
                    'value' => $preferredCategoryName,
                    'detail' => 'Current default pulled from Session Setup',
                ],
                [
                    'label' => $isActivitiesView ? 'Recommended Activity' : 'Coach Focus',
                    'value' => $isActivitiesView ? (string) ($recommendedActivity['title'] ?? 'Quick Drill') : $focusLabel,
                    'detail' => $isActivitiesView
                        ? 'Best fit from current setup and saved learning signals'
                        : 'The current coaching emphasis applied before each practice run',
                ],
                [
                    'label' => 'Pacing',
                    'value' => $pacingLabel,
                    'detail' => 'Saved answer timing target for the next launch',
                ],
                [
                    'label' => 'Saved Answers',
                    'value' => (string) $answers->count(),
                    'detail' => $answers->isNotEmpty()
                        ? 'Average recorded score: '.$averageScore
                        : 'No evaluated answers have been saved yet',
                ],
            ],
            'learningModules' => $learningModules,
            'learningActivities' => $learningActivities,
            'practiceTracks' => $categoryCards,
            'secondarySections' => [
                [
                    'title' => 'Recent Learning Signals',
                    'description' => 'These signals come from your saved workspace data and help decide the next drill.',
                    'items' => [
                        [
                            'title' => 'Completed Sessions',
                            'value' => (string) $sessions->count(),
                            'body' => $sessions->isNotEmpty()
                                ? 'Saved sessions are available for comparison across multiple interview rounds.'
                                : 'Start a practice session to begin building learning history.',
                            'tone' => 'brand',
                        ],
                        [
                            'title' => 'Latest Reviewed Track',
                            'value' => (string) ($latestAnswer['categoryName'] ?? 'No answers yet'),
                            'body' => $latestAnswer
                                ? 'Most recent answer mode: '.(string) ($latestAnswer['inputMode'] ?? 'Text')
                                : 'No evaluated answer has been stored yet.',
                            'tone' => 'blue',
                        ],
                        [
                            'title' => 'Average Saved Score',
                            'value' => $averageScore,
                            'body' => $answers->isNotEmpty()
                                ? 'Use this as a baseline when choosing whether the next drill should focus on structure, delivery, or confidence.'
                                : 'Scores will appear here after your first evaluated answer.',
                            'tone' => 'success',
                        ],
                    ],
                ],
                [
                    'title' => 'Best Next Move',
                    'description' => 'A quick recommendation based on the currently saved defaults.',
                    'items' => [
                        [
                            'title' => 'Recommended activity',
                            'value' => (string) ($recommendedActivity['title'] ?? 'Quick Drill'),
                            'body' => $recommendedReason,
                            'list' => [
                                'Preferred track: '.$preferredCategoryName,
                                'Coach focus: '.$focusLabel,
                                'Pacing: '.$pacingLabel,
                                'Response preference: '.$responseLabel,
                            ],
                            'tone' => 'brand',
                        ],
                        [
                            'title' => 'Before you begin',
                            'value' => 'Optional',
                            'body' => 'If delivery or presence is the priority, visit Camera Readiness before opening the next Practice run.',
                            'tone' => 'warning',
                        ],
                    ],
                ],
            ],
            'actionsDescription' => $actionsDescription,
            'actions' => $actions,
        ];
    }

    protected function voicePracticePage(array $context): array
    {
        $setup = $context['setup'];
        $answers = $context['answers'];
        $voiceAnswers = $answers->filter(fn (array $answer) => ($answer['inputMode'] ?? '') === 'Voice');
        $hybridAnswers = $answers->filter(fn (array $answer) => ($answer['inputMode'] ?? '') === 'Hybrid');
        $textAnswers = $answers->filter(fn (array $answer) => ($answer['inputMode'] ?? '') === 'Text');
        $voiceSessions = $context['sessions']->filter(function (array $session) {
            return collect($session['answers'] ?? [])->contains(function (array $answer) {
                return in_array((string) ($answer['inputMode'] ?? ''), ['Voice', 'Hybrid'], true);
            });
        });

        return [
            'eyebrow' => 'Speech-to-text and hybrid practice flow',
            'title' => 'Voice Practice',
            'description' => 'Review how voice-first and hybrid answering fit into the current workspace defaults, what has been saved so far, and how the browser-based flow works during practice.',
            'gradient' => 'from-emerald-500/10 via-white to-blue-light-500/10 dark:from-emerald-500/5 dark:via-gray-900 dark:to-blue-light-500/5',
            'summaryCards' => [
                [
                    'label' => 'Preferred Response',
                    'value' => $this->responsePreferenceLabel((string) ($setup['voiceMode'] ?? 'text')),
                    'detail' => 'Current default pulled from Session Setup',
                ],
                [
                    'label' => 'Voice Answers',
                    'value' => (string) ($voiceAnswers->count() + $hybridAnswers->count()),
                    'detail' => 'Saved answers that used voice-only or hybrid input',
                ],
                [
                    'label' => 'Voice Sessions',
                    'value' => (string) $voiceSessions->count(),
                    'detail' => 'Saved sessions containing at least one voice-enabled answer',
                ],
                [
                    'label' => 'Best Browser Fit',
                    'value' => 'Chrome / Edge',
                    'detail' => 'Speech recognition works best in Chromium-based browsers',
                ],
            ],
            'primarySections' => [
                [
                    'title' => 'Response Modes',
                    'description' => 'The workspace supports three answer modes, and the saved default can be changed at any time.',
                    'columns' => 'md:grid-cols-3',
                    'items' => $context['responsePreferences']->map(function (string $mode) use ($setup) {
                        $label = $this->responsePreferenceLabel($mode);
                        $isSelected = ($setup['voiceMode'] ?? 'text') === $mode;

                        return [
                            'title' => $label,
                            'value' => $isSelected ? 'Selected' : 'Available',
                            'body' => match ($mode) {
                                'voice' => 'Start with speech input and keep answers hands-free when the browser supports recognition.',
                                'hybrid' => 'Switch between voice and typed edits within the same answer without losing context.',
                                default => 'Lead with typed answers while keeping voice input available for quick practice rounds.',
                            },
                            'list' => match ($mode) {
                                'voice' => [
                                    'Good for verbal fluency and fast repetitions',
                                    'Falls back cleanly if speech recognition is unavailable',
                                ],
                                'hybrid' => [
                                    'Lets you dictate ideas, then tighten them before submission',
                                    'Useful when you want natural speaking plus final editing control',
                                ],
                                default => [
                                    'Keeps the session predictable and easy to review',
                                    'Voice input can still be used later inside the workspace',
                                ],
                            },
                            'tone' => $isSelected ? 'brand' : match ($mode) {
                                'voice' => 'blue',
                                'hybrid' => 'success',
                                default => 'warning',
                            },
                        ];
                    })->values()->all(),
                ],
                [
                    'title' => 'Voice Workflow',
                    'description' => 'The practice modal already exposes the controls needed for a full speech-based loop.',
                    'columns' => 'md:grid-cols-2',
                    'items' => [
                        [
                            'title' => 'Start voice input',
                            'value' => 'Step 1',
                            'body' => 'Begin recording inside Practice when you are ready to answer aloud.',
                            'list' => [
                                'Best used on localhost, HTTPS, or supported desktop browsers',
                                'Pairs well with Voice First and Hybrid defaults',
                            ],
                            'tone' => 'brand',
                        ],
                        [
                            'title' => 'Speak naturally',
                            'value' => 'Step 2',
                            'body' => 'Speech-to-text captures the answer while the timer and question context stay visible.',
                            'list' => [
                                'The active question remains visible while you answer',
                                'You can still stop and switch to manual edits if needed',
                            ],
                            'tone' => 'blue',
                        ],
                        [
                            'title' => 'Stop and edit',
                            'value' => 'Step 3',
                            'body' => 'Pause recognition whenever you want to clean up the draft before submission.',
                            'list' => [
                                'Hybrid mode is designed for quick transitions between speaking and typing',
                                'Text corrections stay inside the same answer box',
                            ],
                            'tone' => 'success',
                        ],
                        [
                            'title' => 'Submit the answer',
                            'value' => 'Step 4',
                            'body' => 'The saved answer records its input mode together with score breakdowns and feedback.',
                            'list' => [
                                'Saved answers retain Text, Voice, or Hybrid mode labels',
                                'Feedback and progress pages can then review those entries later',
                            ],
                            'tone' => 'warning',
                        ],
                    ],
                ],
            ],
            'secondarySections' => [
                [
                    'title' => 'Saved Voice Activity',
                    'description' => 'These counts are pulled from the current saved session history.',
                    'items' => [
                        [
                            'title' => 'Text answers',
                            'value' => (string) $textAnswers->count(),
                            'body' => 'Answers submitted without any saved voice activity.',
                            'tone' => 'brand',
                        ],
                        [
                            'title' => 'Voice answers',
                            'value' => (string) $voiceAnswers->count(),
                            'body' => 'Answers completed fully through voice input.',
                            'tone' => 'blue',
                        ],
                        [
                            'title' => 'Hybrid answers',
                            'value' => (string) $hybridAnswers->count(),
                            'body' => 'Answers that combined voice capture and manual edits.',
                            'tone' => 'success',
                        ],
                        [
                            'title' => 'Latest saved mode',
                            'value' => (string) ($answers->first()['inputMode'] ?? 'No answers yet'),
                            'body' => 'The newest saved answer determines what shows here.',
                            'tone' => 'warning',
                        ],
                    ],
                ],
                [
                    'title' => 'Setup Snapshot',
                    'description' => 'Voice practice still depends on the same session defaults as the rest of the workspace.',
                    'items' => [
                        [
                            'title' => 'Preferred Category',
                            'value' => (string) ($context['preferredCategory']['name'] ?? 'Interview Practice'),
                            'body' => 'The next voice-enabled session will open on this category first.',
                            'tone' => 'brand',
                        ],
                        [
                            'title' => 'Question Count',
                            'value' => $this->questionCountLabel($context['questionCountOptions'], (int) ($setup['questionCount'] ?? 3)),
                            'body' => 'Voice and text sessions share the same saved interview length.',
                            'tone' => 'blue',
                        ],
                        [
                            'title' => 'Coach Focus',
                            'value' => $this->focusModeLabel($context['focusModes'], (int) ($setup['focusModeIndex'] ?? 0)),
                            'body' => 'The coaching lens still shapes the feedback you receive after a voice answer.',
                            'tone' => 'success',
                        ],
                    ],
                ],
            ],
            'actionsDescription' => 'Use these existing pages to switch defaults or jump directly into a voice-ready session.',
            'actions' => [
                ['label' => 'Open Practice', 'href' => route('practice'), 'style' => 'primary'],
                ['label' => 'Open Session Setup', 'href' => route('session-setup'), 'style' => 'secondary'],
                ['label' => 'Open Session Review', 'href' => route('session-review'), 'style' => 'ghost'],
            ],
        ];
    }

    protected function cameraReadinessPage(array $context): array
    {
        $appUrl = $context['appUrl'];
        $isSecureMediaOrigin = Str::startsWith($appUrl, 'https://')
            || Str::contains($appUrl, ['localhost', '127.0.0.1']);

        return [
            'eyebrow' => 'Camera preview and interviewer guidance',
            'title' => 'Camera Readiness',
            'description' => 'Review the browser requirements and built-in controls that support the AI interviewer, face visibility checks, and spoken question playback in Practice.',
            'gradient' => 'from-sky-500/10 via-white to-emerald-500/10 dark:from-sky-500/5 dark:via-gray-900 dark:to-emerald-500/5',
            'summaryCards' => [
                [
                    'label' => 'Camera Preview',
                    'value' => 'Built In',
                    'detail' => 'The practice workspace already includes start and stop camera controls',
                ],
                [
                    'label' => 'Face Status',
                    'value' => 'Browser Based',
                    'detail' => 'Face visibility is shown inside the AI interviewer panel',
                ],
                [
                    'label' => 'Question Voice',
                    'value' => 'Speech Output',
                    'detail' => 'The interviewer can read the active question aloud using browser speech synthesis',
                ],
                [
                    'label' => 'Media Origin',
                    'value' => $isSecureMediaOrigin ? 'Ready' : 'Needs secure origin',
                    'detail' => $isSecureMediaOrigin
                        ? 'Current app URL is suitable for camera and microphone features'
                        : 'Phone browsers may block media access on plain LAN HTTP',
                ],
            ],
            'primarySections' => [
                [
                    'title' => 'Interviewer Controls',
                    'description' => 'The Practice modal already exposes the controls needed to prepare the camera-assisted flow.',
                    'columns' => 'md:grid-cols-3',
                    'items' => [
                        [
                            'title' => 'Start camera',
                            'value' => 'Control',
                            'body' => 'Open the video preview and allow the face visibility status to update while you answer.',
                            'list' => [
                                'Shown in the AI interviewer panel',
                                'Pairs with face status and camera state indicators',
                            ],
                            'tone' => 'brand',
                        ],
                        [
                            'title' => 'Ask current question',
                            'value' => 'Control',
                            'body' => 'Replay the active interview prompt aloud when you want the interviewer to repeat it.',
                            'list' => [
                                'Uses browser speech synthesis voices',
                                'Works alongside the generated question set in Practice',
                            ],
                            'tone' => 'blue',
                        ],
                        [
                            'title' => 'Stop camera',
                            'value' => 'Control',
                            'body' => 'Close the live preview any time you want to pause the interviewer flow.',
                            'list' => [
                                'Useful when switching devices or troubleshooting permissions',
                                'The rest of the practice session can continue without the camera',
                            ],
                            'tone' => 'success',
                        ],
                    ],
                ],
                [
                    'title' => 'Readiness Checklist',
                    'description' => 'The most important conditions to verify before relying on camera-assisted practice.',
                    'columns' => 'md:grid-cols-2',
                    'items' => [
                        [
                            'title' => 'Allow camera permission',
                            'value' => 'Required',
                            'body' => 'The face preview and camera state indicators only work after the browser grants camera access.',
                            'list' => [
                                'Grant permission when Practice requests it',
                                'Retry on the same browser tab if access was blocked the first time',
                            ],
                            'tone' => 'brand',
                        ],
                        [
                            'title' => 'Use a secure origin',
                            'value' => $isSecureMediaOrigin ? 'Ready' : 'Check APP_URL',
                            'body' => 'Mobile browsers are much stricter than desktop browsers about camera and microphone access.',
                            'list' => [
                                'Best options: localhost or HTTPS',
                                'Plain LAN HTTP can still load the page but media features may stay blocked',
                            ],
                            'tone' => $isSecureMediaOrigin ? 'success' : 'warning',
                        ],
                        [
                            'title' => 'Keep the question set ready',
                            'value' => 'Recommended',
                            'body' => 'The interviewer experience feels best after the workspace already has an active generated question set.',
                            'list' => [
                                'Choose a category and finish the field builder first',
                                'Then use Ask Current Question to hear the active prompt aloud',
                            ],
                            'tone' => 'blue',
                        ],
                        [
                            'title' => 'Combine with voice input',
                            'value' => $this->responsePreferenceLabel((string) ($context['setup']['voiceMode'] ?? 'text')),
                            'body' => 'Camera preview, speech output, and voice input can all be used together in a single session.',
                            'list' => [
                                'Voice practice still works even if the camera stays off',
                                'Hybrid mode is often the easiest way to test the full interviewer flow',
                            ],
                            'tone' => 'warning',
                        ],
                    ],
                ],
            ],
            'secondarySections' => [
                [
                    'title' => 'Mobile Device Setup',
                    'description' => 'Additional steps needed when accessing the app from phones or tablets.',
                    'items' => [
                        [
                            'title' => 'Use LAN IP address',
                            'value' => 'Required for mobile',
                            'body' => 'Change APP_URL in .env to your computer\'s LAN IP (e.g., 192.168.1.100) instead of localhost.',
                            'list' => [
                                'Find your LAN IP using ipconfig (Windows) or ifconfig (Mac/Linux)',
                                'Use HTTP (not HTTPS) for LAN access as mobile browsers may block camera on self-signed HTTPS',
                                'Restart the Laravel server after changing APP_URL',
                            ],
                            'tone' => 'brand',
                        ],
                        [
                            'title' => 'Grant permissions',
                            'value' => 'When prompted',
                            'body' => 'Mobile browsers require explicit permission grants for camera and microphone access.',
                            'list' => [
                                'Allow camera permission when Practice requests it',
                                'Allow microphone permission for voice input features',
                                'Permissions are remembered per browser, not per site',
                            ],
                            'tone' => 'blue',
                        ],
                        [
                            'title' => 'Use supported browsers',
                            'value' => 'Chrome or Edge recommended',
                            'body' => 'Safari on iOS has limited speech recognition support compared to Chrome/Edge.',
                            'list' => [
                                'Chrome or Edge provide the best camera and voice experience',
                                'Firefox works but has fewer voice features',
                                'Avoid Internet Explorer or older browsers',
                            ],
                            'tone' => 'success',
                        ],
                    ],
                ],
                [
                    'title' => 'Current Launch Context',
                    'description' => 'These saved defaults shape the next camera-assisted session.',
                    'items' => [
                        [
                            'title' => 'Preferred Category',
                            'value' => (string) ($context['preferredCategory']['name'] ?? 'Interview Practice'),
                            'body' => 'The interviewer workflow starts from this category by default.',
                            'tone' => 'brand',
                        ],
                        [
                            'title' => 'Question Count',
                            'value' => $this->questionCountLabel($context['questionCountOptions'], (int) ($context['setup']['questionCount'] ?? 3)),
                            'body' => 'The next session length is already set before the modal opens.',
                            'tone' => 'blue',
                        ],
                        [
                            'title' => 'Provider Route',
                            'value' => $this->providerLabel($context['providers'], $context['defaultProviderId']),
                            'body' => 'Question generation and answer review can still use the current provider route.',
                            'tone' => 'success',
                        ],
                    ],
                ],
                [
                    'title' => 'Environment Snapshot',
                    'description' => 'The app URL affects whether desktop and mobile browsers can use camera and microphone features.',
                    'items' => [
                        [
                            'title' => 'APP_URL',
                            'value' => $appUrl,
                            'body' => 'This is the current app URL the feature pages can see from configuration.',
                            'tone' => 'brand',
                        ],
                        [
                            'title' => 'Media status',
                            'value' => $isSecureMediaOrigin ? 'Likely allowed' : 'May be blocked',
                            'body' => $isSecureMediaOrigin
                                ? 'Localhost or HTTPS usually keeps camera and microphone features available.'
                                : 'Switch to localhost or HTTPS when you want the broadest browser support.',
                            'tone' => $isSecureMediaOrigin ? 'success' : 'warning',
                        ],
                    ],
                ],
            ],
            'actionsDescription' => 'The camera and interviewer controls already live inside Practice. Use Session Setup when you want to adjust the next run first.',
            'actions' => [
                ['label' => 'Open Practice', 'href' => route('practice'), 'style' => 'primary'],
                ['label' => 'Adjust Session Setup', 'href' => route('session-setup'), 'style' => 'ghost'],
            ],
        ];
    }

    protected function mobileLanPage(array $context): array
    {
        $appUrl = $context['appUrl'];
        $viteDevServerHost = $context['viteDevServerHost'];
        $usesLanAddress = ! Str::contains($appUrl, ['localhost', '127.0.0.1']);
        $mediaSafe = Str::startsWith($appUrl, 'https://') || Str::contains($appUrl, ['localhost', '127.0.0.1']);

        return [
            'eyebrow' => 'Phone testing and shared-network access',
            'title' => 'Mobile LAN',
            'description' => 'Check the current LAN-related environment values, review the admin phone testing workflow, and see which admin pages work best over local network access.',
            'gradient' => 'from-blue-light-500/10 via-white to-orange-500/10 dark:from-blue-light-500/5 dark:via-gray-900 dark:to-orange-500/5',
            'summaryCards' => [
                [
                    'label' => 'APP_URL',
                    'value' => $appUrl,
                    'detail' => 'Current application URL used by the Laravel app',
                ],
                [
                    'label' => 'Vite Host',
                    'value' => $viteDevServerHost !== '' ? $viteDevServerHost : 'Not set',
                    'detail' => 'Set this to your LAN IP when you want phone access during development',
                ],
                [
                    'label' => 'LAN Profile',
                    'value' => $usesLanAddress ? 'Network aware' : 'Localhost only',
                    'detail' => $usesLanAddress
                        ? 'The current APP_URL is not pinned to localhost'
                        : 'Switch APP_URL and VITE_DEV_SERVER_HOST to a LAN IP to test on a phone',
                ],
                [
                    'label' => 'Media Caveat',
                    'value' => $mediaSafe ? 'Best case' : 'Limited on phones',
                    'detail' => $mediaSafe
                        ? 'Localhost or HTTPS is usually best for camera, mic, and speech APIs'
                        : 'Camera and microphone features may stay blocked on plain LAN HTTP',
                ],
            ],
            'primarySections' => [
                [
                    'title' => 'LAN Setup Flow',
                    'description' => 'The project already documents a clear development flow for opening the app on a phone over the same network.',
                    'columns' => 'md:grid-cols-2',
                    'items' => [
                        [
                            'title' => 'Set APP_URL',
                            'value' => 'Step 1',
                            'body' => 'Point APP_URL to the computer’s LAN IP instead of localhost when you want phone access.',
                            'list' => [
                                'Current APP_URL: '.$appUrl,
                                'Example format: http://YOUR-LAN-IP:8000',
                            ],
                            'tone' => 'brand',
                        ],
                        [
                            'title' => 'Set VITE_DEV_SERVER_HOST',
                            'value' => 'Step 2',
                            'body' => 'Match the Vite dev server host to the same LAN IP so frontend assets load from the phone.',
                            'list' => [
                                'Current value: '.($viteDevServerHost !== '' ? $viteDevServerHost : 'Not set'),
                                'This matters when running the dev server instead of a production build',
                            ],
                            'tone' => 'blue',
                        ],
                        [
                            'title' => 'Run the LAN command',
                            'value' => 'Step 3',
                            'body' => 'Use the development command documented in the README when starting a LAN-accessible session.',
                            'list' => [
                                'Command: composer run dev:lan',
                                'Then open the app from a phone on the same Wi-Fi network',
                            ],
                            'tone' => 'success',
                        ],
                        [
                            'title' => 'Test feature coverage',
                            'value' => 'Step 4',
                            'body' => 'Admin dashboard, user management, API management, monitoring records, and the responsive sidebar should all load on a phone once the LAN host is correct.',
                            'list' => [
                                'Best first checks: Admin Dashboard, User Management, API Management, and Monitoring Records',
                                'Camera and microphone features still depend on browser security rules',
                            ],
                            'tone' => 'warning',
                        ],
                    ],
                ],
                [
                    'title' => 'Admin Pages That Work Best On Phone',
                    'description' => 'The responsive UI is already in place, but not every browser capability behaves the same over LAN HTTP.',
                    'columns' => 'md:grid-cols-2',
                    'items' => [
                        [
                            'title' => 'Admin dashboard and monitoring',
                            'value' => 'Good fit',
                            'body' => 'Admin dashboard and monitoring records remain strong candidates for phone testing.',
                            'list' => [
                                'These pages are primarily data and layout driven',
                                'They help confirm the admin sidebar and dark mode work well on mobile',
                            ],
                            'tone' => 'brand',
                        ],
                        [
                            'title' => 'User management',
                            'value' => 'Good fit',
                            'body' => 'User Management works well for checking account lists, profile fields, role controls, and protected admin forms from a phone.',
                            'list' => [
                                'Review add-user and edit-user forms on mobile',
                                'Confirm role and delete controls stay readable',
                            ],
                            'tone' => 'blue',
                        ],
                        [
                            'title' => 'API management',
                            'value' => 'Mostly ready',
                            'body' => 'API Management can be checked on phone for provider visibility, status cards, and configuration notes.',
                            'list' => [
                                'Provider status checks still depend on backend availability',
                                'Configuration cards should remain readable in narrow screens',
                            ],
                            'tone' => 'success',
                        ],
                        [
                            'title' => 'Camera and microphone',
                            'value' => $mediaSafe ? 'Best case' : 'Needs caution',
                            'body' => 'Plain LAN HTTP can block camera, microphone, and speech APIs on many mobile browsers if an admin needs to test media-heavy user flows separately.',
                            'list' => [
                                'HTTPS or localhost is still the safest path for media-heavy testing',
                                'If phone media fails, confirm the same feature works on desktop first',
                            ],
                            'tone' => $mediaSafe ? 'success' : 'warning',
                        ],
                    ],
                ],
            ],
            'secondarySections' => [
                [
                    'title' => 'Current Admin Checks',
                    'description' => 'These admin routes are worth checking after the app loads on a phone.',
                    'items' => [
                        [
                            'title' => 'Admin Dashboard',
                            'value' => 'Overview',
                            'body' => 'A quick way to confirm the admin shell, summary cards, and quick links load correctly on mobile.',
                            'tone' => 'brand',
                        ],
                        [
                            'title' => 'User Management',
                            'value' => 'Accounts',
                            'body' => 'Useful for checking account forms, role controls, and protected admin actions on a smaller viewport.',
                            'tone' => 'blue',
                        ],
                        [
                            'title' => 'API Management',
                            'value' => 'Providers',
                            'body' => 'Helps confirm provider configuration visibility stays readable on phone screens.',
                            'tone' => 'success',
                        ],
                    ],
                ],
                [
                    'title' => 'Recommended Phone Checks',
                    'description' => 'A quick sequence for validating the most important mobile-facing parts of the app.',
                    'items' => [
                        [
                            'title' => 'Sidebar navigation',
                            'value' => 'Admin routes',
                            'body' => 'Confirm the admin sidebar still feels usable and readable on a smaller viewport.',
                            'tone' => 'brand',
                        ],
                        [
                            'title' => 'Theme switching',
                            'value' => 'Light + dark',
                            'body' => 'Toggle themes on mobile to verify the dashboard and feature pages remain legible.',
                            'tone' => 'blue',
                        ],
                        [
                            'title' => 'Admin workflow pages',
                            'value' => 'Core controls',
                            'body' => 'Open User Management, API Management, and Monitoring Records before checking any media-sensitive user flows.',
                            'tone' => 'success',
                        ],
                    ],
                ],
            ],
            'actionsDescription' => 'Use these admin pages as the fastest checks once the app is visible from a phone.',
            'actions' => [
                ['label' => 'Open Admin Dashboard', 'href' => route('admin.dashboard'), 'style' => 'primary'],
                ['label' => 'Open API Management', 'href' => route('admin.apis'), 'style' => 'secondary'],
                ['label' => 'Open Monitoring Records', 'href' => route('admin.monitoring'), 'style' => 'ghost'],
            ],
        ];
    }

    protected function questionCountLabel(Collection $options, int $value): string
    {
        return (string) ($options->first(fn (int $count) => $count === $value) ?? $value).' questions';
    }

    protected function focusModeLabel(Collection $focusModes, int $index): string
    {
        return (string) ($focusModes->get($index)['label'] ?? $focusModes->first()['label'] ?? 'Balanced Coach');
    }

    protected function pacingModeLabel(Collection $pacingModes, int $index): string
    {
        $mode = $pacingModes->get($index) ?? $pacingModes->first() ?? ['label' => 'Standard', 'seconds' => 180];

        return sprintf('%s (%d sec)', (string) ($mode['label'] ?? 'Standard'), (int) ($mode['seconds'] ?? 180));
    }

    protected function responsePreferenceLabel(string $value): string
    {
        return match ($value) {
            'voice' => 'Voice First',
            'hybrid' => 'Hybrid',
            default => 'Text First',
        };
    }

    protected function providerLabel(Collection $providers, string $providerId): string
    {
        return (string) ($providers->firstWhere('id', $providerId)['label'] ?? Str::headline(str_replace(['-', '_'], ' ', $providerId)));
    }

    protected function fieldBlueprints(): array
    {
        return [
            'job' => [
                'fieldLabel' => 'Target role',
                'placeholder' => 'Customer Service Representative',
                'tip' => 'Mention the role, level, and strengths you want the chatbot to emphasize before the interview begins.',
                'suggestions' => [
                    'Customer Service Representative',
                    'Administrative Assistant',
                    'Virtual Assistant',
                    'Sales Associate',
                ],
            ],
            'scholarship' => [
                'fieldLabel' => 'Target study field',
                'placeholder' => 'Nursing',
                'tip' => 'Tie the field to academic goals, financial need, service, and long-term contribution.',
                'suggestions' => [
                    'Nursing',
                    'Education',
                    'Information Technology',
                    'Accountancy',
                ],
            ],
            'admission' => [
                'fieldLabel' => 'Target course',
                'placeholder' => 'BS Information Technology',
                'tip' => 'Use the field builder to sharpen how you explain course fit, readiness, and future direction.',
                'suggestions' => [
                    'BS Information Technology',
                    'BS Nursing',
                    'BS Accountancy',
                    'BS Education',
                ],
            ],
            'it' => [
                'fieldLabel' => 'Target IT role',
                'placeholder' => 'Junior Laravel Developer',
                'tip' => 'Highlight the specific IT role, stack, or specialization you want the next question set to reflect.',
                'suggestions' => [
                    'Junior Web Developer',
                    'QA Tester',
                    'Technical Support Specialist',
                    'Junior Data Analyst',
                ],
            ],
        ];
    }
}
