<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PushHistory;
use App\Models\PushReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $data = PushHistory::join('users', 'users.id', 'push_histories.target_id')
            ->select([
                'push_histories.id', 'push_histories.created_at',
                'push_histories.title', 'push_histories.message', 'push_histories.type',
                'users.nickname',
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
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
