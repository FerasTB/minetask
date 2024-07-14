<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class TransactionType extends Enum
{
    const PatientInvoice = 1; // sell invoice
    const PatientReceipt = 2; // reset voucher
    const PaymentVoucher = 3;
    const SupplierInvoice = 4; // percher invoice

    public static $types = [
        self::PatientInvoice => 'sell invoice',
        self::PatientReceipt => 'reset voucher',
        self::PaymentVoucher => 'payment voucher',
        self::SupplierInvoice => 'purchase invoice',
    ];

    public static function getNewValue($type)
    {
        return self::$types[$type] ?? 'Unknown';
    }
}
