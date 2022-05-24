<?php

use App\Http\Controllers\v1_1\BaseController;
use App\Models\Area;
use App\Models\Feed;
use App\Models\Mission;
use App\Models\MissionArea;
use App\Models\MissionStat;
use App\Utils\Replace;
use Firebase\JWT\JWT;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

const SORT_POPULAR = 0;
const SORT_RECENT = 1;
const SORT_USER = 2;
const SORT_COMMENT = 3;

const DAY_OF_WEEK = ['일', '월', '화', '수', '목', '금', '토'];

/* 결과 정상 전달 */
function success($data): array
{
    return [
        'success' => true,
        'data' => $data,
    ];
}

/* 도중 오류 발생 */
function exceped(Exception $e): array
{
    if (env('APP_ENV') !== 'local') {
        $request = request();
        (new BaseController())->error_logging($request, 'back', date('Y-m-d H:i:s'),
            $request->server('HTTP_REFERER'), $e->getMessage(), $e->getTraceAsString());
    }

    return [
        'success' => false,
        'reason' => 'error',
        'message' => $e->getMessage(),
    ];
}

function token()
{
    try {
        $token = request()->header('token');

        if (is_null($token)) {
            abort(403, '토큰이 없습니다.');
        }

        $key = env('JWT_SECRET');

        return JWT::decode($token, env('JWT_SECRET'), ['HS256']);
    } catch (Exception $e) {
        abort(403, '토큰이 없습니다.');
        return null;
    }
}

function token_option(): object|null
{
    try {
        $token = request()->header('token');

        if (is_null($token)) {
            return null;
        }

        return JWT::decode($token, env('JWT_SECRET'), ['HS256']);
    } catch (Exception $e) {
        return null;
    }
}

/**
 * ftp url 자동완성 $server: (2, 3, 4)
 */
function image_url($image_url): string|null
{
    if ($image_url) {
        return "https://circlin-app.s3.ap-northeast-2.amazonaws.com/$image_url";
    } else {
        return null;
    }
}

/**
 * 배열 그룹화
 */
function arr_group(&$arr, $list, string $prefix = ''): array
{
    $res = [];
    foreach ($list as $item) {
        $res[$item] = Arr::pull($arr, $prefix . $item);
    }
    return $res;
}

function code_replace($message, $replaces)
{
    $pattern = '/{%([^}|]+)(\|([^}]*))?}/';

    preg_match_all($pattern, $message, $res);

    $res[0] = array_unique($res[0]);
    $res[1] = array_unique($res[1]);

    foreach ($res[1] as $i => $key) {
        $replace = $replaces instanceof Replace ? $replaces->get($key) : $replaces[$key];
        $message = str_replace($res[0][$i], $replace ?? $res[3][$i], $message);
    }
    return $message;
}

function random_password($length = 8): string
{
    $chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()";
    $str = '';

    while ($length--) {
        $str .= $chars[mt_rand(0, strlen($chars) - 1)];
    }

    return $str;
}

/**
 * 사진 업로드
 */
function upload_image(UploadedFile $file, $upload_dir): string
{
    return Storage::disk('s3')->put($upload_dir, $file);
}

// 이미지 압축 210810 JM 추가
function compress($source, $destination, $quality): string
{

    $info = getimagesize($source);

    if ($info['mime'] == 'image/jpeg') $image = imagecreatefromjpeg($source);

    elseif ($info['mime'] == 'image/gif') $image = imagecreatefromgif($source);

    elseif ($info['mime'] == 'image/png') $image = imagecreatefrompng($source);

    $exif = exif_read_data($source);
    if (!empty($exif['Orientation'])) {
        switch ($exif['Orientation']) {
            case 8:
                $image = imagerotate($image, 90, 0);
                break;
            case 3:
                $image = imagerotate($image, 180, 0);
                break;
            case 6:
                $image = imagerotate($image, -90, 0);
                break;
        }
    }

    imagejpeg($image, $destination, $quality);

    return $destination;
}

// 정방형 컷팅 210810 JM 추가
function image_to_square($imgSrc, $imgDes, $thumbSize = 640)
{
    [$width, $height] = getimagesize($imgSrc);
    $myImage = imagecreatefromjpeg($imgSrc);
    if ($width > $height) {
        $y = 0;
        $x = ($width - $height) / 2;
        $smallestSide = $height;
    } else {
        $x = 0;
        $y = ($height - $width) / 2;
        $smallestSide = $width;
    }
    $thumb = imagecreatetruecolor($thumbSize, $thumbSize);
    imagecopyresampled($thumb, $myImage, 0, 0, $x, $y, $thumbSize, $thumbSize, $smallestSide, $smallestSide);
    if (file_exists($imgSrc)) {
        unlink($imgSrc);
    }
    imagejpeg($thumb, $imgDes, 100);
    @imagedestroy($myImage);
    @imagedestroy($thumb);
}

// 동영상압축서버로 영상파일 보내기 210810 JM 추가
function uploadVideoResizing($uid, $ftp_server, $ftp_user_name, $ftp_user_pass, $dbProfile, $dbProfile2, $feedPk): bool
{
    try {
        $url = "https://www.circlinad.co.kr/videoResizing"; // 여기로 영상던져서 압축을 하고, 압축된 결과는 저쪽 서버에서 같은이름으로 FTP에 덮어 씌움
        $arr = [
            'url' => $dbProfile,
            'url_thumb' => $dbProfile2,
            'ftp_server' => $ftp_server,
            'ftp_user_name' => $ftp_user_name,
            'ftp_user_pass' => $ftp_user_pass,
            'feedPk' => $feedPk,
            'uid' => $uid,
        ];
        $post_field_string = http_build_query($arr, '', '&');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_field_string);
        curl_setopt($ch, CURLOPT_POST, true);
        $mh = curl_multi_init();
        curl_multi_add_handle($mh, $ch);
        do {
            curl_multi_exec($mh, $active);
        } while ($active);
        curl_multi_remove_handle($mh, $ch);
        curl_multi_close($mh);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function profile_image($user): string|null
{
    return $user?->profile_image ?: ($user?->gender === 'M' ? 'https://www.circlin.co.kr/SNS/assets/img/man.png' :
        ($user?->gender === 'W' ? 'https://www.circlin.co.kr/SNS/assets/img/woman.png' : 'https://www.circlin.co.kr/SNS/assets/img/x.png'));
}

/**
 * 공통 쿼리
 */
function area($table = 'users')
{
    return Area::select('name')->whereColumn('code', "$table.area_code")->limit(1);
}

function area_like($table = 'users')
{
    return Area::select('name')
        ->where('code', DB::raw("CONCAT(SUBSTRING($table.area_code,1,5),'00000')"))
        ->orderBy('code')
        ->limit(1);
}

function is_available($as = true)
{
    $time = date('Y-m-d H:i:s');

    return DB::raw(
        "(missions.reserve_started_at > '$time') as is_reserve_before" .
        "," .
        "(missions.reserve_started_at is null or missions.reserve_started_at<='$time') and
        (missions.reserve_ended_at is null or missions.reserve_ended_at>'$time')" . ($as ? 'as is_reserve_available' : '') .
        "," .
        "(missions.started_at is null or missions.started_at<='$time') and
        (missions.ended_at is null or missions.ended_at>'$time')" . ($as ? 'as is_available' : '') .
        "," .
        "(missions.ended_at < '$time') as is_ended"
    );
}

/**
 * 하루 초기화 시간
 */
function init_today($time = null)
{
    return date('Y-m-d 00:00:00', ($time ?? time()));
}

/**
 * 미션 참여자 목록
 */
function mission_users($mission_id, $user_id, $has_owner = false)
{
    return MissionStat::withTrashed()->where('mission_stats.mission_id', $mission_id)
        ->when(!$has_owner, function ($query) {
            $query->where(Mission::select('user_id')
                ->whereColumn('id', 'feed_missions.mission_id')
                ->limit(1), '!=', DB::raw('feeds.user_id'));
        })
        ->leftJoin('users', 'users.id', 'mission_stats.user_id')
        ->leftJoin('feed_missions', 'feed_missions.mission_stat_id', 'mission_stats.id')
        ->leftJoin('feeds', function ($query) use ($user_id) {
            $query->on('feeds.id', 'feed_missions.feed_id')
                ->whereNull('feeds.deleted_at')
                ->where(function ($query) use ($user_id) {
                    $query->where('feeds.is_hidden', 0)->orWhere('feeds.user_id', $user_id);
                });
        })
        ->select(['mission_stats.mission_id', 'users.id', 'users.nickname', 'users.profile_image', 'users.gender'])
        ->groupBy('users.id', 'mission_stats.mission_id')
        ->orderBy(DB::raw("COUNT(distinct feeds.id)"), 'desc')
        ->take(2);
}

function mission_areas($mission_id)
{
    return MissionArea::where('mission_areas.mission_id', $mission_id)
        ->join('areas', 'areas.code', DB::raw("CONCAT(mission_areas.area_code,'00000')"))
        ->select(['mission_id', 'areas.name'])
        ->orderBy('areas.code');
}

function mission_ground_text($data, $is_available, $mission_id, $user_id, &$cert = [])
{
    $AiText = '';

    foreach ($data->groupBy('type') as $type => $data) {
        if (!array_key_exists($type, $cert)) {
            if ($is_available) {
                if ($type === 'cert') {
                    $cert[$type] = Feed::where('feeds.user_id', $user_id)
                        ->join('feed_missions', function ($query) use ($mission_id) {
                            $query->on('feed_missions.feed_id', 'feeds.id')
                                ->where('feed_missions.mission_id', $mission_id);
                        })
                        ->value(DB::raw("COUNT(1) > 0"));
                } elseif ($type === 'today_cert') {
                    $cert[$type] = Feed::where('feeds.user_id', $user_id)
                        ->where('feeds.created_at', '>=', date('Y-m-d'))
                        ->join('feed_missions', function ($query) use ($mission_id) {
                            $query->on('feed_missions.feed_id', 'feeds.id')
                                ->where('feed_missions.mission_id', $mission_id);
                        })
                        ->value(DB::raw("COUNT(1) > 0"));
                } elseif ($type === 'complete') {
                    $cert[$type] = Feed::select([
                        DB::raw("CAST(feeds.created_at as DATE) as c"),
                        DB::raw("SUM(feeds.distance) as s"),
                    ])
                        ->join('feed_missions', 'feed_missions.feed_id', 'feeds.id')
                        ->join('mission_stats', 'mission_stats.id', 'feed_missions.mission_stat_id')
                        ->where('mission_stats.mission_id', $mission_id)
                        ->where('feeds.user_id', $user_id)
                        ->groupBy([DB::raw("CAST(feeds.created_at as DATE)"), 'mission_stats.goal_distance'])
                        ->having('s', '>=', DB::raw("mission_stats.goal_distance"))
                        ->exists() ? 1 : 0;
                } elseif ($type === 'today_complete') {
                    $cert[$type] = Feed::select([
                        DB::raw("CAST(feeds.created_at as DATE) as c"),
                        DB::raw("SUM(feeds.distance) as s"),
                    ])
                        ->join('feed_missions', 'feed_missions.feed_id', 'feeds.id')
                        ->join('mission_stats', 'mission_stats.id', 'feed_missions.mission_stat_id')
                        ->where('mission_stats.mission_id', $mission_id)
                        ->where('feeds.user_id', $user_id)
                        ->where('feeds.created_at', '>=', date('Y-m-d'))
                        ->groupBy([DB::raw("CAST(feeds.created_at as DATE)"), 'mission_stats.goal_distance'])
                        ->having('s', '>=', DB::raw("mission_stats.goal_distance"))
                        ->exists() ? 1 : 0;
                }
            } else {
                if ($type === 'default') {
                    $cert[$type] = 1;
                } elseif ($type === 'end') {
                    $cert[$type] = Mission::where('id', $mission_id)
                        ->where('ended_at', '<', now())
                        ->exists() ? 1 : 0;
                }
            }
        }

        if (isset($cert[$type])) {
            foreach ($data as $item) {
                if ($item->value == $cert[$type]) {
                    $AiText = $item->message;
                }
            }
        }
    }

    return $AiText;
}

function arraySnakeToCamelCase(array|object $array): array
{
    if ($array instanceof Collection || $array instanceof Model) {
        $array = $array->toArray();
    }
    $res = [];
    foreach ($array as $key => $item) {
        Arr::set($res,
            snakeToCamelCase($key),
            Arr::accessible($item) || $item instanceof StdClass ? arraySnakeToCamelCase($item) : $item
        );
    }
    return $res;
}

function snakeToCamelCase($string): string
{
    $str = str_replace('_', '', ucwords($string, '_'));
    $str[0] = strtolower($str[0]);
    return dashToDot($str);
}

function dashToDot($string): string
{
    $str = str_replace('-', '.', $string);
    $str[0] = strtolower($str[0]);
    return $str;
}
