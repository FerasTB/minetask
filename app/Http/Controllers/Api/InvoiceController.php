<?php

namespace App\Http\Controllers\Api;

use App\Enums\COAGeneralType;
use App\Enums\COASubType;
use App\Enums\COAType;
use App\Enums\DentalDoctorTransaction;
use App\Enums\DoubleEntryType;
use App\Enums\OfficeType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\AcceptDentalLabInvoiceForDoctorRequest;
use App\Http\Requests\ProcessDraftInvoiceRequest;
use App\Http\Requests\StoreDentalLabInvoiceForDoctorRequest;
use App\Http\Requests\StoreDentalLabInvoiceRequest;
use App\Http\Requests\StoreInvoiceAndItemForPatientRequest;
use App\Http\Requests\storeJournalInvoiceRequest;
use App\Http\Requests\StoreLabInvoiceWithItems;
use App\Http\Requests\StorePatientInvoiceRequest;
use App\Http\Requests\StoreReversePatientInvoice;
use App\Http\Requests\StoreSupplierInvoiceRequest;
use App\Http\Requests\UpdatePatientInvoiceStatusRequest;
use App\Http\Resources\DirectDoubleEntryResource;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\PatientInvoiceResource;
use App\Models\AccountingProfile;
use App\Models\COA;
use App\Models\DirectDoubleEntry;
use App\Models\DirectDoubleEntryInvoice;
use App\Models\Doctor;
use App\Models\DoubleEntry;
use App\Models\EmployeeSetting;
use App\Models\HasRole;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Office;
use App\Models\Patient;
use App\Models\Role;
use App\Models\TeethRecord;
use App\Models\TransactionPrefix;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Profiler\Profile;

class InvoiceController extends Controller
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        //
    }

    public function storePatientInvoice(StorePatientInvoiceRequest $request, Patient $patient)
    {
        $fields = $request->validated();
        $office = Office::findOrFail($request->office_id);
        $transactionNumber = TransactionPrefix::where(['office_id' => $office->id, 'doctor_id' => auth()->user()->doctor->id, 'type' => TransactionType::PatientInvoice])->first();
        if ($office->type == OfficeType::Combined) {
            $owner = User::findOrFail($office->owner->user_id);
            $profile = AccountingProfile::where([
                'patient_id' => $patient->id,
                'office_id' => $office->id,
                'doctor_id' => $owner->doctor->id
            ])->first();
            $fields['type'] = DentalDoctorTransaction::SellInvoice;
            if (!$request->has('date_of_invoice')) {
                $fields['date_of_invoice'] = now();
            }
            $fields['running_balance'] = $this->patientBalance($profile->id, $fields['total_price']);
            $fields['invoice_number'] = $transactionNumber->last_transaction_number + 1;
            $invoice = $profile->invoices()->create($fields);
            $transactionNumber->update(['last_transaction_number' => $fields['invoice_number']]);
        } else {
            $profile = AccountingProfile::where([
                'patient_id' => $patient->id,
                'office_id' => $office->id,
                'doctor_id' => $request->doctor_id
            ])->first();
            $fields['running_balance'] = $this->patientBalance($profile->id, $fields['total_price']);
            $fields['invoice_number'] = $transactionNumber->last_transaction_number + 1;
            $fields['type'] = DentalDoctorTransaction::SellInvoice;
            $invoice = $profile->invoices()->create($fields);
            $transactionNumber->update(['last_transaction_number' => $fields['invoice_number']]);
        }
        return new PatientInvoiceResource($invoice);
    }

    public function reverseInvoice(StoreReversePatientInvoice $request, $invoice)
    {

        // Validate the main invoice fields
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
        // Begin database transaction
        DB::beginTransaction();

        try {
            // Fetch the original invoice
            $originalInvoice = Invoice::findOrFail($invoice);

            // Perform necessary validations
            // - Check if the invoice is already reversed
            if ($originalInvoice->status == TransactionStatus::Reversed) {
                return response()->json(['error' => 'Invoice already reversed.'], 400);
            }
            // Authorization: Ensure the user has permission to reverse invoices
            abort_unless($originalInvoice->doctor->id == $doctor->id, 403, 'bad request');

            // - Check if the invoice can be reversed (e.g., not paid)

            // Create a reversal invoice
            $reversalInvoice = $this->createReversalInvoice($originalInvoice, $doctor, $office);

            // Update the original invoice status
            $originalInvoice->status = TransactionStatus::Reversed;
            $originalInvoice->save();
            $reversalInvoice->reversed_by_id = auth()->id();
            $reversalInvoice->save();
            // Commit transaction
            DB::commit();

            // Return the reversal invoice
            return new PatientInvoiceResource($reversalInvoice);
        } catch (\Exception $e) {
            // Rollback transaction
            DB::rollBack();
            Log::error('Error reversing invoice: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function createReversalInvoice($originalInvoice, $doctor, $office)
    {
        // Clone the original invoice data
        $invoiceData = $originalInvoice->toArray();

        // Modify necessary fields
        $invoiceData['id'] = null; // Set ID to null to create a new record
        $invoiceData['type'] = $originalInvoice->type; // Set type to indicate reversal
        $invoiceData['status'] = TransactionStatus::Reversed; // Set type to indicate reversal
        $invoiceData['total_price'] = -$originalInvoice->total_price; // Invert the total price
        $invoiceData['running_balance'] = $this->calculatePatientBalance(
            $originalInvoice->accounting_profile_id,
            -$originalInvoice->total_price
        );
        $invoiceData['note'] = 'Reversal of Invoice #' . $originalInvoice->invoice_number;
        $invoiceData['date_of_invoice'] = now();
        $transactionNumber = $this->getTransactionNumber($office, TransactionType::PatientInvoice, $doctor);

        $invoiceData['invoice_number'] = $transactionNumber->last_transaction_number + 1;

        // Create the reversal invoice
        $reversalInvoice = Invoice::create($invoiceData);

        // Link reversal to original invoice (optional but recommended)
        $reversalInvoice->original_invoice_id = $originalInvoice->id;
        $reversalInvoice->save();

        // Reverse the invoice items
        foreach ($originalInvoice->items as $item) {
            $this->createReversalInvoiceItem($item, $reversalInvoice->id, $originalInvoice);
        }
        return $reversalInvoice;
    }

    protected function createReversalInvoiceItem($originalItem, $reversalInvoiceId, $invoice)
    {
        $itemData = $originalItem->toArray();
        $itemData['id'] = null;
        $itemData['invoice_id'] = $reversalInvoiceId;
        $itemData['amount'] = -$originalItem->amount;
        $itemData['total_price'] = -$originalItem->total_price;
        $itemData['price_per_one'] = -$originalItem->price_per_one;
        $itemData['coa_id'] = $originalItem->coa_id;

        // Create the reversal invoice item
        $reversalItem = InvoiceItem::create($itemData);
        $coa = COA::findOrFail($itemData['coa_id']);
        $this->createDoubleEntry($coa, $reversalItem->id, -$reversalItem->total_price, DoubleEntryType::Negative, $invoice->accounting_profile_id);
        // Return reversal item (if needed)
        $this->createProfileDoubleEntry($invoice->accounting_profile_id, $reversalItem->id, -$reversalItem->total_price, DoubleEntryType::Negative);

        return $reversalItem;
    }

    protected function createReversalAccountingEntries($originalInvoice, $reversalInvoice)
    {
        // Reverse Accounts Receivable and Revenue
        $this->createDoubleEntry(
            $coaId = $originalInvoice->accounting_profile->coa_id, // Patient's Accounts Receivable
            $transactionId = $reversalInvoice->id,
            $amount = -$originalInvoice->total_price,
            $type = DoubleEntryType::Negative,
            $accountingProfileId = $originalInvoice->accounting_profile_id
        );

        // Reverse Revenue Entries
        foreach ($originalInvoice->items as $item) {
            $this->createDoubleEntry(
                $coaId = $item->coa_id, // Revenue Account
                $transactionId = $reversalInvoice->id,
                $amount = -$item->total_price,
                $type = DoubleEntryType::Positive,
                $accountingProfileId = $originalInvoice->accounting_profile_id
            );
        }
    }






    public function storeInvoiceWithReceipts(StoreInvoiceWithReceiptsRequest $request, Patient $patient)
    {
        $fields = $request->validated();
        $office = Office::findOrFail($request->office_id);
        $transactionNumber = TransactionPrefix::where(['office_id' => $office->id, 'doctor_id' => auth()->user()->doctor->id, 'type' => TransactionType::PatientInvoice])->first();
        $profile = AccountingProfile::where([
            'patient_id' => $patient->id,
            'office_id' => $office->id,
            'doctor_id' => $request->doctor_id
        ])->first();

        // Create Invoice
        $fields['type'] = DentalDoctorTransaction::SellInvoice;
        $fields['date_of_invoice'] = $request->has('date_of_invoice') ? $request->date_of_invoice : now();
        $fields['running_balance'] = $this->patientBalance($profile->id, $fields['total_price']);
        $fields['invoice_number'] = $transactionNumber->last_transaction_number + 1;
        $invoice = $profile->invoices()->create($fields);
        $transactionNumber->update(['last_transaction_number' => $fields['invoice_number']]);

        // Create Receipts
        foreach ($request->receipts as $receiptData) {
            $receiptFields = $receiptData;
            $receiptProfile = AccountingProfile::where([
                'patient_id' => $receiptData['patient_id'],
                'office_id' => $office->id,
                'doctor_id' => $request->doctor_id
            ])->first();

            $receiptFields['running_balance'] = $this->patientBalance($receiptProfile->id, $receiptFields['total_price']);
            $receiptFields['receipt_number'] = $transactionNumber->last_transaction_number + 1;
            $receiptFields['type'] = DentalDoctorTransaction::ResetVoucher;
            $receiptFields['date_of_payment'] = $receiptData['date_of_payment'] ?? now();
            $receipt = $receiptProfile->receipts()->create($receiptFields);
            $transactionNumber->update(['last_transaction_number' => $receiptFields['receipt_number']]);

            // Link Receipt to Invoice
            $invoice->receipts()->attach($receipt->id);
        }

        return new PatientInvoiceResource($invoice->load('receipts.personalAccount'));
    }

    // public function storePatientInvoiceWithItems(StoreInvoiceAndItemForPatientRequest $request, Patient $patient)
    // {
    //     // Validate the main invoice fields
    //     $fields = $request->validated();
    //     $office = Office::findOrFail($request->office_id);
    //     $transactionNumber = TransactionPrefix::where(['office_id' => $office->id, 'doctor_id' => auth()->user()->doctor->id, 'type' => TransactionType::PatientInvoice])->first();

    //     // Determine the doctor and profile based on office type
    //     if ($office->type == OfficeType::Combined) {
    //         $owner = User::findOrFail($office->owner->user_id);
    //         $profile = AccountingProfile::where([
    //             'patient_id' => $patient->id,
    //             'office_id' => $office->id, 'doctor_id' => $owner->doctor->id
    //         ])->first();
    //     } else {
    //         $profile = AccountingProfile::where([
    //             'patient_id' => $patient->id,
    //             'office_id' => $office->id, 'doctor_id' => $request->doctor_id
    //         ])->first();
    //     }

    //     // Set fields for invoice
    //     $fields['type'] = DentalDoctorTransaction::SellInvoice;
    //     if (!$request->has('date_of_invoice')) {
    //         $fields['date_of_invoice'] = now();
    //     }
    //     $fields['running_balance'] = $this->patientBalance($profile->id, $fields['total_price']);
    //     $fields['invoice_number'] = $transactionNumber->last_transaction_number + 1;

    //     // Create the invoice
    //     $invoice = $profile->invoices()->create($fields);
    //     $transactionNumber->update(['last_transaction_number' => $fields['invoice_number']]);

    //     // Process binding charges
    //     if ($request->has('binding_charges')) {
    //         foreach ($request->binding_charges as $bindingChargeId) {
    //             $bindingCharge = InvoiceItem::findOrFail($bindingChargeId);
    //             $itemData = [
    //                 'name' => $bindingCharge->name,
    //                 'description' => $bindingCharge->description,
    //                 'amount' => $bindingCharge->amount,
    //                 'total_price' => $bindingCharge->total_price,
    //                 'price_per_one' => $bindingCharge->price_per_one,
    //                 'coa_id' => $bindingCharge->coa_id,
    //                 'service_percentage' => $bindingCharge->service_percentage,
    //                 'teeth_record_id' => $bindingCharge->invoice->record->id,
    //             ];
    //             $item = $invoice->items()->create($itemData);

    //             // Determine the receivable based on office type
    //             if ($office->type == OfficeType::Combined) {
    //                 $receivable = COA::where([
    //                     'office_id' => $office->id,
    //                     'doctor_id' => null, 'sub_type' => COASubType::Receivable
    //                 ])->first();
    //             } else {
    //                 $doctor = $invoice->doctor;
    //                 $receivable = COA::where([
    //                     'office_id' => $office->id,
    //                     'doctor_id' => $doctor->id, 'sub_type' => COASubType::Receivable
    //                 ])->first();
    //             }

    //             // Create double entry for receivable
    //             $doubleEntryFields = [
    //                 'COA_id' => $receivable->id,
    //                 'invoice_item_id' => $item->id,
    //                 'total_price' => $item->total_price,
    //                 'type' => DoubleEntryType::Positive,
    //             ];
    //             $receivable->doubleEntries()->create($doubleEntryFields);

    //             // Create double entry for service COA
    //             $serviceCoa = COA::findOrFail($itemData['coa_id']);
    //             $doubleEntryFields['COA_id'] = $serviceCoa->COA_id;
    //             $serviceCoa->doubleEntries()->create($doubleEntryFields);
    //             $bindingCharge->delete();
    //         }
    //     }

    //     // Process invoice items
    //     if ($request->has('items')) {
    //         foreach ($request->items as $itemData) {
    //             $item = $invoice->items()->create($itemData);

    //             // Determine the receivable based on office type
    //             if ($office->type == OfficeType::Combined) {
    //                 $receivable = COA::where([
    //                     'office_id' => $office->id,
    //                     'doctor_id' => null, 'sub_type' => COASubType::Receivable
    //                 ])->first();
    //             } else {
    //                 $doctor = $invoice->doctor;
    //                 $receivable = COA::where([
    //                     'office_id' => $office->id,
    //                     'doctor_id' => $doctor->id, 'sub_type' => COASubType::Receivable
    //                 ])->first();
    //             }

    //             // Create double entry for receivable
    //             $doubleEntryFields = [
    //                 'COA_id' => $receivable->id,
    //                 'invoice_item_id' => $item->id,
    //                 'total_price' => $item->total_price,
    //                 'type' => DoubleEntryType::Positive,
    //             ];
    //             $receivable->doubleEntries()->create($doubleEntryFields);

    //             // Create double entry for service COA
    //             $serviceCoa = COA::findOrFail($itemData['coa_id']);
    //             $doubleEntryFields['COA_id'] = $serviceCoa->COA_id;
    //             $serviceCoa->doubleEntries()->create($doubleEntryFields);
    //         }
    //     }
    //     return new PatientInvoiceResource($invoice);
    // }

    private function createJVDoubleEntry($accountId, $invoiceId, $amount, $type, $isCoa)
    {
        if ($isCoa) {
            $coa = COA::findOrFail($accountId);
            $runningBalance = $this->calculateCOABalance($coa->id, $amount, $type);
        } else {
            $profile = AccountingProfile::findOrFail($accountId);
            $runningBalance = $this->calculatePatientBalance($profile->id, $type == 'positive' ? $amount : -$amount);
        }

        DirectDoubleEntry::create([
            'COA_id' => $isCoa ? $accountId : null,
            'accounting_profile_id' => !$isCoa ? $accountId : null,
            'direct_double_entry_invoice_id' => $invoiceId,
            'total_price' => $amount,
            'type' => $type,
            'running_balance' => $runningBalance
        ]);
    }

    private function determineTransactionType($accountType, $transactionNature)
    {
        $debitPositive = ['Asset', 'Expense', 'PatientAccount'];
        $creditPositive = ['Liability', 'Equity', 'Revenue', 'SupplierAccount', 'DentalLabDoctorAccount'];

        if (in_array($accountType, $debitPositive)) {
            return $transactionNature === 'debit' ? DoubleEntryType::Positive : DoubleEntryType::Negative;
        }

        if (in_array($accountType, $creditPositive)) {
            return $transactionNature === 'credit' ? DoubleEntryType::Positive : DoubleEntryType::Negative;
        }

        throw new \Exception('Invalid account type or transaction nature.');
    }

    public function storeJVWithTransactions(storeJournalInvoiceRequest $request)
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
        // Extract arrays from request
        $debitTransactions = $request->debit_transactions;
        $creditTransactions = $request->credit_transactions;

        // Calculate total amounts
        $totalDebit = array_sum(array_column($debitTransactions, 'amount'));
        $totalCredit = array_sum(array_column($creditTransactions, 'amount'));

        // Ensure the total amounts are equal
        if ($totalDebit !== $totalCredit) {
            throw new \Exception('Total debit and credit amounts must be equal.');
        }

        DB::beginTransaction();
        try {
            $transactionNumber = $this->getTransactionNumber($office, TransactionType::JournalVoucher, $doctor);
            // Create the invoice
            $fields['total_price'] = $totalDebit;
            $fields['date_of_transaction'] = $request->has('date_of_invoice') ? $request->date_of_invoice : now();
            $fields['type'] = DentalDoctorTransaction::JournalVoucher;
            $fields['receipt_number'] = $transactionNumber->last_transaction_number + 1;
            $fields['office_id'] = $request->office_id;
            $fields['doctor_id'] = $request->doctor_id;

            $invoice = DirectDoubleEntryInvoice::create($fields);
            $transactionNumber->update(['last_transaction_number' => $fields['receipt_number']]);

            // Process debit transactions
            foreach ($debitTransactions as $transaction) {
                $transactionType = $this->determineTransactionType($transaction['type'], 'debit');
                $this->createJVDoubleEntry($transaction['account_id'], $invoice->id, $transaction['amount'], $transactionType, $transaction['is_coa']);
            }

            // Process credit transactions
            foreach ($creditTransactions as $transaction) {
                $transactionType = $this->determineTransactionType($transaction['type'], 'credit');
                $this->createJVDoubleEntry($transaction['account_id'], $invoice->id, $transaction['amount'], $transactionType, $transaction['is_coa']);
            }

            DB::commit();
            return new DirectDoubleEntryResource($invoice);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating invoice with transactions: ' . $e->getMessage());
            throw $e;
        }
    }


    public function storePatientInvoiceWithItems(StoreInvoiceAndItemForPatientRequest $request, Patient $patient)
    {
        // Validate the main invoice fields
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
            $transactionNumber = $this->getTransactionNumber($office, TransactionType::PatientInvoice, $doctor);

            // Determine the doctor and profile based on office type
            $profile = $this->getAccountingProfile($office, $patient, $doctor->id);

            // Set fields for invoice
            $fields['type'] = DentalDoctorTransaction::SellInvoice;
            $fields['date_of_invoice'] = $fields['date_of_invoice'] ?? now();
            $fields['invoice_number'] = $transactionNumber->last_transaction_number + 1;
            if ($request->paid_done) {
                $fields['running_balance'] = $this->calculatePatientBalance($profile->id, 0);
                $fields['date_of_payment'] = $request->date_of_invoice;
                $invoice = $profile->invoiceReceipt()->create($fields);
                $cashCOA = COA::findOrFail($request->cash_coa);
                $this->createPaidDoubleEntry($cashCOA, $invoice->id, $request->total_price, DoubleEntryType::Positive, $invoice->accounting_profile_id);
            } else {
                $fields['running_balance'] = $this->calculatePatientBalance($profile->id, $fields['total_price']);
                // Create the invoice
                $invoice = $profile->invoices()->create($fields);
            }
            $transactionNumber->update(['last_transaction_number' => $fields['invoice_number']]);

            // Process binding charges
            if ($request->has('binding_charges')) {
                foreach ($request->binding_charges as $bindingChargeId) {
                    $checkBinding = InvoiceItem::findOrFail($bindingChargeId);
                    abort_if($checkBinding->coa->doctor->id != $doctor->id, 403, 'the coa have conflict');
                    $this->processBindingCharge($bindingChargeId, $invoice, $request->paid_done);
                }
            }

            // Process invoice items
            if ($request->has('items')) {
                foreach ($request->items as $itemData) {
                    $checkCOA = COA::findOrFail($itemData['coa_id']);
                    if (array_key_exists('teeth_record_id', $itemData)) {
                        // if($itemData['teeth_record_id'] == 0){

                        // }
                        $teethRecordId = TeethRecord::findOrFail($itemData['teeth_record_id']);
                        abort_if($teethRecordId->PatientCase->case->doctor_id != $doctor->id, 403, 'the teeth record id have conflict');
                    } else {
                        $itemData['teeth_record_id'] = null;
                    }
                    abort_if($checkCOA->doctor_id != $doctor->id, 403, 'the coa have conflict');
                    $this->processInvoiceItem($itemData, $invoice, $request->paid_done);
                }
            }

            DB::commit();
            return new PatientInvoiceResource($invoice);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating patient invoice with items: ' . $e->getMessage());
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

    private function getAccountingProfile($office, $patient, $doctorId)
    {
        if ($office->type == OfficeType::Combined) {
            $owner = User::findOrFail($office->owner->user_id);
            return AccountingProfile::where([
                'patient_id' => $patient->id,
                'office_id' => $office->id,
                'doctor_id' => $owner->doctor->id
            ])->first();
        } else {
            return AccountingProfile::where([
                'patient_id' => $patient->id,
                'office_id' => $office->id,
                'doctor_id' => $doctorId
            ])->first();
        }
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



    private function processBindingCharge($bindingChargeId, $invoice, $paid_done)
    {
        $bindingCharge = InvoiceItem::findOrFail($bindingChargeId);
        $itemData = [
            'name' => $bindingCharge->name,
            'description' => $bindingCharge->description,
            'amount' => $bindingCharge->amount,
            'total_price' => $bindingCharge->total_price,
            'price_per_one' => $bindingCharge->price_per_one,
            'coa_id' => $bindingCharge->coa_id,
            'service_percentage' => $bindingCharge->service_percentage,
            'teeth_record_id' => $bindingCharge->invoice->record->id,
        ];
        $item = $invoice->items()->create($itemData);

        // $receivable = $this->getReceivableCOA($invoice->office, $invoice->doctor);
        // $this->createDoubleEntry($receivable, $item->id, $item->total_price, DoubleEntryType::Positive, $invoice->accounting_profile_id);

        $serviceCoa = COA::findOrFail($itemData['coa_id']);
        $this->createDoubleEntry($serviceCoa, $item->id, $item->total_price, DoubleEntryType::Positive, $invoice->accounting_profile_id);
        if (!$paid_done) {
            // Add Positive double entry for the accounting profile
            $this->createProfileDoubleEntry($invoice->accounting_profile_id, $item->id, $item->total_price, DoubleEntryType::Positive);
        }
        $bindingCharge->delete();
    }


    private function processInvoiceItem($itemData, $invoice, $paid_done)
    {
        $item = $invoice->items()->create($itemData);

        // $receivable = $this->getReceivableCOA($invoice->office, $invoice->doctor);
        // $this->createDoubleEntry($receivable, $item->id, $item->total_price, DoubleEntryType::Positive, $invoice->accounting_profile_id);

        $serviceCoa = COA::findOrFail($itemData['coa_id']);
        $this->createDoubleEntry($serviceCoa, $item->id, $item->total_price, DoubleEntryType::Positive, $invoice->accounting_profile_id);
        if (!$paid_done) {
            // Add positive double entry for the accounting profile
            $this->createProfileDoubleEntry($invoice->accounting_profile_id, $item->id, $item->total_price, DoubleEntryType::Positive);
        }
    }


    private function createProfileDoubleEntry($accountingProfileId, $itemId, $totalPrice, $type)
    {
        $runningBalance = $this->calculatePatientBalance($accountingProfileId, $type == DoubleEntryType::Positive ? $totalPrice : -$totalPrice);

        DoubleEntry::create([
            'accounting_profile_id' => $accountingProfileId,
            'invoice_item_id' => $itemId,
            'total_price' => $totalPrice,
            'type' => $type,
            'running_balance' => $runningBalance
        ]);
    }


    private function getReceivableCOA($office, $doctor)
    {
        if ($office->type == OfficeType::Combined) {
            return COA::where([
                'office_id' => $office->id,
                'doctor_id' => null,
                'sub_type' => COASubType::Receivable
            ])->first();
        } else {
            return COA::where([
                'office_id' => $office->id,
                'doctor_id' => $doctor->id,
                'sub_type' => COASubType::Receivable
            ])->first();
        }
    }

    private function createDoubleEntry($coa, $itemId, $totalPrice, $type, $accountingProfileId)
    {
        $runningBalance = $this->calculateCOABalance($coa->id, $totalPrice, $type);

        DoubleEntry::create([
            'COA_id' => $coa->id,
            'invoice_item_id' => $itemId,
            'total_price' => $totalPrice,
            'type' => $type,
            // 'accounting_profile_id' => $accountingProfileId,
            'running_balance' => $runningBalance
        ]);
    }

    private function createPaidDoubleEntry($coa, $itemId, $totalPrice, $type, $accountingProfileId)
    {
        $runningBalance = $this->calculateCOABalance($coa->id, $totalPrice, $type);

        DoubleEntry::create([
            'COA_id' => $coa->id,
            'invoice_receipt_id' => $itemId,
            'total_price' => $totalPrice,
            'type' => $type,
            // 'accounting_profile_id' => $accountingProfileId,
            'running_balance' => $runningBalance
        ]);
    }



    public function changePatientInvoiceStatus(UpdatePatientInvoiceStatusRequest $request, Invoice $invoice)
    {
        $fields = $request->validated();
        $office = Office::findOrFail($request->office_id);
        $patient = $invoice->patient;
        abort_unless($patient != null, 403);
        abort_unless($invoice->doctor->id == auth()->user()->doctor->id, 403);
        $fields['status'] = TransactionStatus::getValue($fields['status']);
        if (($fields['status'] == TransactionStatus::Approved || $fields['status'] == TransactionStatus::Paid) && $invoice->status == TransactionStatus::Draft) {
            $transactionNumber = TransactionPrefix::where(['office_id' => $office->id, 'doctor_id' => auth()->user()->doctor->id, 'type' => TransactionType::PatientInvoice])->first();
            if ($office->type == OfficeType::Combined) {
                $owner = User::findOrFail($office->owner->user_id);
                $profile = AccountingProfile::where([
                    'patient_id' => $patient->id,
                    'office_id' => $office->id,
                    'doctor_id' => $owner->doctor->id
                ])->first();
                if (!$request->has('date_of_invoice')) {
                    $fields['date_of_invoice'] = now();
                }
                $fields['running_balance'] = $this->patientBalance($profile->id, $fields['total_price']);
                $fields['invoice_number'] = $transactionNumber->last_transaction_number + 1;
                $invoice->update($fields);
                $transactionNumber->update(['last_transaction_number' => $fields['invoice_number']]);
            } else {
                $profile = AccountingProfile::where([
                    'patient_id' => $patient->id,
                    'office_id' => $office->id,
                    'doctor_id' => $request->doctor_id
                ])->first();
                $fields['running_balance'] = $this->patientBalance($profile->id, $fields['total_price']);
                $fields['invoice_number'] = $transactionNumber->last_transaction_number + 1;
                $invoice->update($fields);
                $transactionNumber->update(['last_transaction_number' => $fields['invoice_number']]);
            }
            return new PatientInvoiceResource($invoice);
        } elseif ($fields['status'] == TransactionStatus::Paid && $invoice->status == TransactionStatus::Approved) {
            $invoice->update([
                'status' => $fields['status'],
            ]);
            return new PatientInvoiceResource($invoice);
        } else {
            return response('something went wrong', 404);
        }
    }

    public function storeSupplierInvoiceWithItems(StoreSupplierInvoiceRequest $request)
    {
        $fields = $request->validated();
        $profile = AccountingProfile::findOrFail($request->supplier_account_id);
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
        DB::beginTransaction();
        try {


            // Fetch transaction number
            $transactionNumber = $this->getTransactionNumber($office, TransactionType::SupplierInvoice, $doctor);

            // Set fields for the invoice
            $fields['running_balance'] = $this->calculatePatientBalance($profile->id, $fields['total_price']);
            $fields['invoice_number'] = $transactionNumber->last_transaction_number + 1;
            $fields['type'] = DentalDoctorTransaction::PercherInvoice;
            $fields['date_of_invoice'] = $request->has('date_of_invoice') ? $request->date_of_invoice : now();

            // Create the invoice
            $invoice = $profile->invoices()->create($fields);
            $transactionNumber->update(['last_transaction_number' => $fields['invoice_number']]);

            // Process each item
            foreach ($request->items as $itemData) {
                $item = $invoice->items()->create($itemData);

                // Create double entry for payable
                $this->createProfileDoubleEntry($invoice->accounting_profile_id, $item->id, $item->total_price, DoubleEntryType::Positive);

                // Create double entry for expense
                $expensesCoa = COA::findOrFail($itemData['item_coa']);
                $this->createDoubleEntry($expensesCoa, $item->id, $item->total_price, DoubleEntryType::Positive, $profile->id);
            }

            DB::commit();
            return new InvoiceResource($invoice->load(['doctor', 'office', 'items']));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating supplier invoice with items: ' . $e->getMessage());
            throw $e;
        }
    }


    public function storeDentalLabInvoice(StoreDentalLabInvoiceForDoctorRequest $request, AccountingProfile $profile)
    {
        $fields = $request->validated();
        $office = Office::findOrFail($request->office_id);
        if ($request->has('items')) {
            return $request;
        }
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
        $this->authorize('storeDentalLabInvoiceForDoctor', [Invoice::class, $profile, $doctor]);
        DB::beginTransaction();
        try {
            if ($request->invoice_id) {
                $invoice2 = Invoice::findOrFail($request->invoice_id);
                $this->authorize('acceptDentalLabInvoice', [$invoice2, $doctor]);
                $invoice2->update([
                    'status' => TransactionStatus::Approved,
                ]);
            }

            $fields['running_balance'] = $this->calculateLapBalance($profile->id, $fields['total_price']);
            $transactionNumber = TransactionPrefix::where(['office_id' => $profile->office->id, 'doctor_id' => $doctor->id, 'type' => TransactionType::SupplierInvoice])->first();
            $fields['invoice_number'] = $transactionNumber->last_transaction_number + 1;
            $fields['type'] = DentalDoctorTransaction::PercherInvoice;
            $fields['status'] = TransactionStatus::Approved;
            if (!$request->has('date_of_invoice')) {
                $fields['date_of_invoice'] = now();
            }
            $invoice = $profile->invoices()->create($fields);
            $transactionNumber->update(['last_transaction_number' => $fields['invoice_number']]);
            // Process binding charges
            // if ($request->has('binding_charges')) {
            //     foreach ($request->binding_charges as $bindingChargeId) {
            //         $checkBinding = InvoiceItem::findOrFail($bindingChargeId);
            //         abort_if($checkBinding->coa->doctor->id != $doctor->id, 403, 'the coa have conflict');
            //         $this->processBindingCharge($bindingChargeId, $invoice, $request->paid_done);
            //     }
            // }

            // Process invoice items
            // if ($request->has('items')) {
            //     foreach ($request->items as $itemData) {
            //         $checkCOA = COA::findOrFail($itemData['coa_id']);
            //         abort_if($checkCOA->doctor_id != $doctor->id, 403, 'the coa have conflict');
            //         $this->processInvoiceItem($itemData, $invoice, $request->paid_done);
            //     }
            // }

            DB::commit();
            return new InvoiceResource($invoice);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating lap invoice : ' . $e->getMessage());
            throw $e;
        }
    }

    public function storeDentalLabInvoiceWithItems(StoreLabInvoiceWithItems $request, AccountingProfile $profile)
    {
        $fields = $request->validated();
        $office = Office::findOrFail($request->office_id);

        if (in_array(auth()->user()->currentRole->name, Role::Technicians)) {
            // Find the role based on user_id and office_id (roleable_id)
            $role = HasRole::where('user_id', auth()->id())
                ->where('roleable_id', $office->id)
                ->first();

            if (!$role) {
                return response()->json([
                    'error' => 'Role not found for the given user and office.',
                ], 403);
            }

            // Find the employee setting based on the has_role_id
            $employeeSetting = EmployeeSetting::where('has_role_id', $role->id)->first();

            if (!$employeeSetting) {
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

        // Authorize the doctor to create a dental lab invoice
        $this->authorize('storeDentalLabInvoiceForDoctor', [Invoice::class, $profile, $doctor]);

        DB::beginTransaction();
        try {
            // Create or update the invoice
            if ($request->invoice_id) {
                $invoice = Invoice::findOrFail($request->invoice_id);
                $this->authorize('acceptDentalLabInvoice', [$invoice, $doctor]);
                $invoice->update([
                    'status' => TransactionStatus::Approved,
                ]);
            } else {
                // New invoice creation
                $fields['running_balance'] = $this->calculateLapBalance($profile->id, $fields['total_price']);
                $transactionNumber = TransactionPrefix::where([
                    'office_id' => $profile->office->id,
                    'doctor_id' => $doctor->id,
                    'type' => TransactionType::SupplierInvoice
                ])->first();
                $fields['invoice_number'] = $transactionNumber->last_transaction_number + 1;
                $fields['type'] = DentalDoctorTransaction::PercherInvoice;
                $fields['status'] = TransactionStatus::Approved;
                if (!$request->has('date_of_invoice')) {
                    $fields['date_of_invoice'] = now();
                }

                $invoice = $profile->invoices()->create($fields);
                $transactionNumber->update(['last_transaction_number' => $fields['invoice_number']]);
            }

            // Process and store invoice items
            if ($request->has('items')) {
                foreach ($request->items as $itemData) {
                    $expensesCoa = COA::findOrFail($itemData['coa_id']);
                    $this->validateCOA($expensesCoa, $doctor);

                    $item = $invoice->items()->create($itemData);
                    $this->createDoubleEntryForItem($invoice, $item, $expensesCoa);
                }
            }

            DB::commit();
            return new InvoiceResource($invoice->load(['doctor', 'office', 'items', 'lab']));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating lab invoice with items: ' . $e->getMessage());
            throw $e;
        }
    }

    private function validateCOA($coa, $doctor)
    {
        // Validate COA (Chart of Accounts) entries
        abort_unless($coa->doctor_id == $doctor->id, 403, 'COA does not belong to the doctor');
        abort_unless($coa->general_type == COAGeneralType::Expenses, 403, 'COA must be of Expenses type');
    }

    private function createDoubleEntryForItem($invoice, $item, $expensesCoa)
    {
        // Create double entry for the invoice item
        $doubleEntryFields = [
            'invoice_item_id' => $item->id,
            'total_price' => $item->total_price,
            'type' => DoubleEntryType::Positive
        ];

        $this->createProfileDoubleEntry($invoice->accounting_profile_id, $item->id, $item->total_price, DoubleEntryType::Positive);
        $expensesCoa->doubleEntries()->create($doubleEntryFields);
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

    public function processDraftInvoice(ProcessDraftInvoiceRequest $request)
    {
        $invoiceItemIds = $request->invoice_item_ids;

        // Fetch the invoice items and ensure they belong to a draft invoice
        $invoiceItems = InvoiceItem::whereIn('id', $invoiceItemIds)
            ->with('invoice')
            ->get();

        if ($invoiceItems->isEmpty()) {
            return response()->json(['message' => 'No valid invoice items found.'], 400);
        }

        // Collect all draft invoices
        $draftInvoices = $invoiceItems->pluck('invoice')->unique();
        $patientId = $invoiceItems->first()->invoice->patient_id;
        // Verify that all invoices are drafts
        foreach ($draftInvoices as $draftInvoice) {
            if ($draftInvoice->status != TransactionStatus::Draft) {
                return response()->json(['message' => 'One or more invoices are not drafts.'], 400);
            }
            if ($draftInvoice->patient_id != $patientId) {
                return response()->json(['message' => 'Invoice items belong to different patients.'], 400);
            }
        }
        $fields = $request->validated();
        $office =  $invoiceItems->first()->invoice->office_id;
        $patient =  $invoiceItems->first()->invoice->patient_id;
        $doctor =  $invoiceItems->first()->invoice->doctor_id;

        $transactionNumber = TransactionPrefix::where(['office_id' => $office->id, 'doctor_id' => auth()->user()->doctor->id, 'type' => TransactionType::PatientInvoice])->first();
        if ($office->type == OfficeType::Combined) {
            $owner = User::findOrFail($office->owner->user_id);
            $profile = AccountingProfile::where([
                'patient_id' => $patient->id,
                'office_id' => $office->id,
                'doctor_id' => $owner->doctor->id
            ])->first();
            $fields['type'] = DentalDoctorTransaction::SellInvoice;
            if (!$request->has('date_of_invoice')) {
                $fields['date_of_invoice'] = now();
            }
            $fields['running_balance'] = $this->patientBalance($profile->id, $fields['total_price']);
            $fields['invoice_number'] = $transactionNumber->last_transaction_number + 1;
            $fields['status'] = TransactionStatus::Paid;
            $newInvoice = $profile->invoices()->create($fields);
            $transactionNumber->update(['last_transaction_number' => $fields['invoice_number']]);
        } else {
            $profile = AccountingProfile::where([
                'patient_id' => $patient->id,
                'office_id' => $office->id,
                'doctor_id' => $doctor->id
            ])->first();
            $fields['running_balance'] = $this->patientBalance($profile->id, $fields['total_price']);
            $fields['invoice_number'] = $transactionNumber->last_transaction_number + 1;
            $fields['type'] = DentalDoctorTransaction::SellInvoice;
            $fields['status'] = TransactionStatus::Paid;
            $newInvoice = $profile->invoices()->create($fields);
            $transactionNumber->update(['last_transaction_number' => $fields['invoice_number']]);
        }

        // Assign the items to the new invoice and remove from the draft invoices
        foreach ($invoiceItems as $item) {
            $item->invoice_id = $newInvoice->id;
            $item->save();
        }

        // Remove the draft invoices if they have no more items
        foreach ($draftInvoices as $draftInvoice) {
            if ($draftInvoice->items()->count() == 0) {
                $draftInvoice->delete();
            }
        }
        return new PatientInvoiceResource($newInvoice);
        // return response()->json(['message' => 'Invoice processed successfully.', 'new_invoice_id' => $newInvoice->id]);
    }

    public function acceptDentalLabInvoice(AcceptDentalLabInvoiceForDoctorRequest $request, Invoice $invoice)
    {
        $fields = $request->validated();
        $this->authorize('acceptDentalLabInvoice', [$invoice]);
        $expensesCoa = COA::findOrFail($request->coa);
        $payable = COA::where([
            'office_id' => $invoice->office->id,
            'doctor_id' => $invoice->doctor->id,
            'sub_type' => COASubType::Payable
        ])->first();
        abort_unless($payable != null && $expensesCoa != null, 403);
        abort_unless($expensesCoa->doctor->id != auth()->user()->decoct->id, 403);
        abort_unless($expensesCoa->type == COAGeneralType::Expenses, 403);
        $account = $invoice->account;
        $fields['running_balance'] = $this->labBalance($account->id, $fields['total_price']);
        $transactionNumber = TransactionPrefix::where(['office_id' => $account->office->id, 'doctor_id' => auth()->user()->doctor->id, 'type' => TransactionType::SupplierInvoice])->first();
        $fields['invoice_number'] = $transactionNumber->last_transaction_number + 1;
        $fields['type'] = DentalDoctorTransaction::PercherInvoice;
        $fields['total_price'] = $invoice->total_price;
        if (!$request->has('date_of_invoice')) {
            $fields['date_of_invoice'] = now();
        }
        $percherInvoice = $account->invoices()->create($fields);
        $transactionNumber->update(['last_transaction_number' => $fields['invoice_number']]);
        foreach ($invoice->items as $item) {
            $percherInvoice->items->create([
                'name' => $item->name,
                'description' => $item->description,
                'amount' => $item->amount,
                'total_price' => $item->total_price,
                'price_per_one' => $item->price_per_one,
            ]);
        }
        $doubleEntryFields['invoice_id'] = $invoice->id;
        $doubleEntryFields['total_price'] = $invoice->total_price;
        $doubleEntryFields['type'] = DoubleEntryType::Positive;
        $payable->doubleEntries()->create($doubleEntryFields);
        $expensesCoa->doubleEntries()->create($doubleEntryFields);
        $invoice->update([
            'status' => TransactionStatus::Approved,
        ]);
        return new InvoiceResource($invoice->with(['doctor', 'office', 'items', 'lab'])->first());
    }

    public function rejectDentalLabInvoice(Invoice $invoice)
    {
        $this->authorize('acceptDentalLabInvoice', [$invoice]);
        $invoice->update([
            'status' => TransactionStatus::Rejected,
        ]);
        return new InvoiceResource($invoice->with(['doctor', 'office', 'items', 'lab'])->first());
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
        $total = $totalPositive - $totalNegative + $thisTransaction + $patient->initial_balance;
        return $total;
    }

    public static function supplierBalance(int $id, int $thisTransaction)
    {
        $supplier = AccountingProfile::findOrFail($id);
        $invoices = $supplier->invoices()->whereNot('status', TransactionStatus::Draft)->get();
        $totalNegative = $invoices != null ?
            $invoices->sum('total_price') : 0;
        $receipts = $supplier->receipts()->get();
        $totalPositive = $receipts != null ?
            $receipts->sum('total_price') : 0;
        $total = $totalPositive - $totalNegative - $thisTransaction + $supplier->initial_balance;
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
        $total = $totalPositive - $totalNegative - $thisTransaction + $supplier->secondary_initial_balance;
        return $total;
    }
}
