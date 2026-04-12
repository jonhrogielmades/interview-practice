@extends('layouts.app')

@section('content')
    @php use Illuminate\Support\Str; @endphp

    <x-common.page-breadcrumb pageTitle="Notifications" />

    <div class="space-y-6">
        @if (session('status'))
            <div class="rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-300">
                {{ session('status') }}
            </div>
        @endif

        <section class="overflow-hidden rounded-[28px] border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="grid gap-6 p-6 lg:grid-cols-[1.15fr_0.85fr] lg:p-8">
                <div class="flex flex-col justify-center">
                    <span class="mb-4 inline-flex w-fit rounded-full bg-brand-50 px-3 py-1 text-xs font-medium uppercase tracking-[0.2em] text-brand-700 dark:bg-brand-500/15 dark:text-brand-300">
                        Notification Center
                    </span>

                    <h1 class="mb-4 text-title-sm font-bold text-gray-900 dark:text-white">
                        Track account activity, practice milestones, and admin updates in one place.
                    </h1>

                    <p class="max-w-2xl text-sm leading-7 text-gray-600 dark:text-gray-400">
                        User notifications cover welcome messages, practice saves, and account updates. Admin notifications add signups, access changes, and completed interview practice activity.
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/5">
                        <p class="mb-2 text-theme-xs text-gray-500 dark:text-gray-400">Unread</p>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $unreadCount }}</h3>
                        <p class="mt-2 text-theme-xs font-medium text-brand-500">Notifications waiting for review</p>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/5">
                        <p class="mb-2 text-theme-xs text-gray-500 dark:text-gray-400">Recent Activity</p>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $notifications->total() }}</h3>
                        <p class="mt-2 text-theme-xs font-medium text-blue-light-600">Stored account and practice notifications</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 lg:p-6">
            <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">All Notifications</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Open a notification to mark it as read and jump to the related page.</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    @if ($unreadCount > 0)
                        <form method="POST" action="{{ route('notifications.read-all') }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                                Mark all as read
                            </button>
                        </form>
                    @endif

                    @if ($notifications->total() > 0)
                        <form method="POST" action="{{ route('notifications.clear') }}" onsubmit="return confirm('Delete all notifications? This cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-error-200 bg-error-50 px-4 py-2 text-sm font-medium text-error-700 transition hover:bg-error-100 dark:border-error-500/20 dark:bg-error-500/10 dark:text-error-300">
                                Clear all
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="space-y-4">
                @forelse ($notifications as $notification)
                    @php
                        $chipClasses = match ($notification['tone']) {
                            'success' => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-300',
                            'warning' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-300',
                            'danger' => 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-300',
                            'blue' => 'bg-blue-light-50 text-blue-light-700 dark:bg-blue-light-500/15 dark:text-blue-light-300',
                            default => 'bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300',
                        };
                        $chipLabel = match ($notification['icon']) {
                            'shield' => 'S',
                            'user' => 'U',
                            'profile' => 'P',
                            'practice' => 'I',
                            'trash' => 'D',
                            'sparkles' => 'W',
                            default => Str::upper(Str::substr($notification['title'], 0, 1)),
                        };
                    @endphp

                    <article class="rounded-2xl border border-gray-200 p-4 transition hover:shadow-theme-sm dark:border-gray-800 {{ $notification['isRead'] ? 'bg-white dark:bg-gray-900' : 'bg-brand-50/30 dark:bg-brand-500/5' }}">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="flex min-w-0 items-start gap-4">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-sm font-semibold {{ $chipClasses }}">
                                    {{ $chipLabel }}
                                </span>

                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ $notification['title'] }}</h3>
                                        @unless ($notification['isRead'])
                                            <span class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/15 dark:text-brand-300">
                                                Unread
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                                Read
                                            </span>
                                        @endunless
                                    </div>

                                    <p class="mt-2 text-sm leading-7 text-gray-600 dark:text-gray-400">{{ $notification['body'] }}</p>
                                    <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                        <span>{{ $notification['createdAt'] }}</span>
                                        <span class="h-1 w-1 rounded-full bg-gray-400"></span>
                                        <span>{{ $notification['time'] }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex shrink-0 items-center gap-3">
                                <form method="POST" action="{{ route('notifications.read', $notification['id']) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="redirect_to" value="{{ $notification['actionUrl'] }}" />
                                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-600">
                                        {{ $notification['actionLabel'] }}
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('notifications.destroy', $notification['id']) }}" onsubmit="return confirm('Delete this notification?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-error-300 hover:bg-error-50 hover:text-error-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:border-error-500/40 dark:hover:bg-error-500/10 dark:hover:text-error-300">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-gray-300 px-5 py-10 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        No notifications yet. Once users sign up, practice sessions are saved, or admin actions happen, they will appear here.
                    </div>
                @endforelse
            </div>

            @if ($notifications->hasPages())
                <div class="mt-6">
                    {{ $notifications->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
