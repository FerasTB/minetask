<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class COAGeneralType extends Enum
{
    const Asset = 1;
    const Liability = 2;
    const Equity = 3;
    const Revenue = 4;
    const Expenses = 5;
}
