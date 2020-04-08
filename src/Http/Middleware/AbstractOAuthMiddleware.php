<?php

namespace LaravelCode\Middleware\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use LaravelCode\Middleware\Exceptions\OAuthScopeInvalid;
use LaravelCode\Middleware\Exceptions\OAuthTokenExpired;
use LaravelCode\Middleware\Exceptions\OAuthTokenInvalid;
use LaravelCode\Middleware\Factories\OAuthClient as Factory;
use LaravelCode\Middleware\Services\AccountService;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;

abstract class AbstractOAuthMiddleware
{
    /**
     * @var Factory
     */
    private $client;

    /**
     * @var AccountService
     */
    private $accountService;

    /**
     * AbstractOAuthMiddleware constructor.
     * @param Factory $client
     * @param AccountService $accountService
     */
    public function __construct(Factory $client, AccountService $accountService)
    {
        $this->client = $client;
        $this->accountService = $accountService;
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @param mixed ...$scopes
     * @return mixed
     */
    abstract public function handle(Request $request, Closure $next, ...$scopes);

    protected function handleClient()
    {
        $this->client->setup();
    }

    /**
     * @param Request $request
     * @param $scopes
     * @throws OAuthTokenExpired
     * @throws OAuthTokenInvalid
     */
    protected function handleUser(Request $request, $scopes)
    {
        $token = (new Parser())->parse($request->bearerToken());
        $publicKey = new Key('file://'.config('oauth.public_key'));

        if ($token->verify(new Sha256(), $publicKey) === false) {
            throw new OAuthTokenInvalid();
        }

        if ($token->isExpired()) {
            throw new OAuthTokenExpired();
        }

        $allScopes = collect($token->getClaim('scopes'))->filter(function ($scope) {
            return $scope === '*';
        })->isNotEmpty();

        if ($allScopes === false) {
            collect($scopes)->each(function ($scope) use ($token) {
                if (! in_array($scope, $token->getClaim('scopes'))) {
                    throw new OAuthScopeInvalid('Missing scope: '.$scope);
                }
            });
        }

        $user = $this->accountService->getProfile($request->bearerToken());
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
    }
}
