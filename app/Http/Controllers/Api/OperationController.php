<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOperationRequest;
use App\Http\Requests\StoreToothRequest;
use App\Http\Requests\UpdateOperationRequest;
use App\Http\Resources\OperationResource;
use App\Http\Resources\ToothResource;
use App\Models\Operation;
use App\Models\Record;
use App\Models\TeethRecord;
use Illuminate\Http\Request;
use PHPUnit\Framework\Constraint\Operator;

class OperationController extends Controller
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
    public function store(StoreOperationRequest $request)
    {
        $fields = $request->validated();
        $record = TeethRecord::find($request->record_id);
        if ($record) {
            $this->authorize('create', [Operation::class, $record]);
            $operation = $record->operations()->create($fields);
            return new OperationResource($operation);
        }
        return response('the is no record', 404);
    }

    /**
     * Display the specified resource.
     */
    public function show(Operation $operation)
    {
        $this->authorize('view', $operation);
        return new OperationResource($operation);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOperationRequest $request, Operation $operation)
    {
        $this->authorize('update', $operation);
        $fields = $request->validated();
        $operation->update($fields);
        return new OperationResource($operation);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Operation $operation)
    {
        //
    }

    public function RecordOperation(TeethRecord $record)
    {
        $this->authorize('viewAny', [Operation::class, $record]);
        return OperationResource::collection($record->operations);
    }

    public function addTooth(StoreToothRequest $request, Operation $operation)
    {
        $fields = $request->validated();
        $tooth = $operation->teeth()->create($fields);
        return new ToothResource($tooth);
    }
}
