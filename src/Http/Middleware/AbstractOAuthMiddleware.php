<?php

namespace LaravelCode\Middleware\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use LaravelCode\Middleware\Exceptions\OAuthScopeInvalid;
use LaravelCode\Middleware\Exceptions\OAuthTokenExpired;
use LaravelCode\Middleware\Exceptions\OAuthTokenInvalid;
use LaravelCode\Middleware\Factories\OAuthClient as Factory;
use LaravelCode\Middleware\Services\AccountService;
use LaravelCode\Middleware\User;
use Lcobucci\Clock\FrozenClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Key\LocalFileReference;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Lcobucci\JWT\Validation\Validator;

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
        $key = LocalFileReference::file(config('oauth.public_key'));
        $configuration = Configuration::forSymmetricSigner(
            new Sha256(),
            $key
        );

        $token = $configuration->parser()->parse($request->bearerToken());

        $configuration->setValidationConstraints(
            new \Lcobucci\JWT\Validation\Constraint\SignedWith($configuration->signer(), $configuration->verificationKey()),
            new StrictValidAt(new FrozenClock(new \DateTimeImmutable()))
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

        $user = $this->accountService->getProfile($request->bearerToken());
        $usr = new User();
        foreach (get_object_vars($user) as $key => $value) {
            $usr->setAttribute($key, $value);
        }
        \Auth::setUser($usr);
    }
}
