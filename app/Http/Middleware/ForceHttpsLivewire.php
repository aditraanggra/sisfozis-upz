<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceHttpsLivewire
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->secure() && str_contains($request->path(), 'livewire')) {
            return redirect()->secure($request->getRequestUri());
        }
        return $next($request);
    }
}
