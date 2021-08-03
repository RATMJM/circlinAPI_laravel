<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MissionCategory extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function favorite_category()
    {
        return $this->hasOne('App\Models\UserFavoriteCategory');
    }
}
