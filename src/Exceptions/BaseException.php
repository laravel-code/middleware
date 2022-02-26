<?php

namespace LaravelCode\Middleware\Exceptions;

abstract class BaseException extends \Exception
{
    public function render($request)
    {
        return response()->json(['message' => $this->message], $this->code ?? 401);
    }
}
