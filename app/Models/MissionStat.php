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

    public function mission()
    {
        return $this->belongsTo('App\Models\Mission');
    }

    public function feed_missions()
    {
        return $this->hasMany('App\Models\FeedMission');
    }
}
