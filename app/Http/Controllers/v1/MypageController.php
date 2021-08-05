<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Feed;
use App\Models\FeedImage;
use App\Models\FeedMission;
use App\Models\Mission;
use App\Models\MissionCategory;
use App\Models\User;
use App\Models\UserMission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MypageController extends Controller
{
    /**
     * 내 프로필 기본 데이터
     * */
    public function index(): array
    {
        $data = User::where('users.id', token()->uid)
            ->leftJoin('areas as a', 'a.ctg_sm', 'users.area_code')
            ->leftJoin('user_stats as us', 'us.user_id', 'users.id')
            ->leftJoin('follows as f1', 'f1.target_id', 'users.id') // 팔로워
            ->leftJoin('follows as f2', 'f2.user_id', 'users.id') // 팔로잉
            ->leftJoin('missions as m', 'm.user_id', 'users.id') // 미션 제작
            ->leftJoin('feeds as f', 'f.user_id', 'users.id')
            ->leftJoin('feed_likes as fl', 'fl.user_id', 'users.id')
            ->leftJoin('feed_missions as fm', 'fm.feed_id', 'f.id')
            ->select(['users.nickname', 'users.point', 'users.profile_image',
                DB::raw("IF(a.name_lg=a.name_md, CONCAT_WS(' ', a.name_md, a.name_sm), CONCAT_WS(' ', a.name_lg, a.name_md, a.name_sm)) as area"),
                DB::raw('COUNT(distinct f1.id) as followers'), DB::raw('COUNT(distinct f2.id) as followings'),
                DB::raw('COUNT(distinct m.id) as make_missions'),
                DB::raw('COUNT(distinct f.id) as feeds'), DB::raw('COUNT(distinct fl.id) as checks'),
                DB::raw('COUNT(distinct fm.id) as missions')])
            ->groupBy('users.id', 'a.id', 'us.id')
            ->first();

        return success([
            'success' => true,
            'user' => $data,
        ]);
    }

    /**
     * 내 피드 데이터
     */
    public function feed(Request $request, $feed_id = null): array
    {
        $user_id = token()->uid;

        $limit = $request->get('limit', 20);
        $page = $request->get('page', 0);

        $categories = MissionCategory::whereNotNull('m.mission_category_id')
            ->where('f.user_id', $user_id)
            ->join('missions as m', 'm.mission_category_id', 'mission_categories.id')
            ->join('feed_missions as fm', 'fm.mission_id', 'm.id')
            ->join('feeds as f', 'f.id', 'fm.feed_id')
            ->select([
                'mission_categories.id', 'mission_categories.title', 'mission_categories.emoji',
                DB::raw('COUNT(distinct f.id) as feeds'),
            ])
            ->groupBy('mission_categories.id')
            ->get();

        $feeds = Feed::where('feeds.user_id', $user_id)
            ->leftJoin('feed_missions as fm', 'fm.feed_id', 'feeds.id')
            ->leftJoin('feed_likes as fl', 'fl.feed_id', 'feeds.id')
            ->leftJoin('feed_comments as fc', 'fc.feed_id', 'feeds.id')
            ->select([
                'feeds.id', 'feeds.created_at', 'feeds.content',
                'image' => FeedImage::select('image_url')->whereColumn('feed_images.feed_id', 'feeds.id')->orderBy('id')->limit(1),
                DB::raw('COUNT(distinct fm.id) as missions'),
                'mission_id' => FeedMission::select('mission_id')->whereColumn('feed_missions.feed_id', 'feeds.id')
                    ->orderBy('id')->limit(1),
                'mission' => Mission::select('title')
                    ->whereHas('feed_missions', function ($query) {
                        $query->whereColumn('feed_missions.feed_id', 'feeds.id')->orderBy('id');
                    })->orderBy('id')->limit(1),
                'emoji' => MissionCategory::select('emoji')
                    ->whereHas('missions', function ($query) {
                        $query->whereHas('feed_missions', function ($query) {
                            $query->whereColumn('feed_missions.feed_id', 'feeds.id')->orderBy('id');
                        });
                    })->limit(1),
                DB::raw('COUNT(distinct fl.id) as checks'),
                DB::raw('COUNT(distinct fc.id) as comments'),
            ])
            ->groupBy('feeds.id')
            ->skip($page * $limit)->take($limit)->get();

        return success([
            'result' => true,
            'categories' => $categories,
            'feeds' => $feeds,
        ]);
    }

    /**
     * 내가 체크한 피드
     * */
    public function check(Request $request): array
    {
        $user_id = token()->uid;

        $limit = $request->get('limit', 20);
        $page = $request->get('page', 0);

        $feeds = Feed::rightJoin('feed_likes as fl', function ($query) use ($user_id) {
            $query->on('fl.feed_id', 'feeds.id')->where('fl.user_id', $user_id); // 내가 체크한
        })
            ->leftJoin('feed_missions as fm', 'fm.feed_id', 'feeds.id')
            ->leftJoin('feed_likes as fl2', 'fl2.feed_id', 'feeds.id') // 체크 수
            ->leftJoin('feed_comments as fc', 'fc.feed_id', 'feeds.id') // 댓글 수
            ->select([
                'feeds.id', 'feeds.created_at', 'feeds.content',
                'image' => FeedImage::select('image_url')->whereColumn('feed_images.feed_id', 'feeds.id')->orderBy('id')->limit(1),
                DB::raw('COUNT(distinct fm.id) as missions'),
                'mission_id' => FeedMission::select('mission_id')->whereColumn('feed_missions.feed_id', 'feeds.id')
                    ->orderBy('id')->limit(1),
                'mission' => Mission::select('title')
                    ->whereHas('feed_missions', function ($query) {
                        $query->whereColumn('feed_missions.feed_id', 'feeds.id')->orderBy('id');
                    })->orderBy('id')->limit(1),
                'emoji' => MissionCategory::select('emoji')
                    ->whereHas('missions', function ($query) {
                        $query->whereHas('feed_missions', function ($query) {
                            $query->whereColumn('feed_missions.feed_id', 'feeds.id')->orderBy('id');
                        });
                    })->limit(1),
                DB::raw('COUNT(distinct fl2.id) as checks'),
                DB::raw('COUNT(distinct fc.id) as comments'),
            ])
            ->groupBy('feeds.id')
            ->skip($page * $limit)->take($limit)->get();

        return success([
            'result' => true,
            'feeds' => $feeds,
        ]);
    }

    /**
     * 내가 진행했던 미션 전체
     * */
    public function mission(Request $request): array
    {
        $user_id = token()->uid;

        $limit = $request->get('limit', 20);
        $page = $request->get('page', 0);

        $categories = MissionCategory::whereNotNull('m.mission_category_id')
            ->where('f.user_id', $user_id)
            ->join('missions as m', 'm.mission_category_id', 'mission_categories.id')
            ->join('feed_missions as fm', 'fm.mission_id', 'm.id')
            ->join('feeds as f', 'f.id', 'fm.feed_id')
            ->select([
                'mission_categories.id', 'mission_categories.title', 'mission_categories.emoji',
                DB::raw('COUNT(distinct f.id) as feeds'),
            ])
            ->groupBy('mission_categories.id')
            ->get();

        $missions = Mission::whereHas('feed_missions', function ($query) use ($user_id) {
            $query->whereHas('feed', function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            });
        })
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
            ->groupBy('missions.id', 'o.id')
            ->get();

        foreach($missions as $i => $mission) {
            $tmp = explode('|', $mission['owner'] ?? '|');
            $missions[$i]['owner'] = ['user_id' => $tmp[0], 'profile_image' => $tmp[1]];
            $tmp1 = explode('|', $mission['user1'] ?? '|');
            $tmp2 = explode('|', $mission['user2'] ?? '|');
            $missions[$i]['user'] = [
                ['user_id' => $tmp1[0], 'profile_image' => $tmp1[1]],['user_id' => $tmp2[0], 'profile_image' => $tmp2[1]]
            ];
            unset($missions[$i]['user1'], $missions[$i]['user2']);
        }

        return success([
            'result' => true,
            'categories' => $categories,
            'missions' => $missions,
        ]);
    }
}
