<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FeedLikeController extends Controller
{
    public function index($id): array
    {
        return (new LikeController())->index('feed', $id);
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
