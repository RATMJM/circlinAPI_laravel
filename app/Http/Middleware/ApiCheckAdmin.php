<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiCheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $remote_addr = $request->server('REMOTE_ADDR');
        if (Admin::where(['type' => 'ip', 'ip' => $remote_addr])->exists() ||
            Admin::where(['type' => 'user', 'user_id' => Auth::id()])->exists()) {
            return $next($request);
        } else {
            return abort(403, Auth::check() ? '권한이 없습니다.' : '로그인이 필요한 서비스입니다.');
        }
    }
}
