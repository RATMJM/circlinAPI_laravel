<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\MissionComment
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $mission_id
 * @property int $user_id
 * @property int $group
 * @property int $depth
 * @property string $comment
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|MissionComment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionComment newQuery()
 * @method static \Illuminate\Database\Query\Builder|MissionComment onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionComment query()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionComment whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionComment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionComment whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionComment whereDepth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionComment whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionComment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionComment whereMissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionComment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionComment whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|MissionComment withTrashed()
 * @method static \Illuminate\Database\Query\Builder|MissionComment withoutTrashed()
 * @mixin \Eloquent
 */
class MissionComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
