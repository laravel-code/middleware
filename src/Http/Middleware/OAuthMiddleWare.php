<?php

namespace LaravelCode\Middleware\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use LaravelCode\Middleware\Exceptions\OAuthProfileException;
use LaravelCode\Middleware\User;

class OAuthMiddleWare extends AbstractMiddleWare
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws \LaravelCode\Middleware\Exceptions\OAuthTokenInvalid|OAuthProfileException
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $this->parser->parseToken();
        $profile = $this->getProfile($token->claims()->get('jti'));

        if (empty($profile->client ?? null) && empty($profile->user ?? null)) {
            throw new OAuthProfileException();
        }

        if (!empty($profile->user)) {
            $request->setUserResolver(function () use ($profile) {
                $usr = new User();
                foreach (get_object_vars($profile->user) as $key => $value) {
                    $usr->setAttribute($key, $value);
                }
                $usr->is_client = false;

                return $usr;
            });

            return $next($request);
        }

        $request->setUserResolver(function () use ($profile) {
            $usr = new User();
            foreach (get_object_vars($profile->client) as $key => $value) {
                $usr->setAttribute($key, $value);
            }

            $usr->is_client = true;

            return $usr;
        });

        return $next($request);
    }
}
