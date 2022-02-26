<?php

namespace LaravelCode\Middleware;

use DateTimeImmutable;
use Illuminate\Http\Request;
use LaravelCode\Middleware\Exceptions\OAuthTokenInvalid;
use Lcobucci\Clock\FrozenClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;

class TokenParser
{
    private Token\Plain  $token;

    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function parseToken(): Token\Plain
    {
        if (isset($this->token)) {
            return $this->token;
        }

        assert((bool) config('oauth.public_key'), 'oauth.public_key should be set with a path to a ' .
            'file containing the public_key from your authorization server');
        assert((bool) $this->request->bearerToken(), 'No BearerToken header present in request');

        $key = InMemory::file(config('oauth.public_key'));
        $configuration = Configuration::forSymmetricSigner(
            new Sha256(),
            $key
        );

        /** @var Token\Plain $token */
        $token = $configuration->parser()->parse($this->request->bearerToken());

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

        $this->token = $token;

        return $token;
    }
}
