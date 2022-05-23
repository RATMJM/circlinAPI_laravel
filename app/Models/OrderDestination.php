<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\OrderDestination
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $order_id
 * @property string $post_code 우편번호
 * @property string $address 주소
 * @property string|null $address_detail 상세 주소
 * @property string $recipient_name 받는사람 이름
 * @property string|null $phone 휴대폰번호
 * @property string|null $comment 요청사항
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDestination newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDestination newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDestination query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDestination whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDestination whereAddressDetail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDestination whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDestination whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDestination whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDestination whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDestination wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDestination wherePostCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDestination whereRecipientName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderDestination whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OrderDestination extends Model
{
    use HasFactory;

    protected $guarded = [];
}
