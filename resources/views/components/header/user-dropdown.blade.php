@php
    use Illuminate\Support\Str;

    $user = auth()->user();
    $fullName = $user?->name ?: 'User';
    $firstName = Str::of($fullName)->trim()->explode(' ')->filter()->first() ?: 'User';
    $initial = Str::upper(Str::substr($fullName, 0, 1));
    $avatar = $user?->avatar_url;
    $isAdmin = $user?->isAdmin() ?? false;
    $dashboardUrl = $isAdmin ? route('admin.dashboard') : route('user.dashboard');
    $dashboardLabel = $isAdmin ? 'Admin Dashboard' : 'User Dashboard';
@endphp

<div class="relative" x-data="{ dropdownOpen: false }" @click.away="dropdownOpen = false">
    <button
        class="flex w-full items-center justify-between text-gray-700 sm:w-auto dark:text-gray-400"
        @click.prevent="dropdownOpen = !dropdownOpen"
        type="button"
    >
        <span class="mr-3 flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full bg-brand-500/10 text-sm font-semibold text-brand-600 dark:bg-brand-500/20 dark:text-brand-300">
            @if ($avatar)
                <img src="{{ $avatar }}" alt="{{ $fullName }}" class="h-full w-full object-cover object-top" />
            @else
                {{ $initial }}
            @endif
        </span>

        <span class="mr-1 block max-w-[9rem] truncate font-medium text-theme-sm">{{ $firstName }}</span>

        <svg
            class="h-5 w-5 shrink-0 transition-transform duration-200"
            :class="{ 'rotate-180': dropdownOpen }"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <div
        x-show="dropdownOpen"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="z-50 absolute right-0 mt-[17px] flex w-[min(16.25rem,calc(100vw-1.5rem))] flex-col rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-lg sm:w-[260px] dark:border-gray-800 dark:bg-gray-dark"
        style="display: none;"
    >
        <div>
            <span class="content-break block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ $fullName }}</span>
            <span class="content-break mt-0.5 block text-theme-xs text-gray-500 dark:text-gray-400">{{ $user?->email }}</span>
            @if ($isAdmin)
                <span class="mt-2 inline-flex items-center rounded-full bg-warning-50 px-2.5 py-1 text-xs font-medium text-warning-700 dark:bg-warning-500/15 dark:text-warning-300">
                    Administrator
                </span>
            @endif
        </div>

        <ul class="flex flex-col gap-1 border-b border-gray-200 pt-4 pb-3 dark:border-gray-800">
            <li>
                <a
                    href="{{ $dashboardUrl }}"
                    class="group flex items-center gap-3 rounded-lg px-3 py-2 font-medium text-gray-700 text-theme-sm hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                >
                    <span class="text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V8.99998C3.25 10.2426 4.25736 11.25 5.5 11.25H9C10.2426 11.25 11.25 10.2426 11.25 8.99998V5.5C11.25 4.25736 10.2426 3.25 9 3.25H5.5ZM12.75 5.5C12.75 4.25736 13.7574 3.25 15 3.25H18.5C19.7426 3.25 20.75 4.25736 20.75 5.5V8.99998C20.75 10.2426 19.7426 11.25 18.5 11.25H15C13.7574 11.25 12.75 10.2426 12.75 8.99998V5.5ZM5.5 12.75C4.25736 12.75 3.25 13.7574 3.25 15V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H9C10.2426 20.75 11.25 19.7427 11.25 18.5V15C11.25 13.7574 10.2426 12.75 9 12.75H5.5ZM15 12.75C13.7574 12.75 12.75 13.7574 12.75 15V18.5C12.75 19.7426 13.7574 20.75 15 20.75H18.5C19.7426 20.75 20.75 19.7427 20.75 18.5V15C20.75 13.7574 19.7426 12.75 18.5 12.75H15Z" fill="currentColor" />
                        </svg>
                    </span>
                    {{ $dashboardLabel }}
                </a>
            </li>
            <li>
                <a
                    href="{{ route('notifications.index') }}"
                    class="group flex items-center gap-3 rounded-lg px-3 py-2 font-medium text-gray-700 text-theme-sm hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                >
                    <span class="text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 4.25C8.27208 4.25 5.25 7.27208 5.25 11V15.25H4.75C4.33579 15.25 4 15.5858 4 16C4 16.4142 4.33579 16.75 4.75 16.75H19.25C19.6642 16.75 20 16.4142 20 16C20 15.5858 19.6642 15.25 19.25 15.25H18.75V11C18.75 7.27208 15.7279 4.25 12 4.25Z" stroke="currentColor" stroke-width="1.5" />
                            <path d="M10 19.25C10 20.3546 10.8954 21.25 12 21.25C13.1046 21.25 14 20.3546 14 19.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                        </svg>
                    </span>
                    Notifications
                </a>
            </li>
            <li>
                <a
                    href="{{ route('profile') }}"
                    class="group flex items-center gap-3 rounded-lg px-3 py-2 font-medium text-gray-700 text-theme-sm hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                >
                    <span class="text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M12 3.5C7.30558 3.5 3.5 7.30558 3.5 12C3.5 14.1526 4.3002 16.1184 5.61936 17.616C6.17279 15.3096 8.24852 13.5955 10.7246 13.5955H13.2746C15.7509 13.5955 17.8268 15.31 18.38 17.6167C19.6996 16.119 20.5 14.153 20.5 12C20.5 7.30558 16.6944 3.5 12 3.5ZM17.0246 18.8566V18.8455C17.0246 16.7744 15.3457 15.0955 13.2746 15.0955H10.7246C8.65354 15.0955 6.97461 16.7744 6.97461 18.8455V18.856C8.38223 19.8895 10.1198 20.5 12 20.5C13.8798 20.5 15.6171 19.8898 17.0246 18.8566ZM2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12Z" fill="currentColor" />
                        </svg>
                    </span>
                    Profile
                </a>
            </li>
        </ul>

        <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf
            <button
                type="submit"
                class="group flex w-full items-center gap-3 rounded-lg px-3 py-2 font-medium text-gray-700 text-theme-sm hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                @click="dropdownOpen = false"
            >
                <span class="text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                </span>
                Sign out
            </button>
        </form>
    </div>
</div>
