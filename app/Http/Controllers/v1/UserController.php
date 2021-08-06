<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Feed;
use App\Models\FeedImage;
use App\Models\FeedMission;
use App\Models\Follow;
use App\Models\Mission;
use App\Models\MissionCategory;
use App\Models\User;
use App\Models\UserFavoriteCategory;
use App\Models\UserMission;
use App\Models\UserStat;
use Exception;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(): array
    {
        $user_id = token()->uid;

        $user = User::where('users.id', $user_id)
            ->join('user_stats', 'user_stats.user_id', 'users.id')
            ->join('areas', 'areas.ctg_sm', 'users.area_code')
            ->select([
                'users.*',
                DB::raw("IF(name_lg=name_md, CONCAT_WS(' ', name_md, name_sm), CONCAT_WS(' ', name_lg, name_md, name_sm)) as area"),
                'user_stats.gender',
            ])->first();

        $category = UserFavoriteCategory::where('user_id', $user_id)
            ->join('mission_categories', 'mission_categories.id', 'user_favorite_categories.mission_category_id')
            ->select(['mission_categories.title'])
            ->get();

        return success([
            'result' => true,
            'user' => $user,
            'category' => $category,
        ]);
    }

    public function update(Request $request): array
    {
        try {
            DB::beginTransaction();

            $user_id = token()->uid;
            $nickname = $request->get('nickname');
            $area_code = $request->get('area_code');
            $phone = preg_replace('/[^\d]/', '', $request->get('phone'));
            $gender = $request->get('gender');
            $birthday = $request->get('birthday');

            $data = User::where('id', $user_id)->first();
            if (isset($data)) {
                $result = [];
                $user_data = [];
                $user_stat_data = [];

                if ($nickname && !(new AuthController())->exists_nickname($nickname)['data']['exists']) {
                    $user_data['nickname'] = $nickname;
                    $result[] = 'nickname';
                }
                if ($area_code && Area::where('ctg_sm', $area_code)->exists()) {
                    $user_data['area_code'] = $area_code;
                    $result[] = 'area_code';
                }
                if ($phone && $phone !== $data->phone) {
                    $user_data['phone'] = $phone;
                    $user_data['phone_verified_at'] = date('Y-m-d H:i:s', time());
                    $result[] = 'phone';
                }
                $user = User::where('id', $user_id)->update($user_data);

                if ($gender) {
                    $user_stat_data['gender'] = $gender;
                    $result[] = 'gender';
                }
                if ($birthday && preg_match('/\d{8}/', $birthday)) {
                    $user_stat_data['birthday'] = date('Y-m-d', strtotime($birthday));
                    $result[] = 'birthday';
                }
                $user_stat = UserStat::where('user_id', $user_id)->update($user_stat_data);

                DB::commit();
                return success([
                    'result' => count($result) > 0,
                    'updated' => $result,
                ]);
            } else {
                DB::rollBack();
                return success([
                    'result' => false,
                    'reason' => 'not enough data',
                ]);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return failed($e);
        }
    }

    // public function change_profile_image1(Request $request): array
    // {

    //     try {
    //         DB::beginTransaction();
    //         $user_id = JWT::decode($request->header('token'), env('JWT_SECRET'), ['HS256'])->uid;
    //         $profile_image_dir = $request->get('imgUrl');
    //         $profile_image_dir = base64_decode($profile_image_dir);
    //       //  echo $profile_image_dir;
    //         $data = User::where('id', $user_id)->first();

    //         if (isset($data)) {
    //             $user_data = [];

    //             $changeProfileImage = DB::update('update users set profile_image = ? where id = ? ',array($profile_image_dir,$user_id));

    //             DB::commit();
    //             return success([
    //                 'result' => true,
    //             ]);
    //         } else {
    //             DB::rollBack();
    //             return success([
    //                 'result' => false,
    //                 'reason' => 'not enough data',
    //             ]);
    //         }
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         return failed($e);
    //     }
    // }

    public function change_profile_image(Request $request): array
    {

            $ftp_server = 'cyld20182.speedgabia.com'; //호스팅 서버 주소
            $ftp_user_name = 'cyld20182';     //아이디
            $ftp_user_pass = 'teamcyld2018!';     //암호
            $port='21';
            // $user_id = token()->uid;
            // $request->get('email');
        // $uid = $request->get('uid');
        //;token()->uid; //$_POST['uid'];
        $uid = JWT::decode($request->header('token'), env('JWT_SECRET'), ['HS256'])->uid;
        // $file_name = '';//$_FILES[1]['VideoCapture_20210620-231319.jpg'];//$_FILES['0']['0']; //업로드한 파일명
        $file_name=$_FILES['file']['name']; //업로드한 파일명
        // $file_tmp_name = '';//;$_FILES[1]['VideoCapture_20210620-231319.jpg'];//$_FILES['0']['0']; // 임시디렉토리에 저장된 파일
        $file_tmp_name = $_FILES['file']['tmp_name']; // 임시디렉토리에 저장된 파일
        $ftp_path = "/Image/profile/".$uid."/".$file_name; // 접속한 서버에 업로드되어 새로 생길 파일
         $local_file = $file_tmp_name; // 접속한 서버로 업로드 할 파일
        //$local_file = 'C:\Users\snipe\Downloads\VideoCapture_20210620-231319.jpg';

        $allowed_ext = array('jpg','jpeg','png','gif');

        // $d = compress($local_file,$local_file,100);
                        // $source, $destination, $quality
        $info = getimagesize($local_file);
        if ($info['mime'] == 'image/jpeg')
                $image = imagecreatefromjpeg($local_file);
            elseif ($info['mime'] == 'image/gif')
                $image = imagecreatefromgif($local_file);
            elseif ($info['mime'] == 'image/png')
                $image = imagecreatefrompng($local_file);
                    $exif = exif_read_data($local_file);
            if(!empty($exif['Orientation'])){
            switch($exif['Orientation']) {
            case 8:
                $image = imagerotate($image,90,0);
                break;
            case 3:
                $image = imagerotate($image,180,0);
                break;
            case 6:
                $image = imagerotate($image,-90,0);
                break;
          }
        }

        imagejpeg($image, $local_file, 100);

        //sq($d,$d);
        $d = $local_file;
        $ext='jpg';
        // $ext =   (explode('.', $file_name);// array_pop($file_name); // (explode('.', $file_name));
        // $ext[1] = array_pop(explode('.', $file_name));

        //호스트 접속
        $conn_id = ftp_connect($ftp_server,$port);  //Returns a FTP stream on success or FALSE on error.
        //호스트 로그인
        $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass); //성공 시 TRUE를, 실패 시 FALSE를 반환합니다. If login fails, PHP will also throw a warning.
        $uploaddir = '/Image/profile/';
        $uploaddirNew = "/Image/profile/".$uid."/";
        $serverfile = $uploaddirNew .$uid."_".strtotime(date('Y-m-d H:i:s')).".".$ext; //업로드 될 폴더 와 파일명
        $dbProfile = "https://cyld20182.speedgabia.com/".$serverfile;
        ftp_pasv($conn_id, true);
        if (ftp_nlist($conn_id, $uploaddirNew) == false) {
            ftp_mkdir($conn_id, $uploaddirNew);
        }
        if (ftp_put($conn_id, $serverfile, $d, FTP_BINARY)) { //파일전송 성공

            try {
                DB::beginTransaction();

                $data = User::where('id', $uid)->first();

                if (isset($data)) {
                    $user_data = [];

                    $changeProfileImage = DB::update('update users set profile_image = ? where id = ? ',array($dbProfile,$uid));

                    DB::commit();
                    return success([
                        'result' => true,
                    ]);
                } else {
                    DB::rollBack();
                    return success([
                        'result' => false,
                        'reason' => 'not enough data',
                    ]);
                }
            } catch (Exception $e) {
                DB::rollBack();
                return failed($e);
            }

            // echo "파일전송";
            // return success(['result' => true]);
        } else {
            $json_result = [
                        "status" => 404,
                ];
            echo json_encode($json_result);
        }
        ftp_close($conn_id);

            // return success([
            //             'result' => true,
            //     ]);


    }

    public function remove_profile_image(Request $request): array
    {
        try {
            DB::beginTransaction();

            $user_id = JWT::decode($request->header('token'), env('JWT_SECRET'), ['HS256'])->uid;
            $result = User::where('id', $user_id)->update(['profile_image' => null]);

            DB::commit();
            return success(['result' => $result > 0]);
        } catch (Exception $e) {
            DB::rollBack();
            return failed($e);
        }
    }

    /* 팔로우 관련 */
    /**
     * 팔로우 추가
     */
    public function follow(Request $request): array
    {
        try {
            $user_id = token()->uid;
            $target_id = $request->get('target_id');

            if (is_null($target_id)) {
                return success([
                    'result' => false,
                    'reason' => 'not enough data',
                ]);
            }

            if (Follow::where(['user_id' => $user_id, 'target_id' => $target_id])->exists()) {
                return success(['result' => false, 'reason' => 'already following']);
            } else {
                $data = Follow::create(['user_id' => $user_id, 'target_id' => $target_id]);
                if ($data) {
                    return success(['result' => true]);
                } else {
                    return success(['result' => false]);
                }
            }
        } catch (Exception $e) {
            return failed($e);
        }
    }

    /**
     * 언팔로우
     */
    public function unfollow($id): array
    {
        try {
            $user_id = token()->uid;

            if (is_null($id)) {
                return success([
                    'result' => false,
                    'reason' => 'not enough data',
                ]);
            }

            $data = Follow::where(['user_id' => $user_id, 'target_id' => $id])->first();
            if ($data) {
                $result = $data->delete();
                return success(['result' => $result]);
            } else {
                return success(['result' => false, 'not following']);
            }
        } catch (Exception $e) {
            return failed($e);
        }
    }

    /**
     * 나를 팔로우
     */
    public function follower(Request $request): array
    {
        $user_id = token()->uid;

        return success([
            'result' => true,
            'users' => Follow::where('follows.target_id', $user_id)
                ->join('users', 'users.id', 'follows.user_id')
                ->leftJoin('user_stats', 'user_stats.user_id', 'users.id')
                ->leftJoin('areas', 'areas.ctg_sm', 'users.area_code')
                ->leftJoin('follows as f2', 'f2.target_id', 'users.id')
                ->select(['users.id', 'users.nickname', 'users.profile_image', 'user_stats.gender',
                    DB::raw("IF(name_lg=name_md, CONCAT_WS(' ', name_md, name_sm), CONCAT_WS(' ', name_lg, name_md, name_sm)) as area"),
                    DB::raw("COUNT(distinct f2.id) as follower")])
                ->groupBy(['follows.id', 'users.id', 'user_stats.id', 'areas.id'])
                ->get(),
        ]);
    }

    /**
     * 내가 팔로우
     */
    public function following(Request $request): array
    {
        $user_id = token()->uid;

        return success([
            'result' => true,
            'users' => Follow::where('follows.user_id', $user_id)
                ->join('users', 'users.id', 'follows.target_id')
                ->leftJoin('user_stats', 'user_stats.user_id', 'users.id')
                ->leftJoin('areas', 'areas.ctg_sm', 'users.area_code')
                ->leftJoin('follows as f2', 'f2.target_id', 'users.id')
                ->select(['users.id', 'users.nickname', 'users.profile_image', 'user_stats.gender',
                    DB::raw("IF(name_lg=name_md, CONCAT_WS(' ', name_md, name_sm), CONCAT_WS(' ', name_lg, name_md, name_sm)) as area"),
                    DB::raw("COUNT(distinct f2.id) as follower")])
                ->groupBy(['follows.id', 'users.id', 'user_stats.id', 'areas.id'])
                ->get(),
        ]);
    }

    /* 유저 상세 페이지 */
    /**
     * 프로필 기본 데이터
     */
    public function show($user_id): array
    {
        $data = User::where('users.id', $user_id)
            ->leftJoin('areas as a', 'a.ctg_sm', 'users.area_code')
            ->leftJoin('user_stats as us', 'us.user_id', 'users.id')
            ->leftJoin('follows as f1', 'f1.target_id', 'users.id') // 팔로워
            ->leftJoin('follows as f2', 'f2.user_id', 'users.id') // 팔로잉
            ->leftJoin('missions as m', 'm.user_id', 'users.id') // 미션 제작
            ->leftJoin('feeds as f', 'f.user_id', 'users.id')
            ->leftJoin('feed_likes as fl', 'fl.user_id', 'users.id')
            ->leftJoin('feed_missions as fm', 'fm.feed_id', 'f.id')
            ->select(['users.nickname', 'users.point', 'users.profile_image',
                DB::raw("IF(a.name_lg=a.name_md, CONCAT_WS(' ', a.name_md, a.name_sm), CONCAT_WS(' ', a.name_lg, a.name_md, a.name_sm)) as area"),
                DB::raw('COUNT(distinct f1.id) as followers'), DB::raw('COUNT(distinct f2.id) as followings'),
                DB::raw('COUNT(distinct m.id) as make_missions'),
                DB::raw('COUNT(distinct f.id) as feeds'), DB::raw('COUNT(distinct fl.id) as checks'),
                DB::raw('COUNT(distinct fm.id) as missions')])
            ->groupBy('users.id', 'a.id', 'us.id')
            ->first();

        return success([
            'success' => true,
            'user' => $data,
        ]);
    }

    /**
     * 피드 데이터
     */
    public function feed(Request $request, $user_id, $feed_id = null): array
    {
        $limit = $request->get('limit', 20);
        $page = $request->get('page', 0);

        $categories = MissionCategory::whereNotNull('m.mission_category_id')
            ->where('f.user_id', $user_id)
            ->join('missions as m', 'm.mission_category_id', 'mission_categories.id')
            ->join('feed_missions as fm', 'fm.mission_id', 'm.id')
            ->join('feeds as f', 'f.id', 'fm.feed_id')
            ->select([
                'mission_categories.id', 'mission_categories.title', 'mission_categories.emoji',
                DB::raw('COUNT(distinct f.id) as feeds'),
            ])
            ->groupBy('mission_categories.id')
            ->get();

        $feeds = Feed::where('feeds.user_id', $user_id)
            ->leftJoin('feed_missions as fm', 'fm.feed_id', 'feeds.id')
            ->leftJoin('feed_likes as fl', 'fl.feed_id', 'feeds.id')
            ->leftJoin('feed_comments as fc', 'fc.feed_id', 'feeds.id')
            ->select([
                'feeds.id', 'feeds.created_at', 'feeds.content',
                'image' => FeedImage::select('image_url')->whereColumn('feed_images.feed_id', 'feeds.id')->orderBy('id')->limit(1),
                DB::raw('COUNT(distinct fm.id) as missions'),
                'mission_id' => FeedMission::select('mission_id')->whereColumn('feed_missions.feed_id', 'feeds.id')
                    ->orderBy('id')->limit(1),
                'mission' => Mission::select('title')
                    ->whereHas('feed_missions', function ($query) {
                        $query->whereColumn('feed_missions.feed_id', 'feeds.id')->orderBy('id');
                    })->orderBy('id')->limit(1),
                'emoji' => MissionCategory::select('emoji')
                    ->whereHas('missions', function ($query) {
                        $query->whereHas('feed_missions', function ($query) {
                            $query->whereColumn('feed_missions.feed_id', 'feeds.id')->orderBy('id');
                        });
                    })->limit(1),
                DB::raw('COUNT(distinct fl.id) as checks'),
                DB::raw('COUNT(distinct fc.id) as comments'),
            ])
            ->groupBy('feeds.id')
            ->skip($page * $limit)->take($limit)->get();

        return success([
            'result' => true,
            'categories' => $categories,
            'feeds' => $feeds,
        ]);
    }

    /**
     * 체크한 피드
     */
    public function check(Request $request, $user_id): array
    {
        $limit = $request->get('limit', 20);
        $page = $request->get('page', 0);

        $feeds = Feed::rightJoin('feed_likes as fl', function ($query) use ($user_id) {
            $query->on('fl.feed_id', 'feeds.id')->where('fl.user_id', $user_id); // 내가 체크한
        })
            ->leftJoin('feed_missions as fm', 'fm.feed_id', 'feeds.id')
            ->leftJoin('feed_likes as fl2', 'fl2.feed_id', 'feeds.id') // 체크 수
            ->leftJoin('feed_comments as fc', 'fc.feed_id', 'feeds.id') // 댓글 수
            ->select([
                'feeds.id', 'feeds.created_at', 'feeds.content',
                'image' => FeedImage::select('image_url')->whereColumn('feed_images.feed_id', 'feeds.id')->orderBy('id')->limit(1),
                DB::raw('COUNT(distinct fm.id) as missions'),
                'mission_id' => FeedMission::select('mission_id')->whereColumn('feed_missions.feed_id', 'feeds.id')
                    ->orderBy('id')->limit(1),
                'mission' => Mission::select('title')
                    ->whereHas('feed_missions', function ($query) {
                        $query->whereColumn('feed_missions.feed_id', 'feeds.id')->orderBy('id');
                    })->orderBy('id')->limit(1),
                'emoji' => MissionCategory::select('emoji')
                    ->whereHas('missions', function ($query) {
                        $query->whereHas('feed_missions', function ($query) {
                            $query->whereColumn('feed_missions.feed_id', 'feeds.id')->orderBy('id');
                        });
                    })->limit(1),
                DB::raw('COUNT(distinct fl2.id) as checks'),
                DB::raw('COUNT(distinct fc.id) as comments'),
            ])
            ->groupBy('feeds.id')
            ->skip($page * $limit)->take($limit)->get();

        return success([
            'result' => true,
            'feeds' => $feeds,
        ]);
    }

    /**
     * 진행했던 미션 전체
     */
    public function mission(Request $request, $user_id): array
    {
        $limit = $request->get('limit', 20);
        $page = $request->get('page', 0);

        $categories = MissionCategory::whereNotNull('m.mission_category_id')
            ->where('f.user_id', $user_id)
            ->join('missions as m', 'm.mission_category_id', 'mission_categories.id')
            ->join('feed_missions as fm', 'fm.mission_id', 'm.id')
            ->join('feeds as f', 'f.id', 'fm.feed_id')
            ->select([
                'mission_categories.id', 'mission_categories.title', 'mission_categories.emoji',
                DB::raw('COUNT(distinct f.id) as feeds'),
            ])
            ->groupBy('mission_categories.id')
            ->get();

        $missions = Mission::whereHas('feed_missions', function ($query) use ($user_id) {
            $query->whereHas('feed', function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            });
        })
            ->join('users as o', 'o.id', 'missions.user_id') // 미션 제작자
            ->leftJoin('user_missions as um', function ($query) {
                $query->on('um.mission_id', 'missions.id')->whereNull('um.deleted_at');
            })
            ->leftJoin('mission_comments as mc', 'mc.mission_id', 'missions.id')
            ->select([
                'missions.id', 'missions.title', 'missions.description',
                DB::raw("CONCAT(COALESCE(o.id, ''), '|', COALESCE(o.profile_image, '')) as owner"),
                'is_bookmark' => UserMission::selectRaw('COUNT(1)>0')->where('user_missions.user_id', $user_id)
                    ->whereColumn('user_missions.mission_id', 'missions.id')->limit(1),
                'user1' => UserMission::selectRaw("CONCAT(COALESCE(u.id, ''), '|', COALESCE(u.profile_image, ''))")
                    ->whereColumn('user_missions.mission_id', 'missions.id')
                    ->join('users as u', 'u.id', 'user_missions.user_id')
                    ->leftJoin('follows as f', 'f.target_id', 'user_missions.user_id')
                    ->groupBy('u.id')->orderBy(DB::raw('COUNT(f.id)'), 'desc')->limit(1),
                'user2' => UserMission::selectRaw("CONCAT(COALESCE(u.id, ''), '|', COALESCE(u.profile_image, ''))")
                    ->whereColumn('user_missions.mission_id', 'missions.id')
                    ->join('users as u', 'u.id', 'user_missions.user_id')
                    ->leftJoin('follows as f', 'f.target_id', 'user_missions.user_id')
                    ->groupBy('u.id')->orderBy(DB::raw('COUNT(f.id)'), 'desc')->skip(1)->limit(1),
                DB::raw('COUNT(distinct um.id) as bookmarks'),
                DB::raw('COUNT(distinct mc.id) as comments'),
            ])
            ->groupBy('missions.id', 'o.id')
            ->skip($page * $limit)->take($limit)->get();

        foreach($missions as $i => $mission) {
            $tmp = explode('|', $mission['owner'] ?? '|');
            $missions[$i]['owner'] = ['user_id' => $tmp[0], 'profile_image' => $tmp[1]];
            $tmp1 = explode('|', $mission['user1'] ?? '|');
            $tmp2 = explode('|', $mission['user2'] ?? '|');
            $missions[$i]['user'] = [
                ['user_id' => $tmp1[0], 'profile_image' => $tmp1[1]],['user_id' => $tmp2[0], 'profile_image' => $tmp2[1]]
            ];
            unset($missions[$i]['user1'], $missions[$i]['user2']);
        }

        return success([
            'result' => true,
            'categories' => $categories,
            'missions' => $missions,
        ]);
    }
}
