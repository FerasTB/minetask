<?php

namespace App\Http\Controllers\Ai;

use App\Enums\AiTaskType;
use App\Helpers\ValidationHelper;
use GuzzleHttp\Client;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\TemporaryTask;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use App\Services\PatientService;

class PatientController extends Controller
{
    protected $patientService;

    public function __construct(PatientService $patientService)
    {
        $this->patientService = $patientService;
    }
    // public function startAddingPatientTask(Request $request)
    // {
    //     // Ensure the request contains 'text'
    //     $validated = $request->validate([
    //         'text' => 'required|string',
    //         'office_id' => 'required|integer|exists:offices,id',

    //     ]);

    //     // Define the endpoint URL
    //     $url = 'https://30b4-34-44-241-34.ngrok-free.app';

    //     // Create a Guzzle HTTP client
    //     $client = new Client();

    //     try {
    //         // Make the POST request to the Flask endpoint
    //         $response = $client->post($url . "/extract", [
    //             'json' => [
    //                 'text' => $validated['text'],
    //                 'office_id' => 'required|integer|exists:offices,id',
    //             ],
    //         ]);

    //         // Parse the response
    //         $responseData = json_decode($response->getBody()->getContents(), true);

    //         if (isset($responseData['extracted_data'])) {
    //             $extractedData = json_decode($responseData['extracted_data'], true);

    //             // Validate the required non-null keys
    //             $requiredKeys = ["first_name", "last_name", "gender", "phone"];
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
    //             $patientResponse = $this->patientService->createPatient($extractedData, $doctorId, $validated['office_id']);

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
    // }
}
