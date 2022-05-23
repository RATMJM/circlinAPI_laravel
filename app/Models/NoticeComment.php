<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\NoticeComment
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $notice_id
 * @property int $user_id
 * @property int $group
 * @property int $depth
 * @property string $comment
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeComment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeComment newQuery()
 * @method static \Illuminate\Database\Query\Builder|NoticeComment onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeComment query()
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeComment whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeComment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeComment whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeComment whereDepth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeComment whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeComment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeComment whereNoticeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeComment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoticeComment whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|NoticeComment withTrashed()
 * @method static \Illuminate\Database\Query\Builder|NoticeComment withoutTrashed()
 * @mixin \Eloquent
 */
class NoticeComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
