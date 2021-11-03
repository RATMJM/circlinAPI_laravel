<?php

namespace App\Http\Controllers\AdminApi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use JetBrains\PhpStorm\ArrayShape;

class AuthController extends Controller
{
    #[ArrayShape(['result' => "bool", 'user' => "array|null"])] public function login(Request $request): array
    {
        $user = $request->only('email', 'password');

        return ['result' => Auth::attempt($user, true), 'user' => $this->my()];
    }

    #[ArrayShape(['nickname' => "mixed", 'email' => "mixed"])] public function my(): array|null
    {
        $data = Auth::user();

        return [
            'nickname' => $data?->nickname ?? null,
            'email' => $data?->email ?? null,
        ];
    }

    #[ArrayShape(['result' => "bool", 'user' => "array|null"])] public function logout(): array
    {
        Auth::logout();

        return ['result' => true, 'user' => $this->my()];
    }
}
