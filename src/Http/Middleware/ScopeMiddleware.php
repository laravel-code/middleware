<?php

namespace LaravelCode\Middleware\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use LaravelCode\Middleware\Exceptions\OAuthScopeInvalid;
use LaravelCode\Middleware\Exceptions\OAuthTokenInvalid;

class ScopeMiddleware extends AbstractMiddleWare
{
    /**
     * @throws OAuthScopeInvalid
     * @throws OAuthTokenInvalid
     */
    public function handle(Request $request, Closure $next, string ...$scopes): mixed
    {
        $token = $this->parser->parseToken();

        $allScopes = collect($token->claims()->get('scopes', []))->filter(function ($scope) {
            return $scope === '*';
        })->isNotEmpty();

        if ($allScopes === false) {
            collect($scopes)->each(
                function (string $scope) use ($token) {
                    if (!in_array($scope, $token->claims()->get('scopes', []))) {
                        throw new OAuthScopeInvalid('Missing scope: ' . $scope, 403);
                    }
                }
            );
        }

        return $next($request);
    }
}
