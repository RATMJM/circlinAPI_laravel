<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\FeedMission
 *
 * @property int $id
 * @property mixed|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $feed_id
 * @property int $mission_stat_id
 * @property int $mission_id
 * @property-read \App\Models\Feed $feed
 * @property-read \App\Models\Mission $mission
 * @property-read \App\Models\MissionStat $mission_stat
 * @method static \Illuminate\Database\Eloquent\Builder|FeedMission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeedMission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeedMission query()
 * @method static \Illuminate\Database\Eloquent\Builder|FeedMission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedMission whereFeedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedMission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedMission whereMissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedMission whereMissionStatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedMission whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FeedMission extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];

    public function feed()
    {
        return $this->belongsTo('App\Models\Feed');
    }

    public function mission()
    {
        return $this->belongsTo('App\Models\Mission');
    }

    public function mission_stat()
    {
        return $this->belongsTo(MissionStat::class, 'mission_id', 'mission_id');
    }
}
