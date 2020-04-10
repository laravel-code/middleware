<?php

namespace TestApp\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Laravel\Passport\Http\Middleware\CheckClientCredentials;
use LaravelCode\Middleware\Http\Middleware\OAuth;
use LaravelCode\Middleware\Http\Middleware\OAuthClient;
use LaravelCode\Middleware\Http\Middleware\OAuthUser;

class Kernel extends HttpKernel
{
    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'oauth.client' => OAuthClient::class,
        'oauth.user' => OAuthUser::class,
        'oauth' => OAuth::class,
        'oauth.api' => CheckClientCredentials::class,
    ];
}
