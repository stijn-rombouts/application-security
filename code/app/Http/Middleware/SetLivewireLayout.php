<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SetLivewireLayout
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Set layout based on user type
        if ($user && $user->isClient()) {
            config(['livewire.layout' => 'components.layouts.client']);
        } else {
            config(['livewire.layout' => 'components.layouts.app']);
        }

        return $next($request);
    }
}
