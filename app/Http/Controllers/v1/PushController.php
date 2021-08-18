<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\PushHistory;
use App\Models\User;
use Exception;

class PushController extends Controller
{
    /**
     * gcm push notice
     */
    public static function send_gcm_notify($uid, $title, $message, $url, $type = null): array|null
    {
        try {
            $user = User::where('id', $uid)->select(['device_type', 'device_token', 'agree_push'])->first();

            if ($user->agree_push && $user->device_token) {
                if ($user->device_type == "ios") {
                    $res = self::send_gcm_notify_ios($user->device_token, $title, $message, $url, $type);
                } else {
                    $res = self::send_gcm_notify_android($user->device_token, $title, $message, $url, $type);
                }

                PushHistory::create([
                    'target_id' => $uid,
                    'title' => $title,
                    'message' => $message,
                    'type' => $type,
                    'result' => $res['success'],
                ]);

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
            'body' => $message,
        ];
        $data = ['link' => $url];

        //This array contains, the token and the notification. The 'to' attribute stores the token.
        $arrayToSend = ['to' => $reg_id, 'notification' => $notification, 'priority' => 'high', 'data' => $data];

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
        if ($result === false) {
            die('Problem occurred: ' . curl_error($ch));
        } else {
            return (array)json_decode($result);
        }
        curl_close($ch);
    }

    public static function send_gcm_notify_ios($reg_id, $title, $message, $url, $tag): array
    {
        //Creating the notification array.
        $notification = [
            'channel_id' => 'Circlin',
            'tag' => $tag,
            'subtitle' => $title,
            'body' => $message,
        ];
        $data = ['link' => $url];

        //This array contains, the token and the notification. The 'to' attribute stores the token.
        $arrayToSend = ['to' => $reg_id, 'notification' => $notification, 'priority' => 'high', 'data' => $data];

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
        if ($result === false) {
            die('Problem occurred: ' . curl_error($ch));
        } else {
            return (array)json_decode($result);
        }
        curl_close($ch);
    }
}
