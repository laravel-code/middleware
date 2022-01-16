<?php

namespace LaravelCode\Middleware\Contracts;

interface RoleCheckProviderContract
{
    public function check(mixed $profile, string $role, string $permission = null): bool;
}
