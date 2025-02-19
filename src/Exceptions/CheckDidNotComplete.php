<?php

namespace Spatie\Health\Exceptions;

use Exception;
use Spatie\Health\Checks\Check;

class CheckDidNotComplete extends Exception
{
    public static function make(Check $check, Exception $exception): self
    {
        return new self(
            "The check named `{$check->getName()}` did not complete. An exception was thrown with this message: `{$exception->getMessage()}`",
            0,
            $exception
        );
    }
}
