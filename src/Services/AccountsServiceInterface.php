<?php

namespace LaravelCode\Middleware\Services;

interface AccountsServiceInterface
{
    /**
     * @return mixed
     */
    public function getProfile(string $token);
}
