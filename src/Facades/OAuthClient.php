<?php

namespace LaravelCode\Middleware\Facades;

use Illuminate\Support\Facades\Facade;

class OAuthClient extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \LaravelCode\Middleware\Factories\OAuthClient::class;
    }
}
