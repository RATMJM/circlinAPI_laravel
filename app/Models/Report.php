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
 * @property int|null $user_id 신고자 user id
 * @property int|null $target_feed_id 피드 신고 시 feed id값
 * @property int|null $target_user_id 유저 신고 시 user id값
 * @property int|null $target_mission_id 미션 신고 시 mission id값
 * @property int|null $target_feed_comment_id 피드 댓글 신고 시 feed_comment id값
 * @property int|null $target_notice_comment_id 공지사항 댓글 신고 시 notice_comment id값
 * @property int|null $target_mission_comment_id 미션 댓글 신고 시 mission_comment id값
 * @property string|null $reason
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\FeedComment[] $feed_comments
 * @property-read int|null $feed_comments_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Feed[] $feeds
 * @property-read int|null $feeds_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MissionComment[] $mission_comments
 * @property-read int|null $mission_comments_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mission[] $missions
 * @property-read int|null $missions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\NoticeComment[] $notice_comments
 * @property-read int|null $notice_comments_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
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
