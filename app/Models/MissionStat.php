<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MissionStat extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function mission()
    {
        return $this->belongsTo('App\Models\Mission');
    }

    public function feed_missions()
    {
        return $this->hasMany('App\Models\FeedMission');
    }
}
