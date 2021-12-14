<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MissionPlace
 *
 * @property int $id
 * @property mixed|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $mission_id
 * @property int $place_id
 * @method static \Illuminate\Database\Eloquent\Builder|MissionPlace newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionPlace newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionPlace query()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionPlace whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionPlace whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionPlace whereMissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionPlace wherePlaceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionPlace whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MissionPlace extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
