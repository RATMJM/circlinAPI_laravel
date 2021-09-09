<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MissionCommentController extends Controller
{
    public function index(Request $request, $id)
    {
        return (new CommentController())->index($request, 'mission', $id);
    }

    public function store(Request $request, $id)
    {
        $group = $request->get('group');
        $comment = $request->get('comment');

        return (new CommentController())->store('mission', $id, $group, $comment);
    }

    public function destroy($mission_id, $id)
    {
        return (new CommentController())->destroy('mission', $mission_id, $id);
    }
}
