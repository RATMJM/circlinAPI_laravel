<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'birthday' => 'date:Ymd',
    ];

    public function stat()
    {
        return $this->hasOne('App\Models\UserStat');
    }

    public function favorite_categories()
    {
        return $this->hasMany('App\Models\UserFavoriteCategory');
    }

    public function followings()
    {
        return $this->hasMany('App\Models\Follow');
    }

    public function followers()
    {
        return $this->hasMany('App\Models\Follow', 'target_id');
    }

    public function mission_stats()
    {
        return $this->hasMany('App\Models\MissionStat');
    }

    public function delete_user()
    {
        return $this->hasOne(DeleteUser::class);
    }

    public function feeds()
    {
        return $this->hasMany(Feed::class);
    }
}
