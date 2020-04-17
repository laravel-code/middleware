<?php

namespace Test;

use Firebase\JWT\JWT;
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
        $app['config']->set('logging.channels.single.path', storage_path('logs').'/laravel.log');
    }
}
