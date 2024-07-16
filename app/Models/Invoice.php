<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    use \Znck\Eloquent\Traits\BelongsToThrough;

    protected $fillable = ['teeth_record_id', 'type', 'status', 'invoice_number', 'note', 'date_of_invoice', 'total_price', 'doctor_id', 'accounting_profile_id', 'running_balance'];

    public function patient()
    {
        return $this->belongsToThrough(
            Patient::class,
            AccountingProfile::class,
            null,
            '',
            [
                AccountingProfile::class => 'accounting_profile_id',
                Patient::class => 'patient_id'
            ]
        );
    }

    public function doctor()
    {
        return $this->belongsToThrough(
            Doctor::class,
            AccountingProfile::class,
            null,
            '',
            [
                AccountingProfile::class => 'accounting_profile_id',
                Doctor::class => 'doctor_id'
            ]
        );
    }

    public function lab()
    {
        return $this->belongsToThrough(
            DentalLab::class,
            AccountingProfile::class,
            null,
            '',
            [
                AccountingProfile::class => 'accounting_profile_id',
                DentalLab::class => 'dental_lab_id'
            ]
        );
    }

    public function office()
    {
        return $this->belongsToThrough(
            Office::class,
            AccountingProfile::class,
            null,
            '',
            [
                AccountingProfile::class => 'accounting_profile_id',
                Office::class => 'office_id'
            ]
        );
    }

    public function account()
    {
        return $this->belongsTo(AccountingProfile::class, 'accounting_profile_id');
    }

    public function record()
    {
        return $this->belongsTo(TeethRecord::class, 'teeth_record_id');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    public function receipts()
    {
        return $this->belongsToMany(Receipt::class, 'invoice_receipt')
            ->withPivot(['total_price']);
    }


    // public function setInvoiceNumberAttribute()
    // {
    //     if ($this->patient != null) {
    //         $this->attributes['invoice_number'] = Invoice::with(['office', 'doctor'])
    //             ->where('office.id', $this->office->attributes['id'])
    //             ->where('doctor.id', $this->doctor->id)
    //             ->has('patient')
    //             ->max('invoice_number') + 1;
    //     } else {
    //         // $this->attributes['invoice_number'] = Invoice::whereHas('office', function ($query) {
    //         //     return $query->where('id', '=', $this->office->id);
    //         // })
    //         //     ->whereHas('doctor', function ($query) {
    //         //         return $query->where('id', '=', $this->doctor->id);
    //         //     })
    //         //     ->doesntHave('patient')
    //         //     ->max('invoice_number') + 1;
    //         $this->attributes['invoice_number'] = Invoice::with(['account.office', 'account.doctor'])
    //             ->where('account.office.id', $this->account->office->id)
    //             ->where('doctor.id', $this->doctor->id)
    //             ->doesntHave('patient')
    //             ->max('invoice_number') + 1;
    //     }
    // }

    public function getInvoiceNumberAttribute()
    {
        return str_pad($this->attributes['invoice_number'], 5, '0', STR_PAD_LEFT);
    }
}
