<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use App\Models\User;
use App\Models\UserMission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MissionController extends Controller
{
    /**
     * 미션 상세
     */
    public function show($mission_id): array
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

    public function user(Request $request, $mission_id): array
    {
        $limit = $request->get('limit', 20);

        $users = UserMission::where('user_missions.mission_id', $mission_id)
            ->join('users', 'users.id', 'user_missions.user_id')
            ->leftJoin('user_stats', 'user_stats.user_id', 'users.id')
            ->leftJoin('areas', 'areas.ctg_sm', 'users.area_code')
            ->leftJoin('follows', 'follows.target_id', 'users.id')
            ->leftJoin('feed_missions', 'feed_missions.mission_id', 'user_missions.mission_id')
            ->select([
                'users.id', 'users.nickname', 'users.profile_image', 'user_stats.gender',
                DB::raw("IF(name_lg=name_md, CONCAT_WS(' ', name_md, name_sm), CONCAT_WS(' ', name_lg, name_md, name_sm)) as area"),
                DB::raw("COUNT(distinct follows.id) as follower"),
                DB::raw("COUNT(distinct feed_missions.feed_id) as mission_feeds"),
            ])
            ->groupBy(['users.id', 'user_stats.id', 'areas.id'])
            ->orderBy('mission_feeds')->orderBy('follower', 'desc')->orderBy('id', 'desc')
            ->take($limit)->get();

        return success([
            'success' => true,
            'users' => $users,
        ]);
    }
}
