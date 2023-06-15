<?php

namespace App\Observers;

use App\Enums\AccountingProfileType;
use App\Models\AccountingProfile;
use Illuminate\Support\Facades\Cache;

class AccountingProfileObserver
{
    /**
     * Handle the AccountingProfile "created" event.
     */
    public function created(AccountingProfile $accountingProfile): void
    {
        if ($accountingProfile->type == AccountingProfileType::PatientAccount) {
            Cache::forget('patient');
        }
    }

    /**
     * Handle the AccountingProfile "updated" event.
     */
    public function updated(AccountingProfile $accountingProfile): void
    {
        //
    }

    /**
     * Handle the AccountingProfile "deleted" event.
     */
    public function deleted(AccountingProfile $accountingProfile): void
    {
        //
    }

    /**
     * Handle the AccountingProfile "restored" event.
     */
    public function restored(AccountingProfile $accountingProfile): void
    {
        //
    }

    /**
     * Handle the AccountingProfile "force deleted" event.
     */
    public function forceDeleted(AccountingProfile $accountingProfile): void
    {
        //
    }
}
