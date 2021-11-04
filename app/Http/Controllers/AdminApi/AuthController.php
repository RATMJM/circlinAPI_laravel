<?php

namespace App\Http\Controllers\AdminApi;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $user = $request->only('email', 'password');

        return response()->json([
            'result' => Auth::attempt($user, true),
            'user' => $this->my()->original,
        ]);
    }

    public function my(): JsonResponse
    {
        $data = Auth::user();

        return response()->json([
            'nickname' => $data?->nickname ?? null,
            'email' => $data?->email ?? null,
        ]);
    }

    public function logout(): JsonResponse
    {
        Auth::logout();

        return response()->json(['result' => true, 'user' => $this->my()]);
    }
}
