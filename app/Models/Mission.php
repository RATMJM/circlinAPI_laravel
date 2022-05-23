<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Mission
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $user_id 미션 제작자
 * @property int $mission_category_id
 * @property string|null $title
 * @property string|null $subtitle
 * @property string|null $description
 * @property string|null $thumbnail_image 썸네일
 * @property string|null $reserve_started_at 사전예약 시작 일시
 * @property string|null $reserve_ended_at 사전예약 종료 일시
 * @property string|null $started_at 시작 일시
 * @property string|null $ended_at 종료 일시
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int $user_limit 최대 참여자 수(0은 무제한)
 * @property int $product_limit 환급 챌린지 최대 구매 제품 수
 * @property int $success_count x회 인증 시 성공 팝업 (지금은 1,0으로 운영)
 * @property int $is_show 노출 여부
 * @property int $is_tutorial
 * @property bool $is_event 이벤트 여부
 * @property int $is_ground 운동장으로 입장 여부
 * @property int $is_ocr OCR 필요한 미션인지
 * @property int $is_require_place 장소 인증 필수 여부
 * @property int $is_not_duplicate_place 일일 장소 중복 인증 불가 여부
 * @property int|null $event_type ~5.0 미션룸 구분
 * @property int $event_order 이벤트 페이지 정렬
 * @property int $reward_point 이벤트 성공 보상
 * @property string|null $treasure_started_at 보물찾기 포인트 지급 시작일자
 * @property string|null $treasure_ended_at 보물찾기 포인트 지급 종료일자
 * @property int|null $week_duration 총 주차
 * @property int|null $week_min_count 주당 최소 인증 횟수
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Area[] $areas
 * @property-read int|null $areas_count
 * @property-read \App\Models\MissionCategory $category
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Content[] $content
 * @property-read int|null $content_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\FeedMission[] $feed_missions
 * @property-read int|null $feed_missions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Feed[] $feeds
 * @property-read int|null $feeds_count
 * @property-read \App\Models\MissionGround|null $ground
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MissionImage[] $images
 * @property-read int|null $images_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MissionArea[] $mission_areas
 * @property-read int|null $mission_areas_count
 * @property-read \App\Models\MissionContent|null $mission_content
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MissionPlace[] $mission_place
 * @property-read int|null $mission_place_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MissionStat[] $mission_stats
 * @property-read int|null $mission_stats_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Place[] $place
 * @property-read int|null $place_count
 * @property-read \App\Models\MissionProduct|null $product
 * @property-read \App\Models\MissionReward|null $reward
 * @method static \Illuminate\Database\Eloquent\Builder|Mission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Mission newQuery()
 * @method static \Illuminate\Database\Query\Builder|Mission onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Mission query()
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereEndedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereEventOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereEventType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereIsEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereIsGround($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereIsNotDuplicatePlace($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereIsOcr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereIsRequirePlace($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereIsShow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereIsTutorial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereMissionCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereProductLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereReserveEndedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereReserveStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereRewardPoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereSubtitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereSuccessCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereThumbnailImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereTreasureEndedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereTreasureStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereUserLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereWeekDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mission whereWeekMinCount($value)
 * @method static \Illuminate\Database\Query\Builder|Mission withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Mission withoutTrashed()
 * @mixin \Eloquent
 */
class Mission extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_event' => 'bool',
        'created_at' => 'date:Y-m-d H:i:s',
    ];

    public function mission_areas()
    {
        return $this->hasMany(MissionArea::class);
    }

    public function areas()
    {
        return $this->hasManyThrough(Area::class, MissionArea::class,
            'mission_id', 'code', 'id', 'area_code');
    }

    public function images()
    {
        return $this->hasMany(MissionImage::class);
    }

    public function product()
    {
        return $this->hasOne(MissionProduct::class);
    }

    public function mission_place()
    {
        return $this->hasMany(MissionPlace::class);
    }

    public function place()
    {
        return $this->belongsToMany(Place::class, MissionPlace::class);
    }

    public function mission_content()
    {
        return $this->hasOne(MissionContent::class);
    }

    public function content()
    {
        return $this->belongsToMany(Content::class, MissionContent::class);
    }

    public function reward()
    {
        return $this->hasOne(MissionReward::class);
    }

    public function ground()
    {
        return $this->hasOne(MissionGround::class);
    }

    public function mission_stats()
    {
        return $this->hasMany(MissionStat::class);
    }

    public function feed_missions()
    {
        return $this->hasMany(FeedMission::class);
    }

    public function feeds()
    {
        return $this->belongsToMany(Feed::class, FeedMission::class);
    }

    public function category()
    {
        return $this->belongsTo(MissionCategory::class, 'mission_category_id');
    }
}
