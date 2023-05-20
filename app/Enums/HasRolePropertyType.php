<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class HasRolePropertyType extends Enum
{
    const PatientInfo = 1;
    const Appointment = 2;
    const Income = 3;
}
