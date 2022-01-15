<?php

namespace LaravelCode\Middleware\Http\Middleware;

use Illuminate\Cache\Repository;
use LaravelCode\Middleware\ClientToken;
use LaravelCode\Middleware\Services\AccountService;
use LaravelCode\Middleware\TokenParser;

class AbstractMiddleWare
{
    public AccountService $accountService;
    public Repository $cache;
    public TokenParser $parser;
    private ClientToken $clientToken;

    /**
     * AbstractOAuthMiddleware constructor.
     * @param ClientToken $clientToken
     * @param TokenParser $parser
     * @param AccountService $accountService
     * @param Repository $cache
     */
    public function __construct(ClientToken $clientToken, TokenParser $parser, AccountService $accountService, Repository $cache)
    {
        $this->clientToken = $clientToken;
        $this->parser = $parser;
        $this->accountService = $accountService;
        $this->cache = $cache;
    }

    /**
     * @param string $jti
     * @return array|mixed
     * @throws \Exception
     */
    public function getProfile(string $jti): mixed
    {
        return $this->cache->remember($jti, 300, function () use ($jti) {
            $clientToken = $this->clientToken->getToken();
            assert((bool) $clientToken, 'client token is not available.');

            return $this->accountService->getByJti($jti);
        });
    }
}
