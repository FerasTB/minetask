<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class Specialization extends Enum
{
    const Dentist = 1;
    const OptionTwo = 2;
    const OptionThree = 3;
}
