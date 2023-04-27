<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccountingProfileResource;
use App\Models\AccountingProfile;
use Illuminate\Http\Request;

class AccountingProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (auth()->user()->doctor) {
            return AccountingProfileResource::collection(auth()->user()->doctor->accountingProfiles);
        }
        return new AccountingProfileResource(auth()->user()->patient->accountingProfiles);
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
    public function show(AccountingProfile $accounting)
    {
        return new AccountingProfileResource($accounting);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AccountingProfile $accountingProfile)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AccountingProfile $accountingProfile)
    {
        //
    }
}
