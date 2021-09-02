<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\CommonCode;
use App\Models\PushHistory;
use App\Models\User;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PushController extends Controller
{
    /**
     * gcm push notice
     */
    public static function send_gcm_notify($uid, $title, $message, $image = '', $type = null, $id = null): array|null
    {
        try {
            $users = User::whereIn('id', Arr::wrap($uid))->where('agree_push', true)
                ->whereNotNull('device_token')->where('device_token', '!=', '')
                ->where(PushHistory::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id')
                    ->where(['result' => true, 'type' => $type])
                    ->where('created_at', '>=', date('Y-m-d H:i:s', time()-5)), 0)
                ->pluck('device_token', 'id')->toArray();

            if (count($users) > 0) {
                $res = self::send_gcm_notify_android(array_values($users), $title, $message, $type, $id, $image);

                $data = [];
                $j = 0;
                foreach ($users as $i => $user) {
                    $data[] = [
                        'created_at' => DB::raw("NOW()"),
                        'updated_at' => DB::raw("NOW()"),
                        'target_id' => $i,
                        'device_token' => $user,
                        'title' => $title,
                        'message' => $message,
                        'type' => $type,
                        'result' => isset($res['results'][$j]?->message_id) ?? false,
                    ];
                    $j += 1;
                }
                PushHistory::insert($data);

                return $res;
            } else {
                return null;
            }
        } catch (Exception $e) {
            return exceped($e);
        }
    }

    public static function send_gcm_notify_android($reg_id, $title, $message, $tag, $id, $image = ''): array
    {
        $action = CommonCode::where('ctg_lg', 'click_action')->pluck('content_ko', 'ctg_sm');
        //Creating the notification array.
        $notification = [
            'channel_id' => 'Circlin',
            'tag' => $tag,
            'title' => $title,
            'subtitle' => $title,
            'body' => $message,
            'image' => $image,
        ];

        $replaces = [
            '{%id}' => $id,
        ];
        $data = [
            'link' => str_replace(array_keys($replaces), array_values($replaces), $action[explode('.', $tag)[0]] ?? ''),
            'image' => $image,
        ];

        //This array contains, the token and the notification. The 'to' attribute stores the token.
        $arrayToSend = ['registration_ids' => $reg_id, 'notification' => $notification, 'priority' => 'high', 'data' => $data];

        $headers = [
            'Authorization: key=AAAALKBQqQQ:APA91bHBUnrkt4QVKuO6FR0ZikkWMQ2zvr_2k7JCkIo4DVBUOB3HUZTK5pH-Rug8ygfgtjzb2lES3SaqQ9Iq8YhmU-HwdbADN5dvDdbq0IjrOPKzqNZ2tTFDWgMQ9ckPVQiBj63q9pGq',
            'Content-Type: application/json',
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($arrayToSend));
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        //echo json_encode($fields);
        $result = curl_exec($ch);
        curl_close($ch);

        if ($result === false) {
            return ['success' => 0];
        } else {
            return (array)json_decode($result);
        }
    }
}
