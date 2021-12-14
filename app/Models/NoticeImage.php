<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\NoticeImage
 *
 * @property int $id
 * @property mixed|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $notice_id
 * @property int|null $order
 * @property string $type 이미지인지 비디오인지 (image / video)
 * @property string $image
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeImage query()
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeImage whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeImage whereNoticeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeImage whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeImage whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeImage whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class NoticeImage extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
