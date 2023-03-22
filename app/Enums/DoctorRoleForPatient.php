<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class DoctorRoleForPatient extends Enum
{
    const DoctorWithoutApprove = 1;
    const DoctorWithApprove = 2;
    // const OptionThree = 2;
}
