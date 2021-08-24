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

        $latest_date = date('Y-m-d', time()-(86400 * 7));

        $data = Notice::select(['notices.id', 'notices.created_at', 'notices.title',
            DB::raw("created_at >= '$latest_date' as is_new")])
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

        $latest_date = date('Y-m-d', time()-(86400 * 7));

        $data = Notice::where('id', $id)
            ->select([
                'id', 'created_at', 'title', 'content', 'link_text', 'link_url',
                DB::raw("created_at >= '$latest_date' as is_new"),
            ])
            ->with('images', function ($query) {
                $query->select(['notice_id', 'type', 'image'])->orderBy('order');
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
