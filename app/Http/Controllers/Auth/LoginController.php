<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Display the login form.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle the login form submission.
     *
     * Authenticates the user via the "web" guard, regenerates the session to
     * prevent session fixation attacks, then redirects to the intended URL or
     * the home page.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        ActivityLogger::log('auth.logged_in', "User \"{$user->name}\" logged in", ['email' => $user->email]);

        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        if ($user->isAuthor()) {
            return redirect()->intended(route('author.dashboard'));
        }

        return redirect()->intended(route('home'));
    }

    /**
     * Destroy the authenticated session (logout).
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            ActivityLogger::log('auth.logged_out', "User \"{$user->name}\" logged out", ['email' => $user->email]);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
