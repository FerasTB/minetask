<?php

namespace App\Http\Controllers\Api\DentalLab;

use App\Enums\AccountingProfileType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDentalLabSupplierAccountingProfileRequest;
use App\Http\Requests\StoreSupplierAccountingProfileRequest;
use App\Http\Resources\AccountingProfileResource;
use App\Models\DentalLab;
use Illuminate\Http\Request;

class AccountingProfileController extends Controller
{
    public function storeSupplier(StoreDentalLabSupplierAccountingProfileRequest $request)
    {
        $fields = $request->validated();
        $fields['type'] = AccountingProfileType::getValue($request->type);
        $lab = DentalLab::find($request->lab_id);
        $this->authorize('createForLab', [AccountingProfile::class, $lab]);
        $profile = $lab->accountingProfiles()->create($fields);
        return new AccountingProfileResource($profile);
    }
}
