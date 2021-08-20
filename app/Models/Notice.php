<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function images()
    {
        return $this->hasMany(NoticeImage::class);
    }

    public function comments()
    {
        return $this->hasMany(NoticeComment::class);
    }
}
