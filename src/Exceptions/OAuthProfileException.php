<?php

namespace LaravelCode\Middleware\Exceptions;

class OAuthProfileException extends BaseException
{
    protected $message = 'Unable to get or decode profile data';

    protected $code = 403;
}
