<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfUnauthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect('/?login=1');
        }

        return $next($request);
    }
}
