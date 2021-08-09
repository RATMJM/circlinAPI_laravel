<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class LastLogin
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
        $token = token_option();

        if ($token) {
            User::where('id', $token->uid)->update([
                'last_login_ip' => request()->server('REMOTE_ADDR'),
                'last_login_at' => date('Y-m-d H:i:s', time()),
            ]);
        }

        return $next($request);
    }
}
