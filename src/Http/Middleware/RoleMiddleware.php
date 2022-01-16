<?php

namespace LaravelCode\Middleware\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use LaravelCode\Middleware\Contracts\RoleCheckProviderContract;
use LaravelCode\Middleware\Exceptions\AclRequirePermissionMissingException;
use LaravelCode\Middleware\Exceptions\AclRequireRoleMissingException;
use LaravelCode\Middleware\Exceptions\OAuthProfileException;
use LaravelCode\Middleware\Providers\DefaultRoleCheck;

/**
 * Check if user has permission to access the protected route.
 *
 * middleware('role:project,write')
 *
 * {
 * "id": 1,
 * "name": "User",
 * "roles": [
 *      {
 *          "id": 1,
 *          "name": "project",
 *          "private": 0,
 *          "init_employee": 1,
 *          "created_at": "2020-10-08T23:09:58.000000Z",
 *          "updated_at": "2020-10-08T23:09:56.000000Z",
 *          "pivot": {
 *              "user_id": 1,
 *              "role_id": 1,
 *              "read": true,
 *              "write": true,
 *              "update": true,
 *              "delete": true
 *              }
 *      }
 * ]
 * }
 */
class RoleMiddleware extends AbstractMiddleWare
{
    protected static RoleCheckProviderContract $provider;

    public static function setProvider(RoleCheckProviderContract $provider): void
    {
        static::$provider = $provider;
    }

    public function getProvider(): RoleCheckProviderContract
    {
        return static::$provider;
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @param string $role
     * @param string|null $permission
     * @return mixed
     * @throws AclRequirePermissionMissingException
     * @throws AclRequireRoleMissingException
     * @throws OAuthProfileException
     */
    public function handle(Request $request, Closure $next, string $role, string $permission = null)
    {
        if (!isset(static::$provider)) {
            static::setProvider(new DefaultRoleCheck());
        }

        $profile = $request->user();
        if (!$profile) {
            throw new OAuthProfileException();
        }

        if (!$permission) {
            $permission = strtolower($request->method());
        }

        if (static::$provider->check($profile, $role, $permission)) {
            return $next($request);
        }

        throw new AclRequireRoleMissingException(sprintf('User does not have required role(%s)', $role), 403);
    }
}
