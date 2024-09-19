<?php

namespace App\Http\Controllers\Api;

use App\Enums\OfficeType;
use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Http\Resources\NoteResource;
use App\Models\Doctor;
use App\Models\EmployeeSetting;
use App\Models\HasRole;
use App\Models\Office;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Office $office)
    {
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
        $this->authorize('inOffice', [Note::class, $office]);
        if ($office->type == OfficeType::Separate) {
            return NoteResource::collection(
                $doctor->notes()
                    ->where('office_id', $office->id)
                    ->with([
                        'doctor',
                        'office',
                        'patient'
                    ])
                    ->get()
            );
        } else {
            return NoteResource::collection($office->notes()->with(['office', 'patient'])->get());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNoteRequest $request, Office $office)
    {
        $fields = $request->validated();
        $this->authorize('inOffice', [Note::class, $office]);
        if ($request->doctor_id) {
            $doctor = Doctor::find($request->doctor_id);
            $this->authorize('createForDoctor', [Note::class, $doctor]);
            $note = $office->notes()->create($fields);
            $note->load('patient');
            $note->load('doctor');
            $note->load('office');
            return new NoteResource($note);
        }
        $this->authorize('officeOwner', [Note::class, $office]);
        $note = $office->notes()->create($fields);
        $note->load('patient');
        $note->load('doctor');
        $note->load('office');
        return new NoteResource($note);
    }

    /**
     * Display the specified resource.
     */
    public function show(Note $note)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNoteRequest $request, Office $office, Note $note)
    {
        if ($note->doctor) {
            $this->authorize('updateForDoctor', [$note, auth()->user()->doctor]);
            $fields = $request->validated();
            $note->update($fields);
            $note->load('patient');
            $note->load('doctor');
            $note->load('office');
            return new NoteResource($note);
        }
        $this->authorize('updateForOffice', [$note, $note->office]);
        $fields = $request->validated();
        $note->update($fields);
        $note->load('patient');
        $note->load('doctor');
        $note->load('office');
        return new NoteResource($note);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Office $office, Note $note)
    {
        if ($note->doctor) {
            $this->authorize('updateForDoctor', [$note, auth()->user()->doctor]);
            $note->delete();
            return response()->noContent();
        }
        $this->authorize('updateForOffice', [$note, $note->office]);
        $note->delete();
        return response()->noContent();
    }
}
