<?php

namespace LaravelCode\Middleware\Facades;

use Illuminate\Support\Facades\Facade;

class ClientToken extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \LaravelCode\Middleware\ClientToken::class;
    }
}
