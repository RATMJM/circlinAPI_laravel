<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mission extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user_mission()
    {
        return $this->hasOne('App\Models\UserMission');
    }

    public function feed_mission()
    {
        return $this->hasOne('App\Models\FeedMission');
    }
}
