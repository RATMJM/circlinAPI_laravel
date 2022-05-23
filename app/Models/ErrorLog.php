<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ErrorLog
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $client_time
 * @property int|null $user_id 누가
 * @property string|null $ip
 * @property string $type 에러 발생 플랫폼 (front, back)
 * @property string|null $message
 * @property string|null $stack_trace
 * @method static \Illuminate\Database\Eloquent\Builder|ErrorLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ErrorLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ErrorLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|ErrorLog whereClientTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ErrorLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ErrorLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ErrorLog whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ErrorLog whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ErrorLog whereStackTrace($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ErrorLog whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ErrorLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ErrorLog whereUserId($value)
 * @mixin \Eloquent
 */
class ErrorLog extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
