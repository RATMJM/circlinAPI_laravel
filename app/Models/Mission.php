<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mission extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_event' => 'bool',
        'created_at' => 'date:Y-m-d H:i:s',
    ];

    public function mission_areas()
    {
        return $this->hasMany(MissionArea::class);
    }

    public function areas()
    {
        return $this->hasManyThrough(Area::class, MissionArea::class,
            'mission_id', 'code', 'id', 'area_code');
    }

    public function images()
    {
        return $this->hasMany(MissionImage::class);
    }

    public function product()
    {
        return $this->hasOne(MissionProduct::class);
    }

    public function mission_place()
    {
        return $this->hasMany(MissionPlace::class);
    }

    public function place()
    {
        return $this->belongsToMany(Place::class, MissionPlace::class);
    }

    public function mission_content()
    {
        return $this->hasOne(MissionContent::class);
    }

    public function content()
    {
        return $this->hasOneThrough(Content::class, MissionContent::class);
    }

    public function mission_stats()
    {
        return $this->hasMany(MissionStat::class);
    }

    public function feed_missions()
    {
        return $this->hasMany(FeedMission::class);
    }

    public function feeds()
    {
        return $this->belongsToMany(Feed::class, FeedMission::class);
    }

    public function category()
    {
        return $this->belongsTo(MissionCategory::class, 'mission_category_id');
    }
}
