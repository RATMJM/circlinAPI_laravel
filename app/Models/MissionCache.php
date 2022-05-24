<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MissionCache
 *
 * @property int $mission_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property mixed $data
 * @method static \Illuminate\Database\Eloquent\Builder|MissionCache newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionCache newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionCache query()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionCache whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionCache whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionCache whereMissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionCache whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionCache whereUserId($value)
 * @mixin \Eloquent
 */
class MissionCache extends Model
{
    use HasFactory;

    protected $guarded = [];
}
