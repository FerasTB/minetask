<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRecordRequest;
use App\Http\Requests\UpdateOfficeRequest;
use App\Http\Requests\UpdateRecordRequest;
use App\Http\Resources\RecordResource;
use App\Models\MedicalCase;
use App\Models\Record;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\Case_;

class RecordController extends Controller
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
    public function store(StoreRecordRequest $request)
    {
        $case = MedicalCase::find($request->case_id);
        $this->authorize('create', [Record::class, $case]);
        $fields = $request->validated();
        $record = $case->record()->create($fields);
        return new RecordResource($record);
    }

    /**
     * Display the specified resource.
     */
    public function show(Record $record)
    {
        return new RecordResource($record);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRecordRequest $request, Record $record)
    {
        $case = MedicalCase::find($request->case_id);
        $this->authorize('update', $record);
        $fields = $request->validated();
        $record->update($fields);
        return new RecordResource($record);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Record $record)
    {
        //
    }

    public function CaseRecords(MedicalCase $case)
    {
        $this->authorize('viewAny', [Record::class, $case]);
        return RecordResource::collection($case->record);
    }
}
