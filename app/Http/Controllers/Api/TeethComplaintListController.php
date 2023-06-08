<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeethComplaintListResource;
use App\Models\TeethComplaintList;
use Illuminate\Http\Request;

class TeethComplaintListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return TeethComplaintListResource::collection(TeethComplaintList::all());
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
    public function show(TeethComplaintList $teethComplaintList)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TeethComplaintList $teethComplaintList)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TeethComplaintList $teethComplaintList)
    {
        //
    }
}
