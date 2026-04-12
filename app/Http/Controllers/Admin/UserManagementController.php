<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Notifications\SystemNotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function store(Request $request, SystemNotificationService $notifications): RedirectResponse
    {
        $validated = $this->validateUserData($request);

        $user = User::query()->create($this->userPayload($validated));
        $actor = $request->user();

        if ($actor) {
            $notifications->notifyUserAboutAdminCreatedAccount($user, $actor);
            $notifications->notifyAdminsAboutUserCreated($user, $actor);
        }

        return back()->with('status', sprintf('%s was created successfully.', $user->name));
    }

    public function update(Request $request, User $user, SystemNotificationService $notifications): RedirectResponse
    {
        $validated = $this->validateUserData($request, $user);
        $actor = $request->user();

        if (
            $user->isPrimaryAdmin()
            && strcasecmp($validated['email'], (string) config('admin.email')) !== 0
        ) {
            return back()->with('error', 'The fixed admin email must remain unchanged.');
        }

        if ($error = $this->roleChangeError($request, $user, $validated['account_role'])) {
            return back()->with('error', $error);
        }

        $roleChanged = $user->account_role !== $validated['account_role'];

        $user->forceFill($this->userPayload($validated, $user))->save();

        if ($actor && ! $actor->is($user)) {
            $notifications->notifyUserAboutAccountUpdated($user, $actor, $roleChanged);
            $notifications->notifyAdminsAboutUserUpdated($user, $actor, $roleChanged);
        }

        return back()->with('status', sprintf('%s was updated successfully.', $user->name));
    }

    public function destroy(Request $request, User $user, SystemNotificationService $notifications): RedirectResponse
    {
        if ($user->isPrimaryAdmin()) {
            return back()->with('error', 'The fixed admin account cannot be deleted.');
        }

        if ($request->user()?->is($user)) {
            return back()->with('error', 'You cannot delete your own account from this dashboard.');
        }

        if ($user->isAdmin() && User::query()->where('account_role', User::ROLE_ADMIN)->count() <= 1) {
            return back()->with('error', 'At least one admin account must remain available.');
        }

        $avatarPath = $user->avatar_path;
        $userName = $user->name;
        $actor = $request->user();
        $targetSnapshot = [
            'name' => $userName,
            'email' => $user->email,
        ];

        DB::table('sessions')->where('user_id', $user->getKey())->delete();
        $user->delete();

        if ($avatarPath && Storage::disk('public')->exists($avatarPath)) {
            Storage::disk('public')->delete($avatarPath);
        }

        if ($actor) {
            $notifications->notifyAdminsAboutUserDeleted($targetSnapshot, $actor);
        }

        return back()->with('status', sprintf('%s was deleted successfully.', $userName));
    }

    public function updateRole(Request $request, User $user, SystemNotificationService $notifications): RedirectResponse
    {
        $validated = $request->validate([
            'account_role' => ['required', 'string', Rule::in([User::ROLE_ADMIN, User::ROLE_USER])],
        ]);

        $targetRole = $validated['account_role'];
        $actor = $request->user();

        if ($error = $this->roleChangeError($request, $user, $targetRole)) {
            return back()->with('error', $error);
        }

        if ($user->account_role === $targetRole) {
            return back()->with('status', 'No account access changes were needed.');
        }

        $user->forceFill([
            'account_role' => $targetRole,
        ])->save();

        if ($actor) {
            $notifications->notifyUserAboutAccountUpdated($user, $actor, true);
            $notifications->notifyAdminsAboutUserUpdated($user, $actor, true);
        }

        return back()->with(
            'status',
            sprintf(
                '%s is now set as %s.',
                $user->name,
                $targetRole === User::ROLE_ADMIN ? 'an administrator' : 'a standard user'
            )
        );
    }

    protected function validateUserData(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class, 'email')->ignore($user)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'account_role' => ['required', 'string', Rule::in([User::ROLE_ADMIN, User::ROLE_USER])],
            'phone' => ['nullable', 'string', 'max:255'],
            'profile_role' => ['nullable', 'string', 'max:255'],
            'profile_location' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'country' => ['nullable', 'string', 'max:255'],
            'city_state' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'x_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
        ]);
    }

    protected function userPayload(array $validated, ?User $user = null): array
    {
        $payload = [
            'name' => trim($validated['name']),
            'email' => strtolower($validated['email']),
            'account_role' => $validated['account_role'],
        ];

        foreach ([
            'phone',
            'profile_role',
            'profile_location',
            'bio',
            'country',
            'city_state',
            'postal_code',
            'tax_id',
            'facebook_url',
            'x_url',
            'linkedin_url',
            'instagram_url',
        ] as $field) {
            if ($user === null || array_key_exists($field, $validated)) {
                $payload[$field] = $validated[$field] ?? null;
            }
        }

        if (filled($validated['password'] ?? null)) {
            $payload['password'] = $validated['password'];
        } elseif ($user === null) {
            $payload['password'] = $validated['password'];
        }

        return $payload;
    }

    protected function roleChangeError(Request $request, User $user, string $targetRole): ?string
    {
        if ($user->isPrimaryAdmin() && $targetRole !== User::ROLE_ADMIN) {
            return 'The fixed admin account must remain an administrator.';
        }

        if ($request->user()?->is($user) && $targetRole !== User::ROLE_ADMIN) {
            return 'You cannot remove your own admin access from this dashboard.';
        }

        if (
            $user->isAdmin()
            && $targetRole !== User::ROLE_ADMIN
            && User::query()->where('account_role', User::ROLE_ADMIN)->count() <= 1
        ) {
            return 'At least one admin account must remain available.';
        }

        return null;
    }
}

