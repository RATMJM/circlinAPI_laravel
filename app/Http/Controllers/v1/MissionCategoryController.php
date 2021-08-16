<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Follow;
use App\Models\Mission;
use App\Models\MissionCategory;
use App\Models\MissionComment;
use App\Models\MissionStat;
use App\Models\User;
use App\Models\UserFavoriteCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class MissionCategoryController extends Controller
{
    public function index($town = null): array
    {
        if ($town === 'town') {
            $user_id = token()->uid;

            $data = MissionCategory::whereNotNull('mission_category_id')
                ->where(function ($query) use ($user_id) {
                    // 관심카테고리
                    $query->whereHas('favorite_category', function ($query) use ($user_id) {
                        $query->where('user_id', $user_id);
                    });
                    // 북마크한 미션이 있는 카테고리
                    $query->orWhereHas('missions', function ($query) use ($user_id) {
                        $query->whereHas('mission_stats', function ($query) use ($user_id) {
                            $query->where('user_id', $user_id);
                        });
                    });
                })
                ->orWhere('id', 0)
                ->select([
                    'id', DB::raw("CAST(id as CHAR(20)) as `key`"), DB::raw("COALESCE(emoji, '') as emoji"),
                    'title',
                    'bookmark_total' => MissionStat::selectRaw("COUNT(1)")->where('mission_stats.user_id', $user_id)
                        ->whereHas('mission', function ($query) use ($user_id) {
                            $query->whereColumn('missions.mission_category_id', 'mission_categories.id');
                        }),
                    'is_favorite' => UserFavoriteCategory::selectRaw("COUNT(1) > 0")->where('user_id', $user_id)
                        ->whereColumn('user_favorite_categories.mission_category_id', 'mission_categories.id'),
                ])
                ->orderBy(DB::raw("id=0"), 'desc')
                ->orderBy('bookmark_total', 'desc')->orderBy('is_favorite', 'desc')->orderBy('id')
                ->get();
        } else {
            $data = MissionCategory::whereNotNull('mission_category_id')
                ->select([
                    'mission_categories.id',
                    DB::raw("CAST(mission_categories.id as CHAR(20)) as `key`"),
                    DB::raw("COALESCE(mission_categories.emoji, '') as emoji"),
                    'mission_categories.title',
                    'mission_categories.description',
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

        $users = User::whereHas('mission_stats', function ($query) use ($category_id) {
            $query->whereHas('mission', function ($query) use ($category_id) {
                $query->whereHas('category', function ($query) use ($category_id) {
                    $query->where('id', $category_id);
                });
            });
        })
            ->leftJoin('follows as f', 'f.target_id', 'users.id')
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
        DB::enableQueryLog();
        $user_id = token()->uid;

        $limit = $limit ?? $request->get('limit', 20);
        $page = $page ?? $request->get('page', 0);
        $sort = $sort ?? $request->get('sort', 'popular');

        $data = Mission::when($id, function ($query, $id) {
            $query->whereIn('missions.mission_category_id', Arr::wrap($id));
        })
            ->join('users as o', 'o.id', 'missions.user_id') // 미션 제작자
            ->join('user_stats as os', 'os.user_id', 'o.id') // 미션 제작자
            ->select([
                'missions.id', 'missions.title', 'missions.description',
                'o.id as user_id', 'o.nickname', 'o.profile_image', 'os.gender',
                'area' => Area::selectRaw("IF(name_lg=name_md, CONCAT_WS(' ', name_md, name_sm), CONCAT_WS(' ', name_lg, name_md, name_sm)) as area")
                    ->whereColumn('ctg_sm', 'o.area_code')->limit(1),
                'followers' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'o.id'),
                'is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('follows.target_id', 'o.id')
                    ->where('follows.user_id', $user_id),
                'is_bookmark' => MissionStat::selectRaw('COUNT(1) > 0')->where('mission_stats.user_id', $user_id)
                    ->whereColumn('mission_stats.mission_id', 'missions.id'),
                'bookmarks' => MissionStat::selectRaw("COUNT(1)")->whereCOlumn('mission_id', 'missions.id'),
                'comments' => MissionComment::selectRaw("COUNT(1)")->whereCOlumn('mission_id', 'missions.id'),
            ])
            ->groupBy('missions.id', 'o.id', 'os.id');

        if ($sort === 'popular') {
            $data->orderBy('bookmarks', 'desc')->orderBy('missions.id', 'desc');
        } elseif ($sort === 'recent') {
            $data->orderBy('missions.id', 'desc');
        } else {
            $data->orderBy('bookmarks', 'desc')->orderBy('missions.id', 'desc');
        }

        $data = $data->skip($page * $limit)->take($limit)->get();

        foreach ($data as $i => $item) {
            $data[$i]->owner = arr_group($item, ['user_id', 'nickname', 'profile_image', 'gender',
                'area', 'followers', 'is_following']);

            $data[$i]->users = $item->mission_stats()
                ->select(['users.id', 'users.nickname', 'users.profile_image', 'user_stats.gender'])
                ->join('users', 'users.id', 'mission_stats.user_id')
                ->leftJoin('user_stats', 'user_stats.user_id', 'users.id')
                ->leftJoin('follows', 'follows.target_id', 'mission_stats.user_id')
                ->groupBy('users.id', 'user_stats.id')->orderBy(DB::raw('COUNT(follows.id)'), 'desc')->take(2)->get();
        }

        return success([
            'result' => true,
            'missions' => $data,
        ]);
    }
}
