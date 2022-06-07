<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MissionCache
 *
 * @method static \Illuminate\Database\Eloquent\Builder|MissionCache newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionCache newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionCache query()
 * @mixin \Eloquent
 */
class MissionCache extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'data' => 'object',
    ];
}
