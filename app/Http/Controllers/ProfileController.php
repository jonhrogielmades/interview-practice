<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();

        return view('pages.profile', [
            'title' => 'Profile',
            'profile' => $this->profilePayload($user),
            'address' => $this->addressPayload($user),
            'socials' => $this->socialPayload($user),
        ]);
    }

    public function updateInfo(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class, 'email')->ignore($user)],
            'phone' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'x_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
        ]);

        $user->forceFill([
            'name' => trim($validated['full_name']),
            'email' => strtolower($validated['email']),
            'phone' => $validated['phone'] ?? null,
            'profile_role' => $validated['role'] ?? null,
            'profile_location' => $validated['location'] ?? null,
            'bio' => $validated['bio'] ?? null,
            'facebook_url' => $validated['facebook_url'] ?? null,
            'x_url' => $validated['x_url'] ?? null,
            'linkedin_url' => $validated['linkedin_url'] ?? null,
            'instagram_url' => $validated['instagram_url'] ?? null,
        ])->save();

        return $this->profileResponse($request, $user, 'Personal information updated successfully.');
    }

    public function updateAddress(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'country' => ['nullable', 'string', 'max:255'],
            'city_state' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:255'],
        ]);

        $user->forceFill([
            'country' => $validated['country'] ?? null,
            'city_state' => $validated['city_state'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
            'tax_id' => $validated['tax_id'] ?? null,
        ])->save();

        return $this->profileResponse($request, $user, 'Address information updated successfully.');
    }

    public function updateAvatar(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'avatar' => ['required', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(2048)],
        ]);

        $user = $request->user();
        $previousAvatarPath = $user->avatar_path;
        $avatarPath = $validated['avatar']->store("avatars/{$user->getKey()}", 'public');

        $user->forceFill([
            'avatar_path' => $avatarPath,
        ])->save();

        if ($previousAvatarPath && $previousAvatarPath !== $avatarPath && Storage::disk('public')->exists($previousAvatarPath)) {
            Storage::disk('public')->delete($previousAvatarPath);
        }

        return redirect()
            ->route('profile')
            ->with('status', 'Profile photo updated successfully.');
    }

    public function avatar(User $user): Response
    {
        if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
            return Storage::disk('public')->response($user->avatar_path);
        }

        return response()->file(public_path('images/user/user-01.jpg'));
    }

    protected function profileResponse(Request $request, User $user, string $message): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'profile' => $this->profilePayload($user),
                'address' => $this->addressPayload($user),
                'socials' => $this->socialPayload($user),
            ]);
        }

        return redirect()
            ->route('profile')
            ->with('status', $message);
    }

    protected function profilePayload(User $user): array
    {
        return [
            'avatar' => $user->avatar_url ?: asset('images/user/user-01.jpg'),
            'fullName' => $user->name ?? '',
            'role' => $user->profile_role ?? '',
            'location' => $user->profile_location ?? '',
            'email' => $user->email ?? '',
            'phone' => $user->phone ?? '',
            'bio' => $user->bio ?? '',
        ];
    }

    protected function addressPayload(User $user): array
    {
        return [
            'country' => $user->country ?? '',
            'cityState' => $user->city_state ?? '',
            'postalCode' => $user->postal_code ?? '',
            'taxId' => $user->tax_id ?? '',
        ];
    }

    protected function socialPayload(User $user): array
    {
        return [
            'facebook' => $user->facebook_url ?? '',
            'x' => $user->x_url ?? '',
            'linkedin' => $user->linkedin_url ?? '',
            'instagram' => $user->instagram_url ?? '',
        ];
    }
}
