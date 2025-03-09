<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    protected $proxies = '*'; // Percayai semua proxy

    protected function getProxyHeader(Request $request): ?string
    {
        return $request->server->get('HTTP_X_FORWARDED_PROTO') === 'https' ? Request::HEADER_X_FORWARDED_PROTO : null;
    }
}
