<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedPlace extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function feed()
    {
        return $this->belongsTo(Feed::class);
    }
}
