<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DrugListResource;
use App\Models\DrugList;
use Illuminate\Http\Request;

class DrugListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return DrugListResource::collection(DrugList::where('doctor_id', auth()->user()->doctor->id)->get());
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
    public function show(DrugList $drugList)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DrugList $drugList)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DrugList $drugList)
    {
        //
    }
}
