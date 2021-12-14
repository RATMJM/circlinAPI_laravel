<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\MissionLike
 *
 * @property int $id
 * @property mixed|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $mission_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|MissionLike newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionLike newQuery()
 * @method static \Illuminate\Database\Query\Builder|MissionLike onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionLike query()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionLike whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionLike whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionLike whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionLike whereMissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionLike whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionLike whereUserId($value)
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
