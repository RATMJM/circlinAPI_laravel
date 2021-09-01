<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Feed;
use App\Models\FeedLike;
use App\Models\Mission;
use App\Models\MissionLike;
use App\Models\PointHistory;
use Exception;
use Illuminate\Support\Facades\DB;

class LikeController extends Controller
{
    public function index($table, $id): array
    {
        try {
            $query = match ($table) {
                'feed' => new FeedLike(),
                'mission' => new MissionLike(),
            };

            $likes = $query->where("{$table}_id", $id)
                ->join('users', 'users.id', "{$table}_likes.user_id")
                ->select(['users.id as user_id', 'users.nickname', 'users.profile_image', 'users.gender'])
                ->get();

            return success([
                'result' => true,
                'likes' => $likes,
            ]);
        } catch (Exception $e) {
            return success([
                'result' => false,
                'reason' => $e,
            ]);
        }
    }

    public function store($type, $id)
    {
        try {
            DB::beginTransaction();

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

            $point = 10;
            $paid_point = false; // 대상에게 포인트 줬는지
            $take_point = false; // 10번 체크해서 포인트 받았는지
            $count = FeedLike::withTrashed()->where('user_id', $user_id)
                ->where('point', '>', 0)
                ->where('feed_likes.created_at', '>=', date('Y-m-d'))
                ->count();
            if ($type === 'feed') {
                if ($table_like->withTrashed()->where(["{$type}_id" => $id, 'user_id' => $user_id])->doesntExist()
                    && PointHistory::where(["{$type}_id" => $id, 'reason' => 'feed_check'])->sum('point') < 1000) {
                    $res = PointController::change_point($target_id, $point, 'feed_check', 'feed', $id);
                    $paid_point = $res['success'] && $res['data']['result'];

                    // 지금이 10번째 피드체크 && 100회까지만 지급
                    if ($count % 10 === 9 && $count < 10) {
                        $res = PointController::change_point($user_id, 100, 'feed_check_cumulate');
                        $take_point = $res['success'] && $res['data']['result'];
                    }
                }
            }

            $data = $table_like->create(["{$type}_id" => $id, 'user_id' => $user_id, 'point' => $point]);

            DB::commit();

            return success([
                'result' => (bool)$data, 'paid_point' => $paid_point,
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
