<?php

namespace Spatie\Health\Enums;

use Spatie\Enum\Enum;

/**
 * @method static self ok()
 * @method static self warning()
 * @method static self failed()
 * @method static self crashed()
 * @method static self skipped()
 */
class Status extends Enum
{
    public function getSlackColor(): string
    {
        switch ($this)
        {
            case self::ok(): return '#2EB67D';
            case self::warning(): return '#ECB22E';
            case self::failed():
            case self::crashed(): return '#E01E5A';
        }

        return '';
    }
}
