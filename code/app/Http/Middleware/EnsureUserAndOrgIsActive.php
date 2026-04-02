<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserAndOrgIsActive
{
    /**
     * Handle an incoming request.
     * Check that the authenticated user and their organisation are both active.
     * If either is inactive, log the user out and redirect to home with a status message.
     * Exempt super admins from these checks. Because the superadmin can otherwise get locked out of the system.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If no authenticated user, continue
        if (! Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // If it's not our User model, continue
        if (! $user instanceof \App\Models\User) {
            return $next($request);
        }

        // Super admins are exempt from active checks
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Check user active flag
        if (! $user->active) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('home')->with(
                'status',
                __('middleware.account_inactivated')
            )->with('error', true);
        }

        // Check organisation active flag (users are always linked to an organisation)
        if (! $user->organisation->active) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('home')->with(
                'status',
                __('middleware.organisation_inactivated')
            )->with('error', true);
        }

            // Check organisation expire_date (if set and in the past, block login)
            if (!empty($user->organisation->expire_date) && strtotime($user->organisation->expire_date) < time()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('home')->with(
                    'status',
                    __('middleware.organisation_expired')
                )->with('error', true);
            }



        return $next($request);
    }
}
