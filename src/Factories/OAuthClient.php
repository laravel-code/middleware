<?php

namespace LaravelCode\Middleware\Factories;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Cache\Repository;
use Illuminate\Http\Request;
use LaravelCode\Middleware\Exceptions\OauthClientContentTypeException;

class OAuthClient
{
    /**
     * @var string
     */
    protected $access_token;
    /**
     * @var int
     */
    protected $expires_in;
    /**
     * @var string
     */
    protected $token_type;
    /**
     * @var Client
     */
    protected $httpClient;
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var Repository
     */
    protected $cache;

    /**
     * OAuthClient constructor.
     * @param $httpClient
     * @param $request
     * @param $cache
     */
    public function __construct($httpClient, $request, $cache)
    {
        $this->httpClient = $httpClient;
        $this->request = $request;
        $this->cache = $cache;
    }

    /**
     * @return string
     */
    public function setUp()
    {
        // Token is about to expire we will request a new token.
        if ($this->cache->has('apiClient') && $this->access_token && $this->expires_in <= time() + 60) {
            $this->cache->forget('apiClient');
        }

        $response = $this->cache->remember('apiClient', 300, function () {
            $response = $this->httpClient->post(config('oauth.host').config('oauth.token'), [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => config('oauth.client_id'),
                    'client_secret' => config('oauth.client_secret'),
                    'scope' => config('oauth.scopes'),
                ],
            ]);
            if (! stristr($response->getHeaderLine('Content-Type'), 'application/json')) {
                throw new OauthClientContentTypeException('JSON accepted but received '.$response->getHeaderLine('Content-Type'), 500);
            }
            $data = json_decode((string) $response->getBody());

            if (! $data || ! $data->access_token) {
                throw new AuthorizationException('Api client could not get authorized', 401);
            }

            return $data;
        });

        $this->access_token = $response->access_token;
        $this->expires_in = $response->expires_in;
        $this->token_type = $response->token_type;

        return $this->access_token;
    }

    /**
     * @param string $method
     * @param string $domain
     * @param string $path
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function client(string $method, string $domain, string $path, array $params = [])
    {
        $params = array_replace_recursive([
            'headers' => [
                'Authorization' => 'Bearer '.$this->access_token,
                'Accept' => 'application/json',
                'X-USER-ID' => $this->request->user(),
                'X-CLIENT-ID' => config('oauth.client_id'),
            ],
        ], $params);

        /** @var Response $response */
        $response = call_user_func([$this->httpClient, $method], $domain.$path, $params);
        if (in_array('application/json', $response->getHeader('Content-Type'))) {
            return json_decode((string) $response->getBody());
        }

        return $response->getBody()->getContents();
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->access_token;
    }

    /**
     * @return string
     */
    public function getExpiresIn()
    {
        return $this->expires_in;
    }

    /**
     * @return string
     */
    public function getTokenType()
    {
        return $this->token_type;
    }
}
