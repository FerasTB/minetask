<?php

namespace App\Http\Controllers\Api;

use App\Enums\OfficeType;
use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Http\Resources\NoteResource;
use App\Models\Doctor;
use App\Models\Office;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Office $office)
    {
        $this->authorize('inOffice', [COA::class, $office]);
        if ($office->type == OfficeType::Separate) {
            $doctor = auth()->user()->doctor;
            return NoteResource::collection(
                $doctor->notes()
                    ->where('office_id', $office->id)
                    ->with([
                        'doctor', 'office', 'patient'
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
        $this->authorize('inOffice', [COA::class, $office]);
        if ($request->doctor_id) {
            $doctor = Doctor::find($request->doctor_id);
            $this->authorize('createForDoctor', [COA::class, $doctor]);
            $note = $office->notes()->create($fields);
            return new NoteResource($note);
        }
        $this->authorize('officeOwner', [COA::class, $office]);
        $note = $office->notes()->create($fields);
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
