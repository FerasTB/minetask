<?php

namespace App\Http\Controllers\Api;

use App\Enums\AccountingProfileType;
use App\Enums\COAType;
use App\Enums\OfficeType;
use App\Http\Controllers\Controller;
use App\Http\Requests\SetInitialBalanceRequest;
use App\Http\Requests\StoreCoaAccountingProfileRequest;
use App\Http\Requests\StoreExpensesAccountingProfileRequest;
use App\Http\Requests\StoreSupplierAccountingProfileRequest;
use App\Http\Resources\AccountingProfileResource;
use App\Http\Resources\DentalLabAccountingProfileResource;
use App\Models\AccountingProfile;
use App\Models\Doctor;
use App\Models\HasRole;
use App\Models\Office;
use App\Models\User;
use Illuminate\Http\Request;

class AccountingProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $office = Office::findOrFail($request->office);
        $this->authorize('inOffice', [AccountingProfile::class, $office]);
        if ($office->type == OfficeType::Separate) {
            $doctor = auth()->user()->doctor;
            // return $doctor->accountingProfiles;
            return AccountingProfileResource::collection($doctor->accountingProfiles);
        } else {
            return AccountingProfileResource::collection($office->accountingProfiles);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(AccountingProfile $accounting)
    {
        return new AccountingProfileResource($accounting);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AccountingProfile $accountingProfile)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AccountingProfile $accountingProfile)
    {
        //
    }

    public function storeSupplier(StoreSupplierAccountingProfileRequest $request)
    {
        $fields = $request->validated();
        $fields['type'] = AccountingProfileType::getValue($request->type);
        if ($request->doctor_id) {
            $doctor = Doctor::find($request->doctor_id);
            $this->authorize('createForDoctor', [AccountingProfile::class, $doctor]);
            $profile = $doctor->accountingProfiles()->create($fields);
            return new AccountingProfileResource($profile);
        }
        $office = Office::find($request->office_id);
        $this->authorize('createForOffice', [AccountingProfile::class, $office]);
        $profile = $office->accountingProfiles()->create($fields);
        return new AccountingProfileResource($profile);
    }

    public function storeExpenses(StoreExpensesAccountingProfileRequest $request)
    {
        $fields = $request->validated();
        $fields['type'] = AccountingProfileType::getValue($request->type);
        if ($request->doctor_id) {
            $doctor = Doctor::find($request->doctor_id);
            $this->authorize('createForDoctor', [AccountingProfile::class, $doctor]);
            $profile = $doctor->accountingProfiles()->create($fields);
            return new AccountingProfileResource($profile);
        }
        $office = Office::find($request->office_id);
        $this->authorize('createForOffice', [AccountingProfile::class, $office]);
        $profile = $office->accountingProfiles()->create($fields);
        return new AccountingProfileResource($profile);
    }

    // public function storeCoa(StoreCoaAccountingProfileRequest $request)
    // {
    //     $fields = $request->validated();
    //     if ($request->doctor_id) {
    //         $doctor = Doctor::find($request->doctor_id);
    //         $this->authorize('createForDoctor', [AccountingProfile::class, $doctor]);
    //         $profile = $doctor->accountingProfiles()->create($fields);
    //         return $profile;
    //     }
    //     $office = Office::find($request->office_id);
    //     $this->authorize('createForOffice', [AccountingProfile::class, $office]);
    //     $profile = $office->accountingProfiles()->create($fields);
    //     return $profile;
    // }

    public function patientProfile(Request $request)
    {
        $office = Office::findOrFail($request->office);
        $this->authorize('inOffice', [AccountingProfile::class, $office]);
        if ($office->type == OfficeType::Separate) {
            $doctor = auth()->user()->doctor;
            // return $doctor->accountingProfiles;
            $accounts = AccountingProfile::where(['doctor_id' => auth()->user()->doctor->id, 'office_id' => $office->id, 'type' => AccountingProfileType::PatientAccount])
                ->with([
                    'invoices', 'invoices.items', 'receipts', 'office', 'doctor', 'office.owner', 'patient', 'patient.doctorImage', 'invoices.receipts'
                ])
                ->get();
            // return AccountingProfileResource::collection($doctor->accountingProfiles)->where(['office_id' => $office->id, 'type' => AccountingProfileType::PatientAccount]);
            return AccountingProfileResource::collection($accounts);
        } else {
            $ownerUser = User::find($office->owner->user_id);
            $ownerDoctor = $ownerUser->doctor;
            $accounts = AccountingProfile::where(['doctor_id' => $ownerDoctor->id, 'office_id' => $office->id, 'type' => AccountingProfileType::PatientAccount])
                ->with([
                    'invoices', 'invoices.items', 'receipts', 'office', 'doctor', 'office.owner', 'patient', 'patient.doctorImage', 'invoices.receipts'
                ])
                ->get();
            // return AccountingProfileResource::collection($office->accountingProfiles)->where('type', AccountingProfileType::PatientAccount);
            return AccountingProfileResource::collection($accounts);
        }
    }

    public function supplierProfile(Request $request)
    {
        $office = Office::findOrFail($request->office);
        $this->authorize('inOffice', [AccountingProfile::class, $office]);
        if ($office->type == OfficeType::Separate) {
            $doctor = auth()->user()->doctor;
            $accounts = $doctor->accountingProfiles;
            $accounts->load(['invoices', 'invoices.items', 'receipts', 'office', 'doctor', 'office.owner']);
            return AccountingProfileResource::collection($accounts)->where('type', AccountingProfileType::SupplierAccount);
        } else {
            $accounts = $office->accountingProfiles;
            $accounts->load(['invoices', 'invoices.items', 'receipts', 'office', 'doctor', 'office.owner']);
            return AccountingProfileResource::collection($accounts)->where('type', AccountingProfileType::SupplierAccount);
        }
    }

    public function expensesProfile(Request $request)
    {
        $office = Office::findOrFail($request->office);
        $this->authorize('inOffice', [AccountingProfile::class, $office]);
        if ($office->type == OfficeType::Separate) {
            $doctor = auth()->user()->doctor;
            $accounts = $doctor->accountingProfiles;
            $accounts->load(['invoices', 'invoices.items', 'receipts', 'office', 'doctor', 'office.owner']);
            return AccountingProfileResource::collection($accounts)->where('type', AccountingProfileType::ExpensesAccount);
        } else {
            $accounts = $office->accountingProfiles;
            $accounts->load(['invoices', 'invoices.items', 'receipts', 'office', 'doctor', 'office.owner']);
            return AccountingProfileResource::collection($accounts)->where('type', AccountingProfileType::ExpensesAccount);
        }
    }

    public function labProfile(Office $office)
    {
        $this->authorize('inOffice', [AccountingProfile::class, $office]);
        // if ($office->type == OfficeType::Separate) {
        $doctor = auth()->user()->doctor;
        $accounts = $doctor->accountingProfiles()->where('type', AccountingProfileType::DentalLabDoctorAccount)->get();
        $accounts->load(['invoices', 'invoices.items', 'receipts', 'lab', 'labOrders', 'labOrders.details', 'labOrders.details.teeth', 'labOrders.orderSteps']);
        return DentalLabAccountingProfileResource::collection($accounts);
        // } else {
        //     $accounts = $office->accountingProfiles;
        //     $accounts->load(['invoices', 'invoices.items', 'receipts', 'lab']);
        //     return AccountingProfileResource::collection($accounts)->where('type', AccountingProfileType::DentalLabDoctorAccount);
        // }
    }

    public function setInitialBalance(SetInitialBalanceRequest $request, AccountingProfile $accounting)
    {
        $fields = $request->validated();
        $this->authorize('update', [$accounting]);
        if ($accounting->initial_balance != 0) {
            return response('the initial balance only can be set once', 403);
        }
        $accounting->update($fields);
        return new AccountingProfileResource($accounting);
    }

    public function accountOutcome(AccountingProfile $accounting)
    {
        $this->authorize('view', [$accounting]);
        $positive = $accounting->invoices();
        $totalPositive = $positive != null ?
            $positive->sum('total_price') : 0;
        $negative = $accounting->receipts();
        $totalNegative = $negative != null ?
            $negative->sum('total_price') : 0;
        $total = $totalPositive - $totalNegative + $accounting->initial_balance;
        return response()->json([
            'account' => new AccountingProfileResource($accounting),
            'total' => $total,
        ]);
    }

    public static function accountOutcomeInt(int $id)
    {
        $accounting = AccountingProfile::findOrFail($id);
        $positive = $accounting->invoices();
        $totalPositive = $positive != null ?
            $positive->sum('total_price') : 0;
        $negative = $accounting->receipts();
        $totalNegative = $negative != null ?
            $negative->sum('total_price') : 0;
        $total = $totalPositive - $totalNegative + $accounting->initial_balance;
        return $total;
    }
}
