<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\CommonCode;
use App\Models\FeedImage;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index(): array
    {
        $user_id = token()->uid;

        DB::enableQueryLog();

        $nogroup = ["follow_feed", "follow_bookmark", "mission_invite", "bookmark_warning"];

        $data = Notification::where('target_id', $user_id)
            ->select([
                DB::raw("IF( type in ('".implode("','", $nogroup)."'), CONCAT(type,'|',id),
                    CONCAT(type,COALESCE(feed_id,''),COALESCE(mission_id,'')) ) as group_type"),
                DB::raw("MAX(created_at) as created_at"),
                DB::raw("COUNT(id) as count"),
                'user_id' => DB::table('notifications as n')->select('user_id')
                    ->where('id', DB::raw("MAX(notifications.id)")),
                DB::raw("MAX(feed_id) as feed_id"),
                DB::raw("MAX(feed_comment_id) as feed_comment_id"),
                DB::raw("MAX(mission_id) as mission_id"),
                DB::raw("MAX(mission_comment_id) as mission_comment_id"),
            ])
            ->groupBy('group_type', DB::raw("CONCAT(YEAR(created_at),'|',WEEK(created_at))"))
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
                'n.*', 'users.nickname', 'users.profile_image',
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
            // common_codes 에 매칭되도록 group_type 치환
            if (preg_match('/^((feed|mission)_.*?)([\d]+)/', $item->group_type, $match)) {
                $item->group_type = $match[1];
            }
            if ($item->count > 1) {
                $res[$i]['group_type'] = match ($item->group_type) {
                    'follow', 'feed_like', 'feed_comment', 'mission_like', 'mission_comment' => $item->group_type.'s',
                    'feed_reply' => 'feed_replies',
                    'mission_reply' => 'mission_replies',
                };
            } elseif (preg_match('/^('.implode('|',$nogroup).')|.+/', $item->group_type, $match)) {
                $res[$i]['group_type'] = $match[0];
            }

            $replaces = [
                '{count}' => $item->count,
                '{nickname}' => $item->nickname,
                '{mission}' => $item->mission_title,
            ];
            $res[$i]['message'] = str_replace(array_keys($replaces), array_values($replaces), $messages[$res[$i]['group_type']] ?? '');
        }

        return success([
            'result' => false,
            'notifies' => $res,
        ]);
    }

    public function send($type, $id, $push = false): array
    {
        /*$id_column = match($type) {
            'follow' => null,
            'feed'
        };*/
    }
}
