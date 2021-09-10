<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function missions()
    {
        return $this->belongsToMany(Mission::class, MissionProduct::class)
            ->where('mission_products.type', 'inside');
    }
}
