<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class DentalDoctorTransaction extends Enum
{
    const PaymentVoucher = 5;
    const ResetVoucher = 6;
    const SellInvoice = 7;
    const PercherInvoice = 8;

    public static $types = [
        self::SellInvoice => 'PINV',
        self::ResetVoucher => 'PREC',
        self::PaymentVoucher => 'PVOC',
        self::PercherInvoice => 'SINV',
    ];

    public static function getNewValue($type)
    {
        return self::$types[$type] ?? 'Unknown';
    }
}
