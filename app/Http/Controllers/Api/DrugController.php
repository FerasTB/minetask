<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDrugRequest;
use App\Http\Resources\DrugResource;
use App\Models\Diagnosis;
use App\Models\Drug;
use App\Models\DrugList;
use Illuminate\Http\Request;

class DrugController extends Controller
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
    public function store(StoreDrugRequest $request)
    {
        $fields = $request->validated();
        $diagnosis = Diagnosis::find($request->diagnosis_id);
        if ($diagnosis) {
            $this->authorize('create', [Drug::class, $diagnosis]);
            $drug = $diagnosis->drug()->create($fields);
            $list = DrugList::firstOrCreate([
                'drug_name' => $drug->drug_name,
                'eat' => $drug->eat,
                'portion' => $drug->portion,
                'frequency' => $drug->frequency,
                'effect' => $drug->effect,
                'doctor_id' => auth()->user()->doctor->id,
            ]);
            return new DrugResource($drug);
        }
        return response('the is no diagnosis', 404);
    }

    /**
     * Display the specified resource.
     */
    public function show(Drug $drug)
    {
        return new DrugResource($drug);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Drug $drug)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Drug $drug)
    {
        //
    }
}
