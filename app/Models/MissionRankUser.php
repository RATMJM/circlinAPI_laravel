<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MissionRankUser
 *
 * @method static \Illuminate\Database\Eloquent\Builder|MissionRankUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionRankUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionRankUser query()
 * @mixin \Eloquent
 */
class MissionRankUser extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];
}
