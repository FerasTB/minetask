<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeethRecordRequest;
use App\Http\Resources\TeethRecordResource;
use App\Models\PatientCase;
use App\Models\TeethRecord;
use Illuminate\Http\Request;

class TeethRecordController extends Controller
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
    public function store(StoreTeethRecordRequest $request)
    {
        $fields = $request->validated();
        $case = PatientCase::find($request->patientCase_id);
        // $this->authorize('create', [Record::class, $case]);
        $fields['report_id'] = $case->patient->teethReport->id;
        $record = $case->teethRecords()->create($fields);
        return new TeethRecordResource($record);
    }

    /**
     * Display the specified resource.
     */
    public function show(TeethRecord $teethRecord)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TeethRecord $teethRecord)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TeethRecord $teethRecord)
    {
        //
    }

    public function CaseRecords(PatientCase $case)
    {
        // $this->authorize('viewAny', [Record::class, $case]);
        return TeethRecordResource::collection($case->teethRecords);
    }
}
