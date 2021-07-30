<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function update_profile(Request $request, $user_id): array
    {
        try {
            $nickname = $request->get('nickname');
            $area_code = $request->get('area_code');
            $gender = $request->get('gender');

            $data = User::where('id', $user_id)->first();
            if (isset($data)) {
                $update_data = [];
                if ($nickname && (new AuthController())->exists_nickname($nickname)['data']['exists']) {
                    $update_data['nickname'] = $nickname;
                }
                if ($area_code) {
                    $update_data['area_code'] = $area_code;
                }

                return User::where('id', $user_id)->update($update_data);
            } else {
                return success([
                    'result' => false,
                    'reason' => 'not enough data',
                ]);
            }
        } catch (Exception $e) {
            return failed($e);
        }
    }

    public function add_favorite_category(Request $request, $user_id)
    {

    }

    public function remove_favorite_category(Request $request, $user_id)
    {

    }

    public function follow(Request $request)
    {
        try {
            $user_id = JWT::decode($request->header('token'), env('JWT_SECRET'), ['HS256'])->uid;
            $target_id = $request->get('target_id');

            if (is_null($target_id)) {
                return success([
                    'result' => false,
                    'reason' => 'not enough data',
                ]);
            }

            if (Follow::where(['user_id' => $user_id, 'target_id' => $target_id])->exists()) {
                return success(['result' => false, 'reason' => 'already following']);
            } else {
                $data = Follow::create(['user_id' => $user_id, 'target_id' => $target_id]);
                if ($data) {
                    return success(['result' => true]);
                } else {
                    return success(['result' => false]);
                }
            }
        } catch (Exception $e) {
            return failed($e);
        }
    }

    public function unfollow(Request $request)
    {
        try {
            $user_id = JWT::decode($request->header('token'), env('JWT_SECRET'), ['HS256'])->uid;
            $target_id = $request->get('target_id');

            if (is_null($target_id)) {
                return success([
                    'result' => false,
                    'reason' => 'not enough data',
                ]);
            }

            if (Follow::where(['user_id' => $user_id, 'target_id' => $target_id])->doesntExist()) {
                return success(['result' => false, 'reason' => 'not following']);
            } else {
                $data = Follow::where(['user_id' => $user_id, 'target_id' => $target_id])->delete();
                if ($data) {
                    return success(['result' => true]);
                } else {
                    return success(['result' => false]);
                }
            }
        } catch (Exception $e) {
            return failed($e);
        }
    }
}
