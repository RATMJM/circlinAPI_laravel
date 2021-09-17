<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
