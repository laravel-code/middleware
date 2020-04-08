<?php

namespace LaravelCode\Middleware\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OAuthClient extends AbstractOAuthMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     * @param mixed ...$scopes
     * @return mixed
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next, ...$scopes)
    {
        try {
            $this->handleClient();

            return $next($request);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}
