<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle($request, \Closure $next, ...$roles)
    {
        if (!session()->has('user_id')) {
            return redirect()->route('login')->withErrors(['error' => 'Please login first.']);
        }
        if (!in_array(session('user_role'), $roles)) {
            abort(403);
        }
        return $next($request);
    }
}
