<?php

use App\Http\Controllers\Api\DentalLab\AccountingProfileController;
use App\Http\Controllers\Api\DentalLab\CoaController;
use App\Http\Controllers\Api\DentalLab\DentalLabController;
use App\Http\Controllers\Api\DentalLab\DentalLabServiceController;
use App\Http\Controllers\Api\DentalLab\DoctorController;
use App\Http\Controllers\Api\DentalLab\InvoiceController;
use App\Http\Controllers\Api\DentalLab\InvoiceItemController;
use App\Http\Controllers\Api\DentalLab\ReceiptController;
use App\Http\Controllers\Api\DentalLabControlle;
use App\Http\Controllers\Api\DoctorInfoController;
use App\Http\Controllers\RoleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/auth/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
Route::post('/auth/register', [\App\Http\Controllers\Api\AuthController::class, 'register']);

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::apiResource('doctor/info', App\Http\Controllers\Api\DoctorInfoController::class);
    Route::get('doctor/profile', [App\Http\Controllers\Api\DoctorInfoController::class, 'showMyInfo']);
    Route::get('doctor/docs', [App\Http\Controllers\Api\DoctorInfoController::class, 'myRecords']);
    Route::get('doctor/patient/docs/{patient}', [App\Http\Controllers\Api\DoctorInfoController::class, 'myRecordsForPatient']);
    Route::get('doctor/case/docs/{case}', [App\Http\Controllers\Api\DoctorInfoController::class, 'myRecordsForCase']);
    Route::get('office/{office}/doctor/my_patient', [App\Http\Controllers\Api\DoctorInfoController::class, 'showMyPatient']);
    Route::get('office/{office}/doctor/active/patient', [App\Http\Controllers\Api\DoctorInfoController::class, 'activePatient']);
    Route::get('office/{office}/doctor/{patient}/drug', [App\Http\Controllers\Api\DoctorInfoController::class, 'drug']);
    Route::apiResource('patient/info', App\Http\Controllers\Api\PatientInfoController::class);
    Route::get('patient/{patient}/medical_info', [App\Http\Controllers\Api\PatientInfoController::class, 'showMedicalInformation']);
    Route::get('office/coa', [App\Http\Controllers\Api\COAController::class, 'indexOwner']);
    Route::apiResource('office', App\Http\Controllers\Api\OfficeController::class);
    Route::get('doctor/{doctor}/office', [App\Http\Controllers\Api\OfficeController::class, 'doctorOffice']);
    Route::get('my_offices', [App\Http\Controllers\Api\OfficeController::class, 'MyOffices']);
    Route::apiResource('availability', App\Http\Controllers\Api\AvailabilityController::class);
    Route::apiResource('appointment', App\Http\Controllers\Api\AppointmentController::class);
    Route::apiResource('vacation', App\Http\Controllers\Api\VacationController::class);
    Route::apiResource('service', App\Http\Controllers\Api\MedicalServiceController::class);
    Route::apiResource('case', App\Http\Controllers\Api\CaseController::class);
    Route::apiResource('patient_case', App\Http\Controllers\Api\PatientCaseController::class);
    Route::put('patient_case/status/{case}', [App\Http\Controllers\Api\PatientCaseController::class, 'ChangeStatus']);
    Route::get('case/patient/{patient}', [App\Http\Controllers\Api\PatientCaseController::class, 'patientCases']);
    Route::put('appointment/{appointment}/update/status', [App\Http\Controllers\Api\AppointmentController::class, 'appointmentStatusUpdate']);
    Route::apiResource('record', App\Http\Controllers\Api\TeethRecordController::class);
    Route::put('record/{record}/after/treatment', [App\Http\Controllers\Api\TeethRecordController::class, 'AfterTreatmentUpdate']);
    Route::post('appointment/first', [App\Http\Controllers\Api\TeethRecordController::class, 'firstStep']);
    Route::get('record/case/{case}', [App\Http\Controllers\Api\TeethRecordController::class, 'CaseRecords']);
    Route::apiResource('operation', App\Http\Controllers\Api\OperationController::class);
    Route::get('operation/record/{record}', [App\Http\Controllers\Api\OperationController::class, 'RecordOperation']);
    Route::post('operation/{operation}/add_tooth', [App\Http\Controllers\Api\OperationController::class, 'addTooth']);
    Route::apiResource('diagnosis', App\Http\Controllers\Api\DiagnosisController::class);
    Route::get('diagnosis/record/{record}', [App\Http\Controllers\Api\DiagnosisController::class, 'RecordDiagnosis']);
    Route::post('diagnosis/{diagnosis}/add_tooth', [App\Http\Controllers\Api\DiagnosisController::class, 'addTooth']);
    Route::apiResource('drug', App\Http\Controllers\Api\DrugController::class);
    Route::get('service/office/{office}', [App\Http\Controllers\Api\MedicalServiceController::class, 'officeService']);
    Route::get('availability/office/{office}', [App\Http\Controllers\Api\AvailabilityController::class, 'officeAvailability']);
    Route::get('availability/doctor/{doctor}', [App\Http\Controllers\Api\AvailabilityController::class, 'doctorAvailability']);
    Route::post('office/{office}/add_doctor', [App\Http\Controllers\Api\OfficeController::class, 'AddDoctor']);
    Route::post('office/{office}/add_employee', [App\Http\Controllers\Api\OfficeController::class, 'addEmployee']);
    Route::put('office/{office}/employee/{patient}/property', [App\Http\Controllers\Api\OfficeController::class, 'updateEmployeeProperty']);
    Route::get('office/{office}/show_employee', [App\Http\Controllers\Api\OfficeController::class, 'AllDoctorInOffice']);
    Route::apiResource('medical/info', App\Http\Controllers\Api\MedicalInformationController::class);
    // Route::apiResource('accounting/debt', App\Http\Controllers\Api\DebtController::class);
    // Route::get('accounting/patient/debt/{patient}', [App\Http\Controllers\Api\DebtController::class, 'patientDebt']);
    Route::apiResource('accounting/receipt', App\Http\Controllers\Api\ReceiptController::class);
    Route::apiResource('accounting', App\Http\Controllers\Api\AccountingProfileController::class);
    Route::get('accounting/patient/profile', [App\Http\Controllers\Api\AccountingProfileController::class, 'patientProfile']);
    Route::put('account/{accounting}/initial', [App\Http\Controllers\Api\AccountingProfileController::class, 'setInitialBalance']);
    Route::put('account/{office}/{patient}/initial', [App\Http\Controllers\Api\PatientInfoController::class, 'setInitialBalance']);
    Route::get('account/{accounting}/balance', [App\Http\Controllers\Api\AccountingProfileController::class, 'accountOutcome']);
    Route::get('accounting/supplier/profile', [App\Http\Controllers\Api\AccountingProfileController::class, 'supplierProfile']);
    Route::get('accounting/expenses/profile', [App\Http\Controllers\Api\AccountingProfileController::class, 'expensesProfile']);
    Route::get('office/{office}/accounting/lab/profile', [App\Http\Controllers\Api\AccountingProfileController::class, 'labProfile']);
    Route::post('accounting/supplier/profile', [App\Http\Controllers\Api\AccountingProfileController::class, 'storeSupplier']);
    // Route::post('accounting/expense/profile', [App\Http\Controllers\Api\AccountingProfileController::class, 'storeExpenses']);
    Route::post('accounting/receipt/{receipt}/invoice/{invoice}', [App\Http\Controllers\Api\ReceiptController::class, 'addReceiptToInvoice']);
    Route::apiResource('accounting/supplier/{office}/item', App\Http\Controllers\Api\SupplierItemController::class);
    Route::apiResource('list/complaint', App\Http\Controllers\Api\TeethComplaintListController::class);
    Route::apiResource('list/diagnosis', App\Http\Controllers\Api\DiagnosisListController::class);
    Route::apiResource('list/drug', App\Http\Controllers\Api\DrugListController::class);
    Route::apiResource('coa', App\Http\Controllers\Api\COAController::class);
    Route::get('office/{office}/coa/group/owner', [App\Http\Controllers\Api\CoaGroupController::class, 'indexOwner']);
    Route::apiResource('office/{office}/coa/group', App\Http\Controllers\Api\CoaGroupController::class);
    Route::put('coa/{coa}/initial', [App\Http\Controllers\Api\COAController::class, 'setInitialBalance']);
    Route::get('coa/{coa}/balance', [App\Http\Controllers\Api\COAController::class, 'coaOutcome']);
    Route::apiResource('coa/{coa}/direct/entry', App\Http\Controllers\Api\DirectDoubleEntryInvoiceController::class);
    Route::post('receipt/patient/{patient}', [App\Http\Controllers\Api\ReceiptController::class, 'storePatientReceipt']);
    Route::post('invoice/patient/{patient}', [App\Http\Controllers\Api\InvoiceController::class, 'storePatientInvoice']);
    Route::post('invoice/patient/{invoice}/item', [App\Http\Controllers\Api\InvoiceItemController::class, 'storePatientInvoiceItem']);
    Route::post('invoice/lap/accept/{invoice}', [App\Http\Controllers\Api\InvoiceController::class, 'acceptDentalLabInvoice']);
    Route::get('invoice/lap/reject/{invoice}', [App\Http\Controllers\Api\InvoiceController::class, 'rejectDentalLabInvoice']);
    Route::post('invoice/lab/{profile}', [App\Http\Controllers\Api\InvoiceController::class, 'storeDentalLabInvoice']);
    Route::post('invoice/lab/{invoice}/item', [App\Http\Controllers\Api\InvoiceItemController::class, 'storeDentalLabInvoiceItem']);
    Route::post('receipt/supplier', [App\Http\Controllers\Api\ReceiptController::class, 'storeSupplierReceipt']);
    Route::post('invoice/supplier', [App\Http\Controllers\Api\InvoiceController::class, 'storeSupplierInvoice']);
    Route::post('invoice/supplier/{invoice}/item', [App\Http\Controllers\Api\InvoiceItemController::class, 'storeSupplierInvoiceItem']);
    Route::post('invoice_receipt/account/{patient}', [App\Http\Controllers\Api\InvoiceReceiptsController::class, 'storeForPatient']);
    Route::post('invoice_receipt/patient/{invoice}/item', [App\Http\Controllers\Api\InvoiceItemController::class, 'storePatientInvoiceReceiptItem']);
    Route::apiResource('office/{office}/note', App\Http\Controllers\Api\NoteController::class);
    Route::post('accounting/expense/profile', [App\Http\Controllers\Api\ExpenseController::class, 'storeExpenseAccount']);
    Route::post('add/prefix/office/{office}/doctor/{doctor}', [App\Http\Controllers\TransactionPrefixController::class, 'temprary']);
    Route::get('get/prefix/office/{office}', [App\Http\Controllers\TransactionPrefixController::class, 'index']);
    Route::post('doctor/image/office/{office}', [App\Http\Controllers\DoctorImageController::class, 'store']);
    Route::get('doctor/image/{image}', [App\Http\Controllers\DoctorImageController::class, 'show']);
    Route::get('app/patient/appointment', [App\Http\Controllers\Api\AppointmentController::class, 'indexForPatient']);
    Route::get('app/patient/record', [App\Http\Controllers\Api\PatientInfoController::class, 'patientsRecord']);
    Route::get('app/patient/doctor', [App\Http\Controllers\Api\PatientInfoController::class, 'patientsDoctor']);
    Route::get('app/patient/drug', [App\Http\Controllers\Api\PatientInfoController::class, 'patientsDrug']);
    Route::post('app/patient/complete/info', [App\Http\Controllers\Api\PatientInfoController::class, 'patientsInfo']);
    Route::put('app/patient/update/info', [App\Http\Controllers\Api\PatientInfoController::class, 'updatePatientsInfo']);
    Route::get('/switch-role/{role}', [RoleController::class, 'switchRole']);
    Route::get('/assign-role/{role}', [RoleController::class, 'assignRole']);
    Route::get('/role', [RoleController::class, 'index']);
    Route::post('/office/{office}/accounting/lab/profile', [DentalLabControlle::class, 'store']);
    Route::get('/doctor/unread/notification', [DoctorInfoController::class, 'unreadNotification']);
    Route::get('/doctor/mark/read/notification', [DoctorInfoController::class, 'markAsRead']);
    Route::get('/doctor/all/notification', [DoctorInfoController::class, 'allNotification']);
});
Route::group(['middleware' => ['auth:sanctum', 'isDentalLab']], function () {
    Route::apiResource('dental/lab', DentalLabController::class);
    Route::get('dental/lab/{lab}/add/inventory', [DentalLabController::class, 'addInventory']);
    Route::post('dental/lab/supplier/create', [AccountingProfileController::class, 'storeSupplier']);
    Route::post('dental/lab/supplier/{profile}/create/invoice', [InvoiceController::class, 'storeSupplierInvoice']);
    Route::post('dental/lab/supplier/create/invoice/{invoice}/item', [InvoiceItemController::class, 'storeSupplierInvoiceItem']);
    Route::post('dental/lab/supplier/{profile}/create/receipt', [ReceiptController::class, 'storeSupplierReceipt']);
    Route::post('dental/lab/{lab}/doctor/{doctor}/create', [AccountingProfileController::class, 'StoreAccountProfileForDoctor']);
    Route::post('dental/lab/{lab}/doctor/not-exist/{doctor}/create', [AccountingProfileController::class, 'StoreAccountProfileForNotExistDoctor']);
    Route::get('dental/lab/{lab}/my-doctor', [DoctorController::class, 'allDoctor']);
    Route::get('dental/lab/{lab}/only-me-doctor', [DoctorController::class, 'labDoctor']);
    Route::post('dental/lab/{lab}/doctor/create', [DoctorController::class, 'storeDoctor']);
    Route::apiResource('dental/lab/{lab}/coa', CoaController::class);
    Route::put('dental/lab/{lab}/coa/{coa}/initial', [CoaController::class, 'setInitialBalance']);
    Route::apiResource('dental/lab/{lab}/service', DentalLabServiceController::class);
    Route::post('dental/lab/invoice/doctor/{profile}', [InvoiceController::class, 'storeDoctorInvoice']);
    Route::post('dental/lab/invoice/doctor/{invoice}/item', [InvoiceController::class, 'storeDoctorInvoiceItem']);
    Route::post('dental/lab/receipt/doctor/{profile}', [ReceiptController::class, 'storeDoctorReceipt']);
    Route::post('dental/lab/accept/receipt/{receipt}', [ReceiptController::class, 'acceptDoctorReceipt']);
});
