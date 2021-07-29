<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BlockIp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $remote_addr = $request->server('REMOTE_ADDR');
        if (in_array($remote_addr, ALLOW_IP)) {
            return $next($request);
        } else {
            abort(403);
        }
    }
}
