<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Feed;
use App\Models\FeedLike;
use App\Models\Mission;
use App\Models\MissionLike;
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
                ->join('users', 'users.id', 'feed_likes.user_id')
                ->join('user_stats', 'user_stats.user_id', 'users.id')
                ->select(['users.id as user_id', 'users.nickname', 'users.profile_image', 'user_stats.gender'])
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

            $query = match ($table) {
                'feed' => [new Feed(), new FeedLike()],
                'mission' => [new Mission(), new MissionLike()],
            };

            if ($query[0]->where('id', $id)->value('user_id') === token()->uid) {
                return success([
                    'result' => false,
                    'reason' => 'my feed',
                ]);
            }

            $data = $query[1]->firstOrCreate([
                "{$table}_id" => $id, 'user_id' => token()->uid,
            ]);

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
            })->where(['feed_id' => $id, 'user_id' => token()->uid])->first();

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
