<?php

namespace LaravelCode\Middleware\Exceptions;

class JsonException extends BaseException
{
    protected $message = 'Unable to decode data as json.';

    protected $code = 500;
}
