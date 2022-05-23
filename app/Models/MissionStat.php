<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\MissionStat
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $user_id
 * @property int $mission_id
 * @property \Illuminate\Support\Carbon|null $ended_at 미션 콤보 마지막 기록 일시
 * @property string|null $completed_at 이벤트 미션 성공 일시
 * @property string|null $code 이벤트 미션 참가할 때 입력한 코드
 * @property int|null $entry_no 미션 참여 순번
 * @property float|null $goal_distance 이벤트 미션 목표 거리
 * @property string|null $certification_image 인증서에 업로드한 이미지
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\FeedMission[] $feed_missions
 * @property-read int|null $feed_missions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Feed[] $feeds
 * @property-read int|null $feeds_count
 * @property-read \App\Models\Mission $mission
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|MissionStat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionStat newQuery()
 * @method static \Illuminate\Database\Query\Builder|MissionStat onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionStat query()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionStat whereCertificationImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionStat whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionStat whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionStat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionStat whereEndedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionStat whereEntryNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionStat whereGoalDistance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionStat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionStat whereMissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionStat whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionStat whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|MissionStat withTrashed()
 * @method static \Illuminate\Database\Query\Builder|MissionStat withoutTrashed()
 * @mixin \Eloquent
 */
class MissionStat extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];

    const DELETED_AT = 'ended_at';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mission()
    {
        return $this->belongsTo(Mission::class);
    }

    public function feed_missions()
    {
        return $this->hasMany(FeedMission::class);
    }

    public function feeds()
    {
        return $this->hasManyThrough(Feed::class, FeedMission::class,
            'mission_id', 'id', 'mission_id', 'feed_id');
    }
}
