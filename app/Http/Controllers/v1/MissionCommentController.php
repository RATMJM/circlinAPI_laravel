<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MissionCommentController extends Controller
{
    public function index($id)
    {
        return (new CommentController())->index('mission', $id);
    }

    public function create()
    {
        abort(404);
    }

    public function store(Request $request, $id)
    {
        $group = $request->get('group');
        $comment = $request->get('comment');

        return (new CommentController())->store('mission', $id, $group, $comment);
    }

    public function show($id)
    {
        abort(404);
    }

    public function edit($id)
    {
        abort(404);
    }

    public function update(Request $request, $id)
    {
        abort(404);
    }

    public function destroy($mission_id, $id)
    {
        return (new CommentController())->destroy('mission', $mission_id, $id);
    }
}
