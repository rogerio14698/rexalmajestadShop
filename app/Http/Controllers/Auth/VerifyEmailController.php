<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless(
            hash_equals((string) $request->route('id'), (string) $user->getKey())
            && hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification())),
            403,
        );

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->route('account.dashboard')->with('status', 'Correo electrónico verificado.');
    }
}
