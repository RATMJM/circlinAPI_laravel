<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FeedCommentController extends Controller
{
    public function index($id)
    {
        return (new CommentController())->index('feed', $id);
    }

    public function store(Request $request, $id)
    {
        $group = $request->get('group');
        $comment = $request->get('comment');

        return (new CommentController())->store('feed', $id, $group, $comment);
    }

    public function destroy($feed_id, $id)
    {
        return (new CommentController())->destroy('feed', $feed_id, $id);
    }
}
