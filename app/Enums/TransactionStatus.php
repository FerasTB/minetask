<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class TransactionStatus extends Enum
{
    const Approved = 1;
    const Draft = 2;
    const Rejected = 3;
    const Canceled = 4;
    const Paid = 5;
    const Reversed = 6;
    const NoStatus = null;
}
