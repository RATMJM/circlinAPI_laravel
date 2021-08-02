<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\MissionCategory;
use Illuminate\Http\Request;

class MissionController extends Controller
{
    public function category(Request $request): array
    {
        return MissionCategory::all()->toArray();
    }
}
