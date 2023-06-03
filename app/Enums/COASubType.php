<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class COASubType extends Enum
{
    const Cash = 1;
    const Receivable = 2;
    const Payable = 3;
}
