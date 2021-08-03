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
    public function category(Request $request): array
    {
        return success([
            'result' => true,
            'categories' => MissionCategory::select([
                    'mission_categories.id',
                    'mission_categories.emoji',
                    'mission_categories.title',
                ])
                ->get(),
        ]);
    }

    public function get_bookmark(Request $request): array
    {
        $user_id = token()->uid;

        $data = Mission::select(['id', 'title', 'description'])
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

    public function get_mission(Request $request, $limit = 20, $page = 0): array
    {
        $category_id = $request->get('category_id');
        $limit = $request->get('limit', $limit);
        $page = $request->get('page', $page);

        if ($category_id) {
            $data = Mission::where('mission_category_id', $category_id)
                ->leftJoin('user_missions', 'user_missions.mission_id', 'missions.id')
                ->leftJoin('mission_comments', 'mission_comments.mission_id', 'missions.id')
                ->select(['missions.title', 'missions.description',
                    DB::raw('COUNT(distinct user_missions.id) as bookmarks'),
                    DB::raw('COUNT(distinct mission_comments.id) as comments')])
                ->groupBy('missions.id')
                ->skip($page)->take($limit)->get();

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
