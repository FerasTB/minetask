<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsDentalDoctorTechnician
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ((auth()->user()->currentRole->id != Role::DentalLabDoctor) && (auth()->user()->currentRole->id != Role::DentalLabTechnician)) {
            return response('access not allow', 403);
        }
        return $next($request);
    }
}
