<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use App\Models\MissionCategory;
use App\Models\User;
use App\Models\UserMission;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class MissionCategoryController extends Controller
{
    public function index(): array
    {
        return success([
            'result' => true,
            'categories' => MissionCategory::select([
                'mission_categories.id',
                DB::raw("COALESCE(mission_categories.emoji, '') as emoji"),
                'mission_categories.title',
            ])
                ->whereNotNull('mission_category_id')
                ->get(),
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

        DB::enableQueryLog();
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
                ->leftJoin('user_missions as um', function ($query) {
                    $query->on('um.mission_id', 'missions.id')->whereNull('um.deleted_at');
                })
                ->leftJoin('mission_comments as mc', 'mc.mission_id', 'missions.id')
                ->select([
                    'missions.id', 'missions.title', 'missions.description',
                    DB::raw("CONCAT(COALESCE(o.id, ''), '|', COALESCE(o.profile_image, '')) as owner"),
                    'is_bookmark' => UserMission::selectRaw('COUNT(1)>0')->where('user_missions.user_id', $user_id)
                        ->whereColumn('user_missions.mission_id', 'missions.id')->limit(1),
                    'user1' => UserMission::selectRaw("CONCAT(COALESCE(u.id, ''), '|', COALESCE(u.profile_image, ''))")
                        ->whereColumn('user_missions.mission_id', 'missions.id')
                        ->join('users as u', 'u.id', 'user_missions.user_id')
                        ->leftJoin('follows as f', 'f.target_id', 'user_missions.user_id')
                        ->groupBy('u.id')->orderBy(DB::raw('COUNT(f.id)'), 'desc')->limit(1),
                    'user2' => UserMission::selectRaw("CONCAT(COALESCE(u.id, ''), '|', COALESCE(u.profile_image, ''))")
                        ->whereColumn('user_missions.mission_id', 'missions.id')
                        ->join('users as u', 'u.id', 'user_missions.user_id')
                        ->leftJoin('follows as f', 'f.target_id', 'user_missions.user_id')
                        ->groupBy('u.id')->orderBy(DB::raw('COUNT(f.id)'), 'desc')->skip(1)->limit(1),
                    DB::raw('COUNT(distinct um.id) as bookmarks'),
                    DB::raw('COUNT(distinct mc.id) as comments'),
                ])
                ->groupBy('missions.id', 'o.id');

            if ($sort === 'popular') {
                $data->orderBy('bookmarks', 'desc')->orderBy('missions.id', 'desc');
            } elseif ($sort === 'recent') {
                $data->orderBy('missions.id', 'desc');
            } else {
                $data->orderBy('bookmarks', 'desc')->orderBy('missions.id', 'desc');
            }

            $data = $data->skip($page * $limit)->take($limit)->get();

            foreach($data as $i => $item) {
                $tmp = explode('|', $item['owner'] ?? '|');
                $data[$i]['owner'] = ['user_id' => $tmp[0], 'profile_image' => $tmp[1]];
                $tmp1 = explode('|', $item['user1'] ?? '|');
                $tmp2 = explode('|', $item['user2'] ?? '|');
                $data[$i]['users'] = [
                    ['user_id' => $tmp1[0], 'profile_image' => $tmp1[1]],['user_id' => $tmp2[0], 'profile_image' => $tmp2[1]]
                ];
                unset($data[$i]['user1'], $data[$i]['user2']);
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
