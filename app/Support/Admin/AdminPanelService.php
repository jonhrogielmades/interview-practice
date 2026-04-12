<?php

namespace App\Support\Admin;

use App\Helpers\InterviewChatbotService;
use App\Models\InterviewSession;
use App\Models\InterviewSessionAnswer;
use App\Models\User;
use App\Support\InterviewPracticeCatalog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminPanelService
{
    public function overview(): array
    {
        $users = $this->orderedUsers();

        return [
            'summaryCards' => $this->summaryCards($users),
            'systemSignals' => $this->systemSignals($users),
            'topCategories' => $this->topCategories(),
            'recentUsers' => $this->recentUsers($users),
            'primaryAdminEmail' => config('admin.email'),
            'quickLinks' => [
                [
                    'title' => 'User Management',
                    'body' => 'Review every account, promote trusted members to admin, and keep standard users separate.',
                    'href' => route('admin.users'),
                    'tone' => 'warning',
                ],
                [
                    'title' => 'API Management',
                    'body' => 'Inspect configured AI providers, key coverage, routing priority, and protected system access.',
                    'href' => route('admin.apis'),
                    'tone' => 'brand',
                ],
                [
                    'title' => 'Question Bank & Announcements',
                    'body' => 'Review practice categories, starter question banks, and manuscript-aligned announcement templates.',
                    'href' => route('admin.content'),
                    'tone' => 'warning',
                ],
                [
                    'title' => 'Monitoring Records',
                    'body' => 'Track saved sessions, feedback records, notification totals, and report-oriented monitoring signals.',
                    'href' => route('admin.monitoring'),
                    'tone' => 'brand',
                ],
                [
                    'title' => 'Mobile LAN',
                    'body' => 'Check LAN access values and phone-ready admin pages from the protected admin area.',
                    'href' => route('admin.mobile-lan'),
                    'tone' => 'brand',
                ],
            ],
        ];
    }

    public function contentManagement(): array
    {
        $questionBank = collect(InterviewPracticeCatalog::practiceQuestionBank());
        $announcements = collect(InterviewPracticeCatalog::defaultAnnouncementTemplates());
        $adminAreas = collect(InterviewPracticeCatalog::manuscriptAdminAreas());

        return [
            'summaryCards' => [
                [
                    'label' => 'Interview Categories',
                    'value' => (string) $questionBank->count(),
                    'detail' => 'Managed categories in the current practice catalog',
                    'tone' => 'brand',
                ],
                [
                    'label' => 'Starter Questions',
                    'value' => (string) $questionBank->sum(fn (array $category) => count($category['questions'] ?? [])),
                    'detail' => 'Seed prompts available before AI-assisted generation',
                    'tone' => 'blue',
                ],
                [
                    'label' => 'Announcement Templates',
                    'value' => (string) $announcements->count(),
                    'detail' => 'Default reminders and notices aligned to the manuscript scope',
                    'tone' => 'warning',
                ],
                [
                    'label' => 'Admin Coverage Areas',
                    'value' => (string) $adminAreas->count(),
                    'detail' => 'User, content, announcement, and monitoring responsibilities',
                    'tone' => 'success',
                ],
            ],
            'questionBanks' => $questionBank->map(function (array $category, string $key) {
                return [
                    'id' => $key,
                    'name' => (string) ($category['name'] ?? 'Interview Category'),
                    'description' => (string) ($category['description'] ?? 'Interview practice category'),
                    'questionCount' => count($category['questions'] ?? []),
                    'questions' => collect($category['questions'] ?? [])->take(4)->values()->all(),
                    'quickPrompts' => collect($category['quickPrompts'] ?? [])->take(3)->values()->all(),
                ];
            })->values()->all(),
            'announcements' => $announcements->values()->all(),
            'adminAreas' => $adminAreas->values()->all(),
        ];
    }

    public function monitoringRecords(): array
    {
        $sessionCount = InterviewSession::query()->count();
        $completedSessionCount = InterviewSession::query()->where('completed', true)->count();
        $answerCount = InterviewSessionAnswer::query()->count();
        $notificationCount = (int) DB::table('notifications')->count();
        $averageScore = (float) (InterviewSessionAnswer::query()->avg('average_score')
            ?? InterviewSession::query()->avg('average_score')
            ?? 0);

        $recentSessions = InterviewSession::query()
            ->withCount('answers')
            ->orderByDesc('saved_at')
            ->orderByDesc('id')
            ->limit(6)
            ->get()
            ->map(function (InterviewSession $session) {
                $criteria = (array) ($session->criteria_averages ?? []);

                return [
                    'category' => $session->category_name ?: 'Unknown Category',
                    'savedAt' => optional($session->saved_at)->format('M j, Y g:i A') ?? 'Not saved yet',
                    'averageScore' => number_format((float) $session->average_score, 1),
                    'capstoneOverall' => number_format((float) ($criteria['manuscriptOverall'] ?? 0), 2),
                    'answeredCount' => $session->answers_count,
                    'questionCount' => (int) $session->question_count,
                    'status' => $session->completed ? 'Completed' : 'Partial',
                ];
            })
            ->values()
            ->all();

        return [
            'summaryCards' => [
                [
                    'label' => 'Saved Sessions',
                    'value' => (string) $sessionCount,
                    'detail' => $completedSessionCount.' completed practice session'.($completedSessionCount === 1 ? '' : 's'),
                    'tone' => 'brand',
                ],
                [
                    'label' => 'Saved Answers',
                    'value' => (string) $answerCount,
                    'detail' => sprintf('%.1f / 10 average runtime score', $averageScore),
                    'tone' => 'blue',
                ],
                [
                    'label' => 'Notifications Logged',
                    'value' => (string) $notificationCount,
                    'detail' => 'Stored reminder and feedback-related notices',
                    'tone' => 'warning',
                ],
                [
                    'label' => 'Report Exports',
                    'value' => 'JSON + CSV',
                    'detail' => 'Progress page already supports downloadable report artifacts',
                    'tone' => 'success',
                ],
            ],
            'signals' => [
                ['label' => 'Session completion rate', 'value' => $sessionCount > 0 ? number_format(($completedSessionCount / $sessionCount) * 100, 1).'%' : '0.0%'],
                ['label' => 'Latest score average', 'value' => number_format($averageScore, 1).' / 10'],
                ['label' => 'Stored notifications', 'value' => (string) $notificationCount],
            ],
            'recentSessions' => $recentSessions,
            'reportNotes' => [
                'Use Progress to export JSON and CSV artifacts for capstone documentation.',
                'Session Review and Feedback Center remain the fastest places to inspect saved answer-level details.',
                'Notifications and admin monitoring records help document activity beyond the live practice modal.',
            ],
        ];
    }

    public function userManagement(): array
    {
        $users = $this->orderedUsers();
        $adminCount = $users->where('account_role', User::ROLE_ADMIN)->count();
        $verifiedUsers = $users->whereNotNull('email_verified_at')->count();
        $googleLinkedUsers = $users->whereNotNull('google_id')->count();

        return [
            'summaryCards' => [
                [
                    'label' => 'Registered Users',
                    'value' => (string) $users->count(),
                    'detail' => $users->count() === 1 ? '1 account in the system' : $users->count().' accounts in the system',
                    'tone' => 'brand',
                ],
                [
                    'label' => 'Admin Accounts',
                    'value' => (string) $adminCount,
                    'detail' => $adminCount === 1 ? '1 privileged account active' : $adminCount.' privileged accounts active',
                    'tone' => 'warning',
                ],
                [
                    'label' => 'Verified Users',
                    'value' => (string) $verifiedUsers,
                    'detail' => max($users->count() - $verifiedUsers, 0).' waiting for verification',
                    'tone' => 'success',
                ],
                [
                    'label' => 'Google Linked',
                    'value' => (string) $googleLinkedUsers,
                    'detail' => 'Accounts connected with Google sign-in',
                    'tone' => 'blue',
                ],
            ],
            'users' => $this->mappedUsers($users, $adminCount),
            'primaryAdminEmail' => config('admin.email'),
        ];
    }

    public function apiManagement(InterviewChatbotService $chatbot): array
    {
        $bootstrap = $chatbot->frontendBootstrap();
        $providers = collect($bootstrap['providers'] ?? []);
        $remoteProviders = $providers
            ->filter(fn (array $provider) => ($provider['type'] ?? null) === 'remote')
            ->values();
        $configuredRemoteProviders = $remoteProviders
            ->filter(fn (array $provider) => (bool) ($provider['configured'] ?? false))
            ->values();
        $priorityIds = collect(explode(',', (string) config('services.interview_chatbot.provider_priority', '')))
            ->map(fn (string $providerId) => trim($providerId))
            ->filter()
            ->values();
        $priorityLabels = $priorityIds
            ->map(fn (string $providerId) => $this->providerLabel($providers, $providerId))
            ->values();

        $providerCards = $providers
            ->filter(fn (array $provider) => in_array($provider['id'] ?? null, [
                'auto',
                'gemini',
                'groq',
                'openrouter',
                'claude',
                'wisdomgate',
                'cohere',
                'local',
            ], true))
            ->map(function (array $provider) {
                $providerId = (string) ($provider['id'] ?? 'unknown');
                $type = (string) ($provider['type'] ?? 'local');
                $configured = (bool) ($provider['configured'] ?? false);

                return [
                    'id' => $providerId,
                    'label' => (string) ($provider['label'] ?? Str::headline($providerId)),
                    'description' => (string) ($provider['description'] ?? 'System provider'),
                    'typeLabel' => Str::headline($type),
                    'model' => (string) ($provider['model'] ?? '') ?: 'Dynamic',
                    'configured' => $configured,
                    'stateLabel' => match ($type) {
                        'router' => 'Routing enabled',
                        'remote' => $configured ? 'Configured' : 'Needs key',
                        default => 'Always available',
                    },
                    'stateTone' => match ($type) {
                        'router' => 'brand',
                        'remote' => $configured ? 'success' : 'warning',
                        default => 'blue',
                    },
                    'note' => match ($type) {
                        'router' => 'Auto routing uses the configured provider priority before local fallback.',
                        'remote' => $configured
                            ? 'Ready for live checks and runtime selection.'
                            : 'Add the matching environment key to enable this provider.',
                        default => 'Built-in fallback that keeps coaching available without external APIs.',
                    },
                    'envKey' => $this->providerEnvKey($providerId),
                ];
            })
            ->values()
            ->all();

        $keyStatuses = [
            [
                'name' => 'GEMINI_API_KEY',
                'configured' => (bool) data_get($remoteProviders->firstWhere('id', 'gemini'), 'configured', false),
                'note' => 'Required for Google Gemini API calls.',
            ],
            [
                'name' => 'GROQ_API_KEY',
                'configured' => (bool) data_get($remoteProviders->firstWhere('id', 'groq'), 'configured', false),
                'note' => 'Required for Groq OpenAI-compatible chat completions.',
            ],
            [
                'name' => 'OPENROUTER_API_KEY',
                'configured' => (bool) data_get($remoteProviders->firstWhere('id', 'openrouter'), 'configured', false),
                'note' => 'Required for OpenRouter requests.',
            ],
            [
                'name' => 'ANTHROPIC_API_KEY / CLAUDE_API_KEY',
                'configured' => (bool) data_get($remoteProviders->firstWhere('id', 'claude'), 'configured', false),
                'note' => 'Required for Claude requests through the Anthropic Messages API.',
            ],
            [
                'name' => 'WISDOMGATE_API_KEY / HUGGINGFACE_API_KEY',
                'configured' => (bool) data_get($remoteProviders->firstWhere('id', 'wisdomgate'), 'configured', false),
                'note' => 'Required for Wisdom Gate compatibility routing.',
            ],
            [
                'name' => 'COHERE_API_KEY',
                'configured' => (bool) data_get($remoteProviders->firstWhere('id', 'cohere'), 'configured', false),
                'note' => 'Required for Cohere chat completions.',
            ],
            [
                'name' => 'GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET',
                'configured' => filled(config('services.google.client_id')) && filled(config('services.google.client_secret')),
                'note' => 'Controls Google sign-in for admin and user authentication.',
            ],
        ];

        return [
            'summaryCards' => [
                [
                    'label' => 'Configured APIs',
                    'value' => $configuredRemoteProviders->count().' / '.$remoteProviders->count(),
                    'detail' => $configuredRemoteProviders->isNotEmpty()
                        ? 'Remote providers available for chatbot and practice requests'
                        : 'No remote provider keys are configured yet',
                    'tone' => 'success',
                ],
                [
                    'label' => 'Default Route',
                    'value' => $this->providerLabel($providers, (string) ($bootstrap['defaultProviderId'] ?? 'auto')),
                    'detail' => 'Current backend default provider',
                    'tone' => 'brand',
                ],
                [
                    'label' => 'Priority Chain',
                    'value' => $priorityLabels->first() ?? 'Not set',
                    'detail' => $priorityLabels->isNotEmpty()
                        ? $priorityLabels->implode(' -> ')
                        : 'No provider priority configured',
                    'tone' => 'warning',
                ],
                [
                    'label' => 'Local Fallback',
                    'value' => 'Always Ready',
                    'detail' => 'The local PH coach stays available without remote APIs',
                    'tone' => 'blue',
                ],
            ],
            'providers' => $providerCards,
            'keyStatuses' => $keyStatuses,
            'systemAreas' => [
                [
                    'title' => 'Admin API Management',
                    'value' => 'Admin only',
                    'body' => 'This page centralizes provider configuration visibility and system routing checks.',
                ],
                [
                    'title' => 'Interview Chatbot Runtime',
                    'value' => 'User feature',
                    'body' => 'Users can still chat with the coach, but API management stays inside the admin system area.',
                ],
                [
                    'title' => 'Google Authentication',
                    'value' => filled(config('services.google.client_id')) ? 'Configured' : 'Needs setup',
                    'body' => 'Google sign-in remains part of the system integration surface that admins monitor.',
                ],
            ],
            'liveCheckProviderIds' => collect($providerCards)
                ->pluck('id')
                ->filter(fn (string $id) => in_array($id, ['gemini', 'groq', 'openrouter', 'claude', 'wisdomgate', 'cohere'], true))
                ->values()
                ->all(),
        ];
    }

    protected function orderedUsers(): Collection
    {
        return User::query()
            ->orderByRaw(
                "case when account_role = '".User::ROLE_ADMIN."' then 0 else 1 end"
            )
            ->orderBy('name')
            ->orderBy('email')
            ->get();
    }

    protected function summaryCards(Collection $users): array
    {
        $sessionCount = InterviewSession::query()->count();
        $completedSessionCount = InterviewSession::query()->where('completed', true)->count();
        $answerCount = InterviewSessionAnswer::query()->count();
        $averageScore = (float) (InterviewSessionAnswer::query()->avg('average_score')
            ?? InterviewSession::query()->avg('average_score')
            ?? 0);
        $adminCount = $users->where('account_role', User::ROLE_ADMIN)->count();

        return [
            [
                'label' => 'Registered Users',
                'value' => (string) $users->count(),
                'detail' => $users->count() === 1 ? '1 account in the system' : $users->count().' accounts in the system',
                'tone' => 'brand',
            ],
            [
                'label' => 'Admin Accounts',
                'value' => (string) $adminCount,
                'detail' => $adminCount === 1 ? '1 privileged account active' : $adminCount.' privileged accounts active',
                'tone' => 'warning',
            ],
            [
                'label' => 'Saved Sessions',
                'value' => (string) $sessionCount,
                'detail' => $completedSessionCount.' completed practice session'.($completedSessionCount === 1 ? '' : 's'),
                'tone' => 'success',
            ],
            [
                'label' => 'AI Answers Saved',
                'value' => (string) $answerCount,
                'detail' => sprintf('%.1f / 10 average scored answer', $averageScore),
                'tone' => 'blue',
            ],
        ];
    }

    protected function systemSignals(Collection $users): array
    {
        $verifiedUsers = $users->whereNotNull('email_verified_at')->count();
        $googleLinkedUsers = $users->whereNotNull('google_id')->count();

        return [
            ['label' => 'Verified emails', 'value' => (string) $verifiedUsers],
            ['label' => 'Google linked', 'value' => (string) $googleLinkedUsers],
            ['label' => 'Pending verification', 'value' => (string) max($users->count() - $verifiedUsers, 0)],
        ];
    }

    protected function topCategories(): array
    {
        return InterviewSession::query()
            ->select('category_name', DB::raw('count(*) as total'))
            ->whereNotNull('category_name')
            ->groupBy('category_name')
            ->orderByDesc('total')
            ->limit(4)
            ->get()
            ->map(fn (InterviewSession $session) => [
                'name' => $session->category_name ?: 'Unknown Category',
                'total' => (int) $session->total,
            ])
            ->all();
    }

    protected function recentUsers(Collection $users): array
    {
        return $users
            ->sortByDesc('created_at')
            ->take(5)
            ->map(fn (User $user) => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => Str::headline($user->account_role ?? User::ROLE_USER),
                'joinedAt' => optional($user->created_at)->format('M j, Y g:i A') ?? 'Unknown',
            ])
            ->values()
            ->all();
    }

    protected function mappedUsers(Collection $users, int $adminCount): array
    {
        return $users->map(function (User $user) use ($adminCount) {
            $isAdmin = $user->isAdmin();

            return [
                'id' => $user->getKey(),
                'name' => $user->name,
                'email' => $user->email,
                'accountRole' => $user->account_role ?? User::ROLE_USER,
                'roleLabel' => Str::headline($user->account_role ?? User::ROLE_USER),
                'isAdmin' => $isAdmin,
                'isPrimaryAdmin' => $user->isPrimaryAdmin(),
                'isCurrentUser' => auth()->id() === $user->getKey(),
                'phone' => $user->phone ?? '',
                'profileRole' => $user->profile_role ?: 'Not set',
                'profileRoleValue' => $user->profile_role ?? '',
                'location' => $user->profile_location ?: 'Not set',
                'profileLocationValue' => $user->profile_location ?? '',
                'bio' => $user->bio ?? '',
                'country' => $user->country ?? '',
                'cityState' => $user->city_state ?? '',
                'postalCode' => $user->postal_code ?? '',
                'taxId' => $user->tax_id ?? '',
                'facebookUrl' => $user->facebook_url ?? '',
                'xUrl' => $user->x_url ?? '',
                'linkedinUrl' => $user->linkedin_url ?? '',
                'instagramUrl' => $user->instagram_url ?? '',
                'joinedAt' => optional($user->created_at)->format('M j, Y'),
                'authMethod' => $this->authMethod($user),
                'emailVerified' => $user->email_verified_at !== null,
                'profileCompletion' => $this->profileCompletion($user),
                'nextRole' => $isAdmin ? User::ROLE_USER : User::ROLE_ADMIN,
                'actionLabel' => $isAdmin ? 'Set as user' : 'Make admin',
                'canManageRole' => ! $user->isPrimaryAdmin() && auth()->id() !== $user->getKey(),
                'canToggleRole' => ! $user->isPrimaryAdmin() && auth()->id() !== $user->getKey(),
                'canDelete' => ! $user->isPrimaryAdmin()
                    && auth()->id() !== $user->getKey()
                    && ! ($isAdmin && $adminCount <= 1),
                'deleteHelp' => $user->isPrimaryAdmin()
                    ? 'Fixed admin access is locked.'
                    : (auth()->id() === $user->getKey()
                        ? 'Use another admin account if you need to remove this profile.'
                        : ($isAdmin && $adminCount <= 1
                            ? 'At least one admin account must remain available.'
                            : 'Delete this account and clear their active sign-in sessions.')),
            ];
        })->all();
    }

    protected function authMethod(User $user): string
    {
        if ($user->google_id && $user->password) {
            return 'Email + Google';
        }

        if ($user->google_id) {
            return 'Google';
        }

        return 'Email';
    }

    protected function profileCompletion(User $user): int
    {
        $fields = [
            $user->name,
            $user->email,
            $user->phone,
            $user->profile_role,
            $user->profile_location,
            $user->bio,
            $user->country,
            $user->city_state,
        ];

        $completed = collect($fields)->filter(fn ($value) => filled($value))->count();

        return (int) round(($completed / count($fields)) * 100);
    }

    protected function providerLabel(Collection $providers, string $providerId): string
    {
        return (string) ($providers->firstWhere('id', $providerId)['label'] ?? Str::headline(str_replace(['-', '_'], ' ', $providerId)));
    }

    protected function providerEnvKey(string $providerId): string
    {
        return match ($providerId) {
            'gemini' => 'GEMINI_API_KEY',
            'groq' => 'GROQ_API_KEY',
            'openrouter' => 'OPENROUTER_API_KEY',
            'claude' => 'ANTHROPIC_API_KEY / CLAUDE_API_KEY',
            'wisdomgate' => 'WISDOMGATE_API_KEY / HUGGINGFACE_API_KEY',
            'cohere' => 'COHERE_API_KEY',
            default => 'Built-in / config-driven',
        };
    }
}


