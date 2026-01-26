<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // If user status is pending
            if ($user->status === 'pending') {

                //Allow access to complete-profile, logout, and any assets if needed (though assets usually bypass middleware)
                //Also checking route names to be safe
                if (
                    !$request->routeIs('complete-profile') &&
                    !$request->routeIs('complete-profile.update') &&
                    !$request->routeIs('logout')
                ) {

                    return redirect()->route('complete-profile');
                }
            }
        }

        return $next($request);


    }
}
