<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MissionGroundText
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $mission_id
 * @property string $tab ground/record
 * @property int $order 체크 순서 (높을수록 우선 출력)
 * @property string $type 조건
 * @property int $value 값
 * @property string $message 출력될 내용
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGroundText newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGroundText newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGroundText query()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGroundText whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGroundText whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGroundText whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGroundText whereMissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGroundText whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGroundText whereTab($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGroundText whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGroundText whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionGroundText whereValue($value)
 * @mixin \Eloquent
 */
class MissionGroundText extends Model
{
    use HasFactory;
}
