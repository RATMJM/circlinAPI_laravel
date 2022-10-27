<?php

namespace App\Http\Controllers\v1_1;

use App\Http\Controllers\Controller;
use App\Models\Feed;
use App\Models\FeedLike;
use App\Models\Follow;
use App\Models\Mission;
use App\Models\MissionLike;
use App\Models\PointHistory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LikeController extends Controller
{
    public function index(Request $request, $table, $id): array
    {
        try {
            $user_id = token()->uid;
            $page = $request->get('page', 0);
            $limit = $request->get('limit', 20);
            $keyword = $request->get('keyword', '');

            $query = match ($table) {
                'feed' => new FeedLike(),
                'mission' => new MissionLike(),
            };

            if ($keyword) {
                $users = $query->where("{$table}_id", $id)
                    ->join('users', 'users.id', "{$table}_likes.user_id")
                    ->select([
                        'users.id',
                        'users.nickname',
                        'users.profile_image',
                        'users.gender',
                        'area' => area_like(),
                        'follower' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id'),
                        'is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('target_id', 'users.id')
                            ->where('user_id', $user_id),
                    ])
                    ->where('nickname', 'like', '%' . $keyword . '%')
                    ->orderBy("{$table}_id", 'desc')
                    ->skip($page * $limit)->take($limit)->get();

                return success([
                    'result' => true,
                    'users' => $users,
                ]);
            } {
                $users = $query->where("{$table}_id", $id)
                    ->join('users', 'users.id', "{$table}_likes.user_id")
                    ->select([
                        'users.id',
                        'users.nickname',
                        'users.profile_image',
                        'users.gender',
                        'area' => area_like(),
                        'follower' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id'),
                        'is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('target_id', 'users.id')
                            ->where('user_id', $user_id),
                    ])
                    ->orderBy("{$table}_id", 'desc')
                    ->skip($page * $limit)->take($limit)->get();

                return success([
                    'result' => true,
                    'users' => $users,
                ]);
            }
        } catch (Exception $e) {
            return exceped($e);
        }
    }

    public function store($type, $id)
    {
        try {
            // $user_id => 본인(피드를 체크한 사람)
            // $target_id (= $data->user_id) => 상대방(피드를 작성한 사람)
            $user_id = token()->uid;

            [$table, $table_like] = match ($type) {
                'feed' => [new Feed(), new FeedLike()],
                'mission' => [new Mission(), new MissionLike()],
                default => [null, null],
            };

            $data = $table->where('id', $id)->first();
            $target_id = $data->user_id;

            if (is_null($data)) {
                return success([
                    'result' => false,
                    'reason' => "not found $type",
                ]);
            }

            if ($target_id === $user_id) {
                return success([
                    'result' => false,
                    'reason' => "my $type",
                ]);
            }

            if ($table_like->where(["{$type}_id" => $id, 'user_id' => $user_id])->exists()) {
                return success([
                    'result' => false,
                    'reason' => "already like",
                ]);
            }

            $point = 0;
            $paid_point = false; // 대상에게 포인트 줬는지
            $take_point = false; // 10번 체크해서 포인트 받았는지

            $real_gathered_point = $type === 'feed' ? 10 : 0; // 일일 수취 가능한도(500p)를 고려할 때 내가 실제로 얻은 포인트(feed_check_reward)
            $real_gave_point = $type === 'feed' ? 10 : 0; // 일일 수취 가능한도(500p)를 고려할 때 상대가 실제로 얻은 포인트(feed_check)

            // 일 수취 가능 한도 1000P(100회 체크카운트) 제한을 해제한 코드
            if ($user_id == 61361 && $type === 'feed') {
                $count = FeedLike::withTrashed()
                    ->where('user_id', $user_id)
                    ->where('point', '>', 0)
                    ->where('feed_likes.created_at', '>=', init_today())
                    ->count(); // 좋아요 한 횟수(취소한 것들은 제외)
                $daily_point_limit = (new PointController)->today_gatherable_point($user_id)['daily_limit'];
                $my_current_gathered_point = (new PointController)->today_gatherable_point($user_id)['today_gathered_point'];
                $my_gatherable_point = $daily_point_limit - $my_current_gathered_point;
                $targets_current_gathered_point = (new PointController)->today_gatherable_point($target_id)['today_gathered_point'];

                if (
                    $table_like->withTrashed()->where(["{$type}_id" => $id, 'user_id' => $user_id])->doesntExist() // 해당 피드를 좋아요 하지 않은 상태이고
                    &&
                    $table_like->withTrashed()->where('user_id', $user_id)
                        ->where('point', '>', 0)
                        ->where('feed_likes.created_at', '>=', init_today())
                        ->where($table->select('user_id')->whereColumn("{$type}s.id", "{$type}_likes.{$type}_id"), $data->user_id)
                        ->doesntExist() // 오늘 좋아요 누른 것들의
                    &&
                    $targets_current_gathered_point > 0
                    // PointHistory::where(["{$type}_id" => $id, 'reason' => 'feed_check'])->sum('point') < 1000
                    &&
                    Feed::where('id', $id)->first()->created_at >= init_today(time() - (86400))
                ) {
                    $res = PointController::change_point($target_id, $real_gave_point, 'feed_check', 'feed', $id);
                    $paid_point = $res['success'] && $res['data']['result'];
                    $real_gave_point = $paid_point ? $res['data']['point'] : 0;  //$res['point']

                    // 지금이 10번째 피드체크 && 오늘 하루 획득한 액수가 획득 가능한 포인트 상한선보다 낮을 경우만 지급
                    if ($count % 10 === 9 && $my_gatherable_point > 0) {
                        $res = PointController::change_point($user_id, $real_gathered_point, 'feed_check_reward');
                        // 위에서 10p 지급을 요청했지만, 실제로는 획득 가능한 포인트 액수를 따르므로 그보다 적을수 있다.
                        $real_gathered_point = $res['data']['result'] ? $res['data']['point'] : 0;

                        $daily_point_limit = (new PointController)->today_gatherable_point($user_id)['daily_limit'];
                        $current_gathered_point = (new PointController)->today_gatherable_point($user_id)['today_gathered_point'];
                        $gatherable_point = $daily_point_limit - $current_gathered_point;

                        NotificationController::send($user_id, 'feed_check_reward', null, null, false,
                            ['point' => $real_gathered_point, 'point2' => $gatherable_point]); // ['point' => 10, 'point2' => 100 - ($count + 1)]);
                        $take_point = $res['success'] && $res['data']['result'];
                    }
                    $real_gathered_point = 0;
                    $real_gave_point = 0;
                    $count += 1;
                }

                $data_like = $table_like->create(["{$type}_id" => $id, 'user_id' => $user_id, 'point' => $real_gave_point]);

                $res = match ($type) {
                    'feed' => $paid_point ?
                        NotificationController::send($data->user_id, 'feed_check', $user_id, $id, true, ['point' => $real_gave_point])
                        : null,
                    // 'mission' => NotificationController::send($data->user_id, 'mission_like', $user_id, $id, true),
                    default => null,
                };
                $my_today_gathered_point = (new PointController)->today_gatherable_point($user_id)['today_gathered_point'];

                return success([
                    'paid_count' => $count ?? 0,
                    'paid_point' => $paid_point,
                    'result' => (bool)$data_like,
                    'take_point' => $take_point,
                    'real_gathered_point' => $real_gathered_point,
                    'today_gathered_point' => $my_today_gathered_point
                ]);

            } else if ($user_id !== 61361 && $type === 'feed') {
                $count = FeedLike::withTrashed()
                    ->where('user_id', $user_id)
                    ->where('point', '>', 0)
                    ->where('feed_likes.created_at', '>=', init_today())
                    ->count(); // 좋아요 한 횟수(취소한 것들은 제외)

                if (
                    $table_like->withTrashed()->where(["{$type}_id" => $id, 'user_id' => $user_id])->doesntExist() // 해당 피드를 좋아요 하지 않은 상태이고
                    &&
                    $table_like->withTrashed()->where('user_id', $user_id)
                        ->where('point', '>', 0)
                        ->where('feed_likes.created_at', '>=', init_today())
                        ->where($table->select('user_id')->whereColumn("{$type}s.id", "{$type}_likes.{$type}_id"), $data->user_id)
                        ->doesntExist() // 오늘 좋아요 누른 것들의
                    &&
                    PointHistory::where(["{$type}_id" => $id, 'reason' => 'feed_check'])->sum('point') < 1000
                    &&
                    Feed::where('id', $id)->first()->created_at >= init_today(time() - (86400))
                ) {
                    $res = PointController::change_point($target_id, $point += 10, 'feed_check', 'feed', $id);
                    $paid_point = $res['success'] && $res['data']['result'];

                    // 지금이 10번째 피드체크 && 100회까지만 지급
                    if ($count % 10 === 9 && $count < 100) {
                        $res = PointController::change_point($user_id, 10, 'feed_check_reward');

                        NotificationController::send($user_id, 'feed_check_reward', null, null, false,
                            ['point' => 10, 'point2' => 100 - ($count+1)]);
                        $take_point = $res['success'] && $res['data']['result'];
                    }

                    $count += 1;
                }

                $data_like = $table_like->create(["{$type}_id" => $id, 'user_id' => $user_id, 'point' => $point]);

                $res = match ($type) {
                    'feed' => $paid_point ?
                        NotificationController::send($data->user_id, 'feed_check', $user_id, $id, true, ['point' => $real_gave_point])
                        : null,
                    // 'mission' => NotificationController::send($data->user_id, 'mission_like', $user_id, $id, true),
                    default => null,
                };
                $my_today_gathered_point = (new PointController)->today_gatherable_point($user_id)['today_gathered_point'];

                return success([
                    'paid_count' => $count ?? 0,
                    'paid_point' => $paid_point,
                    'result' => (bool)$data_like,
                    'take_point' => $take_point,
                    'real_gathered_point' => $real_gathered_point,
                    'today_gathered_point' => $my_today_gathered_point
                ]);
            } else {}

            // $data_like = $table_like->create(["{$type}_id" => $id, 'user_id' => $user_id, 'point' => $point]);
            //
            // $res = match ($type) {
            //     'feed' => $paid_point ?
            //         NotificationController::send($data->user_id, 'feed_check', $user_id, $id, true, ['point' => $real_gave_point])
            //         : null,
            //     // 'mission' => NotificationController::send($data->user_id, 'mission_like', $user_id, $id, true),
            //     default => null,
            // };
            // $my_today_gathered_point = (new PointController)->today_gatherable_point($user_id)['today_gathered_point'];
            //
            // return success([
            //     'paid_count' => $count ?? 0,
            //     'paid_point' => $paid_point,
            //     'result' => (bool)$data_like,
            //     'take_point' => $take_point,
            //     'today_gathered_point' => $my_today_gathered_point
            // ]);
        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }
    }

    public function destroy($table, $id)
    {
        try {
            $data = (match ($table) {
                'feed' => new FeedLike(),
                'mission' => new MissionLike(),
            })->where(["{$table}_id" => $id, 'user_id' => token()->uid])->first();

            if (is_null($data)) {
                return success([
                    'result' => false,
                    'reason' => 'not like',
                ]);
            }

            $data->delete();

            return success(['result' => true]);
        } catch (Exception $e) {
            return exceped($e);
        }
    }
}
