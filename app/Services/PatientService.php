<?php

namespace App\Services;

use App\Enums\AccountingProfileType;
use App\Enums\AiTaskType;
use App\Enums\DoctorRoleForPatient;
use App\Enums\Gender;
use App\Enums\MaritalStatus;
use App\Enums\OfficeType;
use App\Enums\ReportType;
use App\Enums\Role;
use App\Helpers\ValidationHelper;
use App\Http\Resources\MyPatientsResource;
use App\Models\Doctor;
use App\Models\HasRole;
use App\Models\MedicalCase;
use App\Models\Office;
use App\Models\Patient;
use App\Models\TemporaryTask;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;

class PatientService
{
    public function createPatient(array $extractedData, int $doctorId, int $officeId)
    {
        $fields = $extractedData;
        if ($fields['marital'] != null) {
            $fields['marital'] = MaritalStatus::getValue($fields['marital']);
        }
        if ($fields['gender'] != null) {
            $fields['gender'] = Gender::getValue($fields['gender']);
        }
        // Check if the patient is a child
        // $isChild = $request->has('is_child') && $request->is_child == true;

        // // Set parent_id if it's a child
        // if ($isChild) {
        //     $parent = Patient::findOrFail($request->parent_id);

        //     // Set father or mother name based on parent's gender
        //     if ($parent->gender == Gender::Male) {
        //         $fields['father_name'] = $parent->first_name . ' ' . $parent->last_name;
        //     } elseif ($parent->gender == Gender::Female) {
        //         $fields['mother_name'] = $parent->first_name . ' ' . $parent->last_name;
        //     }

        //     unset($fields['phone']); // Remove phone field for child
        //     $fields['parent_id'] = $parent->id;
        // }
        if (auth()->user()->role == Role::Patient) {
            $patient = Patient::where('phone', $fields['phone'])->first();
            if ($patient || auth()->user()->patient) {
                return response()->noContent();
            }
            $patientInfo = auth()->user()->patient()->create($fields);
            $patientTeethReport = $patientInfo->report()->create([
                'report_type' => ReportType::TeethReport,
            ]);
            return response()->json($patientInfo);
        } elseif (auth()->user()->role == Role::Doctor) {
            $patient = Patient::where('phone', $fields['phone'])->first();
            $office = Office::findOrFail($officeId);
            $ownerUser = User::find($office->owner->user_id);
            $ownerDoctor = $ownerUser->doctor;
            if ($office->type == OfficeType::Combined) {
                if ($patient) {
                    $fields['doctor_id'] = $ownerDoctor->id;
                    $temporary = $patient->temporaries()->create($fields);
                    $role = HasRole::create([
                        'user_id' => $ownerUser->id,
                        'roleable_type' => 'App\Models\Patient',
                        'roleable_id' => $patient->id,
                        'sub_role' => DoctorRoleForPatient::DoctorWithoutApprove
                    ]);
                    $patientAccountingProfile = $patient->accountingProfiles()->create([
                        'office_id' => $office->id,
                        'doctor_id' => $ownerDoctor->id,
                        'type' => AccountingProfileType::PatientAccount,
                    ]);
                    $case = MedicalCase::where(['case_name' => Doctor::DefaultCase, 'doctor_id' => $ownerDoctor->id])->first();
                    $defaultCase = $patient->cases()->create([
                        'case_id' => $case->id,
                    ]);
                    return new MyPatientsResource($role);
                } else {
                    $doctor = $ownerDoctor;
                    $patientInfo = $doctor->patients()->create($fields);
                    $role = $ownerUser->roles()->create([
                        'roleable_type' => 'App\Models\Patient',
                        'roleable_id' => $patientInfo->id,
                        'sub_role' => DoctorRoleForPatient::DoctorWithApprove
                    ]);
                    $patientTeethReport = $patientInfo->report()->create([
                        'report_type' => ReportType::TeethReport,
                    ]);
                    $patientAccountingProfile = $patientInfo->accountingProfiles()->create([
                        'office_id' => $office->id,
                        'doctor_id' => $doctor->id,
                        'type' => AccountingProfileType::PatientAccount,
                    ]);
                    $case = MedicalCase::where(['case_name' => Doctor::DefaultCase, 'doctor_id' => $doctor->id])->first();
                    $defaultCase = $patientInfo->cases()->create([
                        'case_id' => $case->id,
                    ]);
                    return new MyPatientsResource($role);
                }
            } elseif ($office->type == OfficeType::Separate) {
                if ($patient) {
                    $fields['doctor_id'] = auth()->user()->doctor->id;
                    $temporary = $patient->temporaries()->create($fields);
                    $role = HasRole::create([
                        'user_id' => auth()->user()->id,
                        'roleable_type' => 'App\Models\Patient',
                        'roleable_id' => $patient->id,
                        'sub_role' => DoctorRoleForPatient::DoctorWithoutApprove
                    ]);
                    $patientAccountingProfile = $patient->accountingProfiles()->create([
                        'office_id' => $office->id,
                        'doctor_id' => $doctorId,
                        'type' => AccountingProfileType::PatientAccount,
                    ]);
                    $case = MedicalCase::where(['case_name' => Doctor::DefaultCase, 'doctor_id' => $doctorId])->first();
                    $defaultCase = $patient->cases()->create([
                        'case_id' => $case->id,
                    ]);
                    return new MyPatientsResource($role);
                } else {
                    $doctor = auth()->user()->doctor;
                    $patientInfo = $doctor->patients()->create($fields);
                    $role = auth()->user()->roles()->create([
                        'roleable_type' => 'App\Models\Patient',
                        'roleable_id' => $patientInfo->id,
                        'sub_role' => DoctorRoleForPatient::DoctorWithApprove
                    ]);
                    $patientTeethReport = $patientInfo->report()->create([
                        'report_type' => ReportType::TeethReport,
                    ]);
                    $patientAccountingProfile = $patientInfo->accountingProfiles()->create([
                        'doctor_id' => auth()->user()->doctor->id,
                        'office_id' => $office->id,
                        'type' => AccountingProfileType::PatientAccount,
                    ]);
                    $case = MedicalCase::where(['case_name' => Doctor::DefaultCase, 'doctor_id' => auth()->user()->doctor->id])->first();
                    $defaultCase = $patientInfo->cases()->create([
                        'case_id' => $case->id,
                    ]);
                    return new MyPatientsResource($role);
                }
            }
        }
    }
    protected function customMerge(array $oldData, array $newData)
    {
        $mergedData = $oldData;

        foreach ($newData as $key => $value) {
            if ($value !== null) {
                $mergedData[$key] = $value;
            }
        }

        return $mergedData;
    }
    public function startAddingPatientTask(Request $request, $oldData)
    {
        // Ensure the request contains 'text'
        $validated = $request->validate([
            'text' => 'required|string',
            'office_id' => 'required|integer|exists:offices,id',

        ]);

        // Define the endpoint URL
        $url = 'https://abbc-34-125-41-4.ngrok-free.app';

        // Create a Guzzle HTTP client
        $client = new Client();

        try {
            // Make the POST request to the Flask endpoint
            $response = $client->post($url . "/extract", [
                'json' => [
                    'text' => $validated['text'],
                    'office_id' => 'required|integer|exists:offices,id',
                ],
            ]);

            // Parse the response
            $responseData = json_decode($response->getBody()->getContents(), true);

            if (isset($responseData['extracted_data'])) {
                $extractedData = json_decode($responseData['extracted_data'], true);
                if ($oldData != null) {
                    $oldData = json_decode($oldData, true);
                    $extractedData = $this->customMerge($extractedData, $oldData);
                }

                // Validate the required non-null keys
                $requiredKeys = ["first_name", "last_name", "gender", "phone"];
                $validationResult = ValidationHelper::validateNonNullKeys($extractedData, $requiredKeys);

                if (!$validationResult['success']) {
                    // Save the task data
                    $task = TemporaryTask::create([
                        'user_id' => auth()->id(),
                        'data' => json_encode($extractedData),
                        'task_type' => AiTaskType::AddingPatient,
                    ]);

                    return response()->json([
                        'status' => 'error',
                        'message' => $validationResult['message'],
                        'task_id' => $task->id
                    ], 400);
                }

                // Get the doctor ID from the authenticated user
                $doctorId = auth()->user()->doctor->id;
                // Use the patient service to create a new patient
                $patientResponse = $this->createPatient($extractedData, $doctorId, $validated['office_id']);

                return $patientResponse;
            } else {
                // Return an error if extracted_data is not present
                return response()->json([
                    'status' => 'error',
                    'message' => 'No extracted data found'
                ], 400);
            }
        } catch (RequestException $e) {
            // Handle errors
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : null;
            $errorMessage = $responseBody ? json_decode($responseBody, true)['error'] ?? 'An error occurred while processing the request' : 'An error occurred while processing the request';
            $extractedData = $responseBody ? json_decode($responseBody, true)['extracted_data'] ?? 'An error occurred while processing the request' : 'An error occurred while processing the request';
            $task = auth()->user()->temporaryTasks()->create([
                'data' => $extractedData,
                'task_type' => AiTaskType::AddingPatient,
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $errorMessage,
                'task_id' => $task->id
            ], $e->getResponse() ? $e->getResponse()->getStatusCode() : 500);
        }
    }

    // public function storeAppointment(Request $request, $oldData)
    // {

    //     // Ensure the request contains 'text'
    //     $validated = $request->validate([
    //         'text' => 'required|string',
    //         'office_id' => 'required|integer|exists:offices,id',
    //     ]);

    //     // Define the endpoint URL
    //     $url = 'https://abbc-34-125-41-4.ngrok-free.app';

    //     // Create a Guzzle HTTP client
    //     $client = new Client();

    //     try {
    //         // Make the POST request to the Flask endpoint
    //         $response = $client->post($url . "/add-appointment", [
    //             'json' => [
    //                 'text' => $validated['text'],
    //                 'office_id' => 'required|integer|exists:offices,id',
    //             ],
    //         ]);

    //         // Parse the response
    //         $responseData = json_decode($response->getBody()->getContents(), true);

    //         if (isset($responseData['extracted_data'])) {
    //             $extractedData = json_decode($responseData['extracted_data'], true);
    //             if ($oldData != null) {
    //                 $oldData = json_decode($oldData, true);
    //                 $extractedData = $this->customMerge($extractedData, $oldData);
    //             }

    //             // Validate the required non-null keys
    //             $requiredKeys = ["start_time", "end_time", "taken_date", "phone"];
    //             $validationResult = ValidationHelper::validateNonNullKeys($extractedData, $requiredKeys);

    //             if (!$validationResult['success']) {
    //                 // Save the task data
    //                 $task = TemporaryTask::create([
    //                     'user_id' => auth()->id(),
    //                     'data' => json_encode($extractedData),
    //                     'task_type' => AiTaskType::AddingPatient,
    //                 ]);

    //                 return response()->json([
    //                     'status' => 'error',
    //                     'message' => $validationResult['message'],
    //                     'task_id' => $task->id
    //                 ], 400);
    //             }

    //             // Get the doctor ID from the authenticated user
    //             $doctorId = auth()->user()->doctor->id;
    //             // Use the patient service to create a new patient
    //             $patientResponse = $this->createPatient($extractedData, $doctorId, $validated['office_id']);

    //             return $patientResponse;
    //         } else {
    //             // Return an error if extracted_data is not present
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'No extracted data found'
    //             ], 400);
    //         }
    //     } catch (RequestException $e) {
    //         // Handle errors
    //         $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : null;
    //         $errorMessage = $responseBody ? json_decode($responseBody, true)['error'] ?? 'An error occurred while processing the request' : 'An error occurred while processing the request';
    //         $extractedData = $responseBody ? json_decode($responseBody, true)['extracted_data'] ?? 'An error occurred while processing the request' : 'An error occurred while processing the request';
    //         $task = auth()->user()->temporaryTasks()->create([
    //             'data' => $extractedData,
    //             'task_type' => AiTaskType::AddingPatient,
    //         ]);
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => $errorMessage,
    //             'task_id' => $task->id
    //         ], $e->getResponse() ? $e->getResponse()->getStatusCode() : 500);
    //     }
    //     $fields = $request->validated();
    //     $office = Office::findOrFail($request->office_id);
    //     if (!$request->doctor_id) {
    //         $doctor = auth()->user()->doctor;
    //         $fields['doctor_id'] = $doctor->id;
    //     } else {
    //         $doctor = Doctor::find($request->doctor_id);
    //     }
    //     $this->authorize('createForDoctor', [Appointment::class, $office, $doctor]);
    //     if ($request->patientCase_id) {
    //         $patientCase = PatientCase::findOrFail($request->patientCase_id);
    //         $this->authorize('update', $patientCase);
    //     }
    //     if ($request->office_room_id) {
    //         $room = OfficeRoom::findOrFail($request->office_room_id);
    //         abort_unless($room->office_id == $office->id, 403);
    //     }
    //     $appointment = $doctor->appointments()->create($fields);
    //     $appointment = Appointment::find($appointment->id);
    //     $appointment->load(
    //         'patient',
    //         'patient.doctorImage',
    //         'doctor',
    //         'office',
    //         'room',
    //         'case',
    //         'case.case',
    //         'case.teethRecords',
    //         'record',
    //         'record.diagnosis',
    //         'record.diagnosis.drug',
    //         'record.operations',
    //         'record.diagnosis.teeth',
    //         'record.operations.teeth',
    //     );
    //     return new AppointmentResource($appointment);
    // }
}
