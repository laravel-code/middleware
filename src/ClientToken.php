<?php

namespace LaravelCode\Middleware;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use LaravelCode\Middleware\Exceptions\JsonException;
use LaravelCode\Middleware\Exceptions\OauthClientContentTypeException;

class ClientToken
{
    public static string $cacheKey = 'oauth.clientToken';

    public function __construct(
    ) {
        assert((bool) config('oauth.host'), 'oauth.host should be set in config/oauth.php');
        assert((bool) config('oauth.path'), 'oauth.path should be set in config/oauth.php');
        assert((bool) config('oauth.client_id'), 'oauth.client_id should be set in config/oauth.php');
        assert((bool) config('oauth.client_secret'), 'oauth.client_secret should be set in config/oauth.php');
        assert((bool) config('oauth.scopes'), 'oauth.scopes should be set in config/oauth.php');
    }

    /**
     * @return mixed
     */
    public function getToken(): mixed
    {
        return Cache::remember(self::$cacheKey, 600, function () {
            $response = $this->getResponse();

            if ($response->expires_in <= time() + 60) {
                Cache::forget('oauth.clientResponse');

                $response = $this->getResponse();
            }

            return $response->access_token;
        });
    }

    /**
     * @return mixed
     */
    private function getResponse(): mixed
    {
        return Cache::remember('oauth.clientResponse', 600, function (): mixed {
            $response = Http::post(config('oauth.host') . config('oauth.path'), [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => config('oauth.client_id'),
                    'client_secret' => config('oauth.client_secret'),
                    'scope' => config('oauth.scopes'),
                ],
            ]);

            if (!stristr($response->header('Content-Type'), 'application/json')) {
                throw new OauthClientContentTypeException('JSON accepted but received ' . $response->header('Content-Type'), 500);
            }

            $json = json_decode($response->body());

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new JsonException();
            }

            if (!$json || !isset($json->access_token)) {
                throw new AuthorizationException('Api client could not get authorized', 401);
            }

            // @phpstan-ignore-next-line
            return $json;
        });
    }
}
