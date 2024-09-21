<?php

namespace App\Http\Controllers\Api;

use App\Enums\AccountingProfileType;
use App\Enums\COAGeneralType;
use App\Enums\COASubType;
use App\Enums\COAType;
use App\Enums\DoubleEntryType;
use App\Enums\OfficeType;
use App\Http\Controllers\Controller;
use App\Http\Requests\SetInitialBalanceRequest;
use App\Http\Requests\StoreCOARequest;
use App\Http\Requests\UpdateCoaRequest;
use App\Http\Resources\COAResource;
use App\Http\Resources\DoubleEntryResource;
use App\Models\AccountingProfile;
use App\Models\COA;
use App\Models\Doctor;
use App\Models\EmployeeSetting;
use App\Models\HasRole;
use App\Models\Office;
use Illuminate\Http\Request;

class COAController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $office = Office::findOrFail($request->office);
        if (auth()->user()->currentRole->name == 'DentalDoctorTechnician') {
            // Find the role based on user_id and office_id (roleable_id)
            $role = HasRole::where('user_id', auth()->id())
                ->where('roleable_id', $office->id)
                ->first();

            if (!$role) {
                // Return JSON response if no role is found
                return response()->json([
                    'error' => 'Role not found for the given user and office.',
                ], 403);
            }

            // Find the employee setting based on the has_role_id
            $employeeSetting = EmployeeSetting::where('has_role_id', $role->id)->first();

            if (!$employeeSetting) {
                // Return JSON response if no employee setting is found
                return response()->json([
                    'error' => 'Employee setting not found for the given role.',
                ], 403);
            }
            $doctor = Doctor::findOrFail($employeeSetting->doctor_id);
            $user = $doctor->user;
        } else {
            // Ensure a valid doctor is authenticated
            $doctor = auth()->user()->doctor;
            $user = auth()->user();
        }

        if (!$doctor) {
            return response('You have to complete your info', 404);
        }
        $this->authorize('inOffice', [COA::class, $office]);
        if ($office->type == OfficeType::Separate) {
            return COAResource::collection(
                $doctor->COAS()
                    ->where('office_id', $office->id)
                    ->with([
                        'doctor',
                        'office',
                        'doubleEntries',
                        'directDoubleEntries'
                    ])
                    ->get()
            );
        } else {
            return COAResource::collection($office->COAS()->with(['office', 'doubleEntries', 'directDoubleEntries'])->get());
        }
    }

    public function indexOwner(Request $request)
    {
        $office = Office::findOrFail($request->office);
        $this->authorize('officeOwner', [COA::class, $office]);
        return COAResource::collection(
            $office->COAS()
                ->with([
                    'office',
                    'doubleEntries',
                ])
                ->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCOARequest $request)
    {
        $fields = $request->validated();
        $office = Office::find($request->office_id);
        if (auth()->user()->currentRole->name == 'DentalDoctorTechnician') {
            // Find the role based on user_id and office_id (roleable_id)
            $role = HasRole::where('user_id', auth()->id())
                ->where('roleable_id', $office->id)
                ->first();

            if (!$role) {
                // Return JSON response if no role is found
                return response()->json([
                    'error' => 'Role not found for the given user and office.',
                ], 403);
            }

            // Find the employee setting based on the has_role_id
            $employeeSetting = EmployeeSetting::where('has_role_id', $role->id)->first();

            if (!$employeeSetting) {
                // Return JSON response if no employee setting is found
                return response()->json([
                    'error' => 'Employee setting not found for the given role.',
                ], 403);
            }
            $doctor = Doctor::findOrFail($employeeSetting->doctor_id);
            $user = $doctor->user;
        } else {
            // Ensure a valid doctor is authenticated
            $doctor = auth()->user()->doctor;
            $user = auth()->user();
        }

        if (!$doctor) {
            return response('You have to complete your info', 404);
        }
        $fields['general_type'] = COAGeneralType::getValue($request->general_type);
        if ($request->type) {
            $fields['type'] = COAType::getValue($request->type);
        }
        if ($request->sub_type) {
            $fields['sub_type'] = COASubType::getValue($request->sub_type);
        }
        if (boolval($request->doctor)) {
            // $doctor = Doctor::find($request->doctor_id);
            // $this->authorize('createForDoctor', [COA::class, $doctor]);
            $coa = $doctor->COAS()->create($fields);
            return new COAResource($coa);
        }
        $this->authorize('createForOffice', [COA::class, $office]);
        $coa = $office->COAS()->create($fields);
        return new COAResource($coa);
    }

    /**
     * Display the specified resource.
     */
    public function show(COA $cOA)
    {
        //
    }

    public function showDoubleEntry(COA $coa)
    {
        if ($coa->doctor) {
            $this->authorize('updateForDoctor', [$coa, auth()->user()->doctor]);
            return DoubleEntryResource::collection($coa->doubleEntries()->with('invoiceItem.invoice', 'invoice.items')->get());
        }
        $this->authorize('updateForOffice', [$coa, $coa->office]);
        return DoubleEntryResource::collection($coa->doubleEntries()->with('invoiceItem.invoice', 'invoice.items')->get());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCoaRequest $request, COA $coa)
    {
        $office = Office::findOrFail($request->office_id);
        if (auth()->user()->currentRole->name == 'DentalDoctorTechnician') {
            // Find the role based on user_id and office_id (roleable_id)
            $role = HasRole::where('user_id', auth()->id())
                ->where('roleable_id', $office->id)
                ->first();

            if (!$role) {
                // Return JSON response if no role is found
                return response()->json([
                    'error' => 'Role not found for the given user and office.',
                ], 403);
            }

            // Find the employee setting based on the has_role_id
            $employeeSetting = EmployeeSetting::where('has_role_id', $role->id)->first();

            if (!$employeeSetting) {
                // Return JSON response if no employee setting is found
                return response()->json([
                    'error' => 'Employee setting not found for the given role.',
                ], 403);
            }
            $doctor = Doctor::findOrFail($employeeSetting->doctor_id);
            $user = $doctor->user;
        } else {
            // Ensure a valid doctor is authenticated
            $doctor = auth()->user()->doctor;
            $user = auth()->user();
        }

        if (!$doctor) {
            return response('You have to complete your info', 404);
        }
        if ($coa->doctor) {
            $this->authorize('updateForDoctor', [$coa, $doctor]);
            $fields = $request->validated();
            $coa->update($fields);
            $coa->load('group');
            return new COAResource($coa);
        }
        $this->authorize('updateForOffice', [$coa, $coa->office]);
        $fields = $request->validated();
        $coa->update($fields);
        $coa->load('group');
        return new COAResource($coa);
    }

    public function setInitialBalance(SetInitialBalanceRequest $request, COA $coa)
    {
        $fields = $request->validated();
        $office = Office::findOrFail($request->office_id);

        if (auth()->user()->currentRole->name == 'DentalDoctorTechnician') {
            // Find the role based on user_id and office_id (roleable_id)
            $role = HasRole::where('user_id', auth()->id())
                ->where('roleable_id', $office->id)
                ->first();

            if (!$role) {
                // Return JSON response if no role is found
                return response()->json([
                    'error' => 'Role not found for the given user and office.',
                ], 403);
            }

            // Find the employee setting based on the has_role_id
            $employeeSetting = EmployeeSetting::where('has_role_id', $role->id)->first();

            if (!$employeeSetting) {
                // Return JSON response if no employee setting is found
                return response()->json([
                    'error' => 'Employee setting not found for the given role.',
                ], 403);
            }
            $doctor = Doctor::findOrFail($employeeSetting->doctor_id);
            $user = $doctor->user;
        } else {
            // Ensure a valid doctor is authenticated
            $doctor = auth()->user()->doctor;
            $user = auth()->user();
        }

        if (!$doctor) {
            return response('You have to complete your info', 404);
        }
        if ($coa->sub_type && ($coa->sub_type == COASubType::Payable || $coa->sub_type == COASubType::Receivable)) {
            return response('you can set initial balance for this coa type', 403);
        }
        $this->authorize('update', [$coa, $doctor]);
        if ($coa->initial_balance != 0) {
            return response('the initial balance only can be set once', 403);
        }
        $coa->update($fields);
        return new COAResource($coa);
    }

    public function JournalVoucherCOA(Request $request)
    {
        $officeId = $request->input('office_id');
        $doctorId = $request->input('doctor_id');

        if (!$officeId || !$doctorId) {
            return response()->json(['error' => 'office_id and doctor_id are required'], 400);
        }

        // Query COA table
        $coaAccounts = COA::where('office_id', $officeId)
            ->where('doctor_id', $doctorId)
            ->get(['id', 'name', 'general_type'])
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'is_coa' => true,
                    'type' => COAGeneralType::getKey($item->general_type),
                ];
            });

        // Query AccountingProfile table with eager loading
        $accountingProfiles = AccountingProfile::with('patient')
            ->where('office_id', $officeId)
            ->where('doctor_id', $doctorId)
            ->get(['id', 'supplier_name', 'patient_id', 'type', 'dental_lab_id'])
            ->map(function ($item) {
                $name = $item->supplier_name ?? ($item->patient ? $item->patient->first_name . " " . $item->patient->last_name : $item->lab->name);
                return [
                    'id' => $item->id,
                    'name' => $name,
                    'is_coa' => false,
                    'type' => AccountingProfileType::getKey($item->type),
                ];
            });

        // Combine results
        $combinedResults = $coaAccounts->merge($accountingProfiles);

        return response()->json($combinedResults);
    }

    public function coaOutcome(COA $coa)
    {
        $this->authorize('view', [$coa]);
        $positiveDoubleEntries = $coa->doubleEntries()->where('type', DoubleEntryType::Positive)->get();
        // return $positiveDoubleEntries;
        $totalPositive = $positiveDoubleEntries != null ?
            $positiveDoubleEntries->sum('total_price') : 0;
        // return $totalPositive;
        $negativeDoubleEntries = $coa->doubleEntries()->where('type', DoubleEntryType::Negative)->get();
        $totalNegative = $negativeDoubleEntries != null ?
            $negativeDoubleEntries->sum('total_price') : 0;
        // return $totalNegative; 
        $positiveDirectDoubleEntries = $coa->directDoubleEntries()->where('type', DoubleEntryType::Positive)->get();
        $totalDirectPositive = $positiveDirectDoubleEntries != null ?
            $positiveDirectDoubleEntries->sum('total_price') : 0;
        $negativeDirectDoubleEntries = $coa->directDoubleEntries()->where('type', DoubleEntryType::Negative)->get();
        $totalDirectNegative = $negativeDirectDoubleEntries != null ?
            $negativeDirectDoubleEntries->sum('total_price') : 0;
        // coa payable or receivable cant have initial balance
        if ($coa->sub_type && $coa->sub_type == COASubType::Payable) {
            if ($coa->doctor != null) {
                $subAccount = AccountingProfile::where(['doctor_id' => $coa->doctor->id, 'office_id' => $coa->office->id, 'type' => AccountingProfileType::SupplierAccount])->get();
                $totalBalanceAccount = $subAccount != null ?
                    $subAccount->sum('initial_balance') : 0;
            } else {
                $subAccount = AccountingProfile::where(['office_id' => $coa->office->id, 'type' => AccountingProfileType::SupplierAccount])->get();
                $totalBalanceAccount = $subAccount != null ?
                    $subAccount->sum('initial_balance') : 0;
            }
            $total = $totalPositive + $totalDirectPositive - $totalNegative - $totalDirectNegative + $totalBalanceAccount;
            return response()->json([
                'coa' => new COAResource($coa),
                'total' => $total,
            ]);
        }
        if ($coa->sub_type && $coa->sub_type == COASubType::Receivable) {
            if ($coa->doctor != null) {
                $subAccount = AccountingProfile::where(['doctor_id' => $coa->doctor->id, 'office_id' => $coa->office->id, 'type' => AccountingProfileType::PatientAccount])->get();
                $totalBalanceAccount = $subAccount != null ?
                    $subAccount->sum('initial_balance') : 0;
            } else {
                $subAccount = AccountingProfile::where(['office_id' => $coa->office->id, 'type' => AccountingProfileType::PatientAccount])->get();
                $totalBalanceAccount = $subAccount != null ?
                    $subAccount->sum('initial_balance') : 0;
            }
            $total = $totalPositive + $totalDirectPositive - $totalNegative - $totalDirectNegative + $totalBalanceAccount;
            return response()->json([
                'coa' => new COAResource($coa),
                'total' => $total,
            ]);
        }
        $total = $totalPositive + $totalDirectPositive - $totalNegative - $totalDirectNegative + $coa->initial_balance;
        return response()->json([
            'coa' => new COAResource($coa),
            'total' => $total,
        ]);
    }

    public static function coaOutcomeInt(int $id)
    {
        $coa = COA::findOrFail($id);
        $positiveDoubleEntries = $coa->doubleEntries()->where('type', DoubleEntryType::Positive)->get();
        $totalPositive = $positiveDoubleEntries != null ?
            $positiveDoubleEntries->sum('total_price') : 0;
        $negativeDoubleEntries = $coa->doubleEntries()->where('type', DoubleEntryType::Negative)->get();
        $totalNegative = $negativeDoubleEntries != null ?
            $negativeDoubleEntries->sum('total_price') : 0;
        $positiveDirectDoubleEntries = $coa->directDoubleEntries()->where('type', DoubleEntryType::Positive)->get();
        $totalDirectPositive = $positiveDirectDoubleEntries != null ?
            $positiveDirectDoubleEntries->sum('total_price') : 0;
        $negativeDirectDoubleEntries = $coa->directDoubleEntries()->where('type', DoubleEntryType::Negative)->get();
        $totalDirectNegative = $negativeDirectDoubleEntries != null ?
            $negativeDirectDoubleEntries->sum('total_price') : 0;
        // coa payable or receivable cant have initial balance
        if ($coa->sub_type && $coa->sub_type == COASubType::Payable) {
            if ($coa->doctor != null) {
                $subAccount = AccountingProfile::where(['doctor_id' => $coa->doctor->id, 'office_id' => $coa->office->id, 'type' => AccountingProfileType::SupplierAccount])->get();
                $totalBalanceAccount = $subAccount != null ?
                    $subAccount->sum('initial_balance') : 0;
            } else {
                $subAccount = AccountingProfile::where(['office_id' => $coa->office->id, 'type' => AccountingProfileType::SupplierAccount])->get();
                $totalBalanceAccount = $subAccount != null ?
                    $subAccount->sum('initial_balance') : 0;
            }
            $total = $totalPositive + $totalDirectPositive - $totalNegative - $totalDirectNegative + $totalBalanceAccount;
            return $total;
        }
        if ($coa->sub_type && $coa->sub_type == COASubType::Receivable) {
            if ($coa->doctor != null) {
                $subAccount = AccountingProfile::where(['doctor_id' => $coa->doctor->id, 'office_id' => $coa->office->id, 'type' => AccountingProfileType::PatientAccount])->get();
                $totalBalanceAccount = $subAccount != null ?
                    $subAccount->sum('initial_balance') : 0;
            } else {
                $subAccount = AccountingProfile::where(['office_id' => $coa->office->id, 'type' => AccountingProfileType::PatientAccount])->get();
                $totalBalanceAccount = $subAccount != null ?
                    $subAccount->sum('initial_balance') : 0;
            }
            $total = $totalPositive + $totalDirectPositive - $totalNegative - $totalDirectNegative + $totalBalanceAccount;
            return $total;
        }
        $total = $totalPositive + $totalDirectPositive - $totalNegative - $totalDirectNegative + $coa->initial_balance;
        return $total;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(COA $cOA)
    {
        //
    }
}
