<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class COAType extends Enum
{
    const Current = 1;
    const NonCurrent = 2;
    const Capital = 3;
    const OwnerWithdraw = 4;
    const Type = null;
    // const Equity = 3;
    // const Revenue = 4;
    // const Expenses = 5;
}
