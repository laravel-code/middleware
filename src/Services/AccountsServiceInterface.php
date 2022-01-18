<?php

namespace LaravelCode\Middleware\Services;

interface AccountsServiceInterface
{
    public function getProfile(): mixed;

    public function getByJti(string $jti): mixed;
}
