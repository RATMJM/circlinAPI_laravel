<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MissionReward
 *
 * @property int $id
 * @property mixed|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $mission_id
 * @property string $title
 * @property string $image
 * @method static \Illuminate\Database\Eloquent\Builder|MissionReward newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionReward newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionReward query()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionReward whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionReward whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionReward whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionReward whereMissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionReward whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionReward whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MissionReward extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
