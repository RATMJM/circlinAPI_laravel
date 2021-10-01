<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function index()
    {
        $data = Notice::orderBy('id', 'desc')
            ->with('images')
            ->paginate(50);

        return view('admin.notice.index', [
            'data' => $data,
        ]);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        $data = Notice::where('id', $id)->firstOrFail();

        return view('admin.notice.show', [
            'data' => $data,
        ]);
    }

    public function edit($id)
    {
        $data = Notice::where('id', $id)->firstOrFail();

        return view('admin.notice.edit', [
            'data' => $data,
        ]);
    }

    public function update(Request $request, $id)
    {
        $title = $request->get('title');
        $content = $request->get('content');
        $is_show = $request->get('is_show');

        if (!$title || !$content || !$is_show) {
            return redirect()->to($request->server('HTTP_REFERER'));
        }

        Notice::where('id', $id)->update([
            'title' => $title,
            'content' => $content,
            'is_show' => $is_show,
        ]);

        return redirect()->route('admin.notice.show', ['notice' => $id]);
    }

    public function update_show(Request $request, $id)
    {
        $is_show = $request->get('is_show');
        if (is_null($is_show)) {
            return redirect()->to($request->server('HTTP_REFERER'));
        }

        Notice::where('id', $id)->update(['is_show' => $is_show]);

        return redirect()->to($request->server('HTTP_REFERER'));
    }

    public function destroy($id)
    {
        Notice::where('id', $id)->delete();

        return redirect()->route('admin.notice.index');
    }
}
