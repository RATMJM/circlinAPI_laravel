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
    public static function gcm_notify($uid, $title, $message, $image = '', $type = null, $id = null): array|null
    {
        try {
            $users = User::whereIn('id', Arr::wrap($uid))->where('agree_push', true)
                ->where(DB::raw("IFNULL(device_token,'')"), '!=', '')
                ->where(PushHistory::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id')
                    ->where(['result' => true, 'type' => $type])
                    ->where('created_at', '>=', date('Y-m-d H:i:s', time() - 5)), 0)
                ->select(['device_token', 'id', 'device_type'])
                ->get();

            if (count($users) > 0) {
                $message = preg_replace('/{(.*?)}/', '$1', $message);

                foreach ($users->groupBy('device_type') as $i => $ids) {
                    $res = self::send_gcm_notify($i, $ids->pluck('device_token')->toArray(), $title, $message, $type, $id, $image);
                }

                $data = [];

                $now = date('Y-m-d H:i:s');

                foreach ($users as $i => $user) {
                    $data[] = [
                        'created_at' => $now,
                        'updated_at' => $now,
                        'target_id' => $user->id,
                        'device_token' => $user->device_token,
                        'title' => $title,
                        'message' => $message,
                        'type' => $type,
                        'result' => isset($res['res']['results'][$i]?->message_id) ?? false,
                        'json' => json_encode($res['json'] ?? null),
                    ];
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

    public static function send_gcm_notify($type, $reg_id, $title, $message, $tag, $id, $image = ''): array
    {
        $action = CommonCode::where('ctg_lg', 'click_action')->pluck('content_ko', 'ctg_sm');
        //Creating the notification array.
        $notification = [
            'channel_id' => 'Circlin',
            'tag' => $tag,
            $type === 'android' ? 'title' : 'subtitle' => $title,
            'body' => $message,
            // 'image' => $image,
        ];

        $data = [
            'link' => code_replace($action[explode('_', explode('.', $tag)[0])[0]] ?? '', ['id' => $id]),
            // 'image' => $image,
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
            return ['res' => (array)json_decode($result), 'json' => $arrayToSend];
        }
    }
}
