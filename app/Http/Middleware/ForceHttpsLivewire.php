<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttpsLivewire
{
    public function handle(Request $request, Closure $next): Response
    {
        // Jangan paksa HTTPS di localhost
        if ($this->isLocalhost($request)) {
            return $next($request);
        }

        // Cek apakah request bukan HTTPS atau tidak melalui proxy HTTPS
        if (!$request->secure() && $request->header('X-Forwarded-Proto') !== 'https') {
            return redirect()->secure($request->getRequestUri());
        }

        return $next($request);
    }

    private function isLocalhost(Request $request): bool
    {
        return in_array($request->getHost(), ['localhost', '127.0.0.1', '::1']);
    }
}
