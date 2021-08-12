<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mission extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function mission_stats()
    {
        return $this->hasMany('App\Models\MissionStat');
    }

    public function feed_missions()
    {
        return $this->hasMany('App\Models\FeedMission');
    }

    public function category()
    {
        return $this->belongsTo('App\Models\MissionCategory', 'mission_category_id');
    }
}
