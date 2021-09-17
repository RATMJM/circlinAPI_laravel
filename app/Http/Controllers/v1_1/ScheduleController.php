<?php

namespace App\Http\Controllers\v1_1;

use App\Http\Controllers\Controller;
use App\Models\CommonCode;
use App\Models\Feed;
use App\Models\Follow;
use App\Models\MissionStat;
use App\Models\SortUser;
use App\Models\User;
use App\Models\UserStat;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    public static function sort_users($con = null)
    {
        $con ? $con->comment("유저 추출 시작") : print("유저 추출 시작\n");

        $max = round(Follow::select('target_id', DB::raw("COUNT(distinct user_id) as c"))
            ->groupBy('target_id')->orderBy('c', 'desc')->value('c') / 2);

        $con ? $con->comment("최대 팔로워 : $max") : print("최대 팔로워 : $max\n");

        $users = User::select('users.id', DB::raw("COUNT(distinct follows.user_id) + (RAND()*$max) r"))
            ->leftJoin('follows', 'follows.target_id', 'users.id')
            ->groupBy('users.id')
            ->orderBy('users.id')
            ->get();

        $con ? $con->comment("유저 불러오기 완료") : print("유저 불러오기 완료\n");

        $i = 0;
        $data = [];

        SortUser::truncate();
        $con ? $con->comment("sort_users 초기화 완료") : print("sort_users 초기화 완료\n");
        foreach ($users as $i => $user) {
            $data[] = [
                'created_at' => DB::raw("now()"), 'updated_at' => DB::raw("now()"),
                'user_id' => $user->id, 'order' => $user->r,
            ];
            if (($i + 1) % 10000 === 0) {
                SortUser::insert($data);
                $con ? $con->comment($i + 1 . "명 등록 완료") : print($i + 1 . "명 등록 완료\n");
                $data = [];
            }
        }
        SortUser::insert($data);
        $con ? $con->comment($i + 1 . "명 등록 완료") : print($i + 1 . "명 등록 완료\n");
    }

    public static function suggest_user($con = null)
    {
        $con ? $con->comment("유저 추출 시작") : print("유저 추출 시작\n");

        $max = Follow::select('target_id', DB::raw("COUNT(distinct user_id) as c"))
            ->groupBy('target_id')->orderBy('c', 'desc')->value('c');

        $con ? $con->comment("최대 팔로워 : $max") : print("최대 팔로워 : $max\n");

        $init = init_today(time()-(86400*7));

        $users = User::select([
            'users.id',
            DB::raw("(
                select GROUP_CONCAT(t.id separator '|') from users u
                left join (
                    select distinct u2.id from feeds
                    left join users u2 on u2.id = feeds.user_id
                    where feeds.created_at >= '$init'
                        and u2.id not in (select target_id from follows where follows.user_id = u2.id)
                        and feeds.user_id != u2.id and feeds.deleted_at is null
                    order by (select COUNT(distinct user_id) from follows where target_id=u2.id) + IF(u2.gender=u.gender,0,500) desc
                    limit 50
                ) t on t.id = u.id
                where u.id = users.id
            ) as suggest_users")
        ])
            ->groupBy('users.id')
            ->take(10)
            ->get();

        return $users;

        function suggest($user_id, $max)
        {
            return Feed::where('feeds.created_at', '>=', init_today(time()-(86400*7)))
                ->whereDoesntHave('followers', function ($query) use ($user_id) {
                    $query->where('user_id', $user_id);
                })
                ->where('feeds.user_id', '!=', $user_id)
                ->leftJoin('sort_users', 'sort_users.user_id', 'feeds.user_id')
                ->select([
                    'sort_users.user_id',
                ])
                ->groupBy('sort_users.id')
                ->orderBy(DB::raw("`order`+
                IF((select gender from users where id=$user_id)=(select gender from users where id=sort_users.user_id),0,500)"), 'desc')
                ->take(50)->dd();
        }

        $users = User::pluck('id');

        $suggest = null;
        foreach ($users as $user) {
            if ($suggest) {
                $suggest->union(suggest($user, $max));
            } else {
                $suggest = suggest($user, $max);
            }
        }

        return $users;

        /*$users = User::joinSub($users, 'u', 'u.id', 'users.id')
            ->select([
                'u.id',
                DB::raw("(select GROUP_CONCAT(user_id) from follows where target_id=u.id group by target_id order by COUNT(distinct user_id) desc) as suggest_users")
            ])
            ->groupBy('u.id')
            ->get();*/



        return $users;

        $con ? $con->comment("유저 불러오기 완료") : print("유저 불러오기 완료\n");

        $i = 0;
        $data = [];

        SortUser::truncate();
        $con ? $con->comment("sort_users 초기화 완료") : print("sort_users 초기화 완료\n");
        foreach ($users as $i => $user) {
            $data[] = [
                'created_at' => DB::raw("now()"), 'updated_at' => DB::raw("now()"),
                'user_id' => $user->id, 'order' => $user->r,
            ];
            if (($i + 1) % 10000 === 0) {
                SortUser::insert($data);
                $con ? $con->comment($i + 1 . "명 등록 완료") : print($i + 1 . "명 등록 완료\n");
                $data = [];
            }
        }
        SortUser::insert($data);
        $con ? $con->comment($i + 1 . "명 등록 완료") : print($i + 1 . "명 등록 완료\n");
    }

    public static function yesterday_feeds_count()
    {
        $yesterday = init_today(time() - 86400);

        $users = Follow::where('feeds.created_at', '>=', $yesterday)
            ->leftJoin('feeds', 'feeds.user_id', 'follows.target_id')
            ->select([
                'follows.user_id', DB::raw("COUNT(distinct feeds.id) as c")
            ])
            ->groupBy('follows.user_id')
            ->orderBy('follows.user_id')
            ->get();

        echo "유저데이터 로드 완료\n";

        $res = [];
        $data = [];
        foreach ($users->groupBy('c') as $i => $user) {
            echo "$i\n";
            foreach ($user as $j => $item) {
                echo "\t$j";
                $data[] = $item->user_id;
                if (($j + 1) % 10000 === 0) {
                    $res[] = UserStat::whereIn('user_id', $data)->update(['yesterday_feeds_count' => $i]);
                    $data = [];
                }
            }
            $res[] = UserStat::whereIn('user_id', $data)->update(['yesterday_feeds_count' => $i]);
            $data = [];
        }

        return $res;
    }

    public static function mission_expire()
    {
        $deadline = init_today(time() - (86400 * 7));

        // 미션 참가하고 피드 올린지 일주일이 넘어간 미션
        $data = MissionStat::where('mission_stats.created_at', '<', $deadline)
            ->whereDoesntHave('feed_missions', function ($query) use ($deadline) {
                $query->where('created_at', '>=', $deadline);
            })
            ->select(['mission_stats.id', 'mission_stats.user_id', 'mission_id'])
            ->get();

        try {
            DB::beginTransaction();

            MissionStat::whereIn('id', $data->pluck('id'))->delete();

            $res = [];
            foreach ($data->groupBy('mission_id') as $i => $item) {
                $res[] = NotificationController::send($item->pluck('user_id')->toArray(), 'mission_expire', null, $i);
            }

            DB::commit();

            return $data;
        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }
    }

    public static function mission_over()
    {
        $deadline = init_today(time() - (86400 * 7));

        // 기간이 종료됐거나 삭제된 미션
        $data = MissionStat::whereNotNull('missions.deleted_at')
            ->orWhere('missions.ended_at', '<=', date('Y-m-d H:i:s'))
            ->join('missions', 'missions.id', 'mission_stats.mission_id')
            ->select(['mission_stats.id', 'mission_stats.user_id', 'mission_id'])
            ->get();

        try {
            DB::beginTransaction();

            MissionStat::whereIn('id', $data->pluck('id'))->delete();

            $res = [];
            foreach ($data->groupBy('mission_id') as $i => $item) {
                $res[] = NotificationController::send($item->pluck('user_id')->toArray(), 'mission_over', null, $i);
            }

            DB::commit();

            return $data;
        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }
    }

    public static function mission_expire_warning_am()
    {
        return self::mission_expire_warning('am');
    }

    public static function mission_expire_warning_pm()
    {
        return self::mission_expire_warning('pm');
    }

    public static function mission_expire_warning($type = 'am')
    {
        $deadline = init_today(time() - (86400 * 4));

        $data = MissionStat::leftJoin('feed_missions', 'feed_missions.mission_stat_id', 'mission_stats.id')
            ->select([
                'mission_stats.id', 'mission_stats.user_id', 'mission_stats.mission_id',
                /*DB::raw("mission_stats.created_at < '$deadline' and
                (MAX(feed_missions.created_at) < '$deadline' or MAX(feed_missions.created_at) is null) as is_warning"),*/
            ])
            ->groupBy('mission_stats.id')
            // ->orderBy('is_warning')
            ->get();

        // $data = $data->groupBy('is_warning');

        $message = CommonCode::where('ctg_md', 'mission_upload')->pluck('content_ko', 'ctg_sm');

        /*$res[0] = PushController::gcm_notify(array_unique($data[0]->pluck('user_id')->toArray()),
            '써클인', $message['mission_upload_'.$type]);
        if (count($data) > 1) {
            $res[1] = PushController::gcm_notify(array_unique($data[1]->pluck('user_id')->toArray()),
                '써클인', $message['mission_expire_warning']);
        }*/
        $data = array_unique($data->pluck('user_id')->toArray());
        $res = [];
        $tmp = [];
        foreach ($data as $i => $item) {
            $tmp[] = $item;
            if (count($tmp) === 10000) {
                $res[] = PushController::gcm_notify($tmp, '써클인', $message['mission_upload_'.$type]);
                $tmp = [];
            }
        }
        $res[] = PushController::gcm_notify($tmp, '써클인', $message['mission_upload_'.$type]);

        return $res;
    }
}
