<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserStat;
use Exception;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function exists_email($email): array
    {
        try {
            $exists = User::where('email', $email)->exists();

            return success([
                'exists' => $exists,
            ]);
        } catch (Exception $e) {
            return failed($e);
        }
    }

    public function exists_nickname($nickname): array
    {
        try {
            $exists = User::where('nickname', $nickname)->exists();

            return success([
                'exists' => $exists,
            ]);
        } catch (Exception $e) {
            return failed($e);
        }
    }

    public function signup(Request $request, $sns = false): array
    {
        try {
            $email = $request->get('email');
            $password = $request->get('password');

            $user = User::where(['email' => $email])->exists();
            if ($user) {
                return success([
                    'result' => false,
                    'reason' => 'exists email',
                ]);
            } else {
                DB::beginTransaction();

                /* 유저 기본 데이터 생성 */
                $user = User::create([
                    'email' => $email,
                    'password' => $sns ? '' : Hash::make($password),
                ]);

                $user_stat = UserStat::create(['user_id' => $user->id]);

                DB::commit();
                return success([
                    'result' => true,
                ]);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return failed($e);
        }
    }

    /* 로그인 */
    public function login_user($user): array
    {
        try {
            return success([
                'result' => true,
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
                    'birth' => $user->stat?->birth,
                    'gender' => $user->stat?->gender,
                    'height' => $user->stat?->height,
                    'weight' => $user->stat?->weight,
                    'bmi' => $user->stat?->bmi,
                ],
            ]);
        } catch (Exception $e) {
            return failed($e);
        }
    }

    public function login(Request $request): array
    {
        try {
            $email = $request->get('email');
            $password = $request->get('password');

            $user = User::where(['email' => $email])->first();
            if (isset($user) && Hash::check($password, $user->password)) {
                return $this->login_user($user);
            } else {
                return success(['result' => false, 'token' => null, 'user' => null]);
            }
        } catch (Exception $e) {
            return failed($e);
        }
    }

    public function login_sns(Request $request): array
    {
        try {
            $email = $request->get('email');

            $user = User::where(['email' => $email])->first();
            if (isset($user) && ($user->password === '')) {
                return $this->login_user($user);
            } else {
                $user = $this->signup($request, true);
                if ($user['data']['result']) {
                    return $this->login_user($user['data']['user']);
                } else {
                    return success(['result' => false, 'token' => null, 'user' => null]);
                }
            }
        } catch (Exception $e) {
            return failed($e);
        }
    }

    public function check_init(Request $request, User $user = null)
    {
        try {
            $user = $user ?? User::where('id', $request->user_id)->first();

            if (is_null($user)) {
                return success([
                    'result' => false,
                    'reason' => 'not enough data'
                ]);
            }

            $need = [];
            if (is_null($user->nickname) || trim($user->nickname) === '') {
                $need[] = 'nickname';
            }
            if (is_null($user->area_code) || trim($user->area_code) === '') {
                $need[] = 'area';
            }
            if ($user->favorite_categories->count() === 0) {
                $need[] = 'favorite_category';
            }
            if (is_null($user->profile_image)) {
                $need[] = 'profile_image';
            }
            if ($user->follows->count() === 0) {
                $need[] = 'follows';
            }
            return success([
                'result' => true,
                'need' => $need,
            ]);
        } catch (Exception $e) {
            return failed($e);
        }
    }
}
