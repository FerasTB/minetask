<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierItem extends Model
{
    use HasFactory;

    protected $fillable = ['accounting_profile_id', 'name', 'COA_id'];

    public function COA()
    {
        return $this->belongsTo(COA::class, 'COA_id');
    }

    public function supplierAccount()
    {
        return $this->belongsTo(AccountingProfile::class, 'accounting_profile_id');
    }
}
