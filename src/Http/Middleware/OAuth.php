<?php

namespace LaravelCode\Middleware\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use LaravelCode\Middleware\Exceptions\OAuthTokenExpired;

class OAuth extends AbstractOAuthMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     * @param mixed ...$scopes
     * @return mixed
     * @throws OAuthTokenExpired
     * @throws \LaravelCode\Middleware\Exceptions\OAuthTokenInvalid
     */
    public function handle(Request $request, Closure $next, ...$scopes)
    {
        $this->handleClient();
        $this->handleUser($request, $scopes);

        return $next($request);
    }
}
