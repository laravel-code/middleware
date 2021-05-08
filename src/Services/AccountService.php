<?php

namespace LaravelCode\Middleware\Services;

class AccountService extends ApiService implements AccountsServiceInterface
{
    public function getProfile(string $token)
    {
        $headers = [
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ];

        return $this->request('get', 'profile', [], [], $headers);
    }

    protected function getBaseUrl()
    {
        return config('oauth.host').'/api/';
    }
}
