<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DiagnosisListResource;
use App\Models\DiagnosisList;
use Illuminate\Http\Request;

class DiagnosisListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return DiagnosisListResource::collection(DiagnosisList::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(DiagnosisList $diagnosisList)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DiagnosisList $diagnosisList)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DiagnosisList $diagnosisList)
    {
        //
    }
}
