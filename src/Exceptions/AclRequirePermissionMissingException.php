<?php

namespace LaravelCode\Middleware\Exceptions;

class AclRequirePermissionMissingException extends BaseException
{
    protected $code = 403;
}
