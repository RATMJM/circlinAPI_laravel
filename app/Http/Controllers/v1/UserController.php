<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Follow;
use App\Models\MissionCategory;
use App\Models\User;
use App\Models\UserFavoriteCategory;
use App\Models\UserStat;
use Exception;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function user(Request $request): array
    {
        $user_id = token()->uid;

        $user = User::where('users.id', $user_id)
            ->join('user_stats', 'user_stats.user_id', 'users.id')
            ->join('areas', 'areas.ctg_sm', 'users.area_code')
            ->select([
                'users.*',
                DB::raw("IF(name_lg=name_md, CONCAT_WS(' ', name_md, name_sm), CONCAT_WS(' ', name_lg, name_md, name_sm)) as area"),
                'user_stats.gender',
            ])->first();

        $category = UserFavoriteCategory::where('user_id', $user_id)
            ->join('mission_categories', 'mission_categories.id', 'user_favorite_categories.mission_category_id')
            ->select(['mission_categories.title'])
            ->get();

        return success([
            'result' => true,
            'user' => $user,
            'category' => $category,
        ]);
    }

    public function update_profile(Request $request): array
    {
        try {
            DB::beginTransaction();

            $user_id = token()->uid;
            $nickname = $request->get('nickname');
            $area_code = $request->get('area_code');
            $phone = preg_replace('/[^\d]/', '', $request->get('phone'));
            $gender = $request->get('gender');
            $birthday = $request->get('birthday');

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
                if ($phone && $phone !== $data->phone) {
                    $user_data['phone'] = $phone;
                    $user_data['phone_verified_at'] = date('Y-m-d H:i:s', time());
                    $result[] = 'phone';
                }
                $user = User::where('id', $user_id)->update($user_data);

                if ($gender) {
                    $user_stat_data['gender'] = $gender;
                    $result[] = 'gender';
                }
                if ($birthday && preg_match('/\d{8}/', $birthday)) {
                    $user_stat_data['birthday'] = date('Y-m-d', strtotime($birthday));
                    $result[] = 'birthday';
                }
                $user_stat = UserStat::where('user_id', $user_id)->update($user_stat_data);

                DB::commit();
                return success([
                    'result' => count($result) > 0,
                    'updated' => $result,
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

    public function remove_profile_image(Request $request): array
    {
        try {
            DB::beginTransaction();

            $user_id = JWT::decode($request->header('token'), env('JWT_SECRET'), ['HS256'])->uid;
            $result = User::where('id', $user_id)->update(['profile_image' => null]);

            DB::commit();
            return success(['result' => $result > 0]);
        } catch (Exception $e) {
            DB::rollBack();
            return failed($e);
        }
    }

    public function get_favorite_category(Request $request): array
    {
        $user_id = token()->uid;

        return success([
            'result' => true,
            'categories' => MissionCategory::whereHas('favorite_category', function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            })->pluck('id'),
        ]);
    }

    public function add_favorite_category(Request $request): array
    {
        try {
            $user_id = token()->uid;
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

    public function remove_favorite_category(Request $request): array
    {
        try {
            $user_id = token()->uid;
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
                return success(['result' => $result > 0]);
            } else {
                return success(['result' => false, 'reason' => 'not favorite']);
            }
        } catch (Exception $e) {
            return failed($e);
        }
    }

    public function follow(Request $request): array
    {
        try {
            $user_id = token()->uid;
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

    public function unfollow(Request $request): array
    {
        try {
            $user_id = token()->uid;
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

    // 나를 팔로우
    public function follower(Request $request): array
    {
        $user_id = token()->uid;

        return success([
            'result' => true,
            'users' => Follow::where('follows.target_id', $user_id)
                ->join('users', 'users.id', 'follows.user_id')
                ->leftJoin('user_stats', 'user_stats.user_id', 'users.id')
                ->leftJoin('areas', 'areas.ctg_sm', 'users.area_code')
                ->leftJoin('follows as f2', 'f2.target_id', 'users.id')
                ->select(['users.id', 'users.nickname', 'users.profile_image', 'user_stats.gender',
                    DB::raw("IF(name_lg=name_md, CONCAT_WS(' ', name_md, name_sm), CONCAT_WS(' ', name_lg, name_md, name_sm)) as area"),
                    DB::raw("COUNT(distinct f2.id) as follower")])
                ->groupBy(['follows.id', 'users.id', 'user_stats.id', 'areas.id'])
                ->get(),
        ]);
    }

    // 내가 팔로우
    public function following(Request $request): array
    {
        $user_id = token()->uid;

        return success([
            'result' => true,
            'users' => Follow::where('follows.user_id', $user_id)
                ->join('users', 'users.id', 'follows.target_id')
                ->leftJoin('user_stats', 'user_stats.user_id', 'users.id')
                ->leftJoin('areas', 'areas.ctg_sm', 'users.area_code')
                ->leftJoin('follows as f2', 'f2.target_id', 'users.id')
                ->select(['users.id', 'users.nickname', 'users.profile_image', 'user_stats.gender',
                    DB::raw("IF(name_lg=name_md, CONCAT_WS(' ', name_md, name_sm), CONCAT_WS(' ', name_lg, name_md, name_sm)) as area"),
                    DB::raw("COUNT(distinct f2.id) as follower")])
                ->groupBy(['follows.id', 'users.id', 'user_stats.id', 'areas.id'])
                ->get(),
        ]);
    }
}
