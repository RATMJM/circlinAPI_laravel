<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\MissionLike
 *
 * @method static \Illuminate\Database\Eloquent\Builder|MissionLike newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionLike newQuery()
 * @method static \Illuminate\Database\Query\Builder|MissionLike onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionLike query()
 * @method static \Illuminate\Database\Query\Builder|MissionLike withTrashed()
 * @method static \Illuminate\Database\Query\Builder|MissionLike withoutTrashed()
 * @mixin \Eloquent
 */
class MissionLike extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
