<?php

namespace App\Http\Middleware;

use Closure;

class NoTimeout
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        set_time_limit(0);
        return $next($request);
    }
}
