<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PushHistory
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $target_id 푸시 받은 사람
 * @property string|null $device_token
 * @property string|null $title
 * @property string $message
 * @property string|null $type tag
 * @property int $result
 * @property mixed|null $json 전송한 데이터
 * @property mixed|null $result_json 반환 데이터
 * @method static \Illuminate\Database\Eloquent\Builder|PushHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PushHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PushHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|PushHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushHistory whereDeviceToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushHistory whereJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushHistory whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushHistory whereResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushHistory whereResultJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushHistory whereTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushHistory whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushHistory whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushHistory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PushHistory extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
