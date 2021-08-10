<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\Mission;
use App\Models\MissionCategory;
use App\Models\User;
use App\Models\UserFavoriteCategory;
use App\Models\UserMission;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class MissionCategoryController extends Controller
{
    public function index(Request $request, $town = null): array
    {
        $town = (bool)($town ?? $request->get('town', 0));

        $data = MissionCategory::whereNotNull('mission_category_id');
        if ($town) {
            $user_id = token()->uid;

            $data = $data->where(function ($query) use ($user_id) {
                // 관심카테고리
                $query->whereHas('favorite_category', function ($query) use ($user_id) {
                    $query->where('user_id', $user_id);
                });
                // 북마크한 미션이 있는 카테고리
                $query->orWhereHas('missions', function ($query) use ($user_id) {
                    $query->whereHas('user_missions', function ($query) use ($user_id) {
                        $query->where('user_id', $user_id);
                    });
                });
            })
                ->select([
                    'mission_categories.id',
                    DB::raw("COALESCE(mission_categories.emoji, '') as emoji"),
                    'mission_categories.title',
                    'bookmark_total' => UserMission::selectRaw("COUNT(1)")->where('user_missions.user_id', $user_id)
                        ->whereHas('mission', function ($query) use ($user_id) {
                            $query->whereColumn('missions.mission_category_id', 'mission_categories.id');
                        }),
                    'is_favorite' => UserFavoriteCategory::selectRaw("COUNT(1) > 0")->where('user_id', $user_id)
                        ->whereColumn('user_favorite_categories.mission_category_id', 'mission_categories.id'),
                ])
                ->orderBy('bookmark_total', 'desc')->orderBy('is_favorite', 'desc')->orderBy('id')
                ->get();
        } else {
            $data = $data->select([
                'mission_categories.id',
                DB::raw("COALESCE(mission_categories.emoji, '') as emoji"),
                'mission_categories.title',
            ])->get();
        }

        return success([
            'result' => true,
            'categories' => $data,
        ]);
    }

    public function show(Request $request, $category_id): array
    {
        if (!$category_id) {
            return success([
                'result' => false,
                'reason' => 'not enough data',
            ]);
        }

        $category = MissionCategory::where('id', $category_id)
            ->select([
                'mission_categories.id',
                DB::raw("COALESCE(mission_categories.emoji, '') as emoji"),
                'mission_categories.title',
                DB::raw("COALESCE(mission_categories.description, '') as description"),
            ])
            ->first();

        $users = User::whereHas('user_missions', function ($query) use ($category_id) {
            $query->whereNull('deleted_at')
                ->whereHas('mission', function ($query) use ($category_id) {
                    $query->whereHas('category', function ($query) use ($category_id) {
                        $query->where('id', $category_id);
                    });
                });
        })
            ->join('follows as f', 'f.target_id', 'users.id')
            ->select(['users.id', 'users.profile_image', DB::raw('COUNT(distinct f.id) as followers')])
            ->groupBy('users.id')
            ->orderBy('followers', 'desc')->paginate(3);

        $banners = (new BannerController())->category_banner($category_id);
        $mission_total = Mission::where('mission_category_id', $category_id)->count();
        $missions = $this->mission($request, $category_id, 3)['data']['missions'];

        return success([
            'result' => true,
            'category' => $category,
            'user_total' => $users->total(),
            'users' => $users->items(),
            'banners' => $banners,
            'mission_total' => $mission_total,
            'missions' => $missions,
        ]);
    }

    public function mission(Request $request, $id = null, $limit = null, $page = null, $sort = null): array
    {
        $user_id = token()->uid;

        $limit = $limit ?? $request->get('limit', 20);
        $page = $page ?? $request->get('page', 0);
        $sort = $sort ?? $request->get('sort', 'popular');

        if ($id) {
            $data = Mission::where('mission_category_id', $id)
                ->join('users as o', 'o.id', 'missions.user_id') // 미션 제작자
                ->join('user_stats as os', 'os.user_id', 'o.id') // 미션 제작자
                ->leftJoin('follows as of', 'of.target_id', 'o.id') // 미션 제작자 팔로워
                ->leftJoin('areas as oa', 'oa.ctg_sm', 'o.area_code')
                ->leftJoin('user_missions as um', function ($query) {
                    $query->on('um.mission_id', 'missions.id')->whereNull('um.deleted_at');
                })
                ->leftJoin('mission_comments as mc', 'mc.mission_id', 'missions.id')
                ->select([
                    'missions.id', 'missions.title', 'missions.description',
                    'o.id as user_id', 'o.nickname', 'o.profile_image', 'os.gender',
                    DB::raw("IF(name_lg=name_md, CONCAT_WS(' ', name_md, name_sm), CONCAT_WS(' ', name_lg, name_md, name_sm)) as area"),
                    DB::raw("COUNT(distinct of.user_id) as followers"),
                    'is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('follows.target_id', 'o.id')
                        ->where('follows.user_id', $user_id),
                    'is_bookmark' => UserMission::selectRaw('COUNT(1) > 0')->where('user_missions.user_id', $user_id)
                        ->whereColumn('user_missions.mission_id', 'missions.id'),
                    'user1' => UserMission::selectRaw("CONCAT_WS('|', COALESCE(u.id, ''), COALESCE(u.nickname, ''), COALESCE(u.profile_image, ''), COALESCE(us.gender, ''))")
                        ->whereColumn('user_missions.mission_id', 'missions.id')
                        ->join('users as u', 'u.id', 'user_missions.user_id')
                        ->leftJoin('user_stats as us', 'us.user_id', 'u.id')
                        ->leftJoin('follows as f', 'f.target_id', 'user_missions.user_id')
                        ->groupBy('u.id', 'us.id')->orderBy(DB::raw('COUNT(f.id)'), 'desc')->limit(1),
                    'user2' => UserMission::selectRaw("CONCAT_WS('|', COALESCE(u.id, ''), COALESCE(u.nickname, ''), COALESCE(u.profile_image, ''), COALESCE(us.gender, ''))")
                        ->whereColumn('user_missions.mission_id', 'missions.id')
                        ->join('users as u', 'u.id', 'user_missions.user_id')
                        ->leftJoin('user_stats as us', 'us.user_id', 'u.id')
                        ->leftJoin('follows as f', 'f.target_id', 'user_missions.user_id')
                        ->groupBy('u.id', 'us.id')->orderBy(DB::raw('COUNT(f.id)'), 'desc')->skip(1)->limit(1),
                    DB::raw('COUNT(distinct um.id) as bookmarks'),
                    DB::raw('COUNT(distinct mc.id) as comments'),
                ])
                ->groupBy('missions.id', 'o.id', 'os.id', 'oa.id');

            if ($sort === 'popular') {
                $data->orderBy('bookmarks', 'desc')->orderBy('missions.id', 'desc');
            } elseif ($sort === 'recent') {
                $data->orderBy('missions.id', 'desc');
            } else {
                $data->orderBy('bookmarks', 'desc')->orderBy('missions.id', 'desc');
            }

            $data = $data->skip($page * $limit)->take($limit)->get();

            foreach ($data as $i => $item) {
                $data[$i]['is_bookmark'] = (bool)$item->is_bookmark;
                $data[$i]['owner'] = [
                    'user_id' => $item->user_id,
                    'nickname' => $item->nickname,
                    'profile_image' => $item->profile_image ?? '',
                    'gender' => $item->gender,
                    'area' => $item->area,
                    'followers' => $item->followers,
                    'is_following' => (bool)$item->is_following,
                ];
                unset($data[$i]->user_id, $data[$i]->nickname, $data[$i]->profile_image, $data[$i]->gender,
                    $data[$i]->area, $data[$i]->followers, $data[$i]->is_following);
                $tmp1 = explode('|', $item['user1'] ?? '|||');
                $tmp2 = explode('|', $item['user2'] ?? '|||');
                $data[$i]['users'] = [
                    ['user_id' => $tmp1[0], 'nickname' => $tmp1[1], 'profile_image' => $tmp1[2], 'gender' => $tmp1[3]],
                    ['user_id' => $tmp2[0], 'nickname' => $tmp2[1], 'profile_image' => $tmp2[2], 'gender' => $tmp2[3]],
                ];
                unset($data[$i]->user1, $data[$i]->user2);
            }

            return success([
                'result' => true,
                'missions' => $data,
            ]);
        } else {
            return success([
                'result' => false,
                'reason' => 'not enough data',
            ]);
        }
    }
}
