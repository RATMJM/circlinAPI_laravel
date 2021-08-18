<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function target()
    {
        return $this->belongsTo('App\Models\User', 'target_id');
    }

    public function feed()
    {
        return $this->belongsTo('App\Models\Feed');
    }

    public function feed_comment()
    {
        return $this->belongsTo('App\Models\FeedComment');
    }

    public function mission()
    {
        return $this->belongsTo('App\Models\Mission');
    }

    public function mission_commenet()
    {
        return $this->belongsTo('App\Models\MissionComment');
    }
}
