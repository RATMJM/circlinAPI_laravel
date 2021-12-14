<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MissionPush
 *
 * @property int $id
 * @property mixed|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $mission_id
 * @property string $type 조건 (bookmark,feed_upload,first_feed_upload,users_count,feeds_count)
 * @property int $value 값
 * @property string $target 푸시 수신 대상 (self,mission,all)
 * @property string $message 푸시 메시지
 * @property int $is_disposable 일회용 여부
 * @property int $count 사용된 횟수
 * @method static \Illuminate\Database\Eloquent\Builder|MissionPush newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionPush newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionPush query()
 * @method static \Illuminate\Database\Eloquent\Builder|MissionPush whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionPush whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionPush whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionPush whereIsDisposable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionPush whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionPush whereMissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionPush whereTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionPush whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionPush whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MissionPush whereValue($value)
 * @mixin \Eloquent
 */
class MissionPush extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
