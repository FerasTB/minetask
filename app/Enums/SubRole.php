<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class SubRole extends Enum
{
    const OfficeOwner = 1;
    const DoctorInOffice = 2;
    const OfficeSecretary = 3;
    const DentalLabOwner = 4;
    const DentalLabDraft = 5;
    const DentalLabTechnician = 6;
    const AdminInOffice = 7;
}
