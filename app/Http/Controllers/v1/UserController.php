<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Follow;
use App\Models\User;
use App\Models\UserFavoriteCategory;
use App\Models\UserStat;
use Exception;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function update_profile(Request $request): array
    {
        try {
            DB::beginTransaction();

            $user_id = JWT::decode($request->header('token'), env('JWT_SECRET'), ['HS256'])->uid;
            $nickname = $request->get('nickname');
            $area_code = $request->get('area_code');
            $gender = $request->get('gender');

            $data = User::where('id', $user_id)->first();
            if (isset($data)) {
                $result = [];
                $user_data = [];
                $user_stat_data = [];

                if ($nickname && !(new AuthController())->exists_nickname($nickname)['data']['exists']) {
                    $user_data['nickname'] = $nickname;
                    $result[] = 'nickname';
                }
                if ($area_code && Area::where('ctg_sm', $area_code)->exists()) {
                    $user_data['area_code'] = $area_code;
                    $result[] = 'area_code';
                }
                $user = User::where('id', $user_id)->update($user_data);

                if ($gender) {
                    $user_stat_date['gender'] = $gender;
                    $result[] = 'gender';
                }
                $user_stat = UserStat::where('user_id', $user_id)->update($user_stat_data);

                DB::commit();
                return success([
                    'result' => count($result) > 0,
                    'changed' => $result,
                ]);
            } else {
                DB::rollBack();
                return success([
                    'result' => false,
                    'reason' => 'not enough data',
                ]);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return failed($e);
        }
    }

    public function change_profile_image(Request $request): array
    {
        
        try { 
            DB::beginTransaction();
            $user_id = JWT::decode($request->header('token'), env('JWT_SECRET'), ['HS256'])->uid;           
            $profile_image_dir = $request->get('imgUrl');
            $profile_image_dir = base64_decode($profile_image_dir);
          //  echo $profile_image_dir;
            $data = User::where('id', $user_id)->first();
            
            if (isset($data)) {
                $user_data = []; 
              
                $changeProfileImage = DB::update('update users set profile_image = ? where id = ? ',array($profile_image_dir,$user_id)); 
                
                DB::commit();
                return success([
                    'result' => true,
                ]);
            } else {
                DB::rollBack();
                return success([
                    'result' => false,
                    'reason' => 'not enough data',
                ]);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return failed($e);
        }
       
    }

    public function add_favorite_category(Request $request)
    {
        try {
            $user_id = JWT::decode($request->header('token'), env('JWT_SECRET'), ['HS256'])->uid;
            $category_id = $request->get('category_id');

            if (is_null($category_id)) {
                return success([
                    'result' => false,
                    'reason' => 'not enough data',
                ]);
            }

            if (UserFavoriteCategory::where(['user_id' => $user_id, 'mission_category_id' => $category_id])->exists()) {
                return success(['result' => false, 'reason' => 'already following']);
            } else {
                $data = UserFavoriteCategory::create(['user_id' => $user_id, 'mission_category_id' => $category_id]);
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

    public function remove_favorite_category(Request $request)
    {
        try {
            $user_id = JWT::decode($request->header('token'), env('JWT_SECRET'), ['HS256'])->uid;
            $category_id = $request->get('category_id');

            if (is_null($category_id)) {
                return success([
                    'result' => false,
                    'reason' => 'not enough data',
                ]);
            }

            $data = UserFavoriteCategory::where(['user_id' => $user_id, 'mission_category_id' => $category_id])->first();
            if ($data) {
                $result = $data->delete();
                return success(['result' => $result]);
            } else {
                return success(['result' => false, 'reason' => 'not favorite']);
            }
        } catch (Exception $e) {
            return failed($e);
        }
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

            $data = Follow::where(['user_id' => $user_id, 'target_id' => $target_id])->first();
            if ($data) {
                $result = $data->delete();
                return success(['result' => $result]);
            } else {
                return success(['result' => false, 'not following']);
            }
        } catch (Exception $e) {
            return failed($e);
        }
    }


    public function area11(Request $request): array
    {
        $text = $request->get('searchText');
        $text = mb_ereg_replace('/\s/', '', $text);

        return Area::select()->where(DB::raw('CONCAT(name_lg, name_md, name_sm)'), 'like', "%$text%")
            ->take(10)->get()->toArray();
    }

}
