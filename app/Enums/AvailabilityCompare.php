<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class AvailabilityCompare extends Enum
{
    const OutSide = 0;
    const HalfOutFromLeft = 1;
    const HalfOutFromRight = 2;
    const OutFromTowSide = 3;
    const EqualFromRight = 4;
    const EqualFromLeft = 5;
    const Same = 6;
    const InSide = 7;
}
