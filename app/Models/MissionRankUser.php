<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MissionRankUser
 *
 * @property int $id
 * @property int $mission_rank_id
 * @property int $user_id
 * @property int $rank feeds_count를 높은 순으로 랭킹을 매기되, 필요 시 거리나 횟수 등을 summation에 저장하고 이를 순위에 사용하게 될 수도 있다(마라톤 챌린지에서 사용한 바 있음).
 * @property int $feeds_count
 * @property int $summation
 * @method static \Illuminate\Database\Eloquent\Builder|MissionRankUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionRankUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionRankUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionRankUser whereFeedsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionRankUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionRankUser whereMissionRankId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionRankUser whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionRankUser whereSummation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionRankUser whereUserId($value)
 * @mixin \Eloquent
 */
class MissionRankUser extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];
}
