<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\PushReservation
 *
 * @property int $id
 * @property mixed|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $target 푸시 대상
 * @property string|null $target_ids 구분자 : |
 * @property string $description 푸시 설명
 * @property string $title 푸시 타이틀
 * @property string $message 푸시 내용
 * @property string|null $send_date 발송 일자 (없으면 매일)
 * @property string $send_time 발송 시간
 * @property string|null $link_type 푸시 링크
 * @property int|null $feed_id
 * @property int|null $mission_id
 * @property int|null $notice_id
 * @property int|null $product_id
 * @property string|null $url
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|PushReservation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PushReservation newQuery()
 * @method static \Illuminate\Database\Query\Builder|PushReservation onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|PushReservation query()
 * @method static \Illuminate\Database\Eloquent\Builder|PushReservation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushReservation whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushReservation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushReservation whereFeedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushReservation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushReservation whereLinkType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushReservation whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushReservation whereMissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushReservation whereNoticeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushReservation whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushReservation whereSendDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushReservation whereSendTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushReservation whereTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushReservation whereTargetIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushReservation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushReservation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushReservation whereUrl($value)
 * @method static \Illuminate\Database\Query\Builder|PushReservation withTrashed()
 * @method static \Illuminate\Database\Query\Builder|PushReservation withoutTrashed()
 * @mixin \Eloquent
 */
class PushReservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
