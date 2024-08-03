<?php

namespace App\Http\Controllers\Ai;

use App\Enums\AiTaskType;
use App\Http\Controllers\Controller;
use App\Models\TemporaryTask;
use App\Services\PatientService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BaseController extends Controller
{
    protected $patientService;

    public function __construct(PatientService $patientService)
    {
        $this->patientService = $patientService;
    }
    public function determineTask(Request $request)
    {
        // Validate the request input
        $request->validate([
            'text' => 'required|string',
            'office_id' => 'required|integer|exists:offices,id',
        ]);

        $text = $request->input('text');

        // Send the text to the Flask endpoint
        $response = Http::post('https://abbc-34-125-41-4.ngrok-free.app/determine-task', [
            'text' => $text,
        ]);

        // Get the response from Flask
        // $result = $response->json();
        $result = json_decode($response->json(), true);

        // Process the task based on the result
        $task = $result['task'];
        $subtasks = $result['subtasks'];

        // Perform actions based on the determined task
        // This is a placeholder for actual task processing logic
        // You need to implement the logic for each task in your database
        if ($task == 'إضافة مريض') {
            return $this->patientService->startAddingPatientTask($request, null);
        }
        // Return the task ID as the result
        return response()->json([
            'error' => "went wrong",
            'task' => $result['task'],
        ]);
    }

    public function continueTask(Request $request, TemporaryTask $task)
    {
        // Validate the request input
        $request->validate([
            'text' => 'required|string',
            'office_id' => 'required|integer|exists:offices,id',
        ]);

        $text = $request->input('text');
        // Perform actions based on the determined task
        // This is a placeholder for actual task processing logic
        // You need to implement the logic for each task in your database
        if ($task->task_type == AiTaskType::AddingPatient) {

            return $this->patientService->startAddingPatientTask($request, $task->data);
        }
        // Return the task ID as the result
        return response()->json([
            'error' => "went wrong",
        ]);
    }
}
