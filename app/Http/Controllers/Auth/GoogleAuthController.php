<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Notifications\SystemNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        if ($response = $this->missingConfigurationResponse()) {
            return $response;
        }

        $driver = Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email']);

        if ($this->shouldUseStatelessGoogleAuth()) {
            $driver = $driver->stateless();
        }

        return $driver->redirect();
    }

    public function callback(
        Request $request,
        SystemNotificationService $notifications,
    ): RedirectResponse
    {
        if ($response = $this->missingConfigurationResponse()) {
            return $response;
        }

        if ($response = $this->oauthErrorResponse($request)) {
            return $response;
        }

        try {
            $googleUser = $this->resolveGoogleUser();
        } catch (InvalidStateException $exception) {
            report($exception);

            return $this->googleStateFailureResponse();
        } catch (Throwable $exception) {
            report($exception);

            return $this->googleFailureResponse();
        }

        $email = strtolower((string) $googleUser->getEmail());

        if ($email === '') {
            return redirect()
                ->route('home')
                ->withInput(['auth_form' => 'signin'])
                ->withErrors([
                    'email' => 'Your Google account did not provide an email address.',
                ]);
        }

        $user = User::query()
            ->where('google_id', $googleUser->getId())
            ->first();

        if (! $user) {
            $user = User::query()
                ->where('email', $email)
                ->first();
        }

        $displayName = $googleUser->getName() ?: Str::headline(Str::before($email, '@'));

        $isNewUser = false;

        if ($user) {
            $user->forceFill([
                'google_id' => $googleUser->getId(),
                'google_avatar' => $googleUser->getAvatar() ?: $user->google_avatar,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ]);

            if (blank($user->name)) {
                $user->name = $displayName;
            }

            $user->save();
        } else {
            $user = User::query()->create([
                'name' => $displayName,
                'email' => $email,
                'password' => null,
                'google_id' => $googleUser->getId(),
                'google_avatar' => $googleUser->getAvatar(),
                'email_verified_at' => now(),
            ]);

            $isNewUser = true;
        }

        if ($isNewUser) {
            $notifications->sendWelcomeNotification($user, 'Google sign-in');
            $notifications->notifyAdminsAboutRegistration($user, 'Google sign-in');
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    protected function resolveGoogleUser()
    {
        $driver = Socialite::driver('google');

        if ($this->shouldUseStatelessGoogleAuth()) {
            return $driver->stateless()->user();
        }

        try {
            return $driver->user();
        } catch (InvalidStateException $exception) {
            if (! $this->shouldRetryStatelessAfterInvalidState()) {
                throw $exception;
            }

            // Local development often flips between localhost and 127.0.0.1.
            return Socialite::driver('google')->stateless()->user();
        }
    }

    protected function shouldUseStatelessGoogleAuth(): bool
    {
        return (bool) config('services.google.stateless', false) || app()->environment('local');
    }

    protected function shouldRetryStatelessAfterInvalidState(): bool
    {
        return app()->environment(['local', 'testing']);
    }

    protected function oauthErrorResponse(Request $request): ?RedirectResponse
    {
        $error = trim((string) $request->query('error'));

        if ($error === '') {
            return null;
        }

        $message = $error === 'access_denied'
            ? 'Google sign-in was cancelled. Please try again.'
            : 'Google sign-in could not be completed. Please try again.';

        return redirect()
            ->route('home')
            ->withInput(['auth_form' => 'signin'])
            ->withErrors([
                'email' => $message,
            ]);
    }

    protected function googleStateFailureResponse(): RedirectResponse
    {
        return redirect()
            ->route('home')
            ->withInput(['auth_form' => 'signin'])
            ->withErrors([
                'email' => 'Google sign-in session expired or the callback URL changed. Try again from the same browser and use one app URL only.',
            ]);
    }

    protected function googleFailureResponse(): RedirectResponse
    {
        return redirect()
            ->route('home')
            ->withInput(['auth_form' => 'signin'])
            ->withErrors([
                'email' => 'Google sign-in could not be completed. Please try again.',
            ]);
    }

    protected function missingConfigurationResponse(): ?RedirectResponse
    {
        $clientId = (string) config('services.google.client_id');
        $clientSecret = (string) config('services.google.client_secret');

        if ($this->usesPlaceholderValue($clientId) || $this->usesPlaceholderValue($clientSecret)) {
            return redirect()
                ->route('home')
                ->withInput(['auth_form' => 'signin'])
                ->withErrors([
                    'email' => 'Google sign-in is not configured yet. Add your Google client ID and secret in the environment file first.',
                ]);
        }

        return null;
    }

    protected function usesPlaceholderValue(string $value): bool
    {
        $normalized = trim($value);

        if ($normalized === '') {
            return true;
        }

        return in_array($normalized, [
            'your_real_google_client_id',
            'your_real_google_client_secret',
            'your_real_client_id',
            'your_real_client_secret',
            'real_client_id_from_google',
            'real_client_secret_from_google',
            'your_actual_client_id',
            'your_actual_client_secret',
            'your_google_client_id',
            'your_google_client_secret',
        ], true);
    }
}
