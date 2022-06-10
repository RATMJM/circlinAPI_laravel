<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\ChatMessage
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $chat_room_id
 * @property int $user_id
 * @property string|null $type 채팅 종류 (chat|chat_image|feed|feed_emojimission|mission_invite)
 * @property string|null $message 메시지
 * @property string|null $image_type image/video
 * @property string|null $image 이미지 url
 * @property int|null $feed_id 피드 공유 및 이모지
 * @property int|null $mission_id 미션 초대
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\ChatRoom $room
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage newQuery()
 * @method static \Illuminate\Database\Query\Builder|ChatMessage onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereChatRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereFeedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereImageType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereMissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChatMessage whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|ChatMessage withTrashed()
 * @method static \Illuminate\Database\Query\Builder|ChatMessage withoutTrashed()
 * @mixin \Eloquent
 */
class ChatMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];

    public function room()
    {
        return $this->belongsTo(ChatRoom::class, 'chat_room_id');
    }
}
