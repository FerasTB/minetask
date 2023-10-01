<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class DentalLabTransaction extends Enum
{
    const PaymentVoucher = 1;
    const ResetVoucher = 2;
    const SellInvoice = 3;
    const PercherInvoice = 4;
}
