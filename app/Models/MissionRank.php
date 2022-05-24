<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MissionRank
 *
 * @property int $id
 * @property int $mission_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MissionRankUser[] $rankUsers
 * @property-read int|null $rank_users_count
 * @method static \Illuminate\Database\Eloquent\Builder|MissionRank newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionRank newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionRank query()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionRank whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionRank whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionRank whereMissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionRank whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MissionRank extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function rankUsers()
    {
        return $this->hasMany(MissionRankUser::class);
    }
}
