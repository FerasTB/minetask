<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class LabOrderStatus extends Enum
{
    const Draft = 1;
    const Approved = 2;
    const Finished = 3;
    const Received = 4;
    const Canceled = 5;
}
