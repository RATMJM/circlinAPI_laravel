<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notice extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_new' => 'bool',
        'created_at' => 'date:Y-m-d H:i:s',
    ];

    public function images()
    {
        return $this->hasMany(NoticeImage::class);
    }

    public function comments()
    {
        return $this->hasMany(NoticeComment::class);
    }

    public function notice_missions()
    {
        return $this->hasMany(NoticeMission::class);
    }

    public function missions()
    {
        return $this->belongsToMany(Mission::class, NoticeMission::class);
    }
}
