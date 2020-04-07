<?php

namespace LaravelCode\Middleware\Facades;

use Illuminate\Support\Facades\Facade;

class HttpClient extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'HttpClient';
    }
}
