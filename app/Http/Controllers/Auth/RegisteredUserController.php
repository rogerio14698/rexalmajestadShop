<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('public.auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:254', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::min(12)->letters()->numbers()],
        ]);

        $user = User::query()->create($data);
        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        return $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail()
            ? redirect()->route('verification.notice')
            : redirect()->route('account.dashboard');
    }
}
