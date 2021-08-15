<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function target()
    {
        return $this->belongsTo(User::class, 'target_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // target_id 가 팔로우하는 사람
    public function user_target_follow()
    {
        return $this->belongsTo(Follow::class, 'user_id', 'target_id');
    }

    // user_id 를 팔로우하는 사람
    public function target_user_follow()
    {
        return $this->belongsTo(Follow::class, 'target_id', 'user_id');
    }
}
