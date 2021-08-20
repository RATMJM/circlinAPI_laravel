<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NoticeController extends Controller
{
    public function index(): array
    {
        $user_id = token()->uid;

        $data = Notice::select([
                'notices.id', 'notices.created_at', 'notices.title',
            ])
            ->with('images')
            ->withCount('comments')
            ->orderBy('notices.id', 'desc')
            ->get();

        return success([
            'result' => true,
            'notices' => $data,
        ]);
    }

    public function show($id): array
    {
        $user_id = token()->uid;

        $data = Notice::where('id', $id)
            ->with('images', function ($query) {
                $query->select(['type', 'image'])->orderBy('order');
            })
            ->first();

        $comments = (new NoticeCommentController())->index($id)['data']['comments'];

        return success([
            'result' => true,
            'notice' => $data,
            'comments' => $comments,
        ]);
    }
}
