<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SetUserLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            // Load the language relationship if not already loaded
            if ($user && !$user->relationLoaded('language')) {
                $user->load('language');
            }

            if ($user && $user->language && $user->language->language_code) {
                app()->setLocale($user->language->language_code);
                // Set the session locale to user's preference for when they log out
                session(['locale' => $user->language->language_code]);
            }
        }
        else {
            // For guests, use the locale from session if available
            if (session('locale')) {
                app()->setLocale(session('locale'));
            }
        }
        return $next($request);
    }
}
