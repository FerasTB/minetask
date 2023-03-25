<?php

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
    Route::get('doctor/my_patient', [App\Http\Controllers\Api\DoctorInfoController::class, 'showMyPatient']);
    Route::apiResource('patient/info', App\Http\Controllers\Api\PatientInfoController::class);
    Route::apiResource('office', App\Http\Controllers\Api\OfficeController::class);
    Route::get('my_offices', [App\Http\Controllers\Api\OfficeController::class, 'MyOffices']);
    Route::apiResource('availability', App\Http\Controllers\Api\AvailabilityController::class);
    Route::apiResource('appointment', App\Http\Controllers\Api\AppointmentController::class);
    Route::apiResource('service', App\Http\Controllers\Api\MedicalServiceController::class);
    Route::get('service/office/{office}', [App\Http\Controllers\Api\MedicalServiceController::class, 'officeService']);
    Route::get('availability/office/{office}', [App\Http\Controllers\Api\AvailabilityController::class, 'officeAvailability']);
    Route::get('availability/doctor/{doctor}', [App\Http\Controllers\Api\AvailabilityController::class, 'doctorAvailability']);
    Route::post('office/{office}/add_employee', [App\Http\Controllers\Api\OfficeController::class, 'AddDoctor']);
    Route::get('office/{office}/show_employee', [App\Http\Controllers\Api\OfficeController::class, 'AllDoctorInOffice']);
});
