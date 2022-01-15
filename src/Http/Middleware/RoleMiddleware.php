<?php

namespace LaravelCode\Middleware\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use LaravelCode\Middleware\Exceptions\AclRequirePermissionMissingException;
use LaravelCode\Middleware\Exceptions\AclRequireRoleMissingException;
use LaravelCode\Middleware\Exceptions\OAuthProfileException;

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
        $profile = $request->user();
        if (!$profile) {
            throw new OAuthProfileException();
        }

        if (!$permission) {
            $permission = strtolower($request->method());
        }

        $permissionToCheck = 'req_' . $permission;
        foreach ($profile->roles ?? [] as $availableRole) {
            if ($availableRole->name === $role) {
                $hasPermission = $availableRole->pivot->{$permissionToCheck} ?? false;

                if ($hasPermission) {
                    return $next($request);
                }

                throw new AclRequirePermissionMissingException(sprintf('User does not have required permission(%s) on role(%s)', $permission, $role), 403);
            }
        }

        throw new AclRequireRoleMissingException(sprintf('User does not have required role(%s)', $role), 403);
    }
}
