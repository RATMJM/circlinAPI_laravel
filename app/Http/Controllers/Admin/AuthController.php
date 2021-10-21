<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function loginForm(Request $request)
    {
        /*if (Auth::check()) {
            return redirect()->route('admin.user.index');
        }*/

        return view('admin.login');
    }

    public function login(Request $request)
    {
        $user = $request->only('email', 'password');

        if (Auth::attempt($user, true)) {
            return redirect()->to($request->get('referer', route('admin.user.index')));
        } else {
            return "<script>alert('로그인에 실패했습니다.');</script>";
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect(request()->headers->get('referer'));
    }
}
