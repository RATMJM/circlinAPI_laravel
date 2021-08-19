<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mission extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function images()
    {
        return $this->hasMany(MissionImage::class);
    }

    public function mission_stats()
    {
        return $this->hasMany(MissionStat::class);
    }

    public function feed_missions()
    {
        return $this->hasMany(FeedMission::class);
    }

    public function category()
    {
        return $this->belongsTo(MissionCategory::class, 'mission_category_id');
    }
}
