<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class AccountingProfileType extends Enum
{
    const PatientAccount = 1;
    const SupplierAccount = 2;
    const ExpensesAccount = 3;
    const DentalLabSupplierAccount = 4;
    const DentalLabDoctorAccount = 5;
}
