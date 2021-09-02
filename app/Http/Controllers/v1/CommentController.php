<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Feed;
use App\Models\FeedComment;
use App\Models\MissionComment;
use App\Models\NoticeComment;
use App\Models\ProductReviewComment;
use Exception;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    public function index($table, $id): array
    {
        try {
            $query = match ($table) {
                'feed' => new FeedComment(),
                'mission' => new MissionComment(),
                'notice' => new NoticeComment(),
                'product_review' => new ProductReviewComment(),
            };

            $query = $query->where("{$table}_id", $id)
                ->join('users', 'users.id', "{$table}_comments.user_id")
                ->select([
                    "{$table}_comments.group", "{$table}_comments.depth",
                    DB::raw("{$table}_comments.deleted_at is not null as is_delete"),
                    DB::raw("IF({$table}_comments.deleted_at is null, {$table}_comments.created_at, null) as created_at"),
                    DB::raw("IF({$table}_comments.deleted_at is null, {$table}_comments.id, null) as id"),
                    DB::raw("IF({$table}_comments.deleted_at is null, {$table}_comments.comment, null) as comment"),
                    DB::raw("IF({$table}_comments.deleted_at is null, users.id, null) as user_id"),
                    DB::raw("IF({$table}_comments.deleted_at is null, users.nickname, null) as nickname"),
                    DB::raw("IF({$table}_comments.deleted_at is null, users.profile_image, null) as profile_image"),
                    DB::raw("IF({$table}_comments.deleted_at is null, users.gender, null) as gender"),
                ])
                ->orderBy('group', 'desc')->orderBy('depth')->orderBy('id');

            $total = $query->count();

            $comments = $query->withTrashed()->get();

            return success([
                'result' => true,
                'total' => $total,
                'comments' => $comments,
            ]);
        } catch (Exception $e) {
            return exceped($e);
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
                'feed' => new FeedComment(),
                'mission' => new MissionComment(),
                'notice' => new NoticeComment(),
                'product_review' => new ProductReviewComment(),
            };

            $max_group = $query->where("{$table}_id", $id)->max('group') ?? -1;
            $group = $group ?? ($max_group + 1);

            $data = $query->create([
                "{$table}_id" => $id, 'user_id' => token()->uid,
                'group' => min($group, $max_group + 1),
                'depth' => ($group >= $max_group + 1) ? 0 : 1,
                'comment' => $comment,
            ]);

            // 답글인 경우 푸시
            $comment_target_id = null;
            if ($group <= $max_group) {
                $comment_target_id = $query->where(['feed_id' => $id, 'group' => $group, 'depth' => 0])->value('user_id');
                NotificationController::send($comment_target_id, true, "{$table}_reply", $data->id);
            }

            // 글 주인한테 푸시
            /*if (($feed_target_id = Feed::where('id', $id)->value('user_id')) !== $comment_target_id) {
                NotificationController::send($feed_target_id, true, "{$table}_comment", $data->id);
            }*/

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
                'notice' => NoticeComment::where(['id' => $comment_id, "{$table}_id" => $id])->first(),
                'product_review' => ProductReviewComment::where(['id' => $comment_id, "{$table}_id" => $id])->first(),
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
