<?php

namespace App\Models;

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
}
