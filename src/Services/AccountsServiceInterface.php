<?php

namespace LaravelCode\Middleware\Services;

interface AccountsServiceInterface
{
    public function getProfile(string $token): mixed;

    public function getByJti(string $jti): mixed;
}
