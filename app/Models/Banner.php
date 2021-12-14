<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Banner
 *
 * @property int $id
 * @property mixed|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $type 어디에 노출되는 광고인지 (float|shop|local)
 * @property int $sort_num 정렬 순서 (높을수록 우선)
 * @property string $name 배너명
 * @property string|null $description 배너 상세설명
 * @property string|null $started_at 배너 시작 일시
 * @property string|null $ended_at 배너 종료 일시
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string $image 배너 이미지
 * @property string|null $link_type 링크 형태 (mission|product|notice|url)
 * @property int|null $mission_id
 * @property int|null $feed_id
 * @property int|null $product_id
 * @property int|null $notice_id
 * @property string|null $link_url
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\BannerLog[] $logs
 * @property-read int|null $logs_count
 * @method static \Illuminate\Database\Eloquent\Builder|Banner newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Banner newQuery()
 * @method static \Illuminate\Database\Query\Builder|Banner onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Banner query()
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereEndedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereFeedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereLinkType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereLinkUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereMissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereNoticeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereSortNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Banner whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Banner withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Banner withoutTrashed()
 * @mixin \Eloquent
 */
class Banner extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];

    public function logs()
    {
        return $this->hasMany(BannerLog::class);
    }
}
