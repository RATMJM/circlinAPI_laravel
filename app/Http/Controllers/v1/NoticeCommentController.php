<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NoticeCommentController extends Controller
{
    public function index($id)
    {
        return (new CommentController())->index('notice', $id);
    }

    public function store(Request $request, $id)
    {
        $group = $request->get('group');
        $comment = $request->get('comment');

        return (new CommentController())->store('notice', $id, $group, $comment);
    }

    public function destroy($notice_id, $id)
    {
        return (new CommentController())->destroy('notice', $notice_id, $id);
    }
}
