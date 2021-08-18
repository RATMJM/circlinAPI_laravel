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

            if (($target_id = $table->where('id', $id)->value('user_id')) === $user_id) {
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

            if ($type === 'feed') {
                if ($table_like->withTrashed()->where(["{$type}_id" => $id, 'user_id' => $user_id])->doesntExist()
                    && PointHistory::where(["{$type}_id" => $id, 'reason' => 'feed_check'])->sum('point') < 1000) {
                    PointController::change_point($target_id, 10, 'feed_check', 'feed', $id);
                }
            }

            $data = $table_like->create(["{$type}_id" => $id, 'user_id' => $user_id]);

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
