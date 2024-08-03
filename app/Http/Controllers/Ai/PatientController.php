<?php

namespace App\Http\Controllers\Ai;

use GuzzleHttp\Client;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function startAddingPatientTask(Request $request)
    {
        // Ensure the request contains 'text'
        $validated = $request->validate([
            'text' => 'required|string',
        ]);

        // Define the endpoint URL
        $url = 'https://30b4-34-44-241-34.ngrok-free.app';

        // Create a Guzzle HTTP client
        $client = new Client();

        try {
            // Make the POST request to the Flask endpoint
            $response = $client->post($url . "/extract", [
                'json' => [
                    'text' => $validated['text'],
                ],
            ]);

            // Parse the response
            $responseData = json_decode($response->getBody()->getContents(), true);

            if (isset($responseData['extracted_data'])) {
                $extractedData = json_decode($responseData['extracted_data'], true);
                // Get the doctor ID from the authenticated user
                $doctorId = auth()->user()->doctor->id;

                // Create a new patient with the extracted data
                $patient = Patient::create([
                    'first_name' => $extractedData['first_name'],
                    'last_name' => $extractedData['last_name'],
                    'gender' => $extractedData['gender'],
                    'note' => $extractedData['note'],
                    'phone' => $extractedData['phone'],
                    'birth_date' => $extractedData['birth_date'],
                    'marital' => $extractedData['marital'],
                    'father_name' => $extractedData['father_name'],
                    'mother_name' => $extractedData['mother_name'],
                    'doctor_id' => $doctorId, // Associate with the doctor ID
                ]);
                return response()->json([
                    'status' => 'success',
                    'patient' => $patient
                ]);
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

            return response()->json([
                'status' => 'error',
                'message' => $errorMessage
            ], $e->getResponse() ? $e->getResponse()->getStatusCode() : 500);
        }
    }
}
