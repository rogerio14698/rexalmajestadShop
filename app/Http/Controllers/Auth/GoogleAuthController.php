<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirige a Google para iniciar el flujo OAuth.
     * La URI de retorno se construye con el dominio actual para que coincida
     * con las URIs autorizadas en Google Cloud (web y subdominio de admin).
     */
    public function redirect(Request $request): RedirectResponse
    {
        return Socialite::driver('google')
            ->redirectUrl($this->callbackUrl($request))
            ->redirect();
    }

    /**
     * Gestiona el retorno de Google: busca o crea el usuario e inicia sesión.
     */
    public function callback(Request $request): RedirectResponse
    {
        $isAdminDomain = $this->isAdminDomain($request);

        try {
            $googleUser = Socialite::driver('google')
                ->redirectUrl($this->callbackUrl($request))
                ->user();
        } catch (\Throwable $e) {
            report($e);

            return $isAdminDomain
                ? redirect()->route('filament.admin.auth.login')->withErrors(['email' => 'No se pudo iniciar sesión con Google. Inténtalo de nuevo.'])
                : redirect()->route('login')->withErrors(['email' => 'No se pudo iniciar sesión con Google. Inténtalo de nuevo.']);
        }

        $user = User::query()
            ->where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if (! $user) {
            // En el subdominio de administración no se crean cuentas nuevas.
            if ($isAdminDomain) {
                abort(403, 'Esta cuenta de Google no tiene acceso a la administración.');
            }

            $user = User::query()->create([
                'name' => $googleUser->getName() ?: ($googleUser->getNickname() ?: 'Cliente'),
                'email' => $googleUser->getEmail(),
                'password' => Hash::make(Str::random(40)),
                'role' => 'customer',
                'google_id' => $googleUser->getId(),
            ]);
            $user->forceFill(['email_verified_at' => now()])->save();
        } elseif (! $user->google_id) {
            // Vincula la cuenta de Google a un usuario existente con el mismo correo.
            $user->forceFill([
                'google_id' => $googleUser->getId(),
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();
        }

        // Solo los administradores pueden entrar por el subdominio de admin.
        if ($isAdminDomain && ! $user->isAdmin()) {
            abort(403, 'Esta cuenta de Google no tiene acceso a la administración.');
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return $isAdminDomain
            ? redirect()->route('filament.admin.pages.dashboard')
            : redirect()->intended(route('account.dashboard'));
    }

    private function callbackUrl(Request $request): string
    {
        return $request->getSchemeAndHttpHost().config('services.google.redirect');
    }

    private function isAdminDomain(Request $request): bool
    {
        return $request->getHost() === env('ADMIN_DOMAIN', 'admin.rexalmajestad.com');
    }
}
