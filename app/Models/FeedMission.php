<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedMission extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function feed()
    {
        return $this->belongsTo('App\Models\Feed');
    }

    public function mission()
    {
        return $this->belongsTo('App\Models\Mission');
    }
}
