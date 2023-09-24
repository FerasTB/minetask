<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\ModelHasRole;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        //
    }

    public function switchRole(Role $role)
    {
        abort_unless(auth()->user()->hasRole($role), 404);

        auth()->user()->update(['current_role_id' => $role->id]);

        return response('Done', 200);
    }

    public function assignRole(Role $role)
    {
        abort_unless(auth()->user()->hasRole($role), 404);

        $role = ModelHasRole::create([
            'role_id' => $role->id,
            'roleable_id' => auth()->id(),
            'roleable_type' => 'App\Models\User',
        ]);

        return response('Done', 200);
    }
}
