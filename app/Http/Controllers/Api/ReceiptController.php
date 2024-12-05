<?php

namespace App\Http\Controllers\Api;

use App\Enums\AccountingProfileType;
use App\Enums\COASubType;
use App\Enums\DentalDoctorTransaction;
use App\Enums\DentalLabType;
use App\Enums\DoubleEntryType;
use App\Enums\OfficeType;
use App\Enums\SubRole;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Http\Controllers\Api\DentalLab\AccountingProfileController;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddReceiptToInvoiceRequest;
use App\Http\Requests\StoreDentalLabReceiptForDoctorRequest;
use App\Http\Requests\StorePatientReceiptRequest;
use App\Http\Requests\StoreReceiptRequest;
use App\Http\Requests\StoreSupplierReceiptRequest;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\ReceiptResource;
use App\Models\AccountingProfile;
use App\Models\COA;
use App\Models\DirectDoubleEntry;
use App\Models\Doctor;
use App\Models\DoubleEntry;
use App\Models\EmployeeSetting;
use App\Models\HasRole;
use App\Models\Invoice;
use App\Models\InvoiceReceipt;
use App\Models\Office;
use App\Models\Patient;
use App\Models\Receipt;
use App\Models\Role;
use App\Models\TransactionPrefix;
use App\Notifications\ReceiptCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReceiptController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReceiptRequest $request, Patient $patient)
    {
        // $fields = $request->validated();
        // $office = Office::findOrFail($request->office_id);
        // if ($office->type == OfficeType::Combined) {
        //     $profile = AccountingProfile::where([
        //         'patient_id' => $patient->id,
        //         'office_id' => $office->id, 'doctor_id' => null
        //     ])->first();
        //     $receipt = $profile->receipts()->create($fields);
        //     $payable = $office->payable;
        //     $cash = $office->cash;
        // } else {
        //     $profile = AccountingProfile::where([
        //         'patient_id' => $patient->id,
        //         'office_id' => $office->id, 'doctor_id' => $request->doctor_id
        //     ])->first();
        //     $receipt = $profile->receipts()->create($fields);
        //     $doctor = Doctor::find($request->doctor_id);
        //     $payable = $doctor->payable;
        //     $cash = $doctor->cash;
        // }
        // $doubleEntryFields['receipt_id'] = $receipt->id;
        // $doubleEntryFields['total_price'] = $receipt->total_price;
        // $doubleEntryFields['type'] = DoubleEntryType::Negative;
        // $payable->doubleEntries()->create($doubleEntryFields);
        // $cash->doubleEntries()->create($doubleEntryFields);
        // return new PatientInvoiceResource($invoice);
    }

    /**
     * Display the specified resource.
     */
    public function show(Receipt $receipt)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Receipt $receipt)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Receipt $receipt)
    {
        //
    }

    // public function storeSupplierReceipt(StoreSupplierReceiptRequest $request)
    // {
    //     $fields = $request->validated();
    //     // if ($request->invoice_id) {
    //     //     $invoice = Invoice::findOrFail($request->invoice_id);
    //     //     $this->authorize('myInvoice', [Receipt::class, $invoice]);
    //     // }
    //     $office = Office::findOrFail($request->office_id);
    //     $profile = AccountingProfile::findOrFail($request->supplier_account_id);
    //     $transactionNumber = TransactionPrefix::where(['office_id' => $office->id, 'doctor_id' => auth()->user()->doctor->id, 'type' => TransactionType::PaymentVoucher])->first();
    //     $fields['running_balance'] = $this->supplierBalance($profile->id, $fields['total_price']);
    //     if ($office->type == OfficeType::Combined) {
    //         // $profile = AccountingProfile::where([
    //         //     'supplier_name' => $request->supplier_name,
    //         //     'office_id' => $office->id, 'doctor_id' => null
    //         // ])->first();
    //         $fields['receipt_number'] = $transactionNumber->last_transaction_number + 1;
    //         $fields['type'] = DentalDoctorTransaction::PaymentVoucher;
    //         if (!$request->has('date_of_payment')) {
    //             $fields['date_of_payment'] = now();
    //         }
    //         $receipt = $profile->receipts()->create($fields);
    //         $transactionNumber->update(['last_transaction_number' => $fields['receipt_number']]);
    //         // $receipt->invoices()->attach($invoice, ['total_price' => $receipt->total_price]);
    //         $payable = COA::where([
    //             'office_id' => $office->id,
    //             'doctor_id' => null, 'sub_type' => COASubType::Payable
    //         ])->first();
    //         $cash = COA::findOrFail($request->cash_coa);
    //     } else {
    //         // $profile = AccountingProfile::where([
    //         //     'supplier_name' => $request->supplier_name,
    //         //     'office_id' => $office->id, 'doctor_id' => $request->doctor_id
    //         // ])->first();
    //         $fields['receipt_number'] = $transactionNumber->last_transaction_number + 1;
    //         $fields['type'] = DentalDoctorTransaction::PaymentVoucher;
    //         $receipt = $profile->receipts()->create($fields);
    //         $transactionNumber->update(['last_transaction_number' => $fields['receipt_number']]);
    //         // $receipt->invoices()->attach($invoice, ['total_price' => $receipt->total_price]);
    //         $doctor = Doctor::find($request->doctor_id);
    //         $payable = COA::where([
    //             'office_id' => $office->id,
    //             'doctor_id' => $doctor->id, 'sub_type' => COASubType::Payable
    //         ])->first();
    //         $cash = COA::findOrFail($request->cash_coa);
    //     }
    //     $doubleEntryFields['receipt_id'] = $receipt->id;
    //     $doubleEntryFields['total_price'] = $receipt->total_price;
    //     $doubleEntryFields['type'] = DoubleEntryType::Negative;
    //     $payable->doubleEntries()->create($doubleEntryFields);
    //     $cash->doubleEntries()->create($doubleEntryFields);
    //     return new ReceiptResource($receipt);
    // }

    public function storeSupplierReceipt(StoreSupplierReceiptRequest $request)
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
        DB::beginTransaction();
        try {

            $profile = AccountingProfile::findOrFail($request->supplier_account_id);

            // Fetch transaction number
            $transactionNumber = $this->getTransactionNumber($office, TransactionType::PaymentVoucher, $doctor);

            // Set fields for the receipt
            $fields = $this->setSupplierReceiptFields($request, $profile, $transactionNumber, $fields);

            // Create the receipt
            $receipt = $profile->receipts()->create($fields);
            $transactionNumber->update(['last_transaction_number' => $fields['receipt_number']]);

            // Fetch COAs
            $cash = COA::findOrFail($request->cash_coa);

            // Create double entries
            $this->createProfileDoubleEntry($receipt->accounting_profile_id, $receipt->id, $receipt->total_price, DoubleEntryType::Negative);
            $this->createDoubleEntry($cash, $receipt, DoubleEntryType::Negative);

            DB::commit();
            return new ReceiptResource($receipt);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating supplier receipt: ' . $e->getMessage());
            throw $e;
        }
    }


    public function storeDentalLabReceipt(StoreDentalLabReceiptForDoctorRequest $request, AccountingProfile $profile)
    {
        $fields = $request->validated();
        abort_unless($profile->type == AccountingProfileType::DentalLabDoctorAccount, 403);
        $office = $profile->office;
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
        $this->authorize('createReceiptForDentalLab', [Receipt::class, $profile, $doctor]);

        $cash = COA::findOrFail($request->cash_coa);
        abort_unless($cash->sub_type == COASubType::Cash && $cash->office_id == $profile->office_id, 403);
        $transactionNumber = TransactionPrefix::where(['office_id' => $office->id, 'doctor_id' => $profile->doctor->id, 'type' => TransactionType::PaymentVoucher])->first();
        DB::beginTransaction();
        try {
            $fields['running_balance'] = $this->calculateLapBalance($profile->id, $fields['total_price']);
            $fields['receipt_number'] = $transactionNumber->last_transaction_number + 1;
            $fields['type'] = DentalDoctorTransaction::PaymentVoucher;
            $role = HasRole::where(['roleable_id' => $profile->lab->id, 'roleable_type' => 'App\Models\DentalLab', 'user_id' => $doctor->user->id])->first();
            if ($role != null && $role->sub_role == SubRole::DentalLabDraft) {
                $fields['status'] = TransactionStatus::Approved;
            } else {
                $fields['status'] = TransactionStatus::Draft;
            }
            if (!$request->has('date_of_payment')) {
                $fields['date_of_payment'] = now();
            }
            $receipt = $profile->receipts()->create($fields);
            if ($profile->lab->type == DentalLabType::Real) {
                $type = 'ReceiptFromDoctor';
                $profile->lab->notify(new ReceiptCreated($receipt, $type));
            }
            $transactionNumber->update(['last_transaction_number' => $fields['receipt_number']]);
            if ($profile->office->type == OfficeType::Combined) {
                $payable = COA::where([
                    'office_id' => $profile->office->id,
                    'doctor_id' => null,
                    'sub_type' => COASubType::Payable
                ])->first();
            } else {
                $doctor = $profile->doctor;
                $payable = COA::where([
                    'office_id' => $office->id,
                    'doctor_id' => $doctor->id,
                    'sub_type' => COASubType::Payable
                ])->first();
            }
            $doubleEntryFields['receipt_id'] = $receipt->id;
            $doubleEntryFields['total_price'] = $receipt->total_price;
            $doubleEntryFields['type'] = DoubleEntryType::Negative;
            $this->createProfileDoubleEntry($receipt->accounting_profile_id, $receipt->id, $receipt->total_price, DoubleEntryType::Negative);
            $cash->doubleEntries()->create($doubleEntryFields);
            $receipt->load('lab');
            DB::commit();
            return new ReceiptResource($receipt);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating supplier invoice with items: ' . $e->getMessage());
            throw $e;
        }
    }

    private function calculateLapBalance(int $id, int $thisTransaction)
    {
        $lap = AccountingProfile::findOrFail($id);
        $doubleEntries = $lap->doubleEntries()->get();
        $directDoubleEntries = $lap->directDoubleEntries()->get();

        // Sum the positive and negative entries from both doubleEntries and directDoubleEntries
        $totalPositive = $doubleEntries->where('type', DoubleEntryType::Positive)->sum('total_price') +
            $directDoubleEntries->where('type', DoubleEntryType::Positive)->sum('total_price');
        $totalNegative = $doubleEntries->where('type', DoubleEntryType::Negative)->sum('total_price') +
            $directDoubleEntries->where('type', DoubleEntryType::Negative)->sum('total_price');

        return $totalPositive - $totalNegative - $thisTransaction + $lap->initial_balance;
    }

    // public function storePatientReceipt(StorePatientReceiptRequest $request, Patient $patient)
    // {
    //     $fields = $request->validated();
    //     $office = Office::findOrFail($request->office_id);
    //     $transactionNumber = TransactionPrefix::where(['office_id' => $office->id, 'doctor_id' => auth()->user()->doctor->id, 'type' => TransactionType::PatientReceipt])->first();
    //     if ($office->type == OfficeType::Combined) {
    //         $profile = AccountingProfile::where([
    //             'patient_id' => $patient->id,
    //             'office_id' => $office->id, 'doctor_id' => null
    //         ])->first();
    //         $fields['running_balance'] = $this->patientBalance($profile->id, $fields['total_price']);
    //         $fields['receipt_number'] = $transactionNumber->last_transaction_number + 1;
    //         $fields['type'] = DentalDoctorTransaction::ResetVoucher;
    //         if (!$request->has('date_of_payment')) {
    //             $fields['date_of_payment'] = now();
    //         }
    //         $receipt = $profile->receipts()->create($fields);
    //         $transactionNumber->update(['last_transaction_number' => $fields['receipt_number']]);
    //         $receivable = COA::where([
    //             'office_id' => $office->id,
    //             'doctor_id' => null, 'sub_type' => COASubType::Receivable
    //         ])->first();
    //         $cash = COA::findOrFail($request->cash_coa);
    //     } else {
    //         $profile = AccountingProfile::where([
    //             'patient_id' => $patient->id,
    //             'office_id' => $office->id, 'doctor_id' => $request->doctor_id
    //         ])->first();
    //         $fields['running_balance'] = $this->patientBalance($profile->id, $fields['total_price']);
    //         $fields['receipt_number'] = $transactionNumber->last_transaction_number + 1;
    //         $fields['type'] = DentalDoctorTransaction::ResetVoucher;
    //         $receipt = $profile->receipts()->create($fields);
    //         $transactionNumber->update(['last_transaction_number' => $fields['receipt_number']]);
    //         $doctor = Doctor::find($request->doctor_id);
    //         $receivable = COA::where([
    //             'office_id' => $office->id,
    //             'doctor_id' => $doctor->id, 'sub_type' => COASubType::Receivable
    //         ])->first();
    //         $cash = COA::findOrFail($request->cash_coa);
    //     }
    //     $doubleEntryFields['receipt_id'] = $receipt->id;
    //     $doubleEntryFields['total_price'] = $receipt->total_price;
    //     $doubleEntryFields['type'] = DoubleEntryType::Negative;
    //     $receivable->doubleEntries()->create($doubleEntryFields);
    //     $doubleEntryFields['type'] = DoubleEntryType::Positive;
    //     $cash->doubleEntries()->create($doubleEntryFields);
    //     return new ReceiptResource($receipt);
    // }

    public function storePatientReceipt(StorePatientReceiptRequest $request, Patient $patient)
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
        DB::beginTransaction();
        try {

            $transactionNumber = TransactionPrefix::where([
                'office_id' => $office->id,
                'doctor_id' => $doctor->id,
                'type' => TransactionType::PatientReceipt
            ])->firstOrFail();

            $profile = $this->getAccountingProfile($office, $patient, $doctor->id);
            $fields = $this->setReceiptFields($request, $profile, $transactionNumber, $fields);

            $receipt = $profile->receipts()->create($fields);
            $transactionNumber->update(['last_transaction_number' => $fields['receipt_number']]);


            $cash = COA::findOrFail($request->cash_coa);

            $this->createDoubleEntry($cash, $receipt, DoubleEntryType::Positive);
            $this->createProfileDoubleEntry($receipt->accounting_profile_id, $receipt->id, $receipt->total_price, DoubleEntryType::Negative);

            DB::commit();
            return new ReceiptResource($receipt);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating patient receipt: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getTransactionNumber($office, $type, $doctor)
    {
        return TransactionPrefix::where([
            'office_id' => $office->id,
            'doctor_id' => $doctor->id,
            'type' => $type
        ])->first();
    }
    private function getAccountingProfile($office, $patient, $doctorId = null)
    {
        return AccountingProfile::where([
            'patient_id' => $patient->id,
            'office_id' => $office->id,
            'doctor_id' => $office->type == OfficeType::Combined ? null : $doctorId
        ])->firstOrFail();
    }

    private function setReceiptFields($request, $profile, $transactionNumber, $fields)
    {
        $fields['running_balance'] = $this->calculatePatientBalance($profile->id, -$fields['total_price']);
        $fields['receipt_number'] = $transactionNumber->last_transaction_number + 1;
        $fields['type'] = DentalDoctorTransaction::ResetVoucher;
        $fields['date_of_payment'] = $request->has('date_of_payment') ? $request->date_of_payment : now();
        return $fields;
    }

    private function setSupplierReceiptFields($request, $profile, $transactionNumber, $fields)
    {
        $fields['running_balance'] = $this->calculatePatientBalance($profile->id, -$fields['total_price']);
        $fields['receipt_number'] = $transactionNumber->last_transaction_number + 1;
        $fields['type'] = DentalDoctorTransaction::PaymentVoucher;
        $fields['date_of_payment'] = $request->has('date_of_payment') ? $request->date_of_payment : now();
        return $fields;
    }

    private function createDoubleEntry($coa, $receipt, $type)
    {
        $doubleEntryFields = [
            'COA_id' => $coa->id,
            'receipt_id' => $receipt->id,
            'total_price' => $receipt->total_price,
            'type' => $type,
            // 'accounting_profile_id' => $receipt->accounting_profile_id,
            'running_balance' => $this->calculateCOABalance($coa->id, $receipt->total_price, $type)
        ];

        $coa->doubleEntries()->create($doubleEntryFields);
    }

    private function createProfileDoubleEntry($accountingProfileId, $itemId, $totalPrice, $type)
    {
        $runningBalance = $this->calculatePatientBalance($accountingProfileId, $type == DoubleEntryType::Positive ? $totalPrice : -$totalPrice);

        DoubleEntry::create([
            'accounting_profile_id' => $accountingProfileId,
            'receipt_id' => $itemId,
            'total_price' => $totalPrice,
            'type' => $type,
            'running_balance' => $runningBalance
        ]);
    }

    private function calculatePatientBalance(int $id, int $thisTransaction)
    {
        $patient = AccountingProfile::findOrFail($id);
        $doubleEntries = $patient->doubleEntries()->get();
        $directDoubleEntries = $patient->directDoubleEntries()->get();

        // Sum the positive and negative entries from both doubleEntries and directDoubleEntries
        $totalPositive = $doubleEntries->where('type', DoubleEntryType::Positive)->sum('total_price') +
            $directDoubleEntries->where('type', DoubleEntryType::Positive)->sum('total_price');
        $totalNegative = $doubleEntries->where('type', DoubleEntryType::Negative)->sum('total_price') +
            $directDoubleEntries->where('type', DoubleEntryType::Negative)->sum('total_price');

        return $totalPositive - $totalNegative + $thisTransaction + $patient->initial_balance;
    }

    private function calculateCOABalance(int $coaId, int $thisTransaction, string $type)
    {
        $doubleEntries = DoubleEntry::where('COA_id', $coaId)->get();
        $directDoubleEntries = DirectDoubleEntry::where('COA_id', $coaId)->get();

        $totalPositive = $doubleEntries->where('type', DoubleEntryType::Positive)->sum('total_price') +
            $directDoubleEntries->where('type', DoubleEntryType::Positive)->sum('total_price');
        $totalNegative = $doubleEntries->where('type', DoubleEntryType::Negative)->sum('total_price') +
            $directDoubleEntries->where('type', DoubleEntryType::Negative)->sum('total_price');

        return ($totalPositive - $totalNegative) + ($type == DoubleEntryType::Positive ? $thisTransaction : -$thisTransaction);
    }




    public function addReceiptToInvoice(AddReceiptToInvoiceRequest $request, Receipt $receipt, Invoice $invoice)
    {
        $this->authorize('myInvoice', [Receipt::class, $invoice]);
        $receipt->invoices()->attach($invoice, ['total_price' => $receipt->total_price]);
        return $invoice;
    }

    public static function patientBalance(int $id, int $thisTransaction)
    {
        $patient = AccountingProfile::findOrFail($id);
        $invoices = $patient->invoices()->get();
        $totalPositive = $invoices != null ?
            $invoices->sum('total_price') : 0;
        $receipts = $patient->receipts()->get();
        $totalNegative = $receipts != null ?
            $receipts->sum('total_price') : 0;
        $total = $totalPositive - $totalNegative - $thisTransaction + $patient->initial_balance;
        return $total;
    }

    public static function supplierBalance(int $id, int $thisTransaction)
    {
        $supplier = AccountingProfile::findOrFail($id);
        $invoices = $supplier->invoices()->get();
        $totalNegative = $invoices != null ?
            $invoices->sum('total_price') : 0;
        $receipts = $supplier->receipts()->get();
        $totalPositive = $receipts != null ?
            $receipts->sum('total_price') : 0;
        $total = $totalPositive - $totalNegative + $thisTransaction + $supplier->initial_balance;
        return $total;
    }

    public static function labBalance(int $id, int $thisTransaction)
    {
        $supplier = AccountingProfile::findOrFail($id);
        $invoices = $supplier->invoices()->whereIn('type', DentalDoctorTransaction::getValues())->get();
        $totalNegative = $invoices != null ?
            $invoices->sum('total_price') : 0;
        $receipts = $supplier->receipts()->whereIn('type', DentalDoctorTransaction::getValues())
            ->whereNot('status', TransactionStatus::Canceled)
            ->get();
        $totalPositive = $receipts != null ?
            $receipts->sum('total_price') : 0;
        $total = $totalPositive - $totalNegative + $thisTransaction + $supplier->secondary_initial_balance;
        return $total;
    }

    public function reverseReceipt(Request $request, $receipt)
    {
        // Validate the request
        $fields = $request->validate([
            'office_id' => 'required|exists:offices,id',
        ]);
        $office = Office::findOrFail($fields['office_id']);

        // Determine doctor and user, handling different roles
        if (in_array(auth()->user()->currentRole->name, Role::Technicians)) {
            // Find the role based on user_id and office_id (roleable_id)
            $role = HasRole::where('user_id', auth()->id())
                ->where('roleable_id', $office->id)
                ->first();

            if (!$role) {
                return response()->json(['error' => 'Role not found for the given user and office.'], 403);
            }

            // Find the employee setting based on the has_role_id
            $employeeSetting = EmployeeSetting::where('has_role_id', $role->id)->first();

            if (!$employeeSetting) {
                return response()->json(['error' => 'Employee setting not found for the given role.'], 403);
            }
            $doctor = Doctor::findOrFail($employeeSetting->doctor_id);
            $user = $doctor->user;
        } else {
            $doctor = auth()->user()->doctor;
            $user = auth()->user();
        }

        if (!$doctor) {
            return response('You have to complete your info', 404);
        }

        // Begin transaction
        DB::beginTransaction();
        try {
            // Fetch the original receipt
            $originalReceipt = Receipt::findOrFail($receipt);

            // Check if the receipt is already reversed
            if ($originalReceipt->status == TransactionStatus::Reversed) {
                return response()->json(['error' => 'Receipt already reversed.'], 400);
            }

            // Authorization: Ensure the user has permission to reverse receipts
            abort_unless($originalReceipt->doctor->id == $doctor->id, 403, 'Unauthorized action.');

            // Create a reversal receipt
            $reversalReceipt = $this->createReversalReceipt($originalReceipt, $doctor, $office);

            // Update the original receipt status
            $originalReceipt->status = TransactionStatus::Reversed;
            $originalReceipt->save();

            $reversalReceipt->reversed_by = auth()->id();
            $reversalReceipt->save();

            // Commit transaction
            DB::commit();

            // Return the reversal receipt
            return new ReceiptResource($reversalReceipt);
        } catch (\Exception $e) {
            // Rollback transaction
            DB::rollBack();
            Log::error('Error reversing receipt: ' . $e->getMessage());
            throw $e;
        }
    }


    protected function createReversalReceipt($originalReceipt, $doctor, $office)
    {
        // Clone the original receipt data
        $receiptData = $originalReceipt->toArray();

        // Modify necessary fields
        $receiptData['id'] = null; // Create new record
        $receiptData['total_price'] = -$originalReceipt->total_price; // Invert amount
        $receiptData['running_balance'] = $this->calculatePatientBalance(
            $originalReceipt->accounting_profile_id,
            $originalReceipt->total_price
        );
        $receiptData['note'] = 'Reversal of Receipt #' . $originalReceipt->receipt_number;
        $receiptData['date_of_payment'] = now();

        $transactionNumber = $this->getTransactionNumber($office, TransactionType::PatientReceipt, $doctor);
        $receiptData['receipt_number'] = $transactionNumber->last_transaction_number + 1;
        $receiptData['status'] = TransactionStatus::Reversed;
        $receiptData['created_by'] = auth()->id();

        // Create the reversal receipt
        $reversalReceipt = Receipt::create($receiptData);

        // Update the transaction number
        $transactionNumber->update(['last_transaction_number' => $receiptData['receipt_number']]);

        // Link reversal to original receipt (if applicable)
        $reversalReceipt->original_receipt_id = $originalReceipt->id; // Make sure this field exists
        $reversalReceipt->save();

        // Create accounting entries
        // Get the cash COA from the original double entry
        $originalCashDoubleEntry = $originalReceipt->doubleEntries()->where('type', DoubleEntryType::Positive)->first();
        if (!$originalCashDoubleEntry) {
            throw new \Exception('Original cash double entry not found.');
        }
        $cash = COA::findOrFail($originalCashDoubleEntry->COA_id);

        // Create double entries
        $this->createDoubleEntry($cash, $reversalReceipt, DoubleEntryType::Negative);
        $this->createProfileDoubleEntry(
            $reversalReceipt->accounting_profile_id,
            $reversalReceipt->id,
            $reversalReceipt->total_price,
            DoubleEntryType::Positive
        );

        return $reversalReceipt;
    }
}
