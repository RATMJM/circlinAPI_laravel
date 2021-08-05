<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mission extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user_missions()
    {
        return $this->hasMany('App\Models\UserMission');
    }

    public function feed_missions()
    {
        return $this->hasMany('App\Models\FeedMission');
    }
}
