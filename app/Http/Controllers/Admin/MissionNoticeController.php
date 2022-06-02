<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use App\Models\MissionNotice;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class MissionNoticeController extends Controller
{
    public function index(Request $request, $mission_id)
    {
        if (!Mission::where('id', $mission_id)->value('is_event')) {
            abort(404);
        }

        $data = MissionNotice::select(['id', 'title', 'created_at'])
            ->where('mission_id', $mission_id)
            ->with('images', fn($query) => $query->take(1))
            ->orderBy('id', 'desc')
            ->paginate(50);

        return view('admin.mission.notice.index', [
            'mission_id' => $mission_id,
            'data' => $data,
        ]);
    }

    public function create($mission_id)
    {
        return view('admin.mission.notice.create', ['mission_id' => $mission_id]);
    }

    public function store(Request $request, $mission_id)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);
        $files = $request->file('files');

        $data = MissionNotice::create(array_merge(['mission_id' => $mission_id], $data));

        foreach ($files as $file) {
            $path = "/mission/$mission_id/notice/$data->id";
            if (str_starts_with($file->getMimeType(), 'image/')) {
                $type = 'image';

                $image = Image::make($file->getPathname());

                $image->orientate();

                $tmp_path = "{$file->getPath()}/" . Str::uuid() . ".{$file->extension()}";
                $image->save($tmp_path);
                $uploaded_file = Storage::disk('s3')->put($path, new File($tmp_path));
                @unlink($tmp_path);
            } elseif (str_starts_with($file->getMimeType(), 'video/')) {
                $type = 'video';
                $uploaded_file = Storage::disk('s3')->put($path, $file);
            } else {
                continue;
            }

            $data->images()->create(['type' => $type, 'image' => image_url($uploaded_file)]);
        }

        return redirect()->route('admin.mission.notice.index', ['mission_id' => $mission_id]);
    }

    public function show($mission_id, $id)
    {
        $data = MissionNotice::select([
            'id', 'title', 'body', 'created_at',
        ])
            ->where(['mission_id' => $mission_id, 'id' => $id])
            ->firstOrFail();

        return view('admin.mission.notice.show', ['data' => $data]);
    }

    public function edit($mission_id, $id)
    {

    }

    public function update(Request $request, $mission_id, $id)
    {

    }
}
