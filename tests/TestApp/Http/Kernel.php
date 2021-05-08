<?php

namespace TestApp\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use LaravelCode\Middleware\Http\Middleware\CheckApiCredentials;
use LaravelCode\Middleware\Http\Middleware\OAuth;
use LaravelCode\Middleware\Http\Middleware\OAuthClient;
use LaravelCode\Middleware\Http\Middleware\OAuthOrGuest;
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
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'oauth.client' => OAuthClient::class,
        'oauth.user' => OAuthUser::class,
        'oauth' => OAuth::class,
        'oauth.guest' => OAuthOrGuest::class,
        'oauth.api' => CheckApiCredentials::class,
    ];
}
