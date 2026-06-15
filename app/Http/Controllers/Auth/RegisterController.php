<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * Display the registration form.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle the registration form submission.
     *
     * Creates the user, assigns the default "reader" role, fires the
     * Registered event (which triggers the email verification notification
     * if the User model implements MustVerifyEmail), and logs the user in.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'status'   => 'active',
        ]);

        // Assign default role via spatie/laravel-permission
        $user->assignRole('user');

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('verification.notice');
    }
}
