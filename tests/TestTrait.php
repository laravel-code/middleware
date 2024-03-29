<?php

namespace Test;

use Firebase\JWT\JWT;
use LaravelCode\Middleware\Factories\HttpClient;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;

trait TestTrait
{
    public function createClientToken($scopes = 'profile', $jti = null): string
    {
        $config = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::file(dirname(__FILE__) . '/oauth-private.key'),
            InMemory::file(dirname(__FILE__) . '/oauth-public.key'),
        );

        return $config->builder()
            ->identifiedBy($jti ?? 'd8ebe93ce08347772a975e568264b685d391be7252872ab4697d4c98390e6d6d6c5ffb795ec1483a')
            ->issuedBy('accounts.com')
            ->permittedFor('accounts.com')
            ->issuedAt(new \DateTimeImmutable())
            ->canOnlyBeUsedAfter(new \DateTimeImmutable())
            ->expiresAt(new \DateTimeImmutable('+1 hour'))
            ->withClaim('scopes', explode(' ', $scopes))
            ->getToken($config->signer(), $config->signingKey())
            ->toString();
    }

    public function assertRequests($requests = [], $assertions = [])
    {
        $this->assertEquals(count($assertions), count($requests), sprintf('All requests should have an assertion. %s called, %s expected', count($requests), count($assertions)));

        foreach ($requests as $request) {
            $callback = array_shift($assertions);
            $callback($request['request'], $request['response'], $request['error'], $request['options']);
        }
    }

    public function createUserToken($scopes = 'profile', $expires = null)
    {
        $privateKey = file_get_contents(realpath(dirname(__FILE__) . '/oauth-private.key'));

        $payload = [
            'aud' => 2, // GRANT ID
            'sub' => 1, // USER ID
            'jti' => 'd8ebe93ce08347772a975e568264b685d391be7252872ab4697d4c98390e6d6d6c5ffb795ec05b53',
            'iat' => time(),
            'nbf' => time(),
            'exp' => $expires ?: time() + 3600,
            'scopes' => explode(' ', $scopes),
        ];

        return JWT::encode($payload, $privateKey, 'RS256');
    }

    public function mockHttpClient($stack)
    {
        app()->bind(HttpClient::class, function () use ($stack) {
            return (new HttpClient(['handler' => $stack]))->getClient();
        });
    }

    public function setStoragePath($app)
    {
        $app->useStoragePath(dirname(__FILE__));
        $app['config']->set('logging.channels.single.path', storage_path('logs') . '/laravel.log');
    }
}
