<?php

namespace Test;

use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use LaravelCode\Middleware\Factories\HttpClient;

trait TestTrait
{
    public function createClientToken($scopes = 'profile', $expires = null)
    {
        $privateKey = file_get_contents(realpath(dirname(__FILE__).'/oauth-private.key'));

        $payload = [
            'iss' => 'accounts.org',
            'aud' => 'accounts.com',
            'iat' => time(),
            'nbf' => time(),
            'exp' => $expires ?: time() + 3600,
            'scopes' => explode(' ', $scopes),
        ];

        return JWT::encode($payload, $privateKey, 'RS256');
    }

    public function assertRequests($requests = [], $assertions = [])
    {
        $this->assertEquals(count($requests), count($assertions), 'All requests should have an assertion.');

        foreach ($requests as $request) {
            $callback = array_shift($assertions);
            $callback($request['request'], $request['response'], $request['error'], $request['options']);
        }
    }

    public function createUserToken($scopes = 'profile', $expires = null)
    {
        $privateKey = file_get_contents(realpath(dirname(__FILE__).'/oauth-private.key'));

        $payload = [
            'aud' => 2, // GRANT ID
            'sub' => 1, // USER ID
            'iss' => 'accounts.org',
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
}
