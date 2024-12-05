<?php

namespace App\Models;

use App\Enums\AccountingProfileType;
use App\Enums\COASubType;
use App\Enums\DoubleEntryType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class COA extends Model
{
    use HasFactory;

    protected $fillable = ['dental_lab_id', 'group_id', 'sub_type', 'general_type', 'doctor_id', 'type', 'office_id', 'name', 'note', 'initial_balance'];

    const Cash = "Cash";
    const Payable =  "Payable";
    const Receivable =  "Receivable";
    const Capital =  "Capital";
    const OwnerWithDraw =  "OwnerWithDraw";
    const Inventory =  "Inventory";
    const COGS =  "Cost of Goods sold";

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function lab()
    {
        return $this->belongsTo(DentalLab::class, 'dental_lab_id');
    }

    public function group()
    {
        return $this->belongsTo(CoaGroup::class, 'group_id');
    }

    public function services()
    {
        return $this->hasMany(MedicalService::class, 'COA_id');
    }

    public function dentalLabServices()
    {
        return $this->hasMany(DentalLabService::class, 'COA_id');
    }

    public function doubleEntries()
    {
        return $this->hasMany(DoubleEntry::class, 'COA_id');
    }

    public function directDoubleEntries()
    {
        return $this->hasMany(DirectDoubleEntry::class, 'COA_id');
    }

    public function supplierItem()
    {
        return $this->hasMany(SupplierItem::class, 'COA_id');
    }

    public function getTotalAttribute()
    {
        $initialBalance = $this->initial_balance;
        $doubleEntries = $this->doubleEntries;
        $directDoubleEntries = $this->directDoubleEntries;


        // General Calculation
        $totalPositive = $doubleEntries->where('type', DoubleEntryType::Positive)->sum('total_price') + $directDoubleEntries->where('type', DoubleEntryType::Positive)->sum('total_price');
        $totalNegative = $doubleEntries->where('type', DoubleEntryType::Negative)->sum('total_price') + $directDoubleEntries->where('type', DoubleEntryType::Negative)->sum('total_price');
        $generalTotal = $initialBalance + $totalPositive - $totalNegative;

        // Fetch related accounting profiles based on doctor_id and office_id
        $relatedProfilesQuery = AccountingProfile::where('doctor_id', $this->doctor_id)
            ->where('office_id', $this->office_id);

        // Special Calculation for Payable Accounts
        if ($this->sub_type == COASubType::Payable) {
            $relatedProfiles = $relatedProfilesQuery->whereIn('type', [AccountingProfileType::SupplierAccount, AccountingProfileType::DentalLabDoctorAccount])->get();

            $profileTotal = $relatedProfiles->reduce(function ($carry, $profile) {
                $profileDoubleEntries = $profile->doubleEntries;
                $profileDirectDoubleEntries = $profile->directDoubleEntries;
                $profilePositive = $profileDoubleEntries->where('type', DoubleEntryType::Positive)->sum('total_price') +  $profileDirectDoubleEntries->where('type', DoubleEntryType::Positive)->sum('total_price');
                $profileNegative = $profileDoubleEntries->where('type', DoubleEntryType::Negative)->sum('total_price') + $profileDirectDoubleEntries->where('type', DoubleEntryType::Negative)->sum('total_price');
                return $carry + $profile->initial_balance + $profilePositive - $profileNegative;
            }, 0);

            return $profileTotal;
        }

        // Special Calculation for Receivable Accounts
        if ($this->sub_type == COASubType::Receivable) {
            $relatedProfiles = $relatedProfilesQuery->where('type', AccountingProfileType::PatientAccount)->get();

            $profileTotal = $relatedProfiles->reduce(function ($carry, $profile) {
                $profilePositive = $profile->doubleEntries()->where('type', DoubleEntryType::Positive)->sum('total_price');
                $profileNegative = $profile->doubleEntries()->where('type', DoubleEntryType::Negative)->sum('total_price');
                return $carry + $profile->initial_balance + $profilePositive - $profileNegative;
            }, 0);

            return $profileTotal;
        }

        // Return general total if no special case applies
        return $generalTotal;
    }


    public function calculateTotal($fromDate = null, $toDate = null)
    {
        // Calculate Opening Balance
        if ($fromDate) {
            $initialBalance = $this->getOpeningBalance($fromDate);
        } else {
            $initialBalance = $this->initial_balance;
        }

        // Fetch date-filtered double entries
        $doubleEntries = $this->doubleEntries()
            ->when($fromDate && $toDate, function ($query) use ($fromDate, $toDate) {
                $query->whereBetween('created_at', [$fromDate, $toDate]);
            })
            ->get();

        // Fetch date-filtered direct double entries
        $directDoubleEntries = $this->directDoubleEntries()
            ->when($fromDate && $toDate, function ($query) use ($fromDate, $toDate) {
                $query->whereBetween('created_at', [$fromDate, $toDate]);
            })
            ->get();

        // Calculate totals
        $totalPositive = $doubleEntries->where('type', DoubleEntryType::Positive)->sum('total_price')
            + $directDoubleEntries->where('type', DoubleEntryType::Positive)->sum('total_price');

        $totalNegative = $doubleEntries->where('type', DoubleEntryType::Negative)->sum('total_price')
            + $directDoubleEntries->where('type', DoubleEntryType::Negative)->sum('total_price');

        $generalTotal = $initialBalance + $totalPositive - $totalNegative;

        // Special Calculation for Payable Accounts
        if ($this->sub_type == COASubType::Payable) {
            $relatedProfiles = AccountingProfile::where('doctor_id', $this->doctor_id)
                ->where('office_id', $this->office_id)
                ->whereIn('type', [
                    AccountingProfileType::SupplierAccount,
                    AccountingProfileType::DentalLabDoctorAccount
                ])
                ->get();

            $profileTotal = $relatedProfiles->reduce(function ($carry, $profile) use ($fromDate, $toDate) {
                $profileDoubleEntries = $profile->doubleEntries()
                    ->when($fromDate && $toDate, function ($query) use ($fromDate, $toDate) {
                        $query->whereBetween('created_at', [$fromDate, $toDate]);
                    })
                    ->get();

                $profileDirectDoubleEntries = $profile->directDoubleEntries()
                    ->when($fromDate && $toDate, function ($query) use ($fromDate, $toDate) {
                        $query->whereBetween('created_at', [$fromDate, $toDate]);
                    })
                    ->get();

                $profilePositive = $profileDoubleEntries->where('type', DoubleEntryType::Positive)->sum('total_price')
                    + $profileDirectDoubleEntries->where('type', DoubleEntryType::Positive)->sum('total_price');

                $profileNegative = $profileDoubleEntries->where('type', DoubleEntryType::Negative)->sum('total_price')
                    + $profileDirectDoubleEntries->where('type', DoubleEntryType::Negative)->sum('total_price');

                $initialBalance = $fromDate
                    ? $profile->getOpeningBalance($fromDate)
                    : $profile->initial_balance;

                return $carry + $initialBalance + $profilePositive - $profileNegative;
            }, 0);

            return $profileTotal;
        }

        return $generalTotal;
    }


    public function getOpeningBalance($fromDate)
    {
        $doubleEntriesBefore = $this->doubleEntries()
            ->where('created_at', '<', $fromDate)
            ->get();

        $directDoubleEntriesBefore = $this->directDoubleEntries()
            ->where('created_at', '<', $fromDate)
            ->get();

        $totalPositive = $doubleEntriesBefore->where('type', DoubleEntryType::Positive)->sum('total_price')
            + $directDoubleEntriesBefore->where('type', DoubleEntryType::Positive)->sum('total_price');

        $totalNegative = $doubleEntriesBefore->where('type', DoubleEntryType::Negative)->sum('total_price')
            + $directDoubleEntriesBefore->where('type', DoubleEntryType::Negative)->sum('total_price');

        $openingBalance = $this->initial_balance + $totalPositive - $totalNegative;

        return $openingBalance;
    }
}
