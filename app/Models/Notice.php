<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Notice
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $title
 * @property string $content
 * @property string|null $link_text
 * @property string|null $link_url
 * @property int $is_show
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\NoticeComment[] $comments
 * @property-read int|null $comments_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\NoticeImage[] $images
 * @property-read int|null $images_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mission[] $missions
 * @property-read int|null $missions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\NoticeMission[] $notice_missions
 * @property-read int|null $notice_missions_count
 * @method static \Illuminate\Database\Eloquent\Builder|Notice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notice newQuery()
 * @method static \Illuminate\Database\Query\Builder|Notice onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Notice query()
 * @method static \Illuminate\Database\Eloquent\Builder|Notice whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notice whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notice whereIsShow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notice whereLinkText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notice whereLinkUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notice whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Notice withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Notice withoutTrashed()
 * @mixin \Eloquent
 */
class Notice extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_new' => 'bool',
        'created_at' => 'date:Y-m-d H:i:s',
    ];

    public function images()
    {
        return $this->hasMany(NoticeImage::class);
    }

    public function comments()
    {
        return $this->hasMany(NoticeComment::class);
    }

    public function notice_missions()
    {
        return $this->hasMany(NoticeMission::class);
    }

    public function missions()
    {
        return $this->belongsToMany(Mission::class, NoticeMission::class);
    }
}
