<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\ChatUser;
use App\Models\Feed;
use App\Models\FeedImage;
use Exception;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ChatController extends Controller
{
    public function create_room(Request $request): array
    {
        $data = ChatRoom::create(['title' => $request->get('title')]);

        $this->enter_room($request, $data->id);

        return success([
            'result' => true,
            'room' => $data,
        ]);
    }

    public function delete_room(Request $request): array
    {
        // 필요하려나?
    }

    public function enter_room(Request $request, $room_id): array
    {
        $data = ChatUser::create(['chat_room_id' => $room_id, 'user_id' => token()->uid]);

        return success(['result' => true]);
    }

    public function leave_room(Request $request, $room_id): array
    {
        $data = ChatUser::where(['chat_room_id' => $room_id, 'user_id' => token()->uid])->delete();

        return success(['result' => $data > 0]);
    }

    public function show_room(Request $request, $room_id): array
    {
        $data = ChatUser::where(['chat_room_id' => $room_id, 'user_id' => token()->uid])->update(['is_block' => false]);

        return success(['result' => $data > 0]);
    }

    public function hide_room(Request $request, $room_id): array
    {
        $data = ChatUser::where(['chat_room_id' => $room_id, 'user_id' => token()->uid])->update(['is_block' => true]);

        return success(['result' => $data > 0]);
    }

    public function send_message(Request $request, $room_id, $type = null, $id = null, $message = null): array
    {
        $user_id = token()->uid;

        $message = trim($message ?? $request->get('message'));
        $file = $request->file('file');
        $mission_id = $type === 'mission' && $id ? $id : $request->get('mission_id');
        $feed_id = $type === 'feed' && $id ? $id : $request->get('feed_id');

        if (!$message && !$file && !$mission_id && !$feed_id) {
            return success([
                'result' => false,
                'reason' => 'not enough data',
            ]);
        }

        if ($mission_id && $feed_id) {
            return success([
                'result' => false,
                'reason' => 'duplicated share',
            ]);
        }

        if (ChatUser::where(['chat_room_id' => $room_id, 'user_id' => token()->uid])->exists()) {
            $image_type = null;
            $uploaded_file = null;
            if (!$feed_id && !$mission_id && $file) {
                if (str_starts_with($file->getMimeType(), 'image/')) {
                    $image_type = 'image';
                    $image = Image::make($file->getPathname());
                    if ($image->width() > $image->height()) {
                        $x = ($image->width() - $image->height()) / 2;
                        $y = 0;
                        $src = $image->height();
                    } else {
                        $x = 0;
                        $y = ($image->height() - $image->width()) / 2;
                        $src = $image->width();
                    }
                    $image->crop($src, $src, round($x), round($y));
                    $tmp_path = "{$file->getPath()}/{$user_id}_" . Str::uuid() . ".{$file->extension()}";
                    $image->save($tmp_path);
                    $uploaded_file = Storage::disk('ftp3')->put("/Image/CHAT/$room_id", new File($tmp_path));
                    @unlink($tmp_path);
                } elseif (str_starts_with($file->getMimeType(), 'video/')) {
                    $image_type = 'video';
                    $uploaded_file = Storage::disk('ftp3')->put("/Image/CHAT/$room_id", $file);

                    $thumbnail = "Image/SNS/$user_id/thumb_" . $file->hashName();
                }
            }

            $data = ChatMessage::create([
                'chat_room_id' => $room_id,
                'user_id' => token()->uid,
                'type' => $feed_id ? ($message ? 'feed_emoji' : 'feed') :
                    ($mission_id ? ($message ? 'mission_invite' : 'mission') : 'chat'),
                'message' => $message,
                'image_type' => $image_type,
                'image' => image_url(3, $uploaded_file),
                'feed_id' => $feed_id,
                'mission_id' => $mission_id,
            ]);

            $sockets = ChatUser::where('chat_room_id', $room_id)->where('user_id', '!=', token()->uid)
                ->whereNotNull('socket_id')->join('users', 'users.id', 'user_id')->pluck('socket_id');

            return success([
                'result' => true,
                'sockets' => $sockets,
                'message' => $data,
            ]);
        } else {
            return success([
                'result' => false,
                'reason' => 'not enter room',
            ]);
        }
    }

    /* 1대1 채팅방 입장 */
    public function create_or_enter_room(Request $request, $target_id): array
    {
        try {
            DB::beginTransaction();

            $user_id = token()->uid;

            $room = ChatRoom::where('is_group', false)
                ->join('chat_users as cu1', function ($query) use ($user_id) {
                    $query->on('cu1.chat_room_id', 'chat_rooms.id')->where('cu1.user_id', $user_id);
                })
                ->join('chat_users as cu2', function ($query) use ($target_id) {
                    $query->on('cu2.chat_room_id', 'chat_rooms.id')->where('cu2.user_id', $target_id);
                })
                ->select('chat_rooms.*')
                ->first();

            if (!$room) {
                $room = $this->create_room($request)['data']['room'];
                ChatUser::create(['chat_room_id' => $room->id, 'user_id' => $target_id]);
            }

            ChatUser::updateOrCreate(['chat_room_id' => $room->id, 'user_id' => $user_id], ['is_block' => false]);

            DB::commit();

            return success([
                'result' => true,
                'room' => $room,
                'message' => $this->show($request, $room->id),
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }
    }

    public function enter_direct(Request $request, $target_id): array
    {
        $user_id = token()->uid;

        $room = ChatRoom::join('chat_users as cu1', function ($query) use ($user_id) {
            $query->on('cu1.chat_room_id', 'chat_rooms.id')->where('cu1.user_id', $user_id);
        })
            ->join('chat_users as cu2', function ($query) use ($target_id) {
                $query->on('cu2.chat_room_id', 'chat_rooms.id')->where('cu2.user_id', $target_id);
            })
            ->where('is_group', false)
            ->select('chat_rooms.*')
            ->first();

        if (isset($room)) {
            $data = $this->show($request, $room->id)['data'];
        }

        return success([
            'result' => true,
            'room' => $room,
            'users' => $data['users'] ?? null,
            'messages' => $data['messages'] ?? null,
        ]);
    }

    /* 1대1 메시지 전송 */
    public function send_direct(Request $request, $target_id, $type = null, $id = null, $message = null): array
    {
        try {
            DB::beginTransaction();

            $user_id = token()->uid;

            if ($user_id == $target_id) {
                return success([
                    'result' => false,
                    'reason' => 'myself',
                ]);
            }

            $room = $this->create_or_enter_room($request, $target_id)['data']['room'];

            $target = ChatUser::firstOrCreate(['chat_room_id' => $room->id, 'user_id' => $target_id]);

            if ($target->is_block) {
                return success([
                    'result' => false,
                    'reason' => 'is block',
                ]);
            }

            $result = $this->send_message($request, $room->id, $type, $id, $message);

            if ($result['data']['result']) {
                DB::commit();
            } else {
                DB::rollBack();
            }

            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }
    }

    public function send_direct_multiple(Request $request): array
    {
        try {
            DB::beginTransaction();

            $users = Arr::wrap($request->get('user_id'));
            $users = array_unique($users);
            $success = [];
            $sockets = [];
            foreach ($users as $user) {
                $res = (new ChatController())->send_direct($request, $user);
                if ($res['success'] && $res['data']['result']) {
                    $success[] = $user;
                    $sockets = Arr::collapse([$sockets, $res['data']['sockets']]);
                }
            }

            DB::commit();

            return success(['result' => true, 'users' => $success, 'sockets' => $sockets]);
        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }
    }

    public function index(Request $request): array
    {
        $user_id = token()->uid;

        $data = ChatUser::where('chat_users.user_id', $user_id)
            ->select([
                'chat_users.chat_room_id', 'chat_users.is_block',
                'user_id' => DB::table('chat_users as cu')->select('users.id')
                    ->whereColumn('cu.chat_room_id', 'chat_users.chat_room_id')
                    ->whereColumn('cu.user_id', '!=', 'chat_users.user_id')
                    ->join('users', 'users.id', 'user_id')->limit(1),
                'nickname' => DB::table('chat_users as cu')->select('nickname')
                    ->whereColumn('cu.chat_room_id', 'chat_users.chat_room_id')
                    ->whereColumn('cu.user_id', '!=', 'chat_users.user_id')
                    ->join('users', 'users.id', 'user_id')->limit(1),
                'profile_image' => DB::table('chat_users as cu')->select('profile_image')
                    ->whereColumn('cu.chat_room_id', 'chat_users.chat_room_id')
                    ->whereColumn('cu.user_id', '!=', 'chat_users.user_id')
                    ->join('users', 'users.id', 'user_id')->limit(1),
                'gender' => DB::table('chat_users as cu')->select('gender')
                    ->whereColumn('cu.chat_room_id', 'chat_users.chat_room_id')
                    ->whereColumn('cu.user_id', '!=', 'chat_users.user_id')
                    ->join('users', 'users.id', 'user_id')->limit(1),
                'latest_message' => ChatMessage::selectRaw("IFNULL(content_ko, chat_messages.message)")
                    ->whereColumn('chat_room_id', 'chat_users.chat_room_id')
                    ->leftJoin('common_codes', function ($query) {
                        $query->on('common_codes.ctg_sm', 'chat_messages.type')
                            ->where('common_codes.ctg_lg', 'chat');
                    })->orderBy('chat_messages.id', 'desc')->limit(1),
                'latest_at' => ChatMessage::select('created_at')->whereColumn('chat_room_id', 'chat_users.chat_room_id')
                    ->orderBy('id', 'desc')->limit(1),
                'unread_total' => ChatMessage::selectRaw("COUNT(1)")->whereColumn('chat_room_id', 'chat_users.chat_room_id')
                    ->whereColumn('chat_messages.id', '>', DB::raw("COALESCE(read_message_id, 0)"))
                    ->whereColumn('chat_messages.created_at', 'chat_users.created_at')
                    ->where('user_id', '!=', $user_id),
            ])
            ->orderBy('is_block')->orderBy('latest_at', 'desc')
            ->get();

        foreach ($data as $i => $item) {
            $replaces = [
                '{%nickname}' => '{' . $item->nickname . '}',
            ];

            $data[$i]->latest_message = str_replace(array_keys($replaces), array_values($replaces), $item->latest_message);
        }

        return success([
            'result' => true,
            'rooms' => $data,
        ]);
    }

    public function show(Request $request, $room_id): array
    {
        try {
            DB::beginTransaction();

            $user_id = token()->uid;

            $loaded_id = $request->get('loaded_id');

            if (!$user = ChatUser::where(['chat_room_id' => $room_id, 'user_id' => $user_id])->first()) {
                return success([
                    'result' => false,
                    'reason' => 'not enter room',
                ]);
            }

            $users = $this->user($request, $room_id)['data']['users'];

            $messages = ChatMessage::where('chat_messages.chat_room_id', $room_id)
                ->when($loaded_id, function ($query, $loaded_id) {
                    $query->where('chat_messages.id', '<', $loaded_id);
                })
                ->where('chat_messages.created_at', '>=', function ($query) use ($room_id, $user_id) {
                    $query->select('created_at')->from('chat_users')
                        ->where('chat_room_id', $room_id)->where('user_id', $user_id)
                        ->whereNull('deleted_at')
                        ->limit(1);
                })
                ->join('users', 'users.id', 'chat_messages.user_id')
                ->leftJoin('missions', 'missions.id', 'chat_messages.mission_id')
                ->select([
                    'chat_messages.user_id', 'users.nickname', 'users.profile_image', 'users.gender',
                    'chat_messages.type', 'chat_messages.created_at', 'chat_messages.message', 'chat_messages.image',
                    'feed_id', 'mission_id',
                    'feed_content' => Feed::select('content')->whereColumn('id', 'chat_messages.feed_id')->limit(1),
                    'feed_image' => FeedImage::select('image')->whereColumn('feed_id', 'chat_messages.feed_id')
                        ->orderBy('order')->limit(1),
                    'feed_user_id' => Feed::select('user_id')->whereColumn('id', 'chat_messages.feed_id')->limit(1),
                    'missions.title as mission_title', 'missions.description as mission_description',
                    'missions.thumbnail_image as mission_thumbnail_image',
                ])
                ->orderBy('chat_messages.id', 'desc')
                ->take(20);
            $max = $messages->max('chat_messages.id');
            $messages = $messages->get();

            foreach ($messages as $i => $message) {
                $messages[$i]->mission = arr_group($messages[$i], ['id', 'title', 'description', 'thumbnail_image'], 'mission_');
                $messages[$i]->image = match ($message->type) {
                    'feed' => $message->feed_image,
                    'mission' => $message->mission_image,
                    default => $message->image,
                };
                Arr::except($messages[$i], ['feed_image', 'mission_image']);
            }

            $user->update(['read_message_id' => max($user->read_message_id, $max)]);

            DB::commit();

            return success([
                'result' => true,
                'users' => $users,
                'messages' => $messages,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }
    }

    public function user(Request $request, $room_id): array
    {
        $users = ChatUser::where('chat_room_id', $room_id)
            ->where('user_id', '!=', token()->uid)
            ->join('users', 'users.id', 'user_id')
            ->select(['users.id', 'users.nickname', 'users.profile_image', 'users.gender', 'area' => area()])
            ->get();

        return success([
            'result' => true,
            'users' => $users,
        ]);
    }
}
