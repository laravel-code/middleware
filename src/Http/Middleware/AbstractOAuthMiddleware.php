<?php

namespace LaravelCode\Middleware\Http\Middleware;

use Closure;
use DateTimeImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use LaravelCode\Middleware\Exceptions\OauthClientContentTypeException;
use LaravelCode\Middleware\Exceptions\OAuthScopeInvalid;
use LaravelCode\Middleware\Exceptions\OAuthTokenInvalid;
use LaravelCode\Middleware\Factories\OAuthClient as Factory;
use LaravelCode\Middleware\Services\AccountService;
use LaravelCode\Middleware\User;
use Lcobucci\Clock\FrozenClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\LocalFileReference;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;

abstract class AbstractOAuthMiddleware
{
    /**
     * @var Factory
     */
    private Factory $client;

    /**
     * @var AccountService
     */
    private AccountService $accountService;

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

    /**
     * @throws OauthClientContentTypeException
     * @throws AuthorizationException
     */
    protected function handleClient()
    {
        $this->client->setup();
    }

    /**
     * @param Request $request
     * @param $scopes
     * @throws OAuthTokenInvalid|OAuthScopeInvalid
     */
    protected function handleUser(Request $request, $scopes)
    {
        $key = LocalFileReference::file(config('oauth.public_key'));
        $configuration = Configuration::forSymmetricSigner(
            new Sha256(),
            $key
        );

        $token = $configuration->parser()->parse($request->bearerToken());

        $configuration->setValidationConstraints(
            new SignedWith($configuration->signer(), $configuration->verificationKey()),
            new StrictValidAt(new FrozenClock(new DateTimeImmutable()))
        );

        $constraints = $configuration->validationConstraints();
        try {
            $configuration->validator()->assert($token, ...$constraints);
        } catch (RequiredConstraintsViolated $e) {
            // list of constraints violation exceptions:
            throw new OAuthTokenInvalid();
        }

        $allScopes = collect($token->claims()->get('scopes', []))->filter(function ($scope) {
            return $scope === '*';
        })->isNotEmpty();

        if ($allScopes === false) {
            collect($scopes)->each(function ($scope) use ($token) {
                if (! in_array($scope, $token->claims()->get('scopes', []))) {
                    throw new OAuthScopeInvalid('Missing scope: '.$scope);
                }
            });
        }

        $jti = $token->claims()->get('jti');
        $user = \Cache::store('array')->remember($jti, 60, function () use ($request) {
            return $this->accountService->getProfile($request->bearerToken());
        });

        $usr = new User();
        foreach (get_object_vars($user) as $key => $value) {
            $usr->setAttribute($key, $value);
        }
        \Auth::setUser($usr);
    }
}
