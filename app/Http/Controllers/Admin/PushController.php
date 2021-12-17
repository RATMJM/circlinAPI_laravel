<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PushHistory;
use App\Models\PushReservation;
use Illuminate\Http\Request;

class PushController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type');
        $keyword = trim($request->get('keyword'));

        $data = PushReservation::orderBy('id', 'desc')->paginate(50);

        return view('admin.push.index', [
            'data' => $data,
            'type' => $type,
            'keyword' => $keyword,
        ]);
    }

    public function history(Request $request)
    {
        $type = $request->get('type');
        $keyword = trim($request->get('keyword'));

        $data = PushHistory::when($type, function ($query, $type) use ($keyword) {
            match ($type) {
                'all' => $query->where(function ($query) use ($keyword) {
                    $query->where('push_histories.title', 'like', "%$keyword%")
                        ->orWhere('push_histories.message', 'like', "%$keyword%")
                        ->orWhere('users.nickname', 'like', "%$keyword%")
                        ->orWhere('users.email', 'like', "%$keyword%");
                }),
                default => null,
            };
        })
            ->join('users', 'users.id', 'push_histories.target_id')
            ->select([
                'push_histories.id',
                'push_histories.created_at',
                'push_histories.title',
                'push_histories.message',
                'push_histories.type',
                'users.nickname',
                'users.email',
            ])
            ->orderBy('push_histories.id', 'desc')
            ->paginate(50);

        return view('admin.push.history', [
            'data' => $data,
            'type' => $type,
            'keyword' => $keyword,
        ]);
    }

    public function create()
    {
        return view('admin.push.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'description' => 'required|max:255',
            'target' => 'required|max:255',
            'target_ids' => 'required_if:target,mission,user',
            'title' => 'required|max:255',
            'message' => 'required|max:255',
            'send_date' => 'required|date_format:Y-m-d',
            'send_time' => 'required|date_format:H:i',
        ]);

        PushReservation::create($data);

        return redirect()->route('admin.push.index');
    }

    public function edit($id)
    {
        $data = PushReservation::where('id', $id)
            ->select(['id', 'description', 'target', 'target_ids', 'title', 'message', 'send_date', 'send_time'])
            ->firstOrFail();

        return view('admin.push.edit', ['data' => $data]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'description' => 'required|max:255',
            'target' => 'required|max:255',
            'target_ids' => 'required_if:target,mission,user',
            'title' => 'required|max:255',
            'message' => 'required|max:255',
            'send_date' => 'required|date_format:Y-m-d',
            'send_time' => 'required|date_format:H:i',
        ]);

        PushReservation::where('id', $id)->update($data);

        return redirect()->route('admin.push.index');
    }

    public function destroy($id)
    {
        PushReservation::where('id', $id)->delete();

        return redirect()->route('admin.push.index');
    }
}
