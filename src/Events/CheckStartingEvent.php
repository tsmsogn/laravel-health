<?php

namespace Spatie\Health\Events;

use Spatie\Health\Checks\Check;

class CheckStartingEvent
{
    public Check $check;

    public function __construct(Check $check)
    {
        $this->check = $check;
    }
}
