<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Feed extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_hidden' => 'bool',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return $this->hasMany(FeedImage::class);
    }

    public function feed_missions()
    {
        return $this->hasMany(FeedMission::class);
    }

    public function missions()
    {
        return $this->belongsToMany(Mission::class, 'feed_missions');
    }

    public function likes()
    {
        return $this->hasMany(FeedLike::class);
    }

    public function comments()
    {
        return $this->hasMany(FeedComment::class);
    }

    public function product()
    {
        return $this->hasOne(FeedProduct::class);
    }

    public function place()
    {
        return $this->belongsTo(Place::class);
    }
}
