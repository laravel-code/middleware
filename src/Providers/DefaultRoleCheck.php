<?php

namespace LaravelCode\Middleware\Providers;

use LaravelCode\Middleware\Contracts\RoleCheckProviderContract;
use LaravelCode\Middleware\Exceptions\AclRequirePermissionMissingException;
use LaravelCode\Middleware\Exceptions\AclRequireRoleMissingException;

class DefaultRoleCheck implements RoleCheckProviderContract
{
    public function check(mixed $profile, string $role, string $permission = null):bool
    {
        $permissionToCheck = 'req_' . $permission;
        foreach ($profile->roles ?? [] as $availableRole) {
            if ($availableRole->name === $role) {
                $hasPermission = $availableRole->pivot->{$permissionToCheck} ?? false;

                if ($hasPermission) {
                    return true;
                }

                throw new AclRequirePermissionMissingException(sprintf('User does not have required permission(%s) on role(%s)', $permission, $role), 403);
            }
        }

        throw new AclRequireRoleMissingException(sprintf('User does not have required role(%s)', $role), 403);
    }
}
