<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class MaritalStatus extends Enum
{
    const Married = 1;
    const Single = 2;
    const Divorced = 3;
    const engaged = 4;
}
