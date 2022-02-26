<?php

namespace LaravelCode\Middleware\Services;

class AccountService extends ApiService implements AccountsServiceInterface
{
    /**
     * @throws \Exception
     */
    public function getProfile(): mixed
    {
        return $this->request('get', 'profile');
    }

    /**
     * @throws \Exception
     */
    public function getByJti(string $jti): mixed
    {
        return $this->show('jti', $jti);
    }

    protected function getBaseUrl(): string
    {
        return config('oauth.host') . '/api/';
    }
}
