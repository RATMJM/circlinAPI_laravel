<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Report
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property int|null $user_id
 * @property int $target_feed_id
 * @property int $target_user_id
 * @property int $target_mission_id
 * @property int $target_feed_comment_id
 * @property int $target_notice_comment_id
 * @property int $target_mission_comment_id
 * @property string|null $reason
 * @method static \Illuminate\Database\Eloquent\Builder|Report newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Report newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Report query()
 * @method static \Illuminate\Database\Eloquent\Builder|Report whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Report whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Report whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Report whereTargetFeedCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Report whereTargetFeedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Report whereTargetMissionCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Report whereTargetMissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Report whereTargetNoticeCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Report whereTargetUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Report whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Report whereUserId($value)
 * @mixin \Eloquent
 */
class Report extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function feed_comments()
    {
        return $this->hasMany('App\Models\FeedComment');
    }

    public function mission_comments()
    {
        return $this->hasMany('App\Models\MissionComment');
    }

    public function notice_comments()
    {
        return $this->hasMany('App\Models\NoticeComment');
    }

    public function feeds()
    {
        return $this->hasMany('App\Models\Feed');
    }

    public function missions()
    {
        return $this->hasMany('App\Models\Mission');
    }

    public function users()
    {
        return $this->hasMany('App\Models\User');
    }
}
