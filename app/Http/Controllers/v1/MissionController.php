<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use App\Models\MissionCategory;
use App\Models\UserMission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MissionController extends Controller
{
    // 카테고리 별 미션 목록
    public function missions(Request $request, $limit = 20, $page = 0, $sort = 'popular'): array
    {
        $category_id = $request->get('category_id');
        $limit = $request->get('limit', $limit);
        $page = $request->get('page', $page);
        $sort = $request->get('sort', $sort);

        if ($category_id) {
            $data = Mission::where('mission_category_id', $category_id)
                ->leftJoin('user_missions', 'user_missions.mission_id', 'missions.id')
                ->leftJoin('mission_comments', 'mission_comments.mission_id', 'missions.id')
                ->select(['missions.title', 'missions.description',
                    DB::raw('COUNT(distinct user_missions.id) as bookmarks'),
                    DB::raw('COUNT(distinct mission_comments.id) as comments')])
                ->groupBy('missions.id');

            if ($sort === 'popular') {
                $data->orderBy('bookmarks', 'desc')->orderBy('missions.id', 'desc');
            } elseif ($sort === 'new') {
                $data->orderBy('missions.id', 'desc');
            } else {
                $data->orderBy('bookmarks', 'desc')->orderBy('missions.id', 'desc');
            }

            $data = $data->skip($page)->take($limit)->get();

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

    // 미션 상세
    public function mission(Request $request, $mission_id): array
    {
        return success([
            'result' => true,
            'mission' => Mission::where('missions.id', $mission_id)
                ->join('users', 'users.id', 'missions.user_id')
                ->select(['users.nickname', 'users.profile_image', 'missions.title', 'missions.description',
                    'missions.image_url'])
                ->first(),
        ]);
    }

    public function categories(Request $request): array
    {
        return success([
            'result' => true,
            'categories' => MissionCategory::select([
                    'mission_categories.id',
                    'mission_categories.emoji',
                    'mission_categories.title',
                ])
                ->whereNotNull('mission_category_id')
                ->get(),
        ]);
    }

    public function get_bookmark(Request $request): array
    {
        $user_id = token()->uid;

        $category_id = $request->get('category_id');

        $data = Mission::select(['id', 'title', 'description'])
            ->when($category_id, function ($query) use ($category_id) {
                $query->where('mission_category_id', $category_id);
            })
            ->whereHas('user_mission', function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            })
            ->take(3)->get();

        return success([
            'result' => true,
            'missions' => $data,
        ]);
    }

    public function add_bookmark(Request $request): array
    {
        $user_id = token()->uid;
        $mission_id = $request->get('mission_id');

        if (is_null($mission_id)) {
            return success([
                'result' => false,
                'reason' => 'not enough data',
            ]);
        }

        if (UserMission::where(['user_id' => $user_id, 'mission_id' => $mission_id])->exists()) {
            return success(['result' => false, 'reason' => 'already bookmark']);
        } else {
            $data = UserMission::create([
                'user_id' => $user_id,
                'mission_id' => $mission_id,
            ]);
            return success(['result' => true]);
        }
    }

    public function remove_bookmark(Request $request): array
    {
        $user_id = token()->uid;
        $mission_id = $request->get('mission_id');

        if (is_null($mission_id)) {
            return success([
                'result' => false,
                'reason' => 'not enough data',
            ]);
        }

        if ($mission = UserMission::where(['user_id' => $user_id, 'mission_id' => $mission_id])) {
            DB::beginTransaction();

            $data = $mission->delete();

            DB::commit();
            return success(['result' => true]);
        } else {
            return success([
                'result' => false,
                'reason' => 'not bookmark',
            ]);
        }
    }
}
