<?php

namespace App\Http\Controllers\v1_1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FeedLikeController extends Controller
{
    public function index(Request $request, $id): array
    {
        return (new LikeController())->index($request, 'feed', $id);
    }

    public function store($id): array
    {
        return (new LikeController())->store('feed', $id);
    }

    public function destroy($id): array
    {
        return (new LikeController())->destroy('feed', $id);
    }
}
