<?php

namespace LaravelCode\Middleware;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

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
        App::bind('HttpClient', function () {
            return new \LaravelCode\Middleware\Factories\HttpClient;
        });

        App::bind('OAuthClient', function () {
            return new \LaravelCode\Middleware\Factories\OAuthClient(\HttpClient::getClient());
        });
    }
}
