<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy());

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    private function contentSecurityPolicy(): string
    {
        $scriptSrc = "'self' 'unsafe-inline' 'unsafe-eval'";
        $styleSrc = "'self' 'unsafe-inline' https://fonts.googleapis.com";
        $fontSrc = "'self' https://fonts.gstatic.com data:";
        $imgSrc = "'self' data: https:";
        $connectSrc = "'self'";

        // En local, con `npm run dev` activo, hay que permitir el dev server de Vite (HMR).
        $hotFile = public_path('hot');

        if (app()->environment('local') && is_file($hotFile)) {
            $devServer = rtrim(trim((string) file_get_contents($hotFile)), '/');
            $devServerWs = preg_replace('/^http/', 'ws', $devServer);

            $scriptSrc .= " {$devServer}";
            $styleSrc .= " {$devServer}";
            $fontSrc .= " {$devServer}";
            $imgSrc .= " {$devServer}";
            $connectSrc .= " {$devServer} {$devServerWs}";
        }

        return "default-src 'self'; base-uri 'self'; object-src 'none'; frame-ancestors 'none'; "
            . 'form-action \'self\' https://checkout.stripe.com https://www.paypal.com https://www.sandbox.paypal.com; '
            . "img-src {$imgSrc}; style-src {$styleSrc}; font-src {$fontSrc}; script-src {$scriptSrc}; connect-src {$connectSrc};";
    }
}
