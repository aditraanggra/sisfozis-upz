<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttpsLivewire
{
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah permintaan sudah berasal dari HTTPS atau tidak
        if (!$request->secure() && $request->header('X-Forwarded-Proto') !== 'https') {
            return redirect()->secure($request->getRequestUri());
        }

        return $next($request);
    }
}
