<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\PushHistory;
use App\Models\User;
use Exception;
use Illuminate\Support\Arr;

class PushController extends Controller
{
    /**
     * gcm push notice
     */
    public static function send_gcm_notify($uid, $title, $message, $url, $type = null): array|null
    {
        try {
            $users = User::whereIn('id', Arr::wrap($uid))->where('agree_push', true)
                ->whereNotNull('device_token')->where('device_token', '!=', '')
                ->pluck('device_token', 'id')->toArray();

            if (count($users) > 0) {
                $res = self::send_gcm_notify_android($users, $title, $message, $url, $type);

                $data = [];
                $j = 0;
                foreach ($users as $i => $user) {
                    $data[] = [
                        'target_id' => $i,
                        'device_token' => $user,
                        'title' => $title,
                        'message' => $message,
                        'type' => $type,
                        'result' => isset($res['results'][$j]?->message_id) ?? false,
                    ];
                    $j += 1;
                }
                PushHistory::createMany($data);

                return $res;
            } else {
                return null;
            }
        } catch (Exception $e) {
            return exceped($e);
        }
    }

    public static function send_gcm_notify_android($reg_id, $title, $message, $url, $tag): array
    {
        //Creating the notification array.
        $notification = [
            'channel_id' => 'Circlin',
            'tag' => $tag,
            'title' => $title,
            'subtitle' => $title,
            'body' => $message,
        ];
        $data = ['link' => $url];

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