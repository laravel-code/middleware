<?php

namespace LemonCMS\LaravelCrud;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use LemonCMS\LaravelCrud\Factories\OAuthClient;

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
            __DIR__.'/config/middleware.php', 'middleware'
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        App::bind('OAuthClient', function () {
            return new OAuthClient;
        });
    }
}
