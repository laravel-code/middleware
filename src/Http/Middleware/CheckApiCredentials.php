<?php

namespace LaravelCode\Middleware\Http\Middleware;

use Auth;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Factory;
use Laravel\Passport\Http\Middleware\CheckClientCredentials;
use Request;
use Session;

class CheckApiCredentials extends CheckClientCredentials
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $psr
     * @param array $scopes
     * @throws AuthenticationException
     * @throws \Laravel\Passport\Exceptions\MissingScopeException
     */
    protected function validate($psr, $scopes)
    {
        $token = $this->repository->find($psr->getAttribute('oauth_access_token_id'));

        if (! $token) {
            throw new AuthenticationException;
        }

        $this->validateScopes($token, $scopes);
        $clientId = $psr->getAttribute('oauth_client_id', null);
        $userId = $psr->getAttribute('oauth_user_id', null);

        Request::merge([
            'X_OAUTH_CLIENT_ID' => $clientId,
            'X_OAUTH_USER_ID' => $userId,
        ]);

        if (null !== $userId) {
            Auth::loginUsingId($userId);
        }
    }
}
