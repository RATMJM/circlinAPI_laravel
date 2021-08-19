<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\CommonCode;
use App\Models\FeedComment;
use App\Models\FeedImage;
use App\Models\MissionComment;
use App\Models\Notification;
use App\Models\User;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index($user_id = null): array
    {
        $user_id = $user_id ?? token()->uid;

        $nogroup = ["follow_feed", "follow_bookmark", "mission_invite", "bookmark_warning"];

        $data = Notification::where('target_id', $user_id)
            ->select([
                // 'type as group_type',
                DB::raw("MAX(id) as id"),
                DB::raw("MAX(created_at) as created_at"),
                // DB::raw("COUNT(id) as count"),
                DB::raw("COUNT(distinct user_id) as count"),
                'user_id' => DB::table('notifications as n')->select('user_id')
                    ->where('id', DB::raw("MAX(notifications.id)")),
                DB::raw("MAX(feed_id) as feed_id"),
                DB::raw("MAX(feed_comment_id) as feed_comment_id"),
                DB::raw("MAX(mission_id) as mission_id"),
                DB::raw("MAX(mission_comment_id) as mission_comment_id"),
            ])
            ->groupBy(DB::raw("IF(type in ('" . implode("','", $nogroup) . "'), id, type)"),
                DB::raw("CONCAT(YEAR(notifications.created_at),'|',WEEK(notifications.created_at))"),
                'notifications.feed_id', 'notifications.mission_id')
            ->orderBy(DB::raw('MAX(id)'), 'desc')
            ->take(50);

        $data = User::rightJoinSub($data, 'n', function ($query) {
            $query->on('users.id', 'n.user_id');
        })
            ->leftJoin('feeds', 'feeds.id', 'n.feed_id')
            ->leftJoin('feed_comments', 'feed_comments.id', 'n.feed_comment_id')
            ->leftJoin('missions', 'missions.id', 'n.mission_id')
            ->leftJoin('mission_comments', 'mission_comments.id', 'n.mission_comment_id')
            ->select([
                'n.*', 'type' => Notification::select('type')->whereColumn('id', 'n.id'),
                'users.nickname', 'users.profile_image', 'users.gender',
                'feed_image_type' => FeedImage::select('type')->whereColumn('feed_images.feed_id', 'feeds.id')
                    ->orderBy('order')->limit(1),
                'feed_image' => FeedImage::select('image')->whereColumn('feed_images.feed_id', 'feeds.id')
                    ->orderBy('order')->limit(1),
                'missions.title as mission_title',
                'missions.thumbnail_image as mission_image',
            ])
            ->get();

        if (is_null($data)) {
            return success([
                'result' => true,
                'data' => $data,
            ]);
        }

        $messages = CommonCode::where('ctg_lg', 'notifications')->pluck('content_ko', 'ctg_sm');

        $res = $data->toArray();
        foreach ($data as $i => $item) {
            // common_codes 에 매칭되도록 type 치환
            if ($item->count > 1 && !in_array($item->type, $nogroup)) {
                $res[$i]['type'] = match ($item->type) {
                    'follow', 'feed_check', 'feed_comment', 'mission_like', 'mission_comment' => $item->type . 's',
                    'feed_reply' => 'feed_replies',
                    'mission_reply' => 'mission_replies',
                    default => null,
                };
            }

            $replaces = [
                '{%count}' => '{' . $item->count - 1 . '}',
                '{%nickname}' => '{' . $item->nickname . '}',
                '{%mission}' => '{' . $item->mission_title . '}',
            ];
            $res[$i]['message'] = str_replace(array_keys($replaces), array_values($replaces), $messages[$res[$i]['type']]);
        }

        Notification::whereIn('id', $data->pluck('id')->toArray())->whereNull('read_at')->update(['read_at' => now()]);

        return success([
            'result' => false,
            'notifies' => $res,
        ]);
    }

    /**
     * 알림 전송
     *
     * @param string|array $target_ids 알림 받을 대상
     * @param string $type 알림 종류
     * @param int|null $id integer 연결될 테이블 id
     * @return array
     */
    public static function send(string|array $target_ids, string $type, int $id = null): array
    {
        try {
            DB::beginTransaction();

            $user_id = token()->uid;

            $data = match ($type) {
                'follow' => ['user_id' => $user_id],
                'feed_check' => ['user_id' => $user_id, 'feed_id' => $id],
                'feed_comment', 'feed_reply' => [
                    'user_id' => $user_id,
                    'feed_id' => FeedComment::where('id', $id)->value('feed_id'),
                    'feed_comment_id' => $id,
                ],
                'mission_like', 'follow_bookmark' => ['user_id' => $user_id, 'mission_id' => $id],
                'mission_comment', 'mission_reply' => [
                    'user_id' => $user_id,
                    'mission_id' => MissionComment::where('id', $id)->value('mission_id'),
                    'mission_comment_id' => $id,
                ],
                'bookmark_warning' => ['mission_id' => $id],
                default => null,
            };

            if (is_null($data)) {
                return success([
                    'success' => false,
                    'reason' => 'not enough data',
                ]);
            }

            foreach (Arr::wrap($target_ids) as $target_id) {
                $res = Notification::create(Arr::collapse([$data, ['type' => $type, 'target_id' => $target_id]]));
            }

            $push = match ($type) {
                'follow', 'feed_check', 'feed_comment', 'feed_reply',
                'mission_like', 'follow_bookmark',
                'mission_comment', 'mission_reply', 'bookmark_warning' => true,
                default => false,
            };

            $user = User::where('id', $user_id)->first();

            if ($push && $user->agree_push) {
                $messages = CommonCode::where('ctg_lg', 'notifications')->pluck('content_ko', 'ctg_sm');

                $item = Notification::where('notifications.id', $res->id)
                    ->leftJoin('users', 'users.id', 'notifications.user_id')
                    ->leftJoin('feeds', 'feeds.id', 'notifications.feed_id')
                    ->leftJoin('feed_comments', 'feed_comments.id', 'notifications.feed_comment_id')
                    ->leftJoin('missions', 'missions.id', 'notifications.mission_id')
                    ->leftJoin('mission_comments', 'mission_comments.id', 'notifications.mission_comment_id')
                    ->select([
                        'users.nickname', 'users.profile_image', 'users.gender',
                        'feed_image_type' => FeedImage::select('type')->whereColumn('feed_images.feed_id', 'feeds.id')
                            ->orderBy('order')->limit(1),
                        'feed_image' => FeedImage::select('image')->whereColumn('feed_images.feed_id', 'feeds.id')
                            ->orderBy('order')->limit(1),
                        'missions.title as mission_title',
                        'missions.thumbnail_image as mission_image',
                    ])
                    ->first();

                $replaces = [
                    '{%nickname}' => $item->nickname,
                    '{%mission}' => $item->mission_title,
                ];
                $message = str_replace(array_keys($replaces), array_values($replaces), $messages[$type]);

                $res = PushController::send_gcm_notify($target_ids, '써클인', $message, '연결될 주소', $type);

                DB::commit();

                return success([
                    'result' => isset($res),
                ]);
            } else {
                DB::commit();

                return success([
                    'result' => true,
                ]);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }
    }
}
