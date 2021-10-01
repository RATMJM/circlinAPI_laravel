<?php

namespace App\Http\Controllers\v1_1;

use App\Http\Controllers\Controller;
use App\Mail\FindPassword;
use App\Models\Area;
use App\Models\ChatUser;
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
            ->where('feed_likes.created_at', '>=', init_today(time() - 86400))
            ->where('feed_likes.created_at', '<', init_today())
            ->count();

        $today_paid_count = FeedLike::withTrashed()->where('feed_likes.user_id', $user_id)
            /*->whereIn('feeds.user_id', function ($query) {
                $query->select('target_id')->from('follows')->whereColumn('user_id', 'feed_likes.user_id');
            })*/
            ->join('feeds', 'feeds.id', 'feed_likes.feed_id')
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

            User::withTrashed()->where('device_token', $token)->where('id', '!=', $user_id)->update(['device_token' => '']);

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

    public function push_recommend(Request $request): array
    {
        $user_id = token()->uid;
        $code = $request->get('code');

        try {
            DB::beginTransaction();

            $user = User::find($user_id);

            if (isset($user->recommend_updated_at)) {
                return success([
                    'result' => false,
                    'reason' => 'already push recommend user',
                ]);
            }

            if ($code) {
                $recommend_user = User::where('invite_code', $code)->select(['id', 'nickname'])->first();

                if (!$recommend_user) {
                    return success([
                        'result' => false,
                        'reason' => 'not found user',
                    ]);
                }

                $data = $user->update([
                    'recommend_user_id' => $recommend_user->id,
                    'recommend_updated_at' => DB::raw("NOW()"),
                ]);

                $res = PointController::change_point($user_id, 500, 'recommended_reward');
                if (!$res['success']) {
                    DB::rollBack();
                    return ['success' => false, 'reason' => 'error', 'message' => $res['message']];
                }
                $res = PointController::change_point($recommend_user->id, 500, 'invite_reward');
                if (!$res['success']) {
                    DB::rollBack();
                    return ['success' => false, 'reason' => 'error', 'message' => $res['message']];
                }
                (new ChatController())->send_direct($request, $recommend_user->id, null, null,
                    "{$recommend_user->nickname}님을 추천인으로 등록했어요! 감사합니다! 😆");
            } else {
                $data = User::where('id', $user_id)->update([
                    'recommend_updated_at' => DB::raw("NOW()"),
                ]);
            }

            DB::commit();

            return success([
                'result' => $data,
            ]);
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

            $old_password = $request->get('old_password');
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
                $temp_password = random_password(8);

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
                    $res = NotificationController::send($target_id, 'follow', $user_id, null, true);

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
                'users.id', 'users.nickname', 'users.profile_image', 'users.gender', 'area' => area_like(),
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
                'users.id', 'users.nickname', 'users.profile_image', 'users.gender', 'area' => area_like(),
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
        $uid = token()->uid;

        $data = User::where('users.id', $user_id)
            ->select([
                'users.nickname', 'users.point', 'users.gender', 'users.profile_image', 'users.greeting', 'area' => ($user_id == $uid ? area() : area_like()),
                'followers' => Follow::selectRaw("COUNT(1)")->whereColumn('follows.target_id', 'users.id'),
                'followings' => Follow::selectRaw("COUNT(1)")->whereColumn('follows.user_id', 'users.id'),
                'created_missions' => Mission::selectRaw("COUNT(1)")->whereColumn('user_id', 'users.id'),
                'feeds' => Feed::selectRaw("COUNT(1)")->whereColumn('user_id', 'users.id')
                    ->where(function ($query) use ($uid) {
                        // $query->where('feeds.is_hidden', 0)->orWhere('feeds.user_id', $uid);
                    }),
                'checks' => FeedLike::selectRaw("COUNT(1)")->whereColumn('user_id', 'users.id'),
                'missions' => Mission::selectRaw("COUNT(distinct missions.id)")
                    ->where(function ($query) use ($user_id) {
                        $query->whereNull('mission_stats.ended_at')
                            ->whereColumn('mission_stats.user_id', 'users.id')
                            ->orWhereColumn('feeds.user_id', 'users.id');
                    })
                    ->join('mission_stats', 'mission_stats.mission_id', 'missions.id')
                    ->leftJoin('feed_missions', 'feed_missions.mission_stat_id', 'mission_stats.id')
                    ->leftJoin('feeds', function ($query) use ($uid) {
                        $query->on('feeds.id', 'feed_missions.feed_id')
                            ->whereNull('feeds.deleted_at')
                            ->where(function ($query) use ($uid) {
                                // $query->where('feeds.is_hidden', 0)->orWhere('feeds.user_id', $uid);
                            });
                    }),
                'is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('target_id', 'users.id')
                    ->where('user_id', $uid),
            ])
            ->first();

        $is_chat_block = ChatUser::where('chat_users.user_id', $uid)
            ->where('chat_rooms.is_group', false)
            ->join('chat_rooms', 'chat_rooms.id', 'chat_users.chat_room_id')
            ->join('chat_users as cu2', function ($query) use ($user_id) {
                $query->on('cu2.chat_room_id', 'chat_users.chat_room_id')
                    ->where('cu2.user_id', $user_id);
            })
            ->value('chat_users.is_block');


        $wallpapers = $this->wallpaper($user_id)['data']['wallpapers'];

        return success([
            'success' => true,
            'user' => $data,
            'is_chat_block' => $is_chat_block,
            'wallpapers' => $wallpapers,
        ]);
    }

    /**
     * 피드 데이터
     */
    public function feed(Request $request, $user_id): array
    {
        $uid = token()->uid;

        $category_id = $request->get('category_id');
        $mission_id = $request->get('mission_id');
        $limit = $request->get('limit', 20);
        $page = $request->get('page', 0);

        $categories = MissionCategory::whereNotNull('mission_categories.mission_category_id')
            ->when($category_id, function ($query, $category_id) {
                $query->whereIn('mission_categories.id', Arr::wrap($category_id));
            })
            ->where('feeds.user_id', $user_id)
            ->join('missions', 'missions.mission_category_id', 'mission_categories.id')
            ->join('feed_missions', 'feed_missions.mission_id', 'missions.id')
            ->join('feeds', function ($query) use ($uid) {
                $query->on('feeds.id', 'feed_missions.feed_id')
                    ->whereNull('feeds.deleted_at')
                    ->where(function ($query) use ($uid) {
                        // $query->where('feeds.is_hidden', 0)->orWhere('feeds.user_id', $uid);
                    });
            })
            ->select([
                'mission_categories.id', 'mission_categories.title', 'mission_categories.emoji',
                DB::raw('COUNT(distinct feeds.id) as feeds'),
            ])
            ->groupBy('mission_categories.id')
            ->get();

        $missions = Feed::where('feeds.user_id', $user_id)
            // ->where('feeds.is_hidden', false)
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
                // $query->where('is_hidden', false);
            })
            ->when($category_id, function ($query, $category_id) {
                $query->whereHas('missions', function ($query) use ($category_id) {
                    $query->whereIn('missions.mission_category_id', Arr::wrap($category_id));
                });
            })
            /*->when($mission_id, function ($query, $mission_id) {
                $query->whereHas('feed_missions', function ($query) use ($mission_id) {
                    $query->whereIn('feed_missions.mission_id', Arr::wrap($mission_id));
                });
            })*/
            ->select([
                'feeds.id', 'feeds.created_at', 'feeds.content', 'feeds.is_hidden',
                'has_images' => FeedImage::selectRaw("COUNT(1) > 1")->whereColumn('feed_id', 'feeds.id'), // 이미지 여러장인지
                'has_product' => FeedProduct::selectRaw("COUNT(1) > 0")->whereColumn('feed_id', 'feeds.id'), // 상품 있는지
                'has_place' => FeedPlace::selectRaw("COUNT(1) > 0")->whereColumn('feed_id', 'feeds.id'), // 위치 있는지
                'image_type' => FeedImage::select('type')->whereColumn('feed_images.feed_id', 'feeds.id')->orderBy('id')->limit(1),
                'image' => FeedImage::select('image')->whereColumn('feed_images.feed_id', 'feeds.id')->orderBy('id')->limit(1),
                'missions' => FeedMission::selectRaw("COUNT(1)")->whereColumn('feed_id', 'feeds.id'),
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

        $missions = Mission::whereNotNull('mission_categories.mission_category_id')
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->whereNull('mission_stats.ended_at')
                        ->orWhere(Feed::selectRaw("COUNT(1)")->whereColumn('feeds.user_id', 'mission_stats.user_id')
                            ->whereColumn('feed_missions.mission_id', 'missions.id')
                            ->join('feed_missions', 'feed_missions.feed_id', 'feeds.id'), '>', 0);
                });
            })
            ->join('mission_categories', 'mission_categories.id', 'missions.mission_category_id')
            ->join('mission_stats', function ($query) use ($user_id) {
                $query->on('mission_stats.mission_id', 'missions.id')
                    ->where('mission_stats.user_id', $user_id);
            });

        $categories = $missions->select([
            'mission_categories.id', 'mission_categories.title', 'mission_categories.emoji',
        ])
            ->groupBy('mission_categories.id')
            ->get();

        $missions->getQuery()->groups = null;

        $missions->when($category_id, function ($query, $category_id) {
            $query->whereHas('missions', function ($query) use ($category_id) {
                $query->whereIn('missions.mission_category_id', Arr::wrap($category_id));
            });
        });

        $missions_count = $missions->count(DB::raw("distinct missions.id"));

        $missions = $missions->join('users', 'users.id', 'missions.user_id')
            ->leftJoin('mission_products', 'mission_products.mission_id', 'missions.id')
            ->leftJoin('products', 'products.id', 'mission_products.product_id')
            ->leftJoin('brands', 'brands.id', 'products.brand_id')
            ->leftJoin('outside_products', 'outside_products.id', 'mission_products.outside_product_id')->select([
                'missions.mission_category_id', 'mission_categories.title', 'mission_categories.emoji',
                'missions.id', 'missions.title', 'missions.description',
                'missions.is_event',
                DB::raw("missions.id <= 1213 and missions.is_event = 1 as is_old_event"), challenge_type(),
                'missions.started_at', 'missions.ended_at',
                'missions.thumbnail_image', 'missions.success_count',
                'mission_stat_id' => MissionStat::withTrashed()->select('id')->whereColumn('mission_id', 'missions.id')
                    ->where('user_id', $user_id)->orderBy('id', 'desc')->limit(1),
                'mission_stat_user_id' => MissionStat::withTrashed()->select('user_id')->whereColumn('mission_id', 'missions.id')
                    ->where('user_id', $user_id)->orderBy('id', 'desc')->limit(1),
                'users.id as user_id', 'users.nickname', 'users.profile_image', 'users.gender', 'area' => area_like(),
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
                'bookmarks' => MissionStat::withTrashed()->selectRaw("COUNT(distinct user_id)")
                    ->whereColumn('mission_id', 'missions.id'),
                'comments' => MissionComment::selectRaw("COUNT(1)")->whereColumn('mission_id', 'missions.id'),
                'has_check' => FeedMission::selectRaw("COUNT(1) > 0")
                    ->whereColumn('feed_missions.mission_id', 'missions.id')->where('feeds.user_id', $user_id)
                    ->where('feeds.created_at', '>=', init_today())
                    ->whereNull('feeds.deleted_at')
                    ->join('feeds', 'feeds.id', 'feed_missions.feed_id'),
                'feed_id' => FeedMission::select('feed_id')
                    ->whereColumn('feed_missions.mission_id', 'missions.id')->where('feeds.user_id', $user_id)
                    ->where('feeds.created_at', '>=', init_today())
                    ->join('feeds', function ($query) use ($uid) {
                        $query->on('feeds.id', 'feed_missions.feed_id')
                            ->whereNull('feeds.deleted_at')
                            ->where(function ($query) use ($uid) {
                                // $query->where('feeds.is_hidden', 0)->orWhere('feeds.user_id', $uid);
                            });
                    })->limit(1),
            ])
            ->withCount('feeds')
            ->groupBy('mission_categories.id', 'missions.id', 'users.id',
                'mission_products.type', 'mission_products.product_id', 'mission_products.outside_product_id')
            ->when($user_id == $uid, function ($query) {
                $query->orderBy('is_bookmark', 'desc');
            })
            ->orderBy(DB::raw("MAX(mission_stats.id)"), 'desc')
            ->skip($page * $limit)->take($limit)->get();

        if (count($missions)) {
            [$users, $areas] = null;
            foreach ($missions as $i => $mission) {
                $mission->owner = arr_group($mission, ['user_id', 'nickname', 'profile_image', 'gender',
                    'area', 'followers', 'is_following']);

                if ($users) {
                    $users = $users->union(mission_users($mission->id, $uid));
                } else {
                    $users = mission_users($mission->id, $uid);
                }

                if ($areas) {
                    $areas = $areas->union(mission_areas($mission->id));
                } else {
                    $areas = mission_areas($mission->id);
                }
            }
            $keys = $missions->pluck('id')->toArray();
            $users = $users->get();
            foreach ($users->groupBy('mission_id') as $i => $mission) {
                $missions[array_search($i, $keys)]->users = $mission;
            }
            $areas = $areas->get();
            foreach ($areas->groupBy('mission_id') as $i => $mission) {
                $missions[array_search($i, $keys)]->areas = $mission->pluck('name');
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
        $sort = $sort ?? $request->get('sort', SORT_POPULAR);

        $missions = MissionCategory::where('missions.user_id', $user_id)
            ->join('missions', function ($query) {
                $query->on('missions.mission_category_id', 'mission_categories.id')
                    ->whereNull('missions.deleted_at');
            })
            ->join('users', 'users.id', 'missions.user_id') // 미션 제작자
            ->leftJoin('mission_products', 'mission_products.mission_id', 'missions.id')
            ->leftJoin('products', 'products.id', 'mission_products.product_id')
            ->leftJoin('brands', 'brands.id', 'products.brand_id')
            ->leftJoin('outside_products', 'outside_products.id', 'mission_products.outside_product_id')
            ->select([
                'mission_categories.id', 'mission_categories.title', 'mission_categories.emoji',
                'missions.mission_category_id', 'missions.id', 'missions.title', 'missions.description',
                'missions.is_event',
                DB::raw("missions.id <= 1213 and missions.is_event = 1 as is_old_event"), challenge_type(),
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
                'bookmark_total' => MissionStat::withTrashed()->selectRaw("COUNT(distinct user_id)")
                    ->whereColumn('mission_id', 'missions.id'),
                'comment_total' => MissionComment::selectRaw("COUNT(1)")->whereColumn('mission_id', 'missions.id'),
            ]);

        if ($sort == SORT_POPULAR) {
            $missions->orderBy('bookmark_total', 'desc')->orderBy('missions.id', 'desc');
        } elseif ($sort == SORT_RECENT) {
            $missions->orderBy('missions.id', 'desc');
        } elseif ($sort == SORT_USER) {
            $missions->orderBy('bookmark_total', 'desc')->orderBy('missions.id', 'desc');
        } elseif ($sort == SORT_COMMENT) {
            $missions->orderBy(MissionComment::selectRaw("COUNT(1)")->whereColumn('mission_id', 'missions.id'), 'desc');
        }

        $missions = $missions->skip($page * $limit)->take($limit)->get();

        if (count($missions)) {
            [$users, $areas] = null;
            foreach ($missions as $i => $item) {
                $item->owner = arr_group($item, ['user_id', 'nickname', 'profile_image', 'gender',
                    'area', 'followers', 'is_following']);

                if ($users) {
                    $users = $users->union(mission_users($item->id, $user_id));
                } else {
                    $users = mission_users($item->id, $user_id);
                }

                if ($areas) {
                    $areas = $areas->union(mission_areas($item->id));
                } else {
                    $areas = mission_areas($item->id);
                }
            }
            $keys = $missions->pluck('id')->toArray();
            $users = $users->get();
            foreach ($users->groupBy('mission_id') as $i => $item) {
                $missions[array_search($i, $keys)]->users = $item;
            }
            $areas = $areas->get();
            foreach ($areas->groupBy('mission_id') as $i => $item) {
                $missions[array_search($i, $keys)]->areas = $item->pluck('name');
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
