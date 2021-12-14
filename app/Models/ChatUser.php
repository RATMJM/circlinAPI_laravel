<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\ChatUser
 *
 * @property int $id
 * @property mixed|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $chat_room_id
 * @property int $user_id
 * @property bool $is_block
 * @property int $message_notify
 * @property int|null $enter_message_id 입장할 때 마지막 메시지 id
 * @property int|null $read_message_id 마지막으로 읽은 메시지 id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|ChatUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChatUser newQuery()
 * @method static \Illuminate\Database\Query\Builder|ChatUser onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ChatUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChatUser whereChatRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatUser whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatUser whereEnterMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatUser whereIsBlock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatUser whereMessageNotify($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatUser whereReadMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatUser whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|ChatUser withTrashed()
 * @method static \Illuminate\Database\Query\Builder|ChatUser withoutTrashed()
 * @mixin \Eloquent
 */
class ChatUser extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_hidden' => 'bool',
        'is_block' => 'bool',
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
