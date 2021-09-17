<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_important' => 'bool',
        'created_at' => 'date:Y-m-d H:i:s',
    ];

    public function missions()
    {
        return $this->belongsToMany(Mission::class, MissionPlace::class);
    }
}
