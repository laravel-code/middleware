<?php

namespace LaravelCode\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use LaravelCode\Middleware\Factories\OAuthClient;
use LaravelCode\Middleware\Services\AccountService;

class MiddlewareProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom($this->configPath(), 'oauth');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([$this->configPath() => config_path('oauth.php')], 'oauth');
        }

        app()->bind(ClientToken::class, function () {
            return new ClientToken();
        });

        app()->bind(TokenParser::class, function () {
            return new TokenParser(
                app()->get(Request::class)
            );
        });

        app()->bind(OAuthClient::class, function () {
            return new OAuthClient(
                app()->get(Request::class),
                app()->get(ClientToken::class)
            );
        });

        app()->bind(AccountService::class, function () {
            return new AccountService(app()->get(OAuthClient::class));
        });
    }

    protected function configPath(): string
    {
        return __DIR__ . '/config/oauth.php';
    }
}
