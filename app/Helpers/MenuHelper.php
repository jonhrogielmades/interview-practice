<?php

namespace App\Helpers;

use App\Support\InterviewPracticeCatalog;

class MenuHelper
{
    public static function getUserMenuGroups(): array
    {
        return [
            [
                'title' => 'Workspace',
                'items' => [
                    [
                        'icon' => 'dashboard',
                        'name' => 'User Dashboard',
                        'path' => '/user/dashboard',
                    ],
                    [
                        'icon' => 'calendar',
                        'name' => 'Session Setup',
                        'path' => '/session-setup',
                    ],
                    [
                        'icon' => 'microphone',
                        'name' => 'Practice',
                        'path' => '/practice',
                        'subItems' => self::getPracticeCategoryItems(),
                    ],
                    [
                        'icon' => 'book-open',
                        'name' => 'Learning Lab',
                        'path' => '/learning-lab',
                        'subItems' => self::getLearningActivityItems(),
                    ],
                ],
            ],
            [
                'title' => 'AI Tools',
                'items' => [
                    [
                        'icon' => 'ai-assistant',
                        'name' => 'Interview Chatbot',
                        'path' => '/chatbot',
                    ],
                ],
            ],
            [
                'title' => 'Review',
                'items' => [
                    [
                        'icon' => 'charts',
                        'name' => 'Progress',
                        'path' => '/progress',
                    ],
                    [
                        'icon' => 'review',
                        'name' => 'Session Review',
                        'path' => '/session-review',
                    ],
                    [
                        'icon' => 'support-ticket',
                        'name' => 'Feedback Center',
                        'path' => '/feedback-center',
                    ],
                    [
                        'icon' => 'categories',
                        'name' => 'Category Insights',
                        'path' => '/category-insights',
                    ],
                ],
            ],
            [
                'title' => 'Account',
                'items' => [
                    [
                        'icon' => 'user-profile',
                        'name' => 'User Profile',
                        'path' => '/profile',
                    ],
                ],
            ],
        ];
    }

    public static function getAdminMenuGroups(): array
    {
        return [
            [
                'title' => 'Administration',
                'items' => [
                    [
                        'icon' => 'shield',
                        'name' => 'Admin Dashboard',
                        'path' => '/admin/dashboard',
                    ],
                    [
                        'icon' => 'user-profile',
                        'name' => 'User Management',
                        'path' => '/admin/users',
                    ],
                    [
                        'icon' => 'task',
                        'name' => 'Question Bank & Announcements',
                        'path' => '/admin/content',
                    ],
                    [
                        'icon' => 'charts',
                        'name' => 'Monitoring Records',
                        'path' => '/admin/monitoring',
                    ],
                ],
            ],
            [
                'title' => 'System',
                'items' => [
                    [
                        'icon' => 'ai-assistant',
                        'name' => 'API Management',
                        'path' => '/admin/apis',
                    ],
                    [
                        'icon' => 'wifi',
                        'name' => 'Mobile LAN',
                        'path' => '/admin/mobile-lan',
                    ],
                ],
            ],
            [
                'title' => 'Account',
                'items' => [
                    [
                        'icon' => 'user-profile',
                        'name' => 'Admin Profile',
                        'path' => '/profile',
                    ],
                ],
            ],
        ];
    }

    public static function getGuestMenuGroups(): array
    {
        return [
            [
                'title' => 'Others',
                'items' => [
                    [
                        'icon' => 'authentication',
                        'name' => 'Authentication',
                        'subItems' => [
                            ['name' => 'Sign In', 'path' => '/signin', 'pro' => false],
                            ['name' => 'Sign Up', 'path' => '/signup', 'pro' => false],
                        ],
                    ],
                ],
            ],
        ];
    }

    public static function getMenuGroups(): array
    {
        return auth()->check()
            ? (auth()->user()?->isAdmin()
                ? self::getAdminMenuGroups()
                : self::getUserMenuGroups())
            : self::getGuestMenuGroups();
    }

    protected static function getPracticeCategoryItems(): array
    {
        return collect(InterviewPracticeCatalog::categories())
            ->map(function (array $category, string $categoryId) {
                return [
                    'icon' => self::getPracticeCategoryIcon($categoryId),
                    'name' => $category['name'],
                    'path' => '/practice?category='.$categoryId,
                    'pro' => false,
                ];
            })
            ->values()
            ->all();
    }

    protected static function getLearningActivityItems(): array
    {
        $items = [
            [
                'icon' => 'book-open',
                'name' => 'Learning Lab Overview',
                'path' => '/learning-lab',
                'pro' => false,
            ],
            [
                'icon' => 'task',
                'name' => 'Learning Activities',
                'path' => '/learning-lab/activities',
                'pro' => false,
            ],
        ];

        foreach (InterviewPracticeCatalog::learningActivityCatalog() as $activityId => $activity) {
            $launchLevel = collect($activity['levels'] ?? [])->first() ?? ['level' => 1, 'targetScore' => 7.0];
            $query = http_build_query([
                'source' => 'learning-lab',
                'module' => (string) ($activity['module'] ?? 'answer-blueprint'),
                'activity' => $activityId,
                'level' => (int) ($launchLevel['level'] ?? 1),
                'target' => (float) ($launchLevel['targetScore'] ?? 7.0),
            ], '', '&', PHP_QUERY_RFC3986);

            $items[] = [
                'icon' => (string) ($activity['icon'] ?? 'task'),
                'name' => (string) ($activity['sidebarLabel'] ?? $activity['title'] ?? 'Learning Activity'),
                'path' => '/practice?'.$query,
                'pro' => false,
            ];
        }

        return $items;
    }

    protected static function getPracticeCategoryIcon(string $categoryId): string
    {
        return match ($categoryId) {
            'job' => 'briefcase',
            'scholarship' => 'award',
            'admission' => 'graduation-cap',
            'it' => 'code',
            default => 'task',
        };
    }

    public static function isActive($path)
    {
        return request()->is(ltrim($path, '/'));
    }

    public static function getIconSvg($iconName)
    {
        $icons = [
            'dashboard' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V8.99998C3.25 10.2426 4.25736 11.25 5.5 11.25H9C10.2426 11.25 11.25 10.2426 11.25 8.99998V5.5C11.25 4.25736 10.2426 3.25 9 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H9C9.41421 4.75 9.75 5.08579 9.75 5.5V8.99998C9.75 9.41419 9.41421 9.74998 9 9.74998H5.5C5.08579 9.74998 4.75 9.41419 4.75 8.99998V5.5ZM5.5 12.75C4.25736 12.75 3.25 13.7574 3.25 15V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H9C10.2426 20.75 11.25 19.7427 11.25 18.5V15C11.25 13.7574 10.2426 12.75 9 12.75H5.5ZM4.75 15C4.75 14.5858 5.08579 14.25 5.5 14.25H9C9.41421 14.25 9.75 14.5858 9.75 15V18.5C9.75 18.9142 9.41421 19.25 9 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V15ZM12.75 5.5C12.75 4.25736 13.7574 3.25 15 3.25H18.5C19.7426 3.25 20.75 4.25736 20.75 5.5V8.99998C20.75 10.2426 19.7426 11.25 18.5 11.25H15C13.7574 11.25 12.75 10.2426 12.75 8.99998V5.5ZM15 4.75C14.5858 4.75 14.25 5.08579 14.25 5.5V8.99998C14.25 9.41419 14.5858 9.74998 15 9.74998H18.5C18.9142 9.74998 19.25 9.41419 19.25 8.99998V5.5C19.25 5.08579 18.9142 4.75 18.5 4.75H15ZM15 12.75C13.7574 12.75 12.75 13.7574 12.75 15V18.5C12.75 19.7426 13.7574 20.75 15 20.75H18.5C19.7426 20.75 20.75 19.7427 20.75 18.5V15C20.75 13.7574 19.7426 12.75 18.5 12.75H15ZM14.25 15C14.25 14.5858 14.5858 14.25 15 14.25H18.5C18.9142 14.25 19.25 14.5858 19.25 15V18.5C19.25 18.9142 18.9142 19.25 18.5 19.25H15C14.5858 19.25 14.25 18.9142 14.25 18.5V15Z" fill="currentColor"></path></svg>',

            'ai-assistant' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18.75 2.42969V7.70424M9.42261 13.673C10.0259 14.4307 10.9562 14.9164 12 14.9164C13.0438 14.9164 13.9742 14.4307 14.5775 13.673M20 12V18.5C20 19.3284 19.3284 20 18.5 20H5.5C4.67157 20 4 19.3284 4 18.5V12C4 7.58172 7.58172 4 12 4C16.4183 4 20 7.58172 20 12Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M18.75 2.42969V2.43969M9.50391 9.875L9.50391 9.885M14.4961 9.875V9.885" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>',

            'interview' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.31641 4H3.49696C4.24468 4 4.87822 4.55068 4.98234 5.29112L5.13429 6.37161M5.13429 6.37161L6.23641 14.2089C6.34053 14.9493 6.97407 15.5 7.72179 15.5L17.0833 15.5C17.6803 15.5 18.2205 15.146 18.4587 14.5986L21.126 8.47023C21.5572 7.4795 20.8312 6.37161 19.7507 6.37161H5.13429Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M7.7832 19.5H7.7932M16.3203 19.5H16.3303" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>',

            'calendar' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M8 2C8.41421 2 8.75 2.33579 8.75 2.75V3.75H15.25V2.75C15.25 2.33579 15.5858 2 16 2C16.4142 2 16.75 2.33579 16.75 2.75V3.75H18.5C19.7426 3.75 20.75 4.75736 20.75 6V9V19C20.75 20.2426 19.7426 21.25 18.5 21.25H5.5C4.25736 21.25 3.25 20.2426 3.25 19V9V6C3.25 4.75736 4.25736 3.75 5.5 3.75H7.25V2.75C7.25 2.33579 7.58579 2 8 2ZM8 5.25H5.5C5.08579 5.25 4.75 5.58579 4.75 6V8.25H19.25V6C19.25 5.58579 18.9142 5.25 18.5 5.25H16H8ZM19.25 9.75H4.75V19C4.75 19.4142 5.08579 19.75 5.5 19.75H18.5C18.9142 19.75 19.25 19.4142 19.25 19V9.75Z" fill="currentColor"></path></svg>',

            'microphone' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 15.5C13.933 15.5 15.5 13.933 15.5 12V7.5C15.5 5.567 13.933 4 12 4C10.067 4 8.5 5.567 8.5 7.5V12C8.5 13.933 10.067 15.5 12 15.5Z" stroke="currentColor" stroke-width="1.5"/><path d="M18 11.5V12C18 15.3137 15.3137 18 12 18C8.68629 18 6 15.3137 6 12V11.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M12 18V21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M9.5 21H14.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',

            'book-open' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 6.25C10.3616 4.89177 8.15027 4.25 5.75 4.25C5.05964 4.25 4.5 4.80964 4.5 5.5V17.25C4.5 17.9404 5.05964 18.5 5.75 18.5C8.15027 18.5 10.3616 19.1418 12 20.5M12 6.25C13.6384 4.89177 15.8497 4.25 18.25 4.25C18.9404 4.25 19.5 4.80964 19.5 5.5V17.25C19.5 17.9404 18.9404 18.5 18.25 18.5C15.8497 18.5 13.6384 19.1418 12 20.5M12 6.25V20.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',

            'camera' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15.5 8L17.2 6.3C17.3875 6.11253 17.6418 6.00721 17.9071 6.00721C18.4594 6.00721 18.9071 6.45492 18.9071 7.00721V16.9928C18.9071 17.5451 18.4594 17.9928 17.9071 17.9928C17.6418 17.9928 17.3875 17.8875 17.2 17.7L15.5 16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M6.5 7.25H14.5C15.1904 7.25 15.75 7.80964 15.75 8.5V15.5C15.75 16.1904 15.1904 16.75 14.5 16.75H6.5C5.80964 16.75 5.25 16.1904 5.25 15.5V8.5C5.25 7.80964 5.80964 7.25 6.5 7.25Z" stroke="currentColor" stroke-width="1.5"/></svg>',

            'sparkles' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 3L13.436 7.564L18 9L13.436 10.436L12 15L10.564 10.436L6 9L10.564 7.564L12 3Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M18.5 14L19.175 16.325L21.5 17L19.175 17.675L18.5 20L17.825 17.675L15.5 17L17.825 16.325L18.5 14Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M6 15.5L6.54 17.46L8.5 18L6.54 18.54L6 20.5L5.46 18.54L3.5 18L5.46 17.46L6 15.5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>',

            'shield' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 3.5L18.5 6V11.75C18.5 15.9897 15.7712 19.747 12 21C8.22876 19.747 5.5 15.9897 5.5 11.75V6L12 3.5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M9.25 12.25L11.25 14.25L14.75 10.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',

            'wifi' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3.5 8.5C8.19444 4.83333 15.8056 4.83333 20.5 8.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M6.5 12C9.75 9.5 14.25 9.5 17.5 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M9.5 15.5C11.1667 14.1667 12.8333 14.1667 14.5 15.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M12 19H12.01" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>',

            'briefcase' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 6.75V6.25C9 5.14543 9.89543 4.25 11 4.25H13C14.1046 4.25 15 5.14543 15 6.25V6.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M5.75 7.25H18.25C19.4926 7.25 20.5 8.25736 20.5 9.5V16.25C20.5 17.4926 19.4926 18.5 18.25 18.5H5.75C4.50736 18.5 3.5 17.4926 3.5 16.25V9.5C3.5 8.25736 4.50736 7.25 5.75 7.25Z" stroke="currentColor" stroke-width="1.5"/><path d="M3.5 11.5H20.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M10.75 13.75H13.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',

            'award' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="9.5" r="4.25" stroke="currentColor" stroke-width="1.5"/><path d="M9.5 13L8 19.25L12 17L16 19.25L14.5 13" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>',

            'graduation-cap' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3.5 9.5L12 5.5L20.5 9.5L12 13.5L3.5 9.5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M7 11.75V15C7 16.5188 9.23858 17.75 12 17.75C14.7614 17.75 17 16.5188 17 15V11.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M20.5 9.5V14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',

            'code' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8.75 8L5 12L8.75 16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M15.25 8L19 12L15.25 16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M13.5 5.5L10.5 18.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',

            'user-profile' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 3.5C7.30558 3.5 3.5 7.30558 3.5 12C3.5 14.1526 4.3002 16.1184 5.61936 17.616C6.17279 15.3096 8.24852 13.5955 10.7246 13.5955H13.2746C15.7509 13.5955 17.8268 15.31 18.38 17.6167C19.6996 16.119 20.5 14.153 20.5 12C20.5 7.30558 16.6944 3.5 12 3.5ZM17.0246 18.8566V18.8455C17.0246 16.7744 15.3457 15.0955 13.2746 15.0955H10.7246C8.65354 15.0955 6.97461 16.7744 6.97461 18.8455V18.856C8.38223 19.8895 10.1198 20.5 12 20.5C13.8798 20.5 15.6171 19.8898 17.0246 18.8566ZM2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12ZM11.9991 7.25C10.8847 7.25 9.98126 8.15342 9.98126 9.26784C9.98126 10.3823 10.8847 11.2857 11.9991 11.2857C13.1135 11.2857 14.0169 10.3823 14.0169 9.26784C14.0169 8.15342 13.1135 7.25 11.9991 7.25ZM8.48126 9.26784C8.48126 7.32499 10.0563 5.75 11.9991 5.75C13.9419 5.75 15.5169 7.32499 15.5169 9.26784C15.5169 11.2107 13.9419 12.7857 11.9991 12.7857C10.0563 12.7857 8.48126 11.2107 8.48126 9.26784Z" fill="currentColor"></path></svg>',

            'task' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M7.75586 5.50098C7.75586 5.08676 8.09165 4.75098 8.50586 4.75098H18.4985C18.9127 4.75098 19.2485 5.08676 19.2485 5.50098L19.2485 15.4956C19.2485 15.9098 18.9127 16.2456 18.4985 16.2456H8.50586C8.09165 16.2456 7.75586 15.9098 7.75586 15.4956V5.50098ZM8.50586 3.25098C7.26322 3.25098 6.25586 4.25834 6.25586 5.50098V6.26318H5.50195C4.25931 6.26318 3.25195 7.27054 3.25195 8.51318V18.4995C3.25195 19.7422 4.25931 20.7495 5.50195 20.7495H15.4883C16.7309 20.7495 17.7383 19.7421 17.7383 18.4995L17.7383 17.7456H18.4985C19.7411 17.7456 20.7485 16.7382 20.7485 15.4956L20.7485 5.50097C20.7485 4.25833 19.7411 3.25098 18.4985 3.25098H8.50586ZM16.2383 17.7456H8.50586C7.26322 17.7456 6.25586 16.7382 6.25586 15.4956V7.76318H5.50195C5.08774 7.76318 4.75195 8.09897 4.75195 8.51318V18.4995C4.75195 18.9137 5.08774 19.2495 5.50195 19.2495H15.4883C15.9025 19.2495 16.2383 18.9137 16.2383 18.4995L16.2383 17.7456Z" fill="currentColor"></path></svg>',

            'charts' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M4.00002 12.0957C4.00002 7.67742 7.58174 4.0957 12 4.0957C16.4183 4.0957 20 7.67742 20 12.0957C20 16.514 16.4183 20.0957 12 20.0957H5.06068L6.34317 18.8132C6.48382 18.6726 6.56284 18.4818 6.56284 18.2829C6.56284 18.084 6.48382 17.8932 6.34317 17.7526C4.89463 16.304 4.00002 14.305 4.00002 12.0957ZM12 2.5957C6.75332 2.5957 2.50002 6.849 2.50002 12.0957C2.50002 14.4488 3.35633 16.603 4.77303 18.262L2.71969 20.3154C2.50519 20.5299 2.44103 20.8525 2.55711 21.1327C2.6732 21.413 2.94668 21.5957 3.25002 21.5957H12C17.2467 21.5957 21.5 17.3424 21.5 12.0957C21.5 6.849 17.2467 2.5957 12 2.5957ZM7.62502 10.8467C6.93467 10.8467 6.37502 11.4063 6.37502 12.0967C6.37502 12.787 6.93467 13.3467 7.62502 13.3467H7.62512C8.31548 13.3467 8.87512 12.787 8.87512 12.0967C8.87512 11.4063 8.31548 10.8467 7.62512 10.8467H7.62502ZM10.75 12.0967C10.75 11.4063 11.3097 10.8467 12 10.8467H12.0001C12.6905 10.8467 13.2501 11.4063 13.2501 12.0967C13.2501 12.787 12.6905 13.3467 12.0001 13.3467H12C11.3097 13.3467 10.75 12.787 10.75 12.0967ZM16.375 10.8467C15.6847 10.8467 15.125 11.4063 15.125 12.0967C15.125 12.787 15.6847 13.3467 16.375 13.3467H16.3751C17.0655 13.3467 17.6251 12.787 17.6251 12.0967C17.6251 11.4063 17.0655 10.8467 16.3751 10.8467H16.375Z" fill="currentColor"></path></svg>',

            'categories' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5.75 4.75H10.25C10.6642 4.75 11 5.08579 11 5.5V10C11 10.4142 10.6642 10.75 10.25 10.75H5.75C5.33579 10.75 5 10.4142 5 10V5.5C5 5.08579 5.33579 4.75 5.75 4.75Z" stroke="currentColor" stroke-width="1.5"/><path d="M13.75 4.75H18.25C18.6642 4.75 19 5.08579 19 5.5V10C19 10.4142 18.6642 10.75 18.25 10.75H13.75C13.3358 10.75 13 10.4142 13 10V5.5C13 5.08579 13.3358 4.75 13.75 4.75Z" stroke="currentColor" stroke-width="1.5"/><path d="M5.75 13.25H10.25C10.6642 13.25 11 13.5858 11 14V18.5C11 18.9142 10.6642 19.25 10.25 19.25H5.75C5.33579 19.25 5 18.9142 5 18.5V14C5 13.5858 5.33579 13.25 5.75 13.25Z" stroke="currentColor" stroke-width="1.5"/><path d="M13 15.5C13 14.2574 14.0074 13.25 15.25 13.25H16.75C17.9926 13.25 19 14.2574 19 15.5V17C19 18.2426 17.9926 19.25 16.75 19.25H15.25C14.0074 19.25 13 18.2426 13 17V15.5Z" stroke="currentColor" stroke-width="1.5"/></svg>',

            'insights' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M5.75 4C5.33579 4 5 4.33579 5 4.75V18.75C5 19.1642 5.33579 19.5 5.75 19.5H13.25C13.6642 19.5 14 19.1642 14 18.75C14 18.3358 13.6642 18 13.25 18H6.5V4.75C6.5 4.33579 6.16421 4 5.75 4ZM9.75 15C9.33579 15 9 15.3358 9 15.75V16.75C9 17.1642 9.33579 17.5 9.75 17.5C10.1642 17.5 10.5 17.1642 10.5 16.75V15.75C10.5 15.3358 10.1642 15 9.75 15ZM9 11.75C9 11.3358 9.33579 11 9.75 11C10.1642 11 10.5 11.3358 10.5 11.75V12.75C10.5 13.1642 10.1642 13.5 9.75 13.5C9.33579 13.5 9 13.1642 9 12.75V11.75ZM13.25 8C12.8358 8 12.5 8.33579 12.5 8.75V16.75C12.5 17.1642 12.8358 17.5 13.25 17.5C13.6642 17.5 14 17.1642 14 16.75V8.75C14 8.33579 13.6642 8 13.25 8ZM16.75 11.5C16.75 9.70507 18.2051 8.25 20 8.25C21.7949 8.25 23.25 9.70507 23.25 11.5C23.25 12.2797 22.9753 12.9952 22.5172 13.5554L23.7803 14.8185C24.0732 15.1114 24.0732 15.5862 23.7803 15.8791C23.4874 16.172 23.0126 16.172 22.7197 15.8791L21.4566 14.616C20.8964 15.074 20.1809 15.3488 19.4012 15.3488C17.6063 15.3488 16.1512 13.8937 16.1512 12.0988C16.1512 11.8914 16.1706 11.6885 16.2077 11.4917C16.2937 11.4968 16.3803 11.5 16.4675 11.5H16.75ZM17.6512 11.5C17.6512 12.4662 18.435 13.25 19.4012 13.25C20.3674 13.25 21.1512 12.4662 21.1512 11.5C21.1512 10.5338 20.3674 9.75 19.4012 9.75C18.435 9.75 17.6512 10.5338 17.6512 11.5Z" fill="currentColor"/></svg>',

            'authentication' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M14 2.75C14 2.33579 14.3358 2 14.75 2C15.1642 2 15.5 2.33579 15.5 2.75V5.73291L17.75 5.73291H19C19.4142 5.73291 19.75 6.0687 19.75 6.48291C19.75 6.89712 19.4142 7.23291 19 7.23291H18.5L18.5 12.2329C18.5 15.5691 15.9866 18.3183 12.75 18.6901V21.25C12.75 21.6642 12.4142 22 12 22C11.5858 22 11.25 21.6642 11.25 21.25V18.6901C8.01342 18.3183 5.5 15.5691 5.5 12.2329L5.5 7.23291H5C4.58579 7.23291 4.25 6.89712 4.25 6.48291C4.25 6.0687 4.58579 5.73291 5 5.73291L6.25 5.73291L8.5 5.73291L8.5 2.75C8.5 2.33579 8.83579 2 9.25 2C9.66421 2 10 2.33579 10 2.75L10 5.73291L14 5.73291V2.75ZM7 7.23291L7 12.2329C7 14.9943 9.23858 17.2329 12 17.2329C14.7614 17.2329 17 14.9943 17 12.2329L17 7.23291L7 7.23291Z" fill="currentColor"></path></svg>',

            'review' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14 3.25H8C6.75736 3.25 5.75 4.25736 5.75 5.5V18.5C5.75 19.7426 6.75736 20.75 8 20.75H11.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M14 3.25L18.25 7.5M14 3.25V6.5C14 7.05228 14.4477 7.5 15 7.5H18.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M9 11.25H15M9 14.25H12.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path><path d="M16.875 20.25C18.9461 20.25 20.625 18.5711 20.625 16.5C20.625 14.4289 18.9461 12.75 16.875 12.75C14.8039 12.75 13.125 14.4289 13.125 16.5C13.125 18.5711 14.8039 20.25 16.875 20.25Z" stroke="currentColor" stroke-width="1.5"></path><path d="M19.5264 19.1514L21.0001 20.6251" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path></svg>',

            'chat' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M4.00002 12.0957C4.00002 7.67742 7.58174 4.0957 12 4.0957C16.4183 4.0957 20 7.67742 20 12.0957C20 16.514 16.4183 20.0957 12 20.0957H5.06068L6.34317 18.8132C6.48382 18.6726 6.56284 18.4818 6.56284 18.2829C6.56284 18.084 6.48382 17.8932 6.34317 17.7526C4.89463 16.304 4.00002 14.305 4.00002 12.0957ZM12 2.5957C6.75332 2.5957 2.50002 6.849 2.50002 12.0957C2.50002 14.4488 3.35633 16.603 4.77303 18.262L2.71969 20.3154C2.50519 20.5299 2.44103 20.8525 2.55711 21.1327C2.6732 21.413 2.94668 21.5957 3.25002 21.5957H12C17.2467 21.5957 21.5 17.3424 21.5 12.0957C21.5 6.849 17.2467 2.5957 12 2.5957ZM7.62502 10.8467C6.93467 10.8467 6.37502 11.4063 6.37502 12.0967C6.37502 12.787 6.93467 13.3467 7.62502 13.3467H7.62512C8.31548 13.3467 8.87512 12.787 8.87512 12.0967C8.87512 11.4063 8.31548 10.8467 7.62512 10.8467H7.62502ZM10.75 12.0967C10.75 11.4063 11.3097 10.8467 12 10.8467H12.0001C12.6905 10.8467 13.2501 11.4063 13.2501 12.0967C13.2501 12.787 12.6905 13.3467 12.0001 13.3467H12C11.3097 13.3467 10.75 12.787 10.75 12.0967ZM16.375 10.8467C15.6847 10.8467 15.125 11.4063 15.125 12.0967C15.125 12.787 15.6847 13.3467 16.375 13.3467H16.3751C17.0655 13.3467 17.6251 12.787 17.6251 12.0967C17.6251 11.4063 17.0655 10.8467 16.3751 10.8467H16.375Z" fill="currentColor"></path></svg>',

            'support-ticket' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 17.0518V12C20 7.58174 16.4183 4 12 4C7.58168 4 3.99994 7.58174 3.99994 12V17.0518M19.9998 14.041V19.75C19.9998 20.5784 19.3282 21.25 18.4998 21.25H13.9998M6.5 18.75H5.5C4.67157 18.75 4 18.0784 4 17.25V13.75C4 12.9216 4.67157 12.25 5.5 12.25H6.5C7.32843 12.25 8 12.9216 8 13.75V17.25C8 18.0784 7.32843 18.75 6.5 18.75ZM17.4999 18.75H18.4999C19.3284 18.75 19.9999 18.0784 19.9999 17.25V13.75C19.9999 12.9216 19.3284 12.25 18.4999 12.25H17.4999C16.6715 12.25 15.9999 12.9216 15.9999 13.75V17.25C15.9999 18.0784 16.6715 18.75 17.4999 18.75Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>',

            'email' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M3.5 8.187V17.25C3.5 17.6642 3.83579 18 4.25 18H19.75C20.1642 18 20.5 17.6642 20.5 17.25V8.18747L13.2873 13.2171C12.5141 13.7563 11.4866 13.7563 10.7134 13.2171L3.5 8.187ZM20.5 6.2286C20.5 6.23039 20.5 6.23218 20.5 6.23398V6.24336C20.4976 6.31753 20.4604 6.38643 20.3992 6.42905L12.4293 11.9867C12.1716 12.1664 11.8291 12.1664 11.5713 11.9867L3.60116 6.42885C3.538 6.38481 3.50035 6.31268 3.50032 6.23568C3.50028 6.10553 3.60577 6 3.73592 6H20.2644C20.3922 6 20.4963 6.10171 20.5 6.2286ZM22 6.25648V17.25C22 18.4926 20.9926 19.5 19.75 19.5H4.25C3.00736 19.5 2 18.4926 2 17.25V6.23398C2 6.22371 2.00021 6.2135 2.00061 6.20333C2.01781 5.25971 2.78812 4.5 3.73592 4.5H20.2644C21.2229 4.5 22 5.27697 22.0001 6.23549C22.0001 6.24249 22.0001 6.24949 22 6.25648Z" fill="currentColor"></path></svg>',
        ];

        return $icons[$iconName] ?? '<svg width="1em" height="1em" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" fill="currentColor"/></svg>';
    }
}
