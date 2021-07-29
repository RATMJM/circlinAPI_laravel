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

            $user = User::where('id', $user_id)->first();
            if (isset($user)) {
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

    public function follow(Request $request, $user_id, $target_id)
    {
        try {
            $follow = Follow::create(['user_id' => $user_id, 'target_id' => $target_id]);
            if ($follow) {
                return success(['result' => true]);
            } else {
                return success(['result' => false]);
            }
        } catch (Exception $e) {
            return failed($e);
        }
    }

    public function unfollow(Request $request, $user_id, $target_id)
    {
        try {
            $follow = Follow::where(['user_id' => $user_id, 'target_id' => $target_id])->delete();
            if ($follow) {
                return success(['result' => true]);
            } else {
                return success(['result' => false]);
            }
        } catch (Exception $e) {
            return failed($e);
        }
    }
}
