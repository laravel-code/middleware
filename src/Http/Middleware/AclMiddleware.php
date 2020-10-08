<?php

namespace LaravelCode\Middleware\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use LaravelCode\Middleware\Exceptions\AclRequirePermissionMissingException;
use LaravelCode\Middleware\Exceptions\AclRequireRoleMissingException;
use LaravelCode\Middleware\Exceptions\OAuthTokenExpired;

class AclMiddleware
{
    /**
     * Check if user has permission to access the protect route.
     *
     * middleware('acl:project,write')
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
     *
     * @param Request $request
     * @param Closure $next
     * @param string $role
     * @param string $permission
     * @return mixed
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next, string $role, string $permission)
    {
        $user = $request->user();
        foreach ($user->roles ?? [] as $availableRole) {
            if ($availableRole->name === $role) {
                $hasPermission = $availableRole->pivot->{$permission} ?? false;

                if ($hasPermission) {
                    return $next($request);
                }

                throw new AclRequirePermissionMissingException(sprintf('User does not have required permission(%s) on role(%s)', $permission, $role));
            }
        }

        throw new AclRequireRoleMissingException(sprintf('User does not have required role(%s)', $role));
    }
}
