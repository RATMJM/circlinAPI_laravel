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

            $query = match ($table) {
                'feed' => new FeedLike(),
                'mission' => new MissionLike(),
            };

            $users = $query->where("{$table}_id", $id)
                ->join('users', 'users.id', "{$table}_likes.user_id")
                ->select([
                    'users.id', 'users.nickname', 'users.profile_image', 'users.gender', 'area' => area_like(),
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
        } catch (Exception $e) {
            return exceped($e);
        }
    }

    public function store($type, $id)
    {
        try {
            $user_id = token()->uid;

            [$table, $table_like] = match ($type) {
                'feed' => [new Feed(), new FeedLike()],
                'mission' => [new Mission(), new MissionLike()],
                default => [null, null],
            };

            $data = $table->where('id', $id)->first();

            if (is_null($data)) {
                return success([
                    'result' => false,
                    'reason' => "not found $type",
                ]);
            }

            if (($target_id = $data->user_id) === $user_id) {
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

            DB::beginTransaction();

            if ($type === 'feed') {
                $count = FeedLike::withTrashed()->where('user_id', $user_id)
                    ->where('point', '>', 0)
                    ->where('feed_likes.created_at', '>=', init_today())
                    ->count();

                if ($table_like->withTrashed()->where(["{$type}_id" => $id, 'user_id' => $user_id])->doesntExist() &&
                    $table_like->withTrashed()->where('user_id', $user_id)
                        ->where('point', '>', 0)
                        ->where('feed_likes.created_at', '>=', init_today())
                        ->where($table->select('user_id')->whereColumn("{$type}s.id", "{$type}_likes.{$type}_id"), $data->user_id)
                        ->doesntExist() &&
                    PointHistory::where(["{$type}_id" => $id, 'reason' => 'feed_check'])->sum('point') < 1000) {
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
            }

            $data_like = $table_like->create(["{$type}_id" => $id, 'user_id' => $user_id, 'point' => $point]);

            $res = match ($type) {
                'feed' => $paid_point ?
                    NotificationController::send($data->user_id, 'feed_check', $user_id, $id, true, ['point' => 10])
                    : null,
                // 'mission' => NotificationController::send($data->user_id, 'mission_like', $user_id, $id, true),
                default => null,
            };

            DB::commit();

            return success([
                'result' => (bool)$data_like, 'paid_point' => $paid_point,
                'paid_count' => $count ?? 0, 'take_point' => $take_point,
            ]);
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
