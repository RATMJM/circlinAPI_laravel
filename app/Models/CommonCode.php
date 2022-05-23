<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CommonCode
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $ctg_lg
 * @property string|null $ctg_md
 * @property string $ctg_sm
 * @property string $content_ko
 * @property string|null $content_en
 * @property string|null $description
 * @method static \Illuminate\Database\Eloquent\Builder|CommonCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CommonCode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CommonCode query()
 * @method static \Illuminate\Database\Eloquent\Builder|CommonCode whereContentEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommonCode whereContentKo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommonCode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommonCode whereCtgLg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommonCode whereCtgMd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommonCode whereCtgSm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommonCode whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommonCode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommonCode whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CommonCode extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];
}
