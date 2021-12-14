<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MissionArea
 *
 * @property int $id
 * @property mixed|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $mission_id
 * @property string $area_code
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|MissionArea newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionArea newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionArea query()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionArea whereAreaCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionArea whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionArea whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionArea whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionArea whereMissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionArea whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MissionArea extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
