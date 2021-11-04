<?php

namespace App\Http\Controllers\AdminApi;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $user = $request->only('email', 'password');

        return arraySnakeToCamelCase([
            'result' => Auth::attempt($user, true),
            'user' => $this->my(),
        ]);
    }

    public function my()
    {
        $data = Auth::user();

        return arraySnakeToCamelCase([
            'nickname' => $data?->nickname ?? null,
            'email' => $data?->email ?? null,
        ]);
    }

    public function logout()
    {
        Auth::logout();

        return arraySnakeToCamelCase(['result' => true, 'user' => $this->my()]);
    }
}
