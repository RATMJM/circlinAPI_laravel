<?php

namespace App\Http\Controllers\v1_1;

use App\Http\Controllers\Controller;
use App\Models\CommonCode;
use App\Models\FeedComment;
use App\Models\FeedImage;
use App\Models\Follow;
use App\Models\MissionCategory;
use App\Models\MissionComment;
use App\Models\Notification;
use App\Models\User;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index(): array
    {
        $user_id = token()->uid;

        $data = $this->get($user_id);

        Notification::whereIn('id', $data->pluck('id')->toArray())->whereNull('read_at')->update(['read_at' => now()]);

        return success([
            'result' => false,
            'notifies' => $data,
        ]);
    }

    public function get($user_id = null)
    {
        $user_id = $user_id ?? token()->uid;

        $group = [
            // 'follow',
            'feed_check', 'feed_comment', 'feed_reply',
            'mission_like', 'mission_comment', 'mission_reply',
        ];

        $q = "'" . implode("','", $group) . "'";

        $data = Notification::where('target_id', $user_id)
            ->where('type', 'not like', '%서로 메이트%')
            ->select([
                DB::raw("MAX(id) as id"),
                DB::raw("MAX(created_at) as created_at"),
                DB::raw("COUNT(distinct IFNULL(user_id,0)) as count"),
                'user_id' => DB::table('notifications as n')->select('user_id')
                    ->where('id', DB::raw("MAX(notifications.id)")),
                DB::raw("MAX(feed_id) as feed_id"),
                DB::raw("MAX(feed_comment_id) as feed_comment_id"),
                DB::raw("MAX(mission_id) as mission_id"),
                DB::raw("MAX(mission_comment_id) as mission_comment_id"),
            ])
            ->groupBy(DB::raw("IF(type in ($q), type, id)"),
                DB::raw("CONCAT(YEAR(notifications.created_at),'|',MONTH(notifications.created_at),'|',DAY(notifications.created_at))"),
                'notifications.feed_id', 'notifications.mission_id')
            ->orderBy(DB::raw('MAX(id)'), 'desc')
            ->take(50);

        $data = Notification::joinSub($data, 'n', function ($query) {
            $query->on('n.id', 'notifications.id');
        })
            ->leftJoin('users', 'users.id', 'n.user_id')
            ->leftJoin('feeds', 'feeds.id', 'n.feed_id')
            // ->leftJoin('feed_comments', 'feed_comments.id', 'n.feed_comment_id')
            ->leftJoin('missions', 'missions.id', 'n.mission_id')
            // ->leftJoin('mission_comments', 'mission_comments.id', 'n.mission_comment_id')
            ->leftJoin('common_codes', function ($query) use ($q) {
                $query->on('common_codes.ctg_sm', DB::raw("IF(type in ($q) and count > 1, CONCAT(type,'_multi'), type)"))
                    ->where('common_codes.ctg_lg', 'notifications');
            })
            ->select([
                'n.*', DB::raw("IF(type in ($q) and count > 1, CONCAT(type,'_multi'), type) as type"),
                DB::raw("IFNULL(NULLIF(content_ko,''), type) as message"),
                DB::raw("!ISNULL(read_at) as is_read"),
                'users.nickname', 'users.profile_image', 'users.gender',
                'is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('target_id', 'users.id')
                    ->where('user_id', $user_id),
                'feed_image_type' => FeedImage::select('type')->whereColumn('feed_images.feed_id', 'feeds.id')
                    ->orderBy('order')->limit(1),
                'feed_image' => FeedImage::select('image')->whereColumn('feed_images.feed_id', 'feeds.id')
                    ->orderBy('order')->limit(1),
                'mission_emoji' => MissionCategory::select('emoji')->whereColumn('id', 'missions.mission_category_id')->limit(1),
                'missions.title as mission_title',
                'missions.thumbnail_image as mission_image',
                'notifications.variables',
            ])
            ->orderBy('id', 'desc')
            ->get();

        if (is_null($data)) {
            return success([
                'result' => true,
                'notifies' => $data,
            ]);
        }

        $action = CommonCode::where('ctg_lg', 'click_action')->pluck('content_ko', 'ctg_sm');

        foreach ($data as $i => $item) {
            $replaces = [
                'count' => $item->count - 1,
                'nickname' => $item->nickname,
                'mission' => $item->mission_emoji . ' ' . $item->mission_title,
            ];
            $replaces = Arr::collapse([$replaces, $item->variables]);
            if (array_key_exists('point', $replaces)) {
                $replaces['point'] = $replaces['point'] * $item->count;
            }
            $item->message = code_replace($item->message, $replaces);
            $item->link = match ($item->type) {
                'follow', 'follow_multi' => code_replace($action['user'], ['id' => $item->user_id]),

                'feed_check', 'feed_check_multi',
                'feed_comment', 'feed_comment_multi', 'feed_reply', 'feed_reply_multi',
                'feed_upload_place', 'feed_upload_product'
                => code_replace($action['feed'], ['id' => $item->feed_id, 'comment_id' => $item->feed_comment_id]),

                'mission_like', 'mission_like_multi',
                'mission_comment', 'mission_comment_multi', 'mission_reply', 'mission_reply_multi',
                'mission_complete', 'mission_invite', 'earn_badge'
                => code_replace($action['mission'], ['id' => $item->mission_id, 'comment_id' => $item->mission_comment_id]),

                /*'mission_over', 'mission_expire'
                => code_replace($action['event_mission'], ['id' => $item->mission_id, 'user_id' => $user_id]),*/

                'feed_check_reward' => code_replace($action['point'], []),

                'feed_emoji' => code_replace($action['chat'], ['id' => $item->user_id]),
                default => null,
            };
            $item->link_left = match ($item->type) {
                'follow', 'follow_multi', 'feed_emoji',
                'feed_check', 'feed_check_multi',
                'feed_comment', 'feed_comment_multi', 'feed_reply', 'feed_reply_multi',
                'mission_like', 'mission_like_multi',
                'mission_comment', 'mission_comment_multi', 'mission_reply', 'mission_reply_multi',
                'mission_invite'
                => code_replace($action['user'], ['id' => $item->user_id]),

                'feed_upload_place', 'feed_upload_product'
                => code_replace($action['user'], ['id' => $user_id]),

                'feed_check_reward' => code_replace($action['point'], []),

                'mission_complete', 'earn_badge', 'mission_expire_warning'
                => code_replace($action['mission'], ['id' => $item->mission_id]),

                /*'mission_over', 'mission_expire'
                => code_replace($action['event_mission'], ['id' => $item->mission_id, 'user_id' => $user_id]),*/
                default => null,
            };
            $item->link_right = match ($item->type) {
                'follow', 'follow_multi' => code_replace($action['user'], ['id' => $item->user_id]),

                'feed_check', 'feed_check_multi',
                'feed_comment', 'feed_comment_multi', 'feed_reply', 'feed_reply_multi',
                'feed_emoji', 'feed_upload_place', 'feed_upload_product'
                => code_replace($action['feed'], ['id' => $item->feed_id, 'comment_id' => $item->feed_comment_id]),

                'mission_like', 'mission_like_multi',
                'mission_comment', 'mission_comment_multi', 'mission_reply', 'mission_reply_multi',
                'mission_complete', 'mission_invite', 'mission_expire_warning'
                => code_replace($action['mission'], ['id' => $item->mission_id, 'comment_id' => $item->mission_comment_id]),

                /*'mission_over', 'mission_expire'
                => code_replace($action['event_mission'], ['id' => $item->mission_id, 'user_id' => $user_id]),*/

                'earn_badge' => code_replace($action['badge'], []),
                default => null,
            };
        }

        return $data;
    }

    /**
     * 알림 전송
     *
     * @param string|array $target_ids 알림 받을 대상
     * @param string $type 알림 종류
     * @param int|null $user_id 알림 보내는사람
     * @param int|null $id integer 연결될 테이블 id
     * @param bool $push 푸시 전송 여부
     * @param null $var 해당 알림에 고정으로 넣어둘 파라미터
     * @return array
     */
    public static function send(string|array $target_ids, string $type, int|null $user_id, int $id = null, bool $push = false, $var = null): array
    {
        $except_ids = [2];

        try {
            $parent_id = null;
            $data = match ($type) {
                'follow' => ['user_id' => $user_id],
                'feed_check', 'feed_emoji', 'feed_upload_place', 'feed_upload_product' => [
                    'user_id' => $user_id,
                    'feed_id' => $parent_id = $id,
                ],
                'feed_check_reward' => [],
                'feed_comment', 'feed_reply' => [
                    'user_id' => $user_id,
                    'feed_id' => $parent_id = FeedComment::where('id', $id)->value('feed_id'),
                    'feed_comment_id' => $id,
                ],
                'mission_like', 'follow_bookmark', 'mission_invite', 'mission_promotion' => [
                    'user_id' => $user_id,
                    'mission_id' => $parent_id = $id,
                ],
                'mission_comment', 'mission_reply' => [
                    'user_id' => $user_id,
                    'mission_id' => $parent_id = MissionComment::where('id', $id)->value('mission_id'),
                    'mission_comment_id' => $id,
                ],
                'notice_reply' => [
                    'user_id' => $user_id,
                    'notice_id' => $parent_id = $id,
                ],
                'mission_complete', 'mission_over', 'mission_expire', 'mission_expire_warning' => ['mission_id' => $id],
                default => null,
            };

            if (is_null($data)) {
                return success([
                    'success' => false,
                    'reason' => 'not enough data',
                ]);
            }

            DB::beginTransaction();

            foreach (Arr::wrap($target_ids) as $target_id) {
                if (!in_array($target_id, $except_ids)) {
                    $res = Notification::create(Arr::collapse([$data, ['type' => $type, 'target_id' => $target_id, 'variables' => $var]]));
                }
            }

            /*$push = match ($type) {
                'feed_check', 'feed_comment', 'feed_reply',
                'mission_like', 'follow_bookmark',
                'mission_comment', 'mission_reply', 'bookmark_warning' => true,
                default => false,
            };*/

            if ($push && isset($res)) {
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
                    'nickname' => $item->nickname,
                    'mission' => $item->mission_title,
                ];
                $replaces = Arr::collapse([$replaces, $var]);
                $message = code_replace($messages[$type], $replaces);

                $res = PushController::gcm_notify($target_ids, '써클인', $message, profile_image(User::find($user_id)),
                    $type . ($parent_id ? ".$parent_id" : ''), $id);

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
