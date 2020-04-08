<?php

namespace LaravelCode\Middleware\Services;

class AccountService extends ApiService
{
    public function getProfile($token)
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
