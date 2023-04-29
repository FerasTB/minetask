<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class AppointmentStatus extends Enum
{
    const New = 1;
    const OnProcessing = 2;
    const Canceled = 3;
    const Done = 4;
}
