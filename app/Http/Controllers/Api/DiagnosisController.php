<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDiagnosisRequest;
use App\Http\Resources\DiagnosisResource;
use App\Models\Diagnosis;
use App\Models\Record;
use Illuminate\Http\Request;

class DiagnosisController extends Controller
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
    public function store(StoreDiagnosisRequest $request)
    {
        $fields = $request->validated();
        $record = Record::find($request->record_id);
        if ($record) {
            $this->authorize('create', [Diagnosis::class, $record]);
            $diagnosis = $record->diagnosis()->create($fields);
            return new DiagnosisResource($diagnosis);
        }
        return response('the is no record', 404);
    }

    /**
     * Display the specified resource.
     */
    public function show(Diagnosis $diagnosi)
    {
        return new DiagnosisResource($diagnosi);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Diagnosis $diagnosis)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Diagnosis $diagnosis)
    {
        //
    }

    public function RecordDiagnosis(Record $record)
    {
        $diagnosis = $record->diagnosis;
        return new DiagnosisResource($diagnosis);
    }
}
