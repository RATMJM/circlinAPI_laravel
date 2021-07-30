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
            $agree1 = $request->get('agree1', false);
            $agree2 = $request->get('agree2', false);
            $agree3 = $request->get('agree3', false);
            $agree4 = $request->get('agree4', false);
            $agree5 = $request->get('agree5', false);

            // 필수 동의 항목 체크
            if (!$agree1 || !$agree2 || !$agree3) {
                return success([
                    'result' => false,
                    'reason' => 'not enough agreements',
                ]);
            }

            // 이메일 validation (SNS 계정 형태도 인증에서 넘어갈 수 있도록
            if (!preg_match('/^[0-9a-zA-Z_.-]+@([KFAN]|[0-9a-zA-Z]([-_\.]?[0-9a-zA-Z])*\.[a-zA-Z]{2,3})$/', $email)) {
                return success([
                    'result' => false,
                    'reason' => 'email validation failed',
                ]);
            }

            // 비밀번호 validation
            if (!$sns && !preg_match('/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[!@#$%^&*()])[a-zA-Z\d!@#$%^&*()]{6,}$/', $password)) {
                return success([
                    'result' => false,
                    'reason' => 'password validation failed',
                ]);
            }

            if (User::where(['email' => $email])->exists()) {
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
                    'agree1' => $agree1,
                    'agree2' => $agree2,
                    'agree3' => $agree3,
                    'agree4' => $agree4,
                    'agree5' => $agree5,
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

    public function signup_sns(Request $request): array
    {
        return $this->signup($request, true);
    }

    /* 로그인 */
    public function login_user($user): array
    {
        try {
            $user_stat = UserStat::firstOrCreate(['user_id' => $user->id]);

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
                    'birth' => $user_stat?->birth,
                    'gender' => $user_stat?->gender,
                    'height' => $user_stat?->height,
                    'weight' => $user_stat?->weight,
                    'bmi' => $user_stat?->bmi,
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
                return success(['result' => false, 'token' => null, 'user' => null]);
            }
        } catch (Exception $e) {
            return failed($e);
        }
    }

    public function check_init(Request $request, $need)
    {
        try {
            $user_id = JWT::decode($request->header('token'), env('JWT_SECRET'), ['HS256'])->uid;
            $user = User::where('id', $user_id)->first();

            if (is_null($user)) {
                return success([
                    'result' => false,
                    'reason' => 'not enough data',
                ]);
            }

            $result = match ($need) {
                'nickname' => (is_null($user->nickname) || trim($user->nickname) === ''),
                'area' => (is_null($user->area_code) || trim($user->area_code) === ''),
                'category' => ($user->favorite_categories->count() === 0),
                'follow' => $user->follows->count() < 3,
            };
            return success([
                'result' => !$result,
            ]);
        } catch (Exception $e) {
            return failed($e);
        }
    }
}
