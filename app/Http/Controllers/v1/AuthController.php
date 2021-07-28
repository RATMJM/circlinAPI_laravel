<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\UserStat;
use Firebase\JWT\JWT;
use \Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    public function exists_email($email)
    {
        try {
            $exists = User::where('email', $email)->exists();

            return response()->json([
                'success' => true,
                'data' => [
                    'exists' => $exists,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
            ]);
        }
    }

    public function exists_nickname($nickname)
    {
        try {
            $exists = User::where('nickname', $nickname)->exists();

            return response()->json([
                'success' => true,
                'data' => [
                    'exists' => $exists,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
            ]);
        }
    }

    public function login(Request $request)
    {
        $email = $request->get('email');
        $password = $request->get('password');

        $user = User::where(['email' => $email])->first();
        $user_stat = UserStat::where('user_id', $user->id)->first();
        if (isset($user) && ($user->password === '' || Hash::check($password, $user->password))) {
            return [
                'success' => true,
                'data' => [
                    'token' => JWT::encode([
                        'iss' => 'https://www.circlin.co.kr',
                        'aud' => 'https://www.circlin.co.kr',
                        'iat' => 1356999524,
                        'nbf' => 1357000000,
                    ], env('JWT_SECRET')),
                    'user' => [

                    ],
                ],
            ];
        } else {
            return [
                'success' => true,
                'data' => ['token' => null, 'user' => null],
            ];
        }
    }
}
