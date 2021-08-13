<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\Mission;
use App\Models\MissionStat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MissionController extends Controller
{
    /**
     * 미션 상세
     */
    public function show($mission_id): array
    {
        $user_id = token()->uid;

        $data = Mission::where('missions.id', $mission_id)
            ->join('users as o', 'o.id', 'missions.user_id') // 미션 제작자
            ->join('user_stats as os', 'os.user_id', 'o.id') // 미션 제작자
            ->leftJoin('follows as of', 'of.target_id', 'o.id') // 미션 제작자 팔로워
            ->leftJoin('areas as oa', 'oa.ctg_sm', 'o.area_code')
            ->leftJoin('mission_stats as ms', function ($query) {
                $query->on('ms.mission_id', 'missions.id')->whereNull('ms.ended_at');
            })
            ->leftJoin('mission_comments as mc', 'mc.mission_id', 'missions.id')
            ->select([
                'missions.id', 'missions.title', 'missions.description',
                'o.id as owner_id', 'o.nickname', 'o.profile_image', 'os.gender',
                DB::raw("IF(name_lg=name_md, CONCAT_WS(' ', name_md, name_sm), CONCAT_WS(' ', name_lg, name_md, name_sm)) as area"),
                DB::raw("COUNT(distinct of.user_id) as followers"),
                'is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('follows.target_id', 'o.id')
                    ->where('follows.user_id', $user_id),
                'is_bookmark' => MissionStat::selectRaw('COUNT(1) > 0')->where('mission_stats.user_id', $user_id)
                    ->whereColumn('mission_stats.mission_id', 'missions.id'),
                DB::raw('COUNT(distinct ms.id) as bookmarks'),
                DB::raw('COUNT(distinct mc.id) as comments'),
            ])
            ->groupBy('missions.id', 'o.id', 'os.id', 'oa.id')
            ->first();

        $data->owner = arr_group($data, ['owner_id', 'nickname', 'profile_image', 'gender', 'area', 'followers', 'is_following']);

        $data->users = $data->mission_stats()
            ->select(['users.id', 'users.nickname', 'users.profile_image', 'user_stats.gender'])
            ->join('users', 'users.id', 'mission_stats.user_id')
            ->leftJoin('user_stats', 'user_stats.user_id', 'users.id')
            ->leftJoin('follows', 'follows.target_id', 'mission_stats.user_id')
            ->groupBy('users.id', 'user_stats.id')->orderBy(DB::raw('COUNT(follows.id)'), 'desc')->take(2)->get();

        return success([
            'result' => true,
            'mission' => $data,
        ]);
    }

    public function user(Request $request, $mission_id): array
    {
        $limit = $request->get('limit', 20);

        $users = MissionStat::where('mission_stats.mission_id', $mission_id)
            ->join('users', 'users.id', 'mission_stats.user_id')
            ->leftJoin('user_stats', 'user_stats.user_id', 'users.id')
            ->leftJoin('areas', 'areas.ctg_sm', 'users.area_code')
            ->leftJoin('follows', 'follows.target_id', 'users.id')
            ->leftJoin('feed_missions', 'feed_missions.mission_id', 'mission_stats.mission_id')
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
