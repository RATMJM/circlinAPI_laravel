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
            return exceped($e);
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
            return exceped($e);
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

            // SNS 한정 먼저 받을 수도 있음
            $nickname = $request->get('nickname');
            $name = $request->get('name');
            $family_name = $request->get('family_name');
            $given_name = $request->get('given_name');
            $device_type = $request->get('device_type');
            $device_token = $request->get('device_token');
            $access_token = $request->get('access_token');
            $refresh_token = $request->get('refresh_token');
            $refresh_token_expire_in = $request->get('refresh_token_expire_in');

            // 필수 동의 항목 체크
            if (!$agree1 || !$agree2 || !$agree3) {
                return success([
                    'result' => false,
                    'reason' => 'not enough agreements',
                ]);
            }

            // 이메일 validation (SNS 계정 형태도 인증에서 넘어갈 수 있도록
            if (!preg_match('/^[0-9a-zA-Z_.-]+@([KFAN]|[0-9a-zA-Z]([-_.]?[0-9a-zA-Z])*\.[a-zA-Z]{2,3})$/', $email)) {
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
                    // SNS 한정 미리 받을 수도 있음
                    'nickname' => $nickname,
                    'name' => $name,
                    'family_name' => $family_name,
                    'given_name' => $given_name,
                    'device_type' => $device_type,
                    'device_token' => $device_token,
                    'access_token' => $access_token,
                    'refresh_token' => $refresh_token,
                    'refresh_token_expire_in' => $refresh_token_expire_in,
                ]);

                UserStat::create(['user_id' => $user->id]);

                DB::commit();
                return $this->login_user($user);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
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
            User::where('id', $user->id)->update([
                'last_login_ip' => request()->server('REMOTE_ADDR'),
                'last_login_at' => date('Y-m-d H:i:s', time()),
            ]);

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
                    'birthday' => $user_stat?->birth,
                    'gender' => $user->gender,
                    'height' => $user_stat?->height,
                    'weight' => $user_stat?->weight,
                    'bmi' => $user_stat?->bmi,
                ],
            ]);
        } catch (Exception $e) {
            return exceped($e);
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
            return exceped($e);
        }
    }

    public function login_sns(Request $request): array
    {
        try {
            $email = $request->get('email');

            $user = User::where(['email' => $email])->first();
            if (isset($user) && preg_match('/.+@[AKFN]/', $email)) {
                return $this->login_user($user);
            } else {
                return success(['result' => false, 'token' => null, 'user' => null]);
            }
        } catch (Exception $e) {
            return exceped($e);
        }
    }

    public function check_init(): array
    {
        try {
            DB::enableQueryLog();
            $user_id = token()->uid;
            $data = User::where('users.id', $user_id)
                ->join('user_stats', 'user_stats.user_id', 'users.id')
                ->leftJoin('areas', 'areas.ctg_sm', 'users.area_code')
                ->select([
                    'users.id', 'users.nickname', 'users.profile_image', 'users.gender', 'user_stats.birthday',
                    'area' => area(),
                ])
                ->first();

            if (is_null($data)) {
                return success([
                    'result' => false,
                    'reason' => 'not enough data',
                ]);
            }

            $need = [
                'nickname' => $data->nickname,
                'gender' => $data->gender,
                'birthday' => date('Ymd', strtotime($data->birthday)),
                'area' => $data->area,
                'profile_image' => $data->profile_image,
                'category' => $data->favorite_categories,
                'follow' => $data->followings->take(3),
            ];
            return success([
                'result' => true,
                'need' => $need,
            ]);
        } catch (Exception $e) {
            return exceped($e);
        }
    }
}
