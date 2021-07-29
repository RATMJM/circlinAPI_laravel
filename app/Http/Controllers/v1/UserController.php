<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
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

    public function follow(Request $request, $user_id)
    {

    }

    public function unfollow(Request $request, $user_id)
    {

    }
}
