<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\Mission;
use App\Models\User;
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
                'o.id as user_id', 'o.nickname', 'o.profile_image', 'os.gender',
                DB::raw("IF(name_lg=name_md, CONCAT_WS(' ', name_md, name_sm), CONCAT_WS(' ', name_lg, name_md, name_sm)) as area"),
                DB::raw("COUNT(distinct of.user_id) as followers"),
                'is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('follows.target_id', 'o.id')
                    ->where('follows.user_id', $user_id),
                'is_bookmark' => MissionStat::selectRaw('COUNT(1) > 0')->where('mission_stats.user_id', $user_id)
                    ->whereColumn('mission_stats.mission_id', 'missions.id'),
                'user1' => MissionStat::selectRaw("CONCAT_WS('|', COALESCE(u.id, ''), COALESCE(u.nickname, ''), COALESCE(u.profile_image, ''), COALESCE(us.gender, ''))")
                    ->whereColumn('mission_stats.mission_id', 'missions.id')
                    ->join('users as u', 'u.id', 'mission_stats.user_id')
                    ->leftJoin('user_stats as us', 'us.user_id', 'u.id')
                    ->leftJoin('follows as f', 'f.target_id', 'mission_stats.user_id')
                    ->groupBy('u.id', 'us.id')->orderBy(DB::raw('COUNT(f.id)'), 'desc')->limit(1),
                'user2' => MissionStat::selectRaw("CONCAT_WS('|', COALESCE(u.id, ''), COALESCE(u.nickname, ''), COALESCE(u.profile_image, ''), COALESCE(us.gender, ''))")
                    ->whereColumn('mission_stats.mission_id', 'missions.id')
                    ->join('users as u', 'u.id', 'mission_stats.user_id')
                    ->leftJoin('user_stats as us', 'us.user_id', 'u.id')
                    ->leftJoin('follows as f', 'f.target_id', 'mission_stats.user_id')
                    ->groupBy('u.id', 'us.id')->orderBy(DB::raw('COUNT(f.id)'), 'desc')->skip(1)->limit(1),
                DB::raw('COUNT(distinct ms.id) as bookmarks'),
                DB::raw('COUNT(distinct mc.id) as comments'),
            ])
            ->groupBy('missions.id', 'o.id', 'os.id', 'oa.id')
            ->first();

            $data->is_bookmark = (bool)$data->is_bookmark;
            $data->owner = [
                'user_id' => $data->user_id,
                'nickname' => $data->nickname,
                'profile_image' => $data->profile_image ?? '',
                'gender' => $data->gender,
                'area' => $data->area,
                'followers' => $data->followers,
                'is_following' => (bool)$data->is_following,
            ];
            unset($data->user_id, $data->nickname, $data->profile_image, $data->gender,
                $data->area, $data->followers, $data->is_following);
            $tmp1 = explode('|', $data->user1 ?? '|||');
            $tmp2 = explode('|', $data->user2 ?? '|||');
            $data->users = [
                ['user_id' => $tmp1[0], 'nickname' => $tmp1[1], 'profile_image' => $tmp1[2], 'gender' => $tmp1[3]],
                ['user_id' => $tmp2[0], 'nickname' => $tmp2[1], 'profile_image' => $tmp2[2], 'gender' => $tmp2[3]],
            ];
            unset($data->user1, $data->user2);

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
