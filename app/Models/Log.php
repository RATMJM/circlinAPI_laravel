<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Log
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $user_id 누가
 * @property string $ip
 * @property string $type 어디서 무엇을
 * @property int|null $feed_id
 * @property int|null $feed_comment_id
 * @property int|null $mission_id
 * @property int|null $mission_comment_id
 * @property int|null $notice_id
 * @property int|null $notice_comment_id
 * @method static \Illuminate\Database\Eloquent\Builder|Log newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Log newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Log query()
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereFeedCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereFeedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereMissionCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereMissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereNoticeCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereNoticeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereUserId($value)
 * @mixin \Eloquent
 */
class Log extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
