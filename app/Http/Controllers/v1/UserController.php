<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Mail\FindPassword;
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
use App\Models\MissionComment;
use App\Models\MissionStat;
use App\Models\Place;
use App\Models\PointHistory;
use App\Models\User;
use App\Models\UserFavoriteCategory;
use App\Models\UserStat;
use App\Models\UserWallpaper;
use Exception;
use Firebase\JWT\JWT;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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
            ->select(['users.*', 'area' => area(), 'user_stats.birthday'])->first();

        $category = UserFavoriteCategory::where('user_id', $user_id)
            ->join('mission_categories', 'mission_categories.id', 'user_favorite_categories.mission_category_id')
            ->select(['mission_categories.id', 'mission_categories.title'])
            ->get();

        $yesterday_point = PointHistory::where('user_id', $user_id)
            ->where('created_at', '>=', init_today())
            ->where('point', '>', 0)
            ->sum('point');

        $yesterday_check = Feed::where('feeds.user_id', $user_id)
            ->where('feeds.created_at', '>=', init_today())
            ->join('feed_likes', function ($query) {
                $query->on('feed_likes.feed_id', 'feeds.id')->whereNull('feed_likes.deleted_at');
            })
            ->count();

        $yesterday_feeds_count = UserStat::where('user_id', $user_id)
            ->value('yesterday_feeds_count');

        $yesterday_paid_count = FeedLike::withTrashed()->where('user_id', $user_id)
            ->where('point', '>', 0)
            ->where('feed_likes.created_at', '>=', init_today(time()-86400))
            ->where('feed_likes.created_at', '<', init_today())
            ->count();

        $today_paid_count = FeedLike::withTrashed()->where('feed_likes.user_id', $user_id)
            ->join('feeds', 'feeds.id', 'feed_likes.feed_id')
            ->whereIn('feeds.user_id', function ($query) {
                $query->select('target_id')->from('follows')->whereColumn('user_id', 'feed_likes.user_id');
            })
            ->where('point', '>', 0)
            ->where('feed_likes.created_at', '>=', init_today())
            ->count();

        $badge = Arr::except((new HomeController())->badge()['data'], 'result');

        $wallpapers = $this->wallpaper($user_id)['data']['wallpapers'];

        return success([
            'result' => true,
            'user' => $user,
            'category' => $category,
            'yesterday_point' => $yesterday_point,
            'yesterday_check' => $yesterday_check,
            'yesterday_feeds_count' => $yesterday_feeds_count,
            'yesterday_paid_count' => $yesterday_paid_count,
            'today_paid_count' => $today_paid_count,
            'badge' => $badge,
            'wallpapers' => $wallpapers,
        ]);
    }

    public function update(Request $request): array
    {
        try {
            DB::beginTransaction();

            $user_id = token()->uid;
            $nickname = $request->get('nickname');
            $area_code = $request->get('area_code');
            $greeting = $request->get('greeting');
            $phone = preg_replace('/[^\d]/', '', $request->get('phone'));
            $gender = $request->get('gender');
            $socket_id = $request->get('socket_id');

            $agree4 = $request->get('agree_email');
            $agree5 = $request->get('agree_sms');
            $agree_push = $request->get('agree_push');
            $agree_push_mission = $request->get('agree_push_mission');
            $agree_ad = $request->get('agree_ad');

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
                if ($area_code /*&& strtotime($data->area_updated_at) + (86400*7) < time()*/
                    && Area::where('code', $area_code)->exists()) {
                    $user_data['area_code'] = $area_code;
                    $user_data['area_updated_at'] = date('Y-m-d H:i:s');
                    $result[] = 'area_code';
                }
                if ($greeting) {
                    $user_data['greeting'] = $greeting;
                    $result[] = 'greeting';
                }
                if ($phone && $phone !== $data->phone) {
                    $user_data['phone'] = $phone;
                    $user_data['phone_verified_at'] = date('Y-m-d H:i:s');
                    $result[] = 'phone';
                }
                if ($gender) {
                    $user_data['gender'] = $gender;
                    $result[] = 'gender';
                }
                if ($socket_id) {
                    $user_data['socket_id'] = $socket_id;
                    $result[] = 'socket_id';
                }
                if (isset($agree4)) {
                    $user_data['agree4'] = $agree4;
                    $result[] = 'agree_email';
                }
                if (isset($agree5)) {
                    $user_data['agree5'] = $agree5;
                    $result[] = 'agree_sms';
                }
                if (isset($agree_push)) {
                    $user_data['agree_push'] = $agree_push;
                    $result[] = 'agree_push';
                }
                if (isset($agree_push_mission)) {
                    $user_data['agree_push_mission'] = $agree_push_mission;
                    $result[] = 'agree_push_mission';
                }
                if (isset($agree_ad)) {
                    $user_data['agree_ad'] = $agree_ad;
                    $result[] = 'agree_ad';
                }
                $user = $data->update($user_data);

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

    public function update_token(Request $request): array
    {
        try {
            DB::beginTransaction();

            $user_id = token()->uid;

            $token = $request->get('token');
            $platform = $request->get('platform');

            User::where('device_token', $token)->where('id', '!=', $user_id)->update(['device_token' => '']);

            User::where('id', $user_id)->update([
                'device_type' => $platform,
                'device_token' => $token,
            ]);

            DB::commit();

            return success(['result' => true]);
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

    public function change_password(Request $request): array
    {
        try {
            $user_id = token()->uid;

            $old_password = $request->get('old');
            $password = $request->get('password');
            $password_confirm = $request->get('password_confirm');

            if ($password !== $password_confirm) {
                return success([
                    'result' => false,
                    'reason' => 'password confirm validation failed',
                ]);
            }

            $user = User::find($user_id);

            if (Hash::check($old_password, $user->password)) {
                $res = $user->update(['password' => Hash::make($password)]);

                return success(['result' => $res > 0]);
            } else {
                return success([
                    'result' => false,
                    'reason' => 'not matched old password',
                ]);
            }
        } catch (Exception $e) {
            return exceped($e);
        }
    }

    public function find_password(Request $request): array
    {
        try {
            DB::beginTransaction();

            $user = User::where('email', $request->get('email'))->first();

            if (isset($user)) {
                $temp_password = Str::random(8);

                $user->update(['password' => Hash::make($temp_password)]);

                Mail::to($user)->send(new FindPassword($temp_password));

                DB::commit();
                return success(['result' => true]);
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

    public function withdraw(Request $request): array
    {
        try {
            DB::beginTransaction();

            $user_id = token()->uid;

            $user = User::find($user_id);

            if (isset($user)) {
                $user->delete_user()->create(['reason' => $request->get('reason')]);
                $user->delete();

                DB::commit();
                return success(['result' => true]);
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

    /* 팔로우 관련 */
    /**
     * 팔로우 추가
     */
    public function follow(Request $request): array
    {
        try {
            DB::beginTransaction();

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
                    $res = NotificationController::send($target_id, 'follow', $user_id);

                    DB::commit();

                    return success(['result' => true]);
                } else {
                    DB::rollBack();
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
                return success(['result' => false, 'reason' => 'not following']);
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
        $uid = token()->uid;

        $users = Follow::where('follows.target_id', $user_id)
            ->join('users', 'users.id', 'follows.user_id')
            ->select([
                'users.id', 'users.nickname', 'users.profile_image', 'users.gender', 'area' => area(),
                'follower' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id'),
                'is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('target_id', 'users.id')
                    ->where('user_id', $uid),
            ])
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
        $uid = token()->uid;

        $users = Follow::where('follows.user_id', $user_id)
            ->join('users', 'users.id', 'follows.target_id')
            ->select([
                'users.id', 'users.nickname', 'users.profile_image', 'users.gender', 'area' => area(),
                'follower' => Follow::selectRaw("COUNT(1)")->whereColumn('target_id', 'users.id'),
                'is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('target_id', 'users.id')
                    ->where('user_id', $uid),
            ])
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
            ->select([
                'users.nickname', 'users.point', 'users.gender', 'users.profile_image', 'users.greeting', 'area' => area(),
                'followers' => Follow::selectRaw("COUNT(1)")->whereColumn('follows.target_id', 'users.id'),
                'followings' => Follow::selectRaw("COUNT(1)")->whereColumn('follows.user_id', 'users.id'),
                'created_missions' => Mission::selectRaw("COUNT(1)")->whereColumn('user_id', 'users.id'),
                'feeds' => Feed::selectRaw("COUNT(1)")->whereColumn('user_id', 'users.id'),
                'checks' => FeedLike::selectRaw("COUNT(1)")->whereColumn('user_id', 'users.id'),
                'missions' => FeedMission::selectRaw("COUNT(1)")->whereColumn('user_id', 'users.id')
                    ->join('feeds', 'feeds.id', 'feed_id'),
                'is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('target_id', 'users.id')
                    ->where('user_id', token()->uid),
            ])
            ->first();

        $wallpapers = $this->wallpaper($user_id)['data']['wallpapers'];

        return success([
            'success' => true,
            'user' => $data,
            'wallpapers' => $wallpapers,
        ]);
    }

    /**
     * 피드 데이터
     */
    public function feed(Request $request, $user_id): array
    {
        $category_id = $request->get('category_id');
        $limit = $request->get('limit', 20);
        $page = $request->get('page', 0);

        $categories = MissionCategory::whereNotNull('mission_categories.mission_category_id')
            ->when($category_id, function ($query, $category_id) {
                $query->whereIn('mission_categories.id', Arr::wrap($category_id));
            })
            ->where('feeds.user_id', $user_id)
            ->join('missions', 'missions.mission_category_id', 'mission_categories.id')
            ->join('feed_missions', 'feed_missions.mission_id', 'missions.id')
            ->join('feeds', 'feeds.id', 'feed_missions.feed_id')
            ->select([
                'mission_categories.id', 'mission_categories.title', 'mission_categories.emoji',
                DB::raw('COUNT(distinct feeds.id) as feeds'),
            ])
            ->groupBy('mission_categories.id')
            ->get();

        $missions = Feed::where('feeds.user_id', $user_id)
            ->join('feed_missions', 'feed_missions.feed_id', 'feeds.id')
            ->join('missions', function ($query) {
                $query->on('missions.id', 'feed_missions.mission_id')
                    ->whereNull('missions.deleted_at');
            })
            ->select(['missions.id', 'missions.title'])
            ->groupBy('missions.id')
            ->orderBy(DB::raw("MAX(feeds.id)"), 'desc')
            ->get();


        $feeds = Feed::where('feeds.user_id', $user_id)
            ->when(token()->uid != $user_id, function ($query) {
                $query->where('is_hidden', false);
            })
            ->when($category_id, function ($query, $category_id) {
                $query->whereHas('missions', function ($query) use ($category_id) {
                    $query->whereIn('missions.mission_category_id', Arr::wrap($category_id));
                });
            })
            ->select([
                'feeds.id', 'feeds.created_at', 'feeds.content', 'feeds.is_hidden',
                'has_images' => FeedImage::selectRaw("COUNT(1) > 1")->whereColumn('feed_id', 'feeds.id'), // 이미지 여러장인지
                'has_product' => FeedProduct::selectRaw("COUNT(1) > 0")->whereColumn('feed_id', 'feeds.id'), // 상품 있는지
                'has_place' => FeedPlace::selectRaw("COUNT(1) > 0")->whereColumn('feed_id', 'feeds.id'), // 위치 있는지
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
            ->orderBy('feeds.id', 'desc');
        $feeds_count = $feeds->count();
        $feeds = $feeds->skip($page * $limit)->take($limit)->get();

        return success([
            'result' => true,
            'categories' => $categories,
            'missions' => $missions,
            'feeds_count' => $feeds_count,
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

        $feeds = FeedLike::where('feed_likes.user_id', $user_id)
            ->join('feeds', 'feeds.id', 'feed_likes.feed_id')
            ->join('users', 'users.id', 'feeds.user_id')
            ->select([
                'feeds.id', 'feeds.created_at', 'feeds.content',
                'users.id as user_id', 'users.nickname', 'users.profile_image', 'users.gender',
                'has_images' => FeedImage::selectRaw("COUNT(1) > 1")->whereColumn('feed_id', 'feeds.id'), // 이미지 여러장인지
                'has_product' => FeedProduct::selectRaw("COUNT(1) > 0")->whereColumn('feed_id', 'feeds.id'), // 상품 있는지
                'has_place' => FeedPlace::selectRaw("COUNT(1) > 0")->whereColumn('feed_id', 'feeds.id'), // 위치 있는지
                'image_type' => FeedImage::select('type')->whereColumn('feed_id', 'feeds.id')->orderBy('id')->limit(1),
                'image' => FeedImage::select('image')->whereColumn('feed_id', 'feeds.id')->orderBy('id')->limit(1),
                'check_total' => FeedLike::selectRaw("COUNT(1)")->whereColumn('feed_id', 'feeds.id'),
                'comment_total' => FeedComment::selectRaw("COUNT(1)")->whereColumn('feed_id', 'feeds.id'),
                'has_check' => FeedLike::selectRaw("COUNT(1) > 0")->whereColumn('feed_id', 'feeds.id')
                    ->where('feed_likes.user_id', token()->uid),
                'has_comment' => FeedComment::selectRaw("COUNT(1) > 0")->whereColumn('feed_id', 'feeds.id')
                    ->where('feed_comments.user_id', token()->uid),
            ])
            ->orderBy('feeds.id', 'desc')
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
        $uid = token()->uid;

        $category_id = $request->get('category_id');
        $limit = $limit ?? $request->get('limit', 20);
        $page = $request->get('page', 0);

        $categories = MissionCategory::whereNotNull('mission_categories.mission_category_id')
            ->where('feeds.user_id', $user_id)
            ->join('missions', 'missions.mission_category_id', 'mission_categories.id')
            ->join('feed_missions', 'feed_missions.mission_id', 'missions.id')
            ->join('feeds', 'feeds.id', 'feed_missions.feed_id')
            ->select([
                'mission_categories.id', 'mission_categories.title', 'mission_categories.emoji',
                DB::raw('COUNT(distinct feeds.id) as feeds'),
            ])
            ->groupBy('mission_categories.id')
            ->get();

        $missions = MissionCategory::where('feeds.user_id', $user_id)
            ->when($category_id, function ($query, $category_id) {
                $query->whereIn('mission_categories.id', Arr::wrap($category_id));
            })
            ->join('missions', 'missions.mission_category_id', 'mission_categories.id')
            ->join('users', 'users.id', 'missions.user_id') // 미션 제작자
            ->leftJoin('mission_products', 'mission_products.mission_id', 'missions.id')
            ->leftJoin('products', 'products.id', 'mission_products.product_id')
            ->leftJoin('brands', 'brands.id', 'products.brand_id')
            ->leftJoin('outside_products', 'outside_products.id', 'mission_products.outside_product_id')
            ->join('feed_missions', 'feed_missions.mission_id', 'missions.id')
            ->join('feeds', 'feeds.id', 'feed_missions.feed_id')
            ->leftJoin('mission_stats', function ($query) use ($user_id) {
                $query->on('mission_stats.id', 'feed_missions.mission_stat_id')
                    ->whereNull('mission_stats.ended_at');
            })
            ->select([
                'mission_categories.mission_category_id', 'mission_categories.title', 'mission_categories.emoji',
                'missions.id', 'missions.title', 'missions.description',
                DB::raw("missions.event_order > 0 as is_event"),
                DB::raw("missions.id <= 1213 and missions.event_order > 0 as is_old_event"), challenge_type(),
                'missions.started_at', 'missions.ended_at',
                'missions.thumbnail_image', 'missions.success_count',
                'mission_stat_id' => MissionStat::withTrashed()->select('id')->whereColumn('mission_id', 'missions.id')
                    ->where('user_id', $user_id)->orderBy('id', 'desc')->limit(1),
                'mission_stat_user_id' => MissionStat::withTrashed()->select('user_id')->whereColumn('mission_id', 'missions.id')
                    ->where('user_id', $user_id)->orderBy('id', 'desc')->limit(1),
                'users.id as user_id', 'users.nickname', 'users.profile_image', 'users.gender', 'area' => area(),
                'mission_products.type as product_type', //'mission_products.product_id',
                DB::raw("IF(mission_products.type='inside', mission_products.product_id, mission_products.outside_product_id) as product_id"),
                DB::raw("IF(mission_products.type='inside', brands.name_ko, outside_products.brand) as product_brand"),
                DB::raw("IF(mission_products.type='inside', products.name_ko, outside_products.title) as product_title"),
                DB::raw("IF(mission_products.type='inside', products.thumbnail_image, outside_products.image) as product_image"),
                'outside_products.url as product_url',
                DB::raw("IF(mission_products.type='inside', products.price, outside_products.price) as product_price"),
                'place_address' => Place::select('address')->whereColumn('mission_places.mission_id', 'missions.id')
                    ->join('mission_places', 'mission_places.place_id', 'places.id')
                    ->orderBy('mission_places.id')->limit(1),
                'place_title' => Place::select('title')->whereColumn('mission_places.mission_id', 'missions.id')
                    ->join('mission_places', 'mission_places.place_id', 'places.id')
                    ->orderBy('mission_places.id')->limit(1),
                'place_description' => Place::select('description')->whereColumn('mission_places.mission_id', 'missions.id')
                    ->join('mission_places', 'mission_places.place_id', 'places.id')
                    ->orderBy('mission_places.id')->limit(1),
                'place_image' => Place::select('image')->whereColumn('mission_places.mission_id', 'missions.id')
                    ->join('mission_places', 'mission_places.place_id', 'places.id')
                    ->orderBy('mission_places.id')->limit(1),
                'place_url' => Place::select('url')->whereColumn('mission_places.mission_id', 'missions.id')
                    ->join('mission_places', 'mission_places.place_id', 'places.id')
                    ->orderBy('mission_places.id')->limit(1),
                'is_bookmark' => MissionStat::selectRaw('COUNT(1) > 0')->where('mission_stats.user_id', $uid)
                    ->whereColumn('mission_id', 'missions.id'),
                'today_upload' => FeedMission::selectRaw("COUNT(1) > 0")
                    ->whereColumn('feed_missions.mission_id', 'missions.id')->where('feeds.user_id', $user_id)
                    ->where('feeds.created_at', '>=', init_today())
                    ->whereNull('feeds.deleted_at')
                    ->join('feeds', 'feeds.id', 'feed_missions.feed_id'),
                'bookmarks' => MissionStat::selectRaw("COUNT(1)")->whereColumn('mission_id', 'missions.id')
                    ->whereColumn('mission_stats.user_id', '!=', 'missions.user_id'),
                'comments' => MissionComment::selectRaw("COUNT(1)")->whereColumn('mission_id', 'missions.id'),
                'has_check' => FeedMission::selectRaw("COUNT(1) > 0")
                    ->whereColumn('feed_missions.mission_id', 'missions.id')->where('feeds.user_id', $user_id)
                    ->where('feeds.created_at', '>=', init_today())
                    ->whereNull('feeds.deleted_at')
                    ->join('feeds', 'feeds.id', 'feed_missions.feed_id'),
                'feed_id' => FeedMission::select('feed_id')
                    ->whereColumn('feed_missions.mission_id', 'missions.id')->where('feeds.user_id', $user_id)
                    ->where('feeds.created_at', '>=', init_today())
                    ->join('feeds', 'feeds.id', 'feed_missions.feed_id')->limit(1),
                DB::raw("COUNT(distinct feeds.id) as feeds_count"),
            ]);
        $missions_count = $missions->count(DB::raw("distinct missions.id"));
        $missions = $missions->groupBy('mission_categories.id', 'missions.id', 'users.id',
            'mission_products.type', 'mission_products.product_id', 'mission_products.outside_product_id')
            ->orderBy('is_bookmark', 'desc')
            ->orderBy(DB::raw("MAX(feeds.id)"), 'desc')
            ->skip($page * $limit)->take($limit)->get();

        if (count($missions)) {
            function mission_user($mission_id)
            {
                return FeedMission::where('feed_missions.mission_id', $mission_id)
                    ->where(Mission::select('user_id')->whereColumn('id', 'feed_missions.mission_id')->limit(1), '!=', DB::raw('feeds.user_id'))
                    ->join('feeds', 'feeds.id', 'feed_missions.feed_id')
                    ->join('users', 'users.id', 'feeds.user_id')
                    ->select(['mission_id', 'users.id', 'users.nickname', 'users.profile_image', 'users.gender'])
                    ->groupBy('users.id', 'mission_id')
                    ->orderBy(DB::raw("COUNT(distinct feeds.id)"), 'desc')
                    ->take(2);
            }

            $users = null;
            foreach ($missions as $i => $mission) {
                if ($users) {
                    $users = $users->union(mission_user($mission->id));
                } else {
                    $users = mission_user($mission->id);
                }
            }
            $users = $users->get();
            $keys = $missions->pluck('id')->toArray();
            foreach ($users->groupBy('mission_id') as $j => $item) {
                $missions[array_search($j, $keys)]->users = $item;
            }
        }

        return success([
            'result' => true,
            'categories' => $categories,
            'missions_count' => $missions_count,
            'missions' => $missions,
        ]);
    }

    public function created_mission(Request $request, $user_id, $limit = null): array
    {
        $uid = token()->uid;

        $limit = $limit ?? $request->get('limit', 20);
        $page = $request->get('page', 0);

        $missions = MissionCategory::where('missions.user_id', $user_id)
            ->join('missions', 'missions.mission_category_id', 'mission_categories.id')
            ->join('users', 'users.id', 'missions.user_id') // 미션 제작자
            ->leftJoin('mission_products', 'mission_products.mission_id', 'missions.id')
            ->leftJoin('products', 'products.id', 'mission_products.product_id')
            ->leftJoin('brands', 'brands.id', 'products.brand_id')
            ->leftJoin('outside_products', 'outside_products.id', 'mission_products.outside_product_id')
            ->select([
                'mission_categories.id', 'mission_categories.title', 'mission_categories.emoji',
                'missions.mission_category_id', 'missions.id', 'missions.title', 'missions.description',
                DB::raw("missions.event_order > 0 as is_event"),
                DB::raw("missions.id <= 1213 and missions.event_order > 0 as is_old_event"), challenge_type(),
                'missions.started_at', 'missions.ended_at',
                'missions.thumbnail_image', 'missions.success_count',
                'mission_stat_id' => MissionStat::select('id')->whereColumn('mission_id', 'missions.id')
                    ->where('user_id', $uid)->limit(1),
                'users.id as mission_stat_user_id',
                'users.id as user_id', 'users.nickname', 'users.profile_image', 'users.gender',
                'is_bookmark' => MissionStat::selectRaw('COUNT(1) > 0')->where('mission_stats.user_id', $uid)
                    ->whereColumn('mission_id', 'missions.id'),
                'mission_products.type as product_type', 'mission_products.product_id', 'mission_products.outside_product_id',
                DB::raw("IF(mission_products.type='inside', brands.name_ko, outside_products.brand) as product_brand"),
                DB::raw("IF(mission_products.type='inside', products.name_ko, outside_products.title) as product_title"),
                DB::raw("IF(mission_products.type='inside', products.thumbnail_image, outside_products.image) as product_image"),
                'outside_products.url as product_url',
                DB::raw("IF(mission_products.type='inside', products.price, outside_products.price) as product_price"),
                'place_address' => Place::select('address')->whereColumn('mission_places.mission_id', 'missions.id')
                    ->join('mission_places', 'mission_places.place_id', 'places.id')
                    ->orderBy('mission_places.id')->limit(1),
                'place_title' => Place::select('title')->whereColumn('mission_places.mission_id', 'missions.id')
                    ->join('mission_places', 'mission_places.place_id', 'places.id')
                    ->orderBy('mission_places.id')->limit(1),
                'place_description' => Place::select('description')->whereColumn('mission_places.mission_id', 'missions.id')
                    ->join('mission_places', 'mission_places.place_id', 'places.id')
                    ->orderBy('mission_places.id')->limit(1),
                'place_image' => Place::select('image')->whereColumn('mission_places.mission_id', 'missions.id')
                    ->join('mission_places', 'mission_places.place_id', 'places.id')
                    ->orderBy('mission_places.id')->limit(1),
                'place_url' => Place::select('url')->whereColumn('mission_places.mission_id', 'missions.id')
                    ->join('mission_places', 'mission_places.place_id', 'places.id')
                    ->orderBy('mission_places.id')->limit(1),
                'bookmark_total' => MissionStat::selectRaw("COUNT(1)")->whereColumn('mission_id', 'missions.id')
                    ->whereColumn('mission_stats.user_id', '!=', 'missions.user_id'),
                'comment_total' => MissionComment::selectRaw("COUNT(1)")->whereColumn('mission_id', 'missions.id'),
            ])
            ->orderBy('missions.id', 'desc')
            ->skip($page * $limit)->take($limit)->get();

        if (count($missions)) {
            function mission_user($mission_id)
            {
                return FeedMission::where('feed_missions.mission_id', $mission_id)
                    ->where(Mission::select('user_id')->whereColumn('id', 'feed_missions.mission_id')->limit(1), '!=', DB::raw('feeds.user_id'))
                    ->join('feeds', 'feeds.id', 'feed_missions.feed_id')
                    ->join('users', 'users.id', 'feeds.user_id')
                    ->select(['mission_id', 'users.id', 'users.nickname', 'users.profile_image', 'users.gender'])
                    ->groupBy('users.id', 'mission_id')
                    ->orderBy(DB::raw("COUNT(distinct feeds.id)"), 'desc')
                    ->take(2);
            }

            $query = null;
            foreach ($missions as $i => $item) {
                if ($query) {
                    $query = $query->union(mission_user($item->id));
                } else {
                    $query = mission_user($item->id);
                }
            }
            $query = $query->get();
            $keys = $missions->pluck('id')->toArray();
            foreach ($query->groupBy('mission_id') as $i => $item) {
                $missions[array_search($i, $keys)]->users = $item;
            }
        }

        return success([
            'result' => true,
            'missions' => $missions,
        ]);
    }

    public function wallpaper($user_id): array
    {
        $uid = token()->uid;

        $data = UserWallpaper::where('user_id', $user_id)
            ->select(['title', 'image', 'thumbnail_image', 'created_at'])
            ->orderBy('id', 'desc')
            ->get();

        return success([
            'result' => true,
            'wallpapers' => $data,
        ]);
    }
}
