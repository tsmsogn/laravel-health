<?php

namespace Spatie\Health\Events;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class CheckEndedEvent
{
    public Check $check;
    public Result $result;

    public function __construct(
        Check $check,
        Result $result
    ) {
        $this->check = $check;
        $this->result = $result;
    }
}
