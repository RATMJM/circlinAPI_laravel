<?php

namespace App\Http\Controllers\v1_1;

use App\Models\Block;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BlockController
{
    public function store(Request $request): array
    {
        $user_id = token()->uid;
        $target_id = $request->get('target_id');

        if ($user_id == $target_id) {
            return success(['result' => false, 'reason' => '올바르지 않은 대상입니다.']);
        }

        $is_blocked = Block::select('id')
            ->where('user_id', $user_id)
            ->where('target_id', $target_id)
            ->get()->count();

        if ($is_blocked > 0) {
            return success(['result' => false, 'reason' => '이미 차단된 유저입니다.']);
        }

        $data =[
            'user_id' => $user_id,
            'target_id' => $target_id
        ];

        Block::create($data);
        try {
            DB::commit();
            return success(['result' => true]);
        } catch(Exception $e) {
            DB::rollBack();
            return exceped($e);
        }
    }

    public function destroy(Request $request): array
    {
        $user_id = token()->uid;
        $target_id = $request->get('target_id');

        if ($user_id == $target_id) {
            return success(['result' => false, 'reason' => '올바르지 않은 대상입니다.']);
        }

        $is_blocked = Block::select('user_id', 'target_id')
            ->where('user_id', $user_id)
            ->where('target_id', $target_id)
            ->get()->count();

        if ($is_blocked < 1) {
            return success(['result' => false, 'reason' => '이미 차단 해제하셨거나 차단하신 기록이 없습니다.']);
        }

        $data = Block::where([
            'user_id' => $user_id,
            'target_id' => $target_id
        ])->first();

        if ($data) {
            $result = $data->delete();
            DB::commit();
            return success(['result' => $result]);
        } else {
            return success(['result' => false, 'reason' => '이미 차단 해제하셨거나 차단하신 기록이 없습니다.']);
        }
    }
}
