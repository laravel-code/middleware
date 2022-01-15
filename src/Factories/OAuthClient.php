<?php

namespace LaravelCode\Middleware\Factories;

use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use LaravelCode\Middleware\ClientToken;

class OAuthClient
{
    /**
     * @var ClientToken
     */
    protected $clientToken;
    /**
     * @var string
     */
    protected $access_token;
    /**
     * @var Request
     */
    protected Request $request;

    /**
     * OAuthClient constructor.
     * @param Request $request
     * @param ClientToken $clientToken
     */
    public function __construct(Request $request, ClientToken $clientToken)
    {
        $this->request = $request;
        $this->clientToken = $clientToken;
    }

    /**
     * @param string $method
     * @param string $domain
     * @param string $path
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function client(string $method, string $domain, string $path, array $params = []): mixed
    {
        assert(in_array($method, ['get', 'post', 'put', 'delete']), sprintf('Unknown request method %s', $method));

        $params = array_replace_recursive([
            'headers' => [
                'Authorization' => 'Bearer ' . $this->clientToken->getToken(),
                'Accept' => 'application/json',
                'X-USER-ID' => $this->request->user(),
                'X-CLIENT-ID' => config('oauth.client_id'),
            ],
        ], $params);

        /** @var Response $response */
        $response = call_user_func([Http::class, $method], $domain . $path, $params);
        if (in_array('application/json', $response->getHeader('Content-Type'))) {
            return json_decode((string)$response->getBody());
        }

        return $response->getBody()->getContents();
    }
}
