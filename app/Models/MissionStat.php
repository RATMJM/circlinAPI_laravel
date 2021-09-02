<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MissionStat extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

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
        return $this->hasMany(FeedMission::class, 'mission_id', 'mission_id');
    }

    public function feeds()
    {
        return $this->hasManyThrough(Feed::class, FeedMission::class,
            'mission_id', 'id', 'mission_id', 'feed_id');
    }
}
