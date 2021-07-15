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
        if (in_array($remote_addr, ['::1', '127.0.0.1', '34.64.248.255', '34.85.2.191'])) {
            return $next($request);
        } else {
            abort(403);
        }
    }
}
