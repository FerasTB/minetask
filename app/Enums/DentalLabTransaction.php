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
    const JournalVoucher = 10;

    public static $types = [
        self::SellInvoice => 'PINV',
        self::ResetVoucher => 'PREC',
        self::PaymentVoucher => 'PVOC',
        self::PercherInvoice => 'SINV',
        self::JournalVoucher => 'JV',
    ];

    public static function getNewValue($type)
    {
        return self::$types[$type] ?? 'Unknown';
    }
}
