<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDiagnosisRequest;
use App\Http\Requests\StoreToothRequest;
use App\Http\Resources\DiagnosisResource;
use App\Http\Resources\ToothResource;
use App\Models\Diagnosis;
use App\Models\DiagnosisList;
use App\Models\Record;
use App\Models\TeethRecord;
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
        $record = TeethRecord::find($request->record_id);
        if ($record) {
            $this->authorize('create', [Diagnosis::class, $record]);
            $diagnosis = $record->diagnosis()->create($fields);
            if ($diagnosis->description != null) {
                $diagnosis = DiagnosisList::firstOrCreate([
                    'description' => $diagnosis->description,
                ]);
            }
            return new DiagnosisResource($diagnosis);
        }
        return response('there is no record', 404);
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

    public function RecordDiagnosis(TeethRecord $record)
    {
        $diagnosis = $record->diagnosis;
        return new DiagnosisResource($diagnosis);
    }

    public function addTooth(StoreToothRequest $request, Diagnosis $diagnosis)
    {
        $fields = $request->validated();
        $tooth = $diagnosis->teeth()->create($fields);
        return new ToothResource($tooth);
    }
}
