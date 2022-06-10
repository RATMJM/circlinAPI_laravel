<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\MissionTreasurePoint
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $mission_id
 * @property int $point_min 최소 지급 포인트(서버가 min ~ max 범위 내에서 랜덤의 정수값으로 포인트 지급함)
 * @property int $point_max 최대 지급 포인트
 * @property int $qty 남은 수량 
 * is_stock == 1 => (qty + count = 총 보물 수량)
 * is_stock == 0 => (count에 관계없이 qty = 총 보물 수량)
 * @property int $count 지급 횟수
 * @property int $is_stock 뽑힐 때마다 하나씩 빠질지
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|MissionTreasurePoint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionTreasurePoint newQuery()
 * @method static \Illuminate\Database\Query\Builder|MissionTreasurePoint onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionTreasurePoint query()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionTreasurePoint whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionTreasurePoint whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionTreasurePoint whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionTreasurePoint whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionTreasurePoint whereIsStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionTreasurePoint whereMissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionTreasurePoint wherePointMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionTreasurePoint wherePointMin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionTreasurePoint whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionTreasurePoint whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|MissionTreasurePoint withTrashed()
 * @method static \Illuminate\Database\Query\Builder|MissionTreasurePoint withoutTrashed()
 * @mixin \Eloquent
 */
class MissionTreasurePoint extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
