<?php

namespace App\Broadcasting;

use App\Models\ChatMessage;
use App\Models\ChatUser;
use App\Models\User;

class ChatChannel
{
    public function __construct()
    {
        //
    }

    public function join($user, $id)
    {
        return ChatUser::where(['chat_room_id' => $id, 'user_id' => $user->id])->exists();
    }
}
