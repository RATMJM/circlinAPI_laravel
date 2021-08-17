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

    public function store($table, $id)
    {
        try {
            DB::beginTransaction();

            $user_id = token()->uid;

            [$feed, $feed_like] = match ($table) {
                'feed' => [new Feed(), new FeedLike()],
                'mission' => [new Mission(), new MissionLike()],
                default => [null, null],
            };

            if ($feed->where('id', $id)->value('user_id') === $user_id) {
                return success([
                    'result' => false,
                    'reason' => "my $table",
                ]);
            }

            if ($feed_like->where(["{$table}_id" => $id, 'user_id' => $user_id])->exists()) {
                return success([
                    'result' => false,
                    'reason' => "already like",
                ]);
            }

            if ($table === 'feed') {
                if ($feed_like->withTrashed()->where(["{$table}_id" => $id, 'user_id' => $user_id])->doesntExist()
                    && PointHistory::where(["{$table}_id" => $id, 'reason' => 'feed_check'])->sum('point') < 1000) {
                    PointController::change_point($user_id, 10, 'feed_check', 'feed', $id);
                }
            }

            $data = $feed_like->create(["{$table}_id" => $id, 'user_id' => $user_id]);

            DB::commit();

            return success(['result' => true]);
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
