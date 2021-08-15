<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Feed;
use App\Models\FeedComment;
use App\Models\FeedImage;
use App\Models\FeedLike;
use App\Models\FeedMission;
use App\Models\FeedPlace;
use App\Models\FeedProduct;
use App\Models\Follow;
use App\Models\Mission;
use App\Models\MissionCategory;
use App\Models\MissionStat;
use App\Models\User;
use App\Models\UserFavoriteCategory;
use App\Models\UserStat;
use Exception;
use Firebase\JWT\JWT;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class UserController extends Controller
{
    public function index(): array
    {
        $user_id = token()->uid;

        $user = User::where('users.id', $user_id)
            ->join('user_stats', 'user_stats.user_id', 'users.id')
            ->leftJoin('areas', 'areas.ctg_sm', 'users.area_code')
            ->select([
                'users.*',
                DB::raw("IF(name_lg=name_md, CONCAT_WS(' ', name_md, name_sm), CONCAT_WS(' ', name_lg, name_md, name_sm)) as area"),
                'user_stats.gender',
            ])->first();

        $category = UserFavoriteCategory::where('user_id', $user_id)
            ->join('mission_categories', 'mission_categories.id', 'user_favorite_categories.mission_category_id')
            ->select(['mission_categories.title'])
            ->get();

        $badge = Arr::except((new HomeController())->badge()['data'], 'result');

        return success([
            'result' => true,
            'user' => $user,
            'category' => $category,
            'badge' => $badge,
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
            return exceped($e);
        }
    }

    public function change_profile_image(Request $request): array
    {
        $user_id = token()->uid;

        $data = User::where('id', $user_id)->first();
        if (is_null($data) || !$request->file('file')) {
            return success([
                'result' => false,
                'reason' => 'not enough data',
            ]);
        }

        /*
        // $d = compress($local_file,$local_file,100);
        // $source, $destination, $quality
        $info = getimagesize($local_file);
        $image = match($info['mime']) {
            'image/jpeg' => imagecreatefromjpeg($local_file),
            'image/gif' => imagecreatefromgif($local_file),
            'image/png' => imagecreatefrompng($local_file),
        };
        $exif = exif_read_data($local_file);
        if (!empty($exif['Orientation'])) {
            $image = match($exif['Orientation']) {
                8 => imagerotate($image, 90, 0),
                3 => imagerotate($image, 180, 0),
                6 => imagerotate($image, -90, 0),
            };
        }

        imagejpeg($image, $local_file, 100);
        */

        $file = $request->file('file');
        if (str_starts_with($file->getMimeType() ?? '', 'image/')) {
            // 정사각형으로 자르기
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

            if ($filename = Storage::disk('ftp2')->put("/Image/profile/$user_id", new File($tmp_path))) { //파일전송 성공
                try {
                    @unlink($tmp_path);
                    DB::beginTransaction();

                    $data = User::where('id', $user_id)->first();

                    if (isset($data)) {
                        $result = User::where('id', $user_id)->update(['profile_image' => image_url(2, $filename)]);

                        DB::commit();
                        return success([
                            'result' => $result > 0,
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
                    return exceped($e);
                }
            } else {
                return success(['result' => false, 'reason' => 'upload failed']);
            }
        } else {
            return success(['result' => false, 'reason' => 'not image']);
        }
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
            return exceped($e);
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

            if ($user_id == $target_id) {
                return success([
                    'result' => false,
                    'reason' => 'follow self',
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
            return exceped($e);
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
            return exceped($e);
        }
    }

    /**
     * 나를 팔로우
     */
    public function follower($user_id): array
    {
        $users = User::where('follows.target_id', $user_id)
            ->join('follows', 'follows.user_id', 'users.id')
            ->leftJoin('user_stats', 'user_stats.user_id', 'users.id')
            ->leftJoin('areas', 'areas.ctg_sm', 'users.area_code')
            ->select([
                'users.id', 'users.nickname', 'users.profile_image', 'user_stats.gender',
                DB::raw("IF(name_lg=name_md, CONCAT_WS(' ', name_md, name_sm), CONCAT_WS(' ', name_lg, name_md, name_sm)) as area"),
                'follower' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id'),
                'is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('target_id', 'users.id')
                    ->where('user_id', $user_id),
            ])
            ->groupBy(['follows.id', 'users.id', 'user_stats.id', 'areas.id'])
            ->orderBy('follows.id', 'desc')
            ->get();

        return success([
            'result' => true,
            'users' => $users,
        ]);
    }

    /**
     * 내가 팔로우
     */
    public function following($user_id): array
    {
        $users = User::where('follows.user_id', $user_id)
            ->join('follows', 'follows.target_id', 'users.id')
            ->leftJoin('user_stats', 'user_stats.user_id', 'users.id')
            ->leftJoin('areas', 'areas.ctg_sm', 'users.area_code')
            ->select([
                'users.id', 'users.nickname', 'users.profile_image', 'user_stats.gender',
                DB::raw("IF(name_lg=name_md, CONCAT_WS(' ', name_md, name_sm), CONCAT_WS(' ', name_lg, name_md, name_sm)) as area"),
                'follower' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id'),
                'is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('target_id', 'users.id')
                    ->where('user_id', $user_id),
            ])
            ->groupBy(['follows.id', 'users.id', 'user_stats.id', 'areas.id'])
            ->orderBy('follows.id', 'desc')
            ->get();

        return success([
            'result' => true,
            'users' => $users,
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
            ->leftJoin('missions as m', 'm.user_id', 'users.id') // 미션 제작
            ->leftJoin('feeds as f', 'f.user_id', 'users.id')
            ->leftJoin('feed_likes as fl', 'fl.user_id', 'users.id')
            ->leftJoin('feed_missions as fm', 'fm.feed_id', 'f.id')
            ->select([
                'users.nickname', 'users.point', 'users.profile_image', 'users.greeting',
                DB::raw("IF(a.name_lg=a.name_md, CONCAT_WS(' ', a.name_md, a.name_sm), CONCAT_WS(' ', a.name_lg, a.name_md, a.name_sm)) as area"),
                'followers' => Follow::selectRaw("COUNT(1)")->whereColumn('follows.target_id', 'users.id'),
                'followings' => Follow::selectRaw("COUNT(1)")->whereColumn('follows.user_id', 'users.id'),
                DB::raw('COUNT(distinct m.id) as created_missions'),
                DB::raw('COUNT(distinct f.id) as feeds'), DB::raw('COUNT(distinct fl.id) as checks'),
                DB::raw('COUNT(distinct fm.id) as missions'),
                'is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('target_id', 'users.id')
                    ->where('user_id', token()->uid),
            ])
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
            ->select([
                'feeds.id', 'feeds.created_at', 'feeds.content',
                'has_images' => FeedImage::selectRaw("COUNT(1) > 1")->whereColumn('feed_id', 'feeds.id'), // 이미지 여러장인지
                'has_product' => FeedProduct::selectRaw("COUNT(1) > 1")->whereColumn('feed_id', 'feeds.id'), // 상품 있는지
                'has_place' => FeedPlace::selectRaw("COUNT(1) > 1")->whereColumn('feed_id', 'feeds.id'), // 위치 있는지
                'image_type' => FeedImage::select('type')->whereColumn('feed_images.feed_id', 'feeds.id')->orderBy('id')->limit(1),
                'image' => FeedImage::select('image')->whereColumn('feed_images.feed_id', 'feeds.id')->orderBy('id')->limit(1),
                'missions' => FeedPlace::selectRaw("COUNT(1)")->whereColumn('feed_id', 'feeds.id'),
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
                'checks' => FeedLike::selectRaw("COUNT(1)")->whereColumn('feed_id', 'feeds.id'),
                'comments' => FeedComment::selectRaw("COUNT(1)")->whereColumn('feed_id', 'feeds.id'),
                'has_check' => FeedLike::selectRaw("COUNT(1) > 0")->whereColumn('feed_likes.feed_id', 'feeds.id')
                    ->where('feed_likes.user_id', token()->uid),
                'has_comment' => FeedComment::selectRaw("COUNT(1) > 0")->whereColumn('feed_comments.feed_id', 'feeds.id')
                    ->where('feed_comments.user_id', token()->uid),
            ])
            ->groupBy('feeds.id')
            ->orderBy('feeds.id', 'desc')
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

        $feeds = FeedLike::where('feed_likes.user_id', $user_id) // 내가 체크한
        ->join('feeds as f', 'f.id', 'feed_likes.feed_id')
            ->join('users as u', 'u.id', 'f.user_id')
            ->leftJoin('feed_images as fi', 'fi.feed_id', 'f.id')
            ->leftJoin('feed_products as fpr', 'fpr.feed_id', 'f.id')
            ->leftJoin('feed_places as fpl', 'fpl.feed_id', 'f.id')
            ->leftJoin('feed_missions as fm', 'fm.feed_id', 'f.id')
            ->leftJoin('feed_likes as fl2', 'fl2.feed_id', 'f.id') // 체크 수
            ->leftJoin('feed_comments as fc', 'fc.feed_id', 'f.id') // 댓글 수
            ->select([
                'f.id', 'f.created_at', 'f.content',
                DB::raw("COUNT(distinct fi.id) > 1 as has_images"), // 이미지 여러장인지
                DB::raw("COUNT(distinct fpr.id) > 0 as has_product"), // 상품 있는지
                DB::raw("COUNT(distinct fpl.id) > 0 as has_place"), // 위치 있는지
                'image_type' => FeedImage::select('type')->whereColumn('feed_images.feed_id', 'f.id')->orderBy('id')->limit(1),
                'image' => FeedImage::select('image')->whereColumn('feed_images.feed_id', 'f.id')
                    ->orderBy('id')->limit(1),
                DB::raw('COUNT(distinct fm.id) as missions'),
                'mission_id' => FeedMission::select('mission_id')->whereColumn('feed_missions.feed_id', 'f.id')
                    ->orderBy('id')->limit(1),
                'mission' => Mission::select('title')
                    ->whereHas('feed_missions', function ($query) {
                        $query->whereColumn('feed_missions.feed_id', 'f.id')->orderBy('id');
                    })->orderBy('id')->limit(1),
                'emoji' => MissionCategory::select('emoji')
                    ->whereHas('missions', function ($query) {
                        $query->whereHas('feed_missions', function ($query) {
                            $query->whereColumn('feed_missions.feed_id', 'f.id')->orderBy('id');
                        });
                    })->limit(1),
                DB::raw('COUNT(distinct fl2.id) as checks'),
                DB::raw('COUNT(distinct fc.id) as comments'),
                'has_check' => FeedLike::selectRaw("COUNT(1) > 0")->whereColumn('feed_likes.feed_id', 'f.id')
                    ->where('feed_likes.user_id', token()->uid),
                'has_comment' => FeedComment::selectRaw("COUNT(1) > 0")->whereColumn('feed_comments.feed_id', 'f.id')
                    ->where('feed_comments.user_id', token()->uid),
            ])
            ->groupBy('f.id')
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
            ->leftJoin('mission_stats as ms', function ($query) {
                $query->on('ms.mission_id', 'missions.id')->whereNull('ms.ended_at');
            })
            ->leftJoin('mission_comments as mc', 'mc.mission_id', 'missions.id')
            ->select([
                'missions.id', 'missions.title', 'missions.description',
                DB::raw("CONCAT(COALESCE(o.id, ''), '|', COALESCE(o.profile_image, '')) as owner"),
                'is_bookmark' => MissionStat::selectRaw('COUNT(1) > 0')->where('mission_stats.user_id', $user_id)
                    ->whereColumn('mission_stats.mission_id', 'missions.id'),
                'user1' => MissionStat::selectRaw("CONCAT(COALESCE(u.id, ''), '|', COALESCE(u.profile_image, ''))")
                    ->whereColumn('mission_stats.mission_id', 'missions.id')
                    ->join('users as u', 'u.id', 'mission_stats.user_id')
                    ->leftJoin('follows as f', 'f.target_id', 'mission_stats.user_id')
                    ->groupBy('u.id')->orderBy(DB::raw('COUNT(f.id)'), 'desc')->limit(1),
                'user2' => MissionStat::selectRaw("CONCAT(COALESCE(u.id, ''), '|', COALESCE(u.profile_image, ''))")
                    ->whereColumn('mission_stats.mission_id', 'missions.id')
                    ->whereNull('mission_stats.ended_at')
                    ->join('users as u', 'u.id', 'mission_stats.user_id')
                    ->leftJoin('follows as f', 'f.target_id', 'mission_stats.user_id')
                    ->groupBy('u.id')->orderBy(DB::raw('COUNT(f.id)'), 'desc')->skip(1)->limit(1),
                DB::raw('COUNT(distinct ms.id) as bookmarks'),
                DB::raw('COUNT(distinct mc.id) as comments'),
            ])
            ->groupBy('missions.id', 'o.id')
            ->skip($page * $limit)->take($limit)->get();

        foreach ($missions as $i => $mission) {
            $tmp = explode('|', $mission['owner'] ?? '|');
            $missions[$i]['owner'] = ['user_id' => $tmp[0], 'profile_image' => $tmp[1]];
            $tmp1 = explode('|', $mission['user1'] ?? '|');
            $tmp2 = explode('|', $mission['user2'] ?? '|');
            $missions[$i]['users'] = [
                ['user_id' => $tmp1[0], 'profile_image' => $tmp1[1]], ['user_id' => $tmp2[0], 'profile_image' => $tmp2[1]]
            ];
            unset($missions[$i]['user1'], $missions[$i]['user2']);
        }

        return success([
            'result' => true,
            'categories' => $categories,
            'missions' => $missions,
        ]);
    }

    public function created_mission(Request $request, $user_id, $limit = null): array
    {
        $limit = $limit ?? $request->get('limit', 20);

        $missions = Mission::where('missions.user_id', $user_id)
            ->join('users as o', 'o.id', 'missions.user_id') // 미션 제작자
            ->leftJoin('mission_stats as ms', function ($query) {
                $query->on('ms.mission_id', 'missions.id')->whereNull('ms.ended_at');
            })
            ->leftJoin('mission_comments as mc', 'mc.mission_id', 'missions.id')
            ->select([
                'missions.id', 'missions.title', 'missions.description',
                DB::raw("CONCAT(COALESCE(o.id, ''), '|', COALESCE(o.profile_image, '')) as owner"),
                'is_bookmark' => MissionStat::selectRaw('COUNT(1) > 0')->where('mission_stats.user_id', $user_id)
                    ->whereColumn('mission_stats.mission_id', 'missions.id'),
                'user1' => MissionStat::selectRaw("CONCAT(COALESCE(u.id, ''), '|', COALESCE(u.profile_image, ''))")
                    ->whereColumn('mission_stats.mission_id', 'missions.id')
                    ->join('users as u', 'u.id', 'mission_stats.user_id')
                    ->leftJoin('follows as f', 'f.target_id', 'mission_stats.user_id')
                    ->groupBy('u.id')->orderBy(DB::raw('COUNT(f.id)'), 'desc')->limit(1),
                'user2' => MissionStat::selectRaw("CONCAT(COALESCE(u.id, ''), '|', COALESCE(u.profile_image, ''))")
                    ->whereColumn('mission_stats.mission_id', 'missions.id')
                    ->join('users as u', 'u.id', 'mission_stats.user_id')
                    ->leftJoin('follows as f', 'f.target_id', 'mission_stats.user_id')
                    ->groupBy('u.id')->orderBy(DB::raw('COUNT(f.id)'), 'desc')->skip(1)->limit(1),
                DB::raw('COUNT(distinct ms.id) as bookmarks'),
                DB::raw('COUNT(distinct mc.id) as comments'),
            ])
            ->groupBy('missions.id', 'o.id')
            ->orderBy('id', 'desc')->take($limit)->get();

        foreach ($missions as $i => $item) {
            $tmp = explode('|', $item['owner'] ?? '|');
            $missions[$i]['owner'] = ['user_id' => $tmp[0], 'profile_image' => $tmp[1]];
            $tmp1 = explode('|', $item['user1'] ?? '|');
            $tmp2 = explode('|', $item['user2'] ?? '|');
            $missions[$i]['users'] = [
                ['user_id' => $tmp1[0], 'profile_image' => $tmp1[1]], ['user_id' => $tmp2[0], 'profile_image' => $tmp2[1]]
            ];
            unset($missions[$i]['user1'], $missions[$i]['user2']);
        }

        return success([
            'result' => true,
            'missions' => $missions,
        ]);
    }
}
