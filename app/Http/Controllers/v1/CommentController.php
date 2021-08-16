<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\FeedComment;
use App\Models\MissionComment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    public function index($table, $id): array
    {
        try {
            $query = match ($table) {
                'feed' => new FeedComment,
                'mission' => new MissionComment,
            };

            $comments = $query->where("{$table}_id", $id)
                ->join('users', 'users.id', "{$table}_comments.user_id")
                ->join('user_stats', 'user_stats.user_id', 'users.id')
                ->select([
                    "{$table}_comments.group", "{$table}_comments.id", "{$table}_comments.comment",
                    "{$table}_comments.created_at",
                    'users.id as user_id', 'users.nickname', 'users.profile_image', 'user_stats.gender',
                ])
                ->orderBy('group')->orderBy('depth')->orderBy('id')
                ->get();

            return success([
                'result' => true,
                'comments' => $comments,
            ]);
        } catch (Exception $e) {
            return success([
                'result' => false,
                'reason' => $e,
            ]);
        }
    }

    public function store($table, $id, $group, $comment)
    {
        try {
            DB::beginTransaction();

            if (!$comment) {
                return success([
                    'result' => false,
                    'reason' => 'not enough data',
                ]);
            }

            $query = match ($table) {
                'feed' => new FeedComment,
                'mission' => new MissionComment,
            };

            $max_group = $query->where("{$table}_id", $id)->max('group') ?? -1;
            $group = $group ?? ($max_group + 1);

            $data = $query->create([
                "{$table}_id" => $id, 'user_id' => token()->uid,
                'group' => min($group, $max_group + 1),
                'depth' => ($group >= $max_group + 1) ? 0 : 1,
                'comment' => $comment,
            ]);

            DB::commit();

            return success(['result' => true]);
        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }
    }

    public function destroy($table, $id, $comment_id)
    {
        try {
            $data = match ($table) {
                'feed' => FeedComment::where(['id' => $comment_id, "{$table}_id" => $id])->first(),
                'mission' => MissionComment::where(['id' => $comment_id, "{$table}_id" => $id])->first(),
            };

            if (is_null($data)) {
                return success([
                    'result' => false,
                    'reason' => 'not exists comment',
                ]);
            }

            if (token()->uid !== $data->user_id) {
                return success([
                    'result' => false,
                    'reason' => 'not access comment',
                ]);
            }

            $data->delete();

            return success(['result' => true]);
        } catch (Exception $e) {
            return exceped($e);
        }
    }
}
