<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\Mission;
use App\Models\MissionImage;
use App\Models\MissionPlace;
use App\Models\MissionProduct;
use App\Models\MissionStat;
use Exception;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class MissionController extends Controller
{
    public function store(Request $request): array
    {
        try {
            DB::beginTransaction();

            $user_id = token()->uid;

            $mission_category_id = $request->get('mission_category_id');
            $title = $request->get('title');
            $description = $request->get('description');
            $thumbnail = $request->file('thumbnail');
            $files = $request->file('files');

            $product_id = $request->get('product_id');
            $product_brand = $request->get('product_brand');
            $product_title = $request->get('product_title');
            $product_image = $request->get('product_image');
            $product_url = $request->get('product_url');
            $product_price = $request->get('product_price');

            $place_address = $request->get('place_address');
            $place_title = $request->get('place_title');
            $place_description = $request->get('place_description');
            $place_image = $request->get('place_image');
            $place_url = $request->get('place_url');

            if (!$title && !$files) {
                return success([
                    'result' => false,
                    'reason' => 'not enough data',
                ]);
            }

            $uploaded_thumbnail = null;
            if ($thumbnail) {
                if (str_starts_with($thumbnail->getMimeType(), 'image/')) {
                    $type = 'image';
                    $image = Image::make($thumbnail->getPathname());
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
                    $tmp_path = "{$thumbnail->getPath()}/{$user_id}_" . Str::uuid() . ".{$thumbnail->extension()}";
                    $image->save($tmp_path);
                    $uploaded_thumbnail = Storage::disk('ftp3')->put("/Image/USERPROMISE/$user_id", new File($tmp_path));
                    @unlink($tmp_path);
                }
            }

            $data = Mission::create([
                'user_id' => $user_id,
                'mission_category_id' => $mission_category_id,
                'title' => $title,
                'description' => $description,
                'thumbnail_image' => image_url(3, $uploaded_thumbnail),
            ]);

            if ($files) {
                foreach ($files as $i => $file) {
                    $uploaded_thumbnail = '';
                    if (str_starts_with($file->getMimeType(), 'image/')) {
                        $type = 'image';
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
                        $uploaded_file = Storage::disk('ftp3')->put("/Image/USERPROMISE/$user_id", new File($tmp_path));
                        @unlink($tmp_path);
                    } elseif (str_starts_with($file->getMimeType(), 'video/')) {
                        $type = 'video';
                        $uploaded_file = Storage::disk('ftp3')->put("/Image/USERPROMISE/$user_id", $file);

                        $thumbnail = "Image/SNS/$user_id/thumb_" . $file->hashName();

                        $host = config('filesystems.disks.ftp3.host');
                        $username = config('filesystems.disks.ftp3.username');
                        $password = config('filesystems.disks.ftp3.password');
                        $url = image_url(3, $uploaded_file);
                        $url2 = image_url(3, $thumbnail);
                        if (uploadVideoResizing($user_id, $host, $username, $password, $url, $url2, $data->id)) {
                            $uploaded_thumbnail = $thumbnail;
                        }
                    } else {
                        continue;
                    }

                    MissionImage::create([
                        'mission_id' => $data->id,
                        'order' => $i,
                        'type' => $type,
                        'image' => image_url(3, $uploaded_file),
                        'thumbnail_image' => image_url(3, $uploaded_thumbnail ?: $uploaded_file),
                    ]);
                }
            }

            if ($product_id) {
                MissionProduct::create([
                    'mission_id' => $data->id,
                    'type' => 'inside',
                    'product_id' => $product_id
                ]);
            } elseif ($product_brand && $product_title && $product_price && $product_url) {
                MissionProduct::create([
                    'mission_id' => $data->id,
                    'type' => 'outside',
                    'image' => $product_image,
                    'brand' => $product_brand,
                    'title' => $product_title,
                    'price' => $product_price,
                    'url' => $product_url,
                ]);
            }

            if ($place_address && $place_title && $place_image) {
                MissionPlace::create([
                    'mission_id' => $data->id,
                    'address' => $place_address,
                    'title' => $place_title,
                    'description' => $place_description,
                    'image' => $place_image,
                    'url' => $place_url ?? urlencode("https://google.com/search?q=$place_title"),
                ]);
            }

            DB::commit();

            return success(['result' => true, 'mission' => $data]);
        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }
    }

    /**
     * 미션 상세
     */
    public function show($mission_id): array
    {
        $user_id = token()->uid;

        $data = Mission::where('missions.id', $mission_id)
            ->join('users as o', 'o.id', 'missions.user_id') // 미션 제작자
            ->join('user_stats as os', 'os.user_id', 'o.id') // 미션 제작자
            ->leftJoin('follows as of', 'of.target_id', 'o.id') // 미션 제작자 팔로워
            ->leftJoin('areas as oa', 'oa.ctg_sm', 'o.area_code')
            ->leftJoin('mission_stats as ms', function ($query) {
                $query->on('ms.mission_id', 'missions.id')->whereNull('ms.ended_at');
            })
            ->leftJoin('mission_comments as mc', 'mc.mission_id', 'missions.id')
            ->select([
                'missions.id', 'missions.title', 'missions.description',
                'o.id as owner_id', 'o.nickname', 'o.profile_image', 'os.gender',
                DB::raw("IF(name_lg=name_md, CONCAT_WS(' ', name_md, name_sm), CONCAT_WS(' ', name_lg, name_md, name_sm)) as area"),
                DB::raw("COUNT(distinct of.user_id) as followers"),
                'is_following' => Follow::selectRaw("COUNT(1) > 0")->whereColumn('follows.target_id', 'o.id')
                    ->where('follows.user_id', $user_id),
                'is_bookmark' => MissionStat::selectRaw('COUNT(1) > 0')->where('mission_stats.user_id', $user_id)
                    ->whereColumn('mission_stats.mission_id', 'missions.id'),
                DB::raw('COUNT(distinct ms.id) as bookmarks'),
                DB::raw('COUNT(distinct mc.id) as comments'),
            ])
            ->groupBy('missions.id', 'o.id', 'os.id', 'oa.id')
            ->first();

        $data->owner = arr_group($data, ['owner_id', 'nickname', 'profile_image', 'gender', 'area', 'followers', 'is_following']);

        $data->users = $data->mission_stats()
            ->select(['users.id', 'users.nickname', 'users.profile_image', 'user_stats.gender'])
            ->join('users', 'users.id', 'mission_stats.user_id')
            ->leftJoin('user_stats', 'user_stats.user_id', 'users.id')
            ->leftJoin('follows', 'follows.target_id', 'mission_stats.user_id')
            ->groupBy('users.id', 'user_stats.id')->orderBy(DB::raw('COUNT(follows.id)'), 'desc')->take(2)->get();

        return success([
            'result' => true,
            'mission' => $data,
        ]);
    }

    public function user(Request $request, $mission_id): array
    {
        $limit = $request->get('limit', 20);

        $users = MissionStat::where('mission_stats.mission_id', $mission_id)
            ->join('users', 'users.id', 'mission_stats.user_id')
            ->leftJoin('user_stats', 'user_stats.user_id', 'users.id')
            ->leftJoin('areas', 'areas.ctg_sm', 'users.area_code')
            ->leftJoin('follows', 'follows.target_id', 'users.id')
            ->leftJoin('feed_missions', 'feed_missions.mission_id', 'mission_stats.mission_id')
            ->select([
                'users.id', 'users.nickname', 'users.profile_image', 'user_stats.gender',
                DB::raw("IF(name_lg=name_md, CONCAT_WS(' ', name_md, name_sm), CONCAT_WS(' ', name_lg, name_md, name_sm)) as area"),
                DB::raw("COUNT(distinct follows.id) as follower"),
                DB::raw("COUNT(distinct feed_missions.feed_id) as mission_feeds"),
            ])
            ->groupBy(['users.id', 'user_stats.id', 'areas.id'])
            ->orderBy('mission_feeds')->orderBy('follower', 'desc')->orderBy('id', 'desc')
            ->take($limit)->get();

        return success([
            'success' => true,
            'users' => $users,
        ]);
    }
}
