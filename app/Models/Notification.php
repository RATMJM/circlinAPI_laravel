<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Notification
 *
 * @property int $id
 * @property mixed|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $target_id 알림 받는 사람
 * @property string $type 알림 구분 (출력 내용은 common_codes)
 * @property int|null $user_id 알림 발생시킨 사람
 * @property int|null $feed_id 알림 발생한 게시물
 * @property int|null $feed_comment_id
 * @property int|null $mission_id
 * @property int|null $mission_comment_id
 * @property int|null $mission_stat_id
 * @property int|null $notice_id
 * @property int|null $notice_comment_id
 * @property string|null $read_at 읽은 일시
 * @property array|null $variables
 * @property-read \App\Models\Feed|null $feed
 * @property-read \App\Models\FeedComment|null $feed_comment
 * @property-read \App\Models\Mission|null $mission
 * @property-read \App\Models\MissionComment $mission_commenet
 * @property-read \App\Models\User $target
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Notification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification query()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereFeedCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereFeedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereMissionCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereMissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereMissionStatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereNoticeCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereNoticeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereVariables($value)
 * @mixin \Eloquent
 */
class Notification extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_read' => 'bool',
        'variables' => 'array',
        'created_at' => 'date:Y-m-d H:i:s',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function target()
    {
        return $this->belongsTo('App\Models\User', 'target_id');
    }

    public function feed()
    {
        return $this->belongsTo('App\Models\Feed');
    }

    public function feed_comment()
    {
        return $this->belongsTo('App\Models\FeedComment');
    }

    public function mission()
    {
        return $this->belongsTo('App\Models\Mission');
    }

    public function mission_commenet()
    {
        return $this->belongsTo('App\Models\MissionComment');
    }
}
