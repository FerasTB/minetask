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
use App\Http\Resources\AccountingProfileResource;
use App\Http\Resources\COAResource;
use App\Http\Resources\COAWithDateResource;
use App\Http\Resources\DentalLabAccountingProfileResource;
use App\Http\Resources\DoubleEntryResource;
use App\Http\Resources\patientDefaultCaseResource;
use App\Models\AccountingProfile;
use App\Models\COA;
use App\Models\Doctor;
use App\Models\EmployeeSetting;
use App\Models\HasRole;
use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class COAController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $office = Office::findOrFail($request->office);
        if (in_array(auth()->user()->currentRole->name, Role::Technicians)) {
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

    public function indexForEmployee(Request $request)
    {
        // Find the office by ID
        $office = Office::findOrFail($request->office);

        // Ensure the doctor_id is provided
        abort_unless($request->doctor_id != null, 403, 'missing info');

        // Find the doctor by ID
        $doctor = Doctor::findOrFail($request->doctor_id);

        // Retrieve the role for the doctor in the specified office
        $role = HasRole::where('user_id', $doctor->user->id)
            ->where('roleable_id', $office->id)
            ->first();

        // If no role is found, return a 403 response
        if (!$role) {
            return response()->json([
                'error' => 'Role not found for the given user and office.',
            ], 403);
        }

        // Check if the user has the necessary authorization
        $this->authorize('officeOwner', [COA::class, $office]);

        // Get the COA data based on the office type
        $coaData = [];
        if ($office->type == OfficeType::Separate) {
            $coaData = COAResource::collection(
                COA::where(['office_id' => $office->id, "doctor_id" => $request->doctor_id])
                    ->with([
                        'doctor',
                        'office',
                        'doubleEntries',
                        'directDoubleEntries'
                    ])
                    ->get()
            );
        } else {
            $coaData = COAResource::collection($office->COAS()->with(['office', 'doubleEntries', 'directDoubleEntries'])->get());
        }

        // Get the doctor's accounting profiles (DentalLabDoctorAccount)
        $accounts = $doctor->accountingProfiles()->where('type', AccountingProfileType::DentalLabDoctorAccount)->get();
        $accounts->load([
            'invoices',
            'invoices.items',
            'receipts',
            'lab',
            'labOrders',
            'labOrders.details',
            'labOrders.details.teeth',
            'labOrders.orderSteps',
            'directDoubleEntries',
            'doubleEntries'
        ]);
        $user = $doctor->user;
        $response = [];
        // Authorization and data retrieval for Combined office type
        if ($office->type == OfficeType::Combined) {
            $this->authorize('inOffice', [Doctor::class, $office]);

            $ownerUser = User::find($office->owner->user_id);
            $ownerDoctor = $ownerUser->doctor;

            $accountsCombined = AccountingProfile::where([
                'doctor_id' => $ownerDoctor->id,
                'office_id' => $office->id,
                'type' => AccountingProfileType::PatientAccount
            ])->with([
                'office',
                'office.owner',
                'office.owner.user',
                'patient',
                'patient.info',
                'patient.doctorImage',
                'patient.labOrders',
                'patient.labOrders.details',
                'patient.labOrders.details.teeth',
                'invoices',
                'invoices.items',
                'receipts',
                'invoices.receipts',
                'invoiceReceipt',
                'invoiceReceipt.items',
                'doubleEntries',
                'directDoubleEntries',
                'patient.notes' => function ($query) use ($doctor, $office) {
                    $query->where('doctor_id', $doctor->id)
                        ->where('office_id', $office->id)
                        ->where('primary', true);
                },
                'patient.cases' => function ($query) use ($doctor, $office) {
                    $query->whereHas('medicalCase', function ($query) use ($doctor, $office) {
                        $query->where([
                            'case_name' => Doctor::DefaultCase,
                            'doctor_id' => $doctor->id,
                            'office_id' => $office->id
                        ]);
                    })->with([
                        'teethRecords',
                        'teethRecords.operations',
                        'teethRecords.diagnosis',
                        'teethRecords.operations.teeth',
                        'teethRecords.diagnosis.teeth'
                    ]);
                }
            ])->get();

            // $response['combined'] = MyPatientCombinedThroughAccountingProfileResource::collection($accountsCombined);
            $response['profile'] = AccountingProfileResource::collection($accountsCombined)->map(function ($accountProfile) use ($ownerUser) {
                return new AccountingProfileResource($accountProfile, $ownerUser);
            });
        } else {
            // return AccountingProfileResource::collection($doctor->accountingProfiles)->where(['office_id' => $office->id, 'type' => AccountingProfileType::PatientAccount]);
            // Authorization and data retrieval for Separate office type
            $this->authorize('inOffice', [AccountingProfile::class, $office]);

            $accountsSeparate = AccountingProfile::where([
                'doctor_id' => $doctor->id,
                'office_id' => $office->id,
                'type' => AccountingProfileType::PatientAccount
            ])->with([
                'office',
                'patient',
                'patient.info',
                'patient.doctorImage',
                'patient.labOrders',
                'patient.labOrders.details',
                'patient.labOrders.lab',
                'patient.labOrders.details.teeth',
                'invoices',
                'invoices.items',
                'receipts',
                'invoices.receipts',
                'invoiceReceipt',
                'invoiceReceipt.items',
                'doubleEntries',
                'directDoubleEntries',
                'patient.notes' => function ($query) use ($doctor, $office) {
                    $query->where('doctor_id', $doctor->id)
                        ->where('office_id', $office->id)
                        ->where('primary', true);
                },
                'patient.cases' => function ($query) use ($doctor, $office) {
                    $query->whereHas('medicalCase', function ($query) use ($doctor, $office) {
                        $query->where([
                            'case_name' => Doctor::DefaultCase,
                            'doctor_id' => $doctor->id,
                            'office_id' => $office->id
                        ]);
                    })->with([
                        'teethRecords',
                        'teethRecords.operations',
                        'teethRecords.diagnosis',
                        'teethRecords.operations.teeth',
                        'teethRecords.diagnosis.teeth'
                    ]);
                },
            ])->get();
            // $response['separate'] = MyPatientSeparateThroughAccountingProfileResource::collection($accountsSeparate);
            $response['profile'] = AccountingProfileResource::collection($accountsSeparate)->map(function ($accountProfile) use ($user) {
                return new AccountingProfileResource($accountProfile, $user);
            });
        }
        // Process each profile to include the default case
        foreach ($response['profile'] as $accountProfile) {
            $patient = $accountProfile->patient;
            if ($patient) {
                $defaultCase = $patient->cases->first();
                if ($defaultCase) {
                    $accountProfile->default_case = new patientDefaultCaseResource($defaultCase);
                }
            } else {
                return $accountProfile->id;
            }
        }

        // Return a combined response with both COA data and accounting profile data
        return response()->json([
            'coa' => $coaData,
            'labs' => DentalLabAccountingProfileResource::collection($accounts),
            'patient' => $response['profile']
        ]);
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

    public function indexOwnerWithDate(Request $request, Office $office)
    {
        // $office = Office::findOrFail($request->office);
        $this->authorize('inOffice', [COA::class, $office]);
        $request->validate([
            // 'office' => 'required|exists:offices,id',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
        ]);
        if (in_array(auth()->user()->currentRole->name, Role::Technicians)) {
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
        // Adjust dates to include time if necessary
        $fromDate = $request->input('from_date') ? $request->input('from_date') . ' 00:00:00' : null;
        $toDate = $request->input('to_date') ? $request->input('to_date') . ' 23:59:59' : null;

        return COAWithDateResource::collection(
            $doctor->COAS()
                ->where('office_id', $office->id)
                ->with([
                    'office',
                    'doubleEntries' => function ($query) use ($fromDate, $toDate) {
                        if ($fromDate && $toDate) {
                            $query->whereBetween('created_at', [$fromDate, $toDate]);
                        }
                    },
                    'directDoubleEntries' => function ($query) use ($fromDate, $toDate) {
                        if ($fromDate && $toDate) {
                            $query->whereBetween('created_at', [$fromDate, $toDate]);
                        }
                    },
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
        if (in_array(auth()->user()->currentRole->name, Role::Technicians)) {
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
        if (in_array(auth()->user()->currentRole->name, Role::Technicians)) {
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

        if (in_array(auth()->user()->currentRole->name, Role::Technicians)) {
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
