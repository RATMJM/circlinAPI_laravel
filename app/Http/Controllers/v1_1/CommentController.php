<?php

namespace App\Http\Controllers\v1_1;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\Feed;
use App\Models\FeedComment;
use App\Models\Mission;
use App\Models\MissionComment;
use App\Models\Notice;
use App\Models\NoticeComment;
use App\Models\PointHistory;
use App\Models\ProductReview;
use App\Models\ProductReviewComment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    public function index(Request $request, $table, $id): array
    {
        try {
            $page = $request->get('page', 0);
            $limit = $request->get('limit', 50);

            $query_comment = match ($table) {
                'feed' => new FeedComment(),
                'mission' => new MissionComment(),
                'notice' => new NoticeComment(),
                'product_review' => new ProductReviewComment(),
            };
            $uid = token()->uid;

            $query_comment = $query_comment->withTrashed()->where("{$table}_id", $id)
                ->whereRaw("({$table}_comments.deleted_at is null or
                    (depth=0 and (select COUNT(1) from {$table}_comments c where c.{$table}_id={$table}_comments.{$table}_id and c.deleted_at is null and c.depth>0 and c.group={$table}_comments.group)>0))")
                ->join('users', 'users.id', "{$table}_comments.user_id")
                ->select([
                    "{$table}_comments.group", "{$table}_comments.depth",
                    DB::raw("{$table}_comments.deleted_at is not null as is_delete"),
                    "{$table}_comments.created_at",
                    "{$table}_comments.id",
                    // DB::raw("IF({$table}_comments.deleted_at is null, {$table}_comments.comment, null) as comment"),
                    "{$table}_comments.comment",
                    'users.id as user_id',
                    'is_blocked' => Block::selectRaw('count(id)')->whereColumn('target_id', 'users.id')->where('user_id', $uid),
                    'users.nickname',
                    'users.profile_image',
                    'users.gender',
                ])
                ->orderBy('group', 'desc')->orderBy('depth')->orderBy('id');

            $total = $query_comment->count();

            $comments = $query_comment->skip($page * $limit)->take($limit)->get();

            return success([
                'result' => true,
                'total' => $total,
                'comment_total' => $total,
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

            $user_id = token()->uid;

            if (!$comment) {
                return success([
                    'result' => false,
                    'reason' => 'not enough data',
                ]);
            }

            $query = match ($table) {
                'feed' => new Feed(),
                'mission' => new Mission(),
                'notice' => new Notice(),
                'product_review' => new ProductReview(),
            };

            $query_comment = match ($table) {
                'feed' => new FeedComment(),
                'mission' => new MissionComment(),
                'notice' => new NoticeComment(),
                'product_review' => new ProductReviewComment(),
            };

            $max_group = $query_comment->withTrashed()->where("{$table}_id", $id)->max('group') ?? -1;
            $group = $group ?? ($max_group + 1);

            $data = $query_comment->create([
                "{$table}_id" => $id, 'user_id' => $user_id,
                'group' => min($group, $max_group + 1),
                'depth' => ($group >= $max_group + 1) ? 0 : 1,
                'comment' => $comment,
            ]);

            $comment_target_id = $query_comment->where(["{$table}_id" => $id, 'group' => $group, 'depth' => 0])->value('user_id');
            $table_target_id = $table !== 'notice' ? $query->where('id', $id)->value('user_id') : null;

            $comment_id = $data['id'];
            // feed comment 이벤트
            if ($table == 'feed' && $user_id == 61361) {
                $feed_writer_id = $query->where('id', $id)->value('user_id');
                $my_total_comment_reward = PointHistory::where([
                    "feed_id" => $id,
                    "user_id" => $user_id,
                ])
                ->where("reason", 'feed_comment_reward')
                ->orWhere("reason", 'feed_comment_delete')
                ->sum('point')['point'] ?? 0;
                // return success(['my_total_comment_reward'=> $my_total_comment_reward]);
                $my_total_comment_reward = (int)$my_total_comment_reward;

                // 내 피드 여부 확인
                if ($feed_writer_id == $user_id) {
                    // 내 피드에 댓글을 다는 경우 (1)답글인지 확인, (2)타인이 작성한 댓글에 답글을 남기는 중인지
                    if ($comment_target_id !== $user_id && $data->depth > 0) {
                        // 댓글 or 답글 남긴 이력 확인: 해당 피드에서의 댓글 이벤트 포인트 총합 > 0
                        if ($my_total_comment_reward > 0 ) {
                            false;
                        } else {
                            $res = PointController::change_point($user_id, 1, 'feed_comment_reward', 'feed_comment');
                            if ($res['data']['result'] === true) {
                                PointHistory::where('id', $res['data']['id'])->update(['feed_id' => $id, 'feed_comment_id'=>$comment_id]);
                            } else {
                                return success(['fail'=>$res['data']['result']]);
                            }
                        }
                    } else {
                        false;
                    }
                } else {
                    // 댓글 또는 대댓글 남긴 이력 확인: 해당 피드에서의 댓글 이벤트 포인트 총합 > 0
                    if ($my_total_comment_reward > 0 ) {
                        false;
                    } else {
                        $res = PointController::change_point($user_id, 1, 'feed_comment_reward', 'feed_comment');

                        if ($res['data']['result'] === true) {
                            PointHistory::where('id', $res['data']['id'])->update(['feed_id' => $id, 'feed_comment_id'=>$comment_id]);
                        } else {
                            return success(['fail'=>$res['data']['result']]);
                        }
                    }
                }
            }


            // 답글인 경우 푸시
            if ($data->depth > 0 && $comment_target_id !== $user_id && $comment_target_id !== $table_target_id) {
                NotificationController::send($comment_target_id, "{$table}_reply", $user_id, $data->id, true,
                    ["{$table}_comment" => $comment]);
            }

            // 글 주인한테 푸시
            if (isset($table_target_id) && $table_target_id !== $user_id) {
                NotificationController::send($table_target_id, "{$table}_comment", $user_id, $data->id, true,
                    ["{$table}_comment" => $comment]);
            }

            DB::commit();

            return success(['result' => true, 'id'=>$id, 'my_total_comment_reward'=>$my_total_comment_reward]);
        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }
    }

    public function destroy($table, $id, $comment_id)
    {
        $user_id = token()->uid;

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
                    'uid' => token()->uid,
                    'user_id' => $data->user_id
                ]);
            }

            // feed comment 이벤트
            if ($table == 'feed' && $user_id == 61361) {
                $rewarded_history = PointHistory::where(['feed_id'=>$id, 'feed_comment_id'=>$comment_id, 'user_id'=>$user_id])->select('id')->get();
                // return success(['rewarded_history'=> $rewarded_history]);
                if (count($rewarded_history) > 0) {
                    $res = PointController::change_point($user_id, -1, 'feed_comment_delete', 'feed_comment');
                    if ($res['data']['result'] === true) {
                        PointHistory::where('id', $res['data']['id'])->update(['feed_id' => $id, 'feed_comment_id'=>$comment_id]);
                    }
                }
            }

            $data->delete();

            return success(['result' => true]);
        } catch (Exception $e) {
            return exceped($e);
        }
    }
}
