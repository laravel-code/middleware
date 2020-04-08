<?php

namespace LaravelCode\Middleware;

use Illuminate\Cache\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use LaravelCode\Middleware\Factories\HttpClient;
use LaravelCode\Middleware\Factories\OAuthClient;
use LaravelCode\Middleware\Services\AccountService;

class OauthProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/oauth.php', 'oauth'
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        app()->bind(HttpClient::class, function () {
            return (new HttpClient)->getClient();
        });

        app()->bind(OAuthClient::class, function () {
            return new OAuthClient(
                app()->get(HttpClient::class),
                app()->get(Request::class),
                app()->get(Repository::class)
            );
        });

        app()->bind(AccountService::class, function () {
            return new AccountService(app()->get(OAuthClient::class));
        });
    }
}
