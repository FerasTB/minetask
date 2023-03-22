<?php

namespace App\Http\Controllers\Api;

use App\Enums\AvailabilityCompare;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAvailabilityRequest;
use App\Http\Requests\UpdateAvailabilityRequest;
use App\Http\Resources\AvailabilityResource;
use App\Models\Availability;
use App\Models\Office;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $doctor = auth()->user()->doctor;
        $availabilities = Availability::where('doctor_id', $doctor->id)->get();
        return AvailabilityResource::collection($availabilities);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAvailabilityRequest $request)
    {

        $fields = $request->validated();
        $office = Office::findOrFail($fields['office_id']);
        $this->authorize('create', [Availability::class, $office]);
        $doctor = auth()->user()->doctor;
        $availabilities = Availability::where(['doctor_id' => $doctor->id, 'office_id' => $office->id])->get();
        if ($availabilities != []) {
            $status = AvailabilityCompare::OutSide;
            foreach ($availabilities as $av) {
                $status = $this->compareTowTime($request, $av);
                if ($status == AvailabilityCompare::HalfOutFromRight) {
                    $fields['start_time'] = $av->start_time;
                    $av->delete();
                    $status = AvailabilityCompare::OutSide;
                }
                if ($status == AvailabilityCompare::HalfOutFromLeft) {
                    $fields['end_time'] = $av->end_time;
                    $av->delete();
                    $status = AvailabilityCompare::OutSide;
                }
                if ($status == AvailabilityCompare::OutFromTowSide) {
                    $av->delete();
                    $status = AvailabilityCompare::OutSide;
                }
                if ($status == AvailabilityCompare::EqualFromLeft) {
                    $fields['end_time'] = $av->end_time;
                    $av->delete();
                    $status = AvailabilityCompare::OutSide;
                }
                if ($status == AvailabilityCompare::EqualFromRight) {
                    $fields['start_time'] = $av->start_time;
                    $av->delete();
                    $status = AvailabilityCompare::OutSide;
                }
                if ($status == AvailabilityCompare::Same || $status == AvailabilityCompare::InSide) {
                    $av->delete();
                    $status = AvailabilityCompare::OutSide;
                }
            }
        }
        $availability = $doctor->availabilities()->create($fields);
        $availability = Availability::find($availability->id);
        return new AvailabilityResource($availability);
    }

    public function compareTowTime(Request $request, Availability $availability)
    {
        // $start_time = Carbon::createFromFormat('H:i:s', $request->start_time)->format('H:i:s');
        // return $start_time;
        // $start_time = Carbon::parse($request->start_time)->format('H:i:s');
        $start_time = Carbon::createFromFormat('Y-m-d H:i:s', '2014-12-12 ' . $request->start_time);
        $end_time = Carbon::createFromFormat('Y-m-d H:i:s', '2014-12-12 ' . $request->end_time);
        $av_start_time = Carbon::createFromFormat('Y-m-d H:i:s', '2014-12-12 ' . $availability->start_time);
        $av_end_time = Carbon::createFromFormat('Y-m-d H:i:s', '2014-12-12 ' . $availability->end_time);
        if ($start_time->gt($av_end_time) || $end_time->lt($av_start_time)) {
            return AvailabilityCompare::OutSide;
        }
        if ($end_time->eq($av_start_time)) {
            return AvailabilityCompare::EqualFromLeft;
        }
        if ($start_time->eq($av_end_time)) {
            return AvailabilityCompare::EqualFromRight;
        }
        if ($start_time->gt($av_start_time) && $end_time->gt($av_end_time)) {
            return AvailabilityCompare::HalfOutFromRight;
        }
        if ($start_time->lt($av_start_time) && $end_time->lt($av_end_time)) {
            return AvailabilityCompare::HalfOutFromLeft;
        }
        if ($start_time->lt($av_start_time) && $end_time->gt($av_end_time)) {
            return AvailabilityCompare::OutFromTowSide;
        }
        if ($end_time->eq($av_end_time) || $start_time->eq($av_start_time)) {
            return AvailabilityCompare::Same;
        }
        if ($end_time->lt($av_end_time) || $start_time->lt($av_start_time)) {
            return AvailabilityCompare::InSide;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Availability $availability)
    {
        return new AvailabilityResource($availability);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAvailabilityRequest $request, Availability $availability)
    {
        $this->authorize('update', $availability);
        $fields = $request->validated();
        $availability->update($fields);
        return new AvailabilityResource($availability);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Availability $availability)
    {
        //
    }
}
