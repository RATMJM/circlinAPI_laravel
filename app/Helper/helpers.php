<?php

use App\Http\Controllers\v1\NotificationController;
use App\Http\Controllers\v1\PushController;
use App\Models\Area;
use App\Models\CommonCode;
use App\Models\Follow;
use App\Models\MissionStat;
use App\Models\SortUser;
use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

const ALLOW_IP = [
    // 테스트
    '::1', '127.0.0.1',
    // 서버
    '34.64.248.255', '34.85.2.191',
    // 사내
    '112.169.13.48',
    // 개인
    '124.5.120.66',
];

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
    return [
        'success' => false,
        'reason' => 'error',
        'message' => $e->getMessage(),
    ];
}

function token(): object
{
    try {
        $token = request()->header('token');

        if (is_null($token)) {
            abort(403);
        }

        $key = env('JWT_SECRET');

        return JWT::decode($token, $key, ['HS256']);
    } catch (Exception $e) {
        abort(403);
    }
}

function token_option(): object|null
{
    try {
        $token = request()->header('token');

        if (is_null($token)) {
            abort(403);
        }

        return JWT::decode($token, env('JWT_SECRET'), ['HS256']);
    } catch (Exception $e) {
        return null;
    }
}

/**
 * ftp url 자동완성 $server: (2, 3, 4)
 */
function image_url($server, $image_url): string|null
{
    if ($image_url) {
        return "https://" . config("filesystems.disks.ftp$server.host") . "/$image_url";
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
        $message = str_replace($res[0][$i], $replaces[$key] ?? $res[3][$i], $message);
    }
    return $message;
}

/**
 * 사진 업로드
 */
function upload_image(UploadedFile $file, $upload_dir): string
{
    return Storage::disk('ftp')->put($upload_dir, $file);
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
    return $user->profile_image ?: ($user->gender === 'M' ? 'https://www.circlin.co.kr/SNS/assets/img/man.png' :
        ($user->gender === 'W' ? 'https://www.circlin.co.kr/SNS/assets/img/woman.png' : 'https://www.circlin.co.kr/SNS/assets/img/x.png'));
}

/**
 * area 변환
 */
function area($table = 'users')
{
    return Area::select('name')->whereColumn('code', "$table.area_code")->limit(1);
}

function area_like($table = 'users')
{
    return Area::select('name')->where('code', 'like', DB::raw("CONCAT($table.area_code, '%')"))->limit(1);
}

/**
 * 기존 챌린지 type
 */
function challenge_type()
{
    return DB::raw("CASE WHEN missions.id in (786,796,811,1396) THEN 1 WHEN missions.id in (1174) THEN 2 WHEN missions.id in (962,1027,1213) THEN 3 END as event_type");
}

function init_today($time = null)
{
    return date('Y-m-d 00:00:00', ($time ?? time()));
}
