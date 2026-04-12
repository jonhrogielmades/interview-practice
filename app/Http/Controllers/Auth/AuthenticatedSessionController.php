<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function store(
        Request $request,
    ): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $email = strtolower($credentials['email']);
        $remember = $request->boolean('remember');

        $user = User::query()->where('email', $email)->first();

        if ($user && is_null($user->password)) {
            return back()
                ->withInput($request->only('email', 'remember', 'auth_form'))
                ->withErrors([
                    'email' => 'This account uses Google sign-in. Please continue with Google.',
                ]);
        }

        if (! Auth::attempt([
            'email' => $email,
            'password' => $credentials['password'],
        ], $remember)) {
            return back()
                ->withInput($request->only('email', 'remember', 'auth_form'))
                ->withErrors([
                    'email' => 'The provided credentials do not match our records.',
                ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
