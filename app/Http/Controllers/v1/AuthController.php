<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserStat;
use Exception;
use Firebase\JWT\JWT;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function exists_email($email): JsonResponse
    {
        try {
            $exists = User::where('email', $email)->exists();

            return response()->json([
                'success' => true,
                'data' => [
                    'exists' => $exists,
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
            ]);
        }
    }

    public function exists_nickname($nickname): JsonResponse
    {
        try {
            $exists = User::where('nickname', $nickname)->exists();

            return response()->json([
                'success' => true,
                'data' => [
                    'exists' => $exists,
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
            ]);
        }
    }

    public function login(Request $request): JsonResponse
    {
        try {
            $email = $request->get('email');
            $password = $request->get('password');

            $user = User::where(['email' => $email])->first();
            if (isset($user) && ($user->password === '' || Hash::check($password, $user->password))) {
                $user_stat = $user->stat;
                return response()->json([
                    'success' => true,
                    'data' => [
                        'token' => JWT::encode([
                            'iss' => 'https://www.circlin.co.kr',
                            'aud' => 'https://www.circlin.co.kr',
                            'iat' => 1356999524,
                            'nbf' => 1357000000,
                            'uid' => $user->id,
                        ], env('JWT_SECRET')),
                        'user' => [
                            'name' => $user->name,
                            'nickname' => $user->nickname,
                            'phone' => $user->phone,
                            'point' => $user->point,
                            'birth' => $user->stat->birth,
                            'gender' => $user->stat->gender,
                            'height' => $user->stat->height,
                            'weight' => $user->stat->weight,
                            'bmi' => $user->stat->bmi,
                        ],
                    ],
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'data' => ['token' => null, 'user' => null],
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
            ]);
        }
    }
}
