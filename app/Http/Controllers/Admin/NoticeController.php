<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeedImage;
use App\Models\Notice;
use App\Models\NoticeImage;
use Exception;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class NoticeController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type');
        $keyword = trim($request->get('keyword'));

        $data = Notice::orderBy('id', 'desc')
            ->when($type, function ($query, $type) use ($keyword) {
                match ($type) {
                    'all' => $query->where(function ($query) use ($keyword) {
                        $query->where('notices.title', 'like', "%$keyword%");
                    }),
                    default => null,
                };
            })
            ->with('images')
            ->paginate(50);

        return view('admin.notice.index', [
            'data' => $data,
            'type' => $type,
            'keyword' => $keyword
        ]);
    }

    public function create()
    {
        return view('admin.notice.create');
    }

    public function store(Request $request)
    {
        $title = $request->get('title');
        $content = $request->get('content');
        $is_show = $request->get('is_show');
        $files = $request->file('files');
        $orders = $request->get('orders');

        if (!$title || !$content || !$files || count($files) !== count($orders)) {
            return "<script>alert('데이터가 부족합니다.');history.back()</script>";
        }

        try {
            DB::beginTransaction();

            $data = Notice::create([
                'title' => $title,
                'content' => $content,
                'is_show' => $is_show ?? 0,
            ]);

            if ($files) {
                foreach ($files as $i => $file) {
                    $uploaded_thumbnail = '';
                    if (str_starts_with($file->getMimeType(), 'image/')) {
                        $type = 'image';
                        $image = Image::make($file->getPathname());
                        if ($image->width() > $image->height()) {
                            $x = ($image->width() - $image->height()) / 2;
                            $y = 0;
                            $src = $image->height();
                        } else {
                            $x = 0;
                            $y = ($image->height() - $image->width()) / 2;
                            $src = $image->width();
                        }
                        $image->crop($src, $src, round($x), round($y));

                        $image->orientate();

                        $tmp_path = "{$file->getPath()}/" . Str::uuid() . ".{$file->extension()}";
                        $image->save($tmp_path);
                        $uploaded_file = Storage::disk('ftp3')->put("/Image/NOTICE/{$data->id}", new File($tmp_path));
                        @unlink($tmp_path);
                    } else {
                        continue;
                    }

                    NoticeImage::create([
                        'notice_id' => $data->id,
                        'order' => $orders[$i],
                        'type' => $type,
                        'image' => image_url(3, $uploaded_file),
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.notice.show', ['notice' => $data->id]);
        } catch (Exception $e) {
            exceped($e);
            $message = $e->getMessage();
            return "<script>alert('오류가 발생했습니다.\\n$message');history.back()</script>";
        }
    }

    public function show($id)
    {
        $data = Notice::where('id', $id)
            ->with('images', function ($query) {
                $query->orderBy('order');
            })
            ->firstOrFail();

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
