@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="User Management" />

    <div class="space-y-6">
        @if (session('status'))
            <div class="rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-300">{{ session('status') }}</div>
        @endif

        @if (session('error'))
            <div class="rounded-xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/20 dark:bg-error-500/10 dark:text-error-300">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/20 dark:bg-error-500/10 dark:text-error-300">
                <p class="font-medium">Please review the form and try again.</p>
                <ul class="mt-2 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="overflow-hidden rounded-[28px] border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="grid gap-6 p-6 lg:grid-cols-[1.1fr_0.9fr] lg:p-8">
                <div class="flex flex-col justify-center">
                    <span class="mb-4 inline-flex w-fit rounded-full bg-warning-50 px-3 py-1 text-xs font-medium uppercase tracking-[0.2em] text-warning-700 dark:bg-warning-500/15 dark:text-warning-300">Account Access</span>
                    <h1 class="mb-4 text-title-sm font-bold text-gray-900 dark:text-white">Control which members can access the admin system.</h1>
                    <p class="mb-6 max-w-2xl text-sm leading-7 text-gray-600 dark:text-gray-400">Create accounts for your team, promote trusted members to administrators, keep the fixed admin protected, and review how complete each profile is before changing access.</p>
                    <div class="flex flex-wrap gap-3">
                        <span class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">Fixed admin email: {{ $primaryAdminEmail }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach ($summaryCards as $card)
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/5">
                            <p class="mb-2 text-theme-xs text-gray-500 dark:text-gray-400">{{ $card['label'] }}</p>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $card['value'] }}</h3>
                            <p @class([
                                'mt-2 text-theme-xs font-medium',
                                'text-success-600' => $card['tone'] === 'success',
                                'text-blue-light-600' => $card['tone'] === 'blue',
                                'text-brand-500' => $card['tone'] === 'brand',
                                'text-warning-600' => $card['tone'] === 'warning',
                            ])>{{ $card['detail'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 lg:p-6">
            <div class="mb-5 flex flex-col gap-2 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Create User</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Add a new account directly from the admin panel. New users created here can be standard users or admins.</p>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Passwords must be at least 8 characters.</p>
            </div>

            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="form_mode" value="create" />

                <div class="grid gap-4 lg:grid-cols-2 xl:grid-cols-4">
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Full name</span>
                        <input type="text" name="name" value="{{ old('form_mode') === 'create' ? old('name') : '' }}" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-900 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white dark:focus:border-brand-800" placeholder="Maria Santos" required />
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Email address</span>
                        <input type="email" name="email" value="{{ old('form_mode') === 'create' ? old('email') : '' }}" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-900 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white dark:focus:border-brand-800" placeholder="maria@example.com" required />
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Password</span>
                        <input type="password" name="password" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-900 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white dark:focus:border-brand-800" required />
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Confirm password</span>
                        <input type="password" name="password_confirmation" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-900 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white dark:focus:border-brand-800" required />
                    </label>
                </div>

                <div class="grid gap-4 lg:grid-cols-2 xl:grid-cols-4">
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Account access</span>
                        <select name="account_role" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-900 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:border-brand-800">
                            <option value="{{ \App\Models\User::ROLE_USER }}" @selected((old('form_mode') === 'create' ? old('account_role') : \App\Models\User::ROLE_USER) === \App\Models\User::ROLE_USER)>Standard user</option>
                            <option value="{{ \App\Models\User::ROLE_ADMIN }}" @selected(old('form_mode') === 'create' && old('account_role') === \App\Models\User::ROLE_ADMIN)>Administrator</option>
                        </select>
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Phone</span>
                        <input type="text" name="phone" value="{{ old('form_mode') === 'create' ? old('phone') : '' }}" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-900 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white dark:focus:border-brand-800" placeholder="+63 912 345 6789" />
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Profile role</span>
                        <input type="text" name="profile_role" value="{{ old('form_mode') === 'create' ? old('profile_role') : '' }}" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-900 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white dark:focus:border-brand-800" placeholder="HR Manager" />
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Location</span>
                        <input type="text" name="profile_location" value="{{ old('form_mode') === 'create' ? old('profile_location') : '' }}" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-900 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white dark:focus:border-brand-800" placeholder="Quezon City" />
                    </label>
                </div>

                <label class="block space-y-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Short bio</span>
                    <textarea name="bio" rows="3" class="w-full rounded-xl border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-900 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white dark:focus:border-brand-800" placeholder="Add a short internal note or role summary.">{{ old('form_mode') === 'create' ? old('bio') : '' }}</textarea>
                </label>

                <div class="flex flex-wrap items-center gap-3">
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">Create user</button>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Admin-only action. Standard users cannot create accounts from the workspace.</p>
                </div>
            </form>
        </section>
        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 lg:p-6">
            <div class="mb-5">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Directory</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Admin controls live here only. Standard user pages do not expose role changes, account editing, or delete actions.</p>
            </div>

            <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-900/70">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">User</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Access</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Profile</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Joined</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-gray-900">
                            @foreach ($users as $user)
                                <tr>
                                    <td class="px-5 py-4 align-top">
                                        <div class="space-y-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <p class="font-medium text-gray-900 dark:text-white">{{ $user['name'] }}</p>
                                                @if ($user['isPrimaryAdmin'])
                                                    <span class="inline-flex items-center rounded-full bg-warning-50 px-2.5 py-1 text-xs font-medium text-warning-700 dark:bg-warning-500/15 dark:text-warning-300">Fixed admin</span>
                                                @endif
                                                @if ($user['isCurrentUser'])
                                                    <span class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/15 dark:text-brand-300">You</span>
                                                @endif
                                            </div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user['email'] }}</p>
                                            <div class="flex flex-wrap gap-2 pt-1">
                                                <span class="inline-flex items-center rounded-full border border-gray-200 px-2.5 py-1 text-xs font-medium text-gray-600 dark:border-gray-700 dark:text-gray-300">{{ $user['authMethod'] }}</span>
                                                <span @class([
                                                    'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium',
                                                    'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-300' => $user['emailVerified'],
                                                    'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' => ! $user['emailVerified'],
                                                ])>{{ $user['emailVerified'] ? 'Verified' : 'Unverified' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 align-top">
                                        <div class="space-y-2">
                                            <span @class([
                                                'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium',
                                                'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-300' => $user['isAdmin'],
                                                'bg-blue-light-50 text-blue-light-700 dark:bg-blue-light-500/15 dark:text-blue-light-300' => ! $user['isAdmin'],
                                            ])>{{ $user['roleLabel'] }}</span>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Only admins can open the admin control area and manage system pages.</p>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 align-top">
                                        <div class="space-y-1 text-sm text-gray-500 dark:text-gray-400">
                                            <p><span class="font-medium text-gray-700 dark:text-gray-200">Role:</span> {{ $user['profileRole'] }}</p>
                                            <p><span class="font-medium text-gray-700 dark:text-gray-200">Location:</span> {{ $user['location'] }}</p>
                                            <p><span class="font-medium text-gray-700 dark:text-gray-200">Profile complete:</span> {{ $user['profileCompletion'] }}%</p>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 align-top text-sm text-gray-500 dark:text-gray-400">{{ $user['joinedAt'] }}</td>
                                    <td class="px-5 py-4 align-top">
                                        <div class="flex min-w-[220px] flex-col gap-3">
                                            @if ($user['canToggleRole'])
                                                <form method="POST" action="{{ route('admin.users.role.update', $user['id']) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="account_role" value="{{ $user['nextRole'] }}" />
                                                    <button type="submit" @class([
                                                        'inline-flex w-full items-center justify-center rounded-xl px-4 py-2 text-sm font-medium transition',
                                                        'bg-warning-500 text-white hover:bg-warning-600' => ! $user['isAdmin'],
                                                        'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700' => $user['isAdmin'],
                                                    ])>{{ $user['actionLabel'] }}</button>
                                                </form>
                                            @endif

                                            <a href="#user-form-{{ $user['id'] }}" class="inline-flex w-full items-center justify-center rounded-xl border border-brand-200 bg-brand-50 px-4 py-2 text-sm font-medium text-brand-700 transition hover:bg-brand-100 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-300 dark:hover:bg-brand-500/20">Edit details</a>

                                            @if ($user['canDelete'])
                                                <form method="POST" action="{{ route('admin.users.destroy', $user['id']) }}" onsubmit="return confirm('Delete this account? This action cannot be undone.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl border border-error-200 bg-error-50 px-4 py-2 text-sm font-medium text-error-700 transition hover:bg-error-100 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-300 dark:hover:bg-error-500/20">Delete user</button>
                                                </form>
                                            @endif

                                            <p class="text-xs leading-5 text-gray-500 dark:text-gray-400">{{ $user['canDelete'] ? 'Delete this account and clear their active sign-in sessions.' : $user['deleteHelp'] }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
        <section class="space-y-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Edit Profiles</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Update account details, profile information, and access level from this admin-only area.</p>
            </div>

            <div class="grid gap-4 xl:grid-cols-2">
                @foreach ($users as $user)
                    @php
                        $editingCurrentUser = old('edit_user_id') == (string) $user['id'];
                    @endphp

                    <section id="user-form-{{ $user['id'] }}" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 lg:p-6">
                        <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ $user['name'] }}</h3>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ $user['roleLabel'] }}</span>
                                    @if ($user['isPrimaryAdmin'])
                                        <span class="inline-flex items-center rounded-full bg-warning-50 px-2.5 py-1 text-xs font-medium text-warning-700 dark:bg-warning-500/15 dark:text-warning-300">Fixed admin</span>
                                    @endif
                                </div>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $user['email'] }}</p>
                            </div>
                            <p class="text-xs leading-5 text-gray-500 dark:text-gray-400 lg:max-w-xs lg:text-right">{{ $user['isPrimaryAdmin'] ? 'The fixed admin email and access level stay locked so the admin system always keeps a protected owner account.' : 'Leave the password blank if you only want to update profile details.' }}</p>
                        </div>

                        <form method="POST" action="{{ route('admin.users.update', $user['id']) }}" class="space-y-5">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="edit_user_id" value="{{ $user['id'] }}" />

                            <div class="grid gap-4 lg:grid-cols-2 xl:grid-cols-4">
                                <label class="space-y-2">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Full name</span>
                                    <input type="text" name="name" value="{{ $editingCurrentUser ? old('name') : $user['name'] }}" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-900 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white dark:focus:border-brand-800" required />
                                </label>
                                <label class="space-y-2">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Email address</span>
                                    @if ($user['isPrimaryAdmin'])
                                        <input type="hidden" name="email" value="{{ $user['email'] }}" />
                                        <input type="email" value="{{ $user['email'] }}" class="h-11 w-full cursor-not-allowed rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-500 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400" disabled />
                                    @else
                                        <input type="email" name="email" value="{{ $editingCurrentUser ? old('email') : $user['email'] }}" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-900 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white dark:focus:border-brand-800" required />
                                    @endif
                                </label>
                                <label class="space-y-2">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">New password</span>
                                    <input type="password" name="password" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-900 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white dark:focus:border-brand-800" placeholder="Leave blank to keep current password" />
                                </label>
                                <label class="space-y-2">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Confirm password</span>
                                    <input type="password" name="password_confirmation" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-900 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white dark:focus:border-brand-800" placeholder="Repeat new password" />
                                </label>
                            </div>

                            <div class="grid gap-4 lg:grid-cols-2 xl:grid-cols-4">
                                <label class="space-y-2">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Account access</span>
                                    @if ($user['canManageRole'])
                                        <select name="account_role" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-900 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:border-brand-800">
                                            <option value="{{ \App\Models\User::ROLE_USER }}" @selected(($editingCurrentUser ? old('account_role') : $user['accountRole']) === \App\Models\User::ROLE_USER)>Standard user</option>
                                            <option value="{{ \App\Models\User::ROLE_ADMIN }}" @selected(($editingCurrentUser ? old('account_role') : $user['accountRole']) === \App\Models\User::ROLE_ADMIN)>Administrator</option>
                                        </select>
                                    @else
                                        <input type="hidden" name="account_role" value="{{ $user['accountRole'] }}" />
                                        <input type="text" value="{{ $user['roleLabel'] }}" class="h-11 w-full cursor-not-allowed rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-500 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400" disabled />
                                    @endif
                                </label>
                                <label class="space-y-2">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Phone</span>
                                    <input type="text" name="phone" value="{{ $editingCurrentUser ? old('phone') : $user['phone'] }}" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-900 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white dark:focus:border-brand-800" />
                                </label>
                                <label class="space-y-2">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Profile role</span>
                                    <input type="text" name="profile_role" value="{{ $editingCurrentUser ? old('profile_role') : $user['profileRoleValue'] }}" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-900 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white dark:focus:border-brand-800" />
                                </label>
                                <label class="space-y-2">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Location</span>
                                    <input type="text" name="profile_location" value="{{ $editingCurrentUser ? old('profile_location') : $user['profileLocationValue'] }}" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-900 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white dark:focus:border-brand-800" />
                                </label>
                            </div>

                            <label class="block space-y-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Bio</span>
                                <textarea name="bio" rows="3" class="w-full rounded-xl border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-900 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white dark:focus:border-brand-800">{{ $editingCurrentUser ? old('bio') : $user['bio'] }}</textarea>
                            </label>

                            <div class="flex flex-wrap items-center gap-3">
                                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">Save changes</button>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user['isPrimaryAdmin'] ? 'The fixed admin account can still update its profile details, but not its protected identity or access level.' : 'Editing stays inside the admin system and does not appear in the user workspace.' }}</p>
                            </div>
                        </form>
                    </section>
                @endforeach
            </div>
        </section>
    </div>
@endsection
