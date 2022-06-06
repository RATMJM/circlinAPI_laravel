<?php

namespace App\Http\Controllers\v1_1;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ReportController
{
    public function store(Request $request):array
    {
        $user_id = token()->uid;

        $target_feed_comment_id = $request->get('target_feed_comment_id');
        $target_mission_comment_id = $request->get('target_mission_comment_id');
        $target_notice_comment_id = $request->get('target_notice_comment_id');
        $target_feed_id = $request->get('target_feed_id');
        $target_user_id = $request->get('target_user_id');
        $target_mission_id = $request->get('target_mission_id');
        $reason = $request->get('reason');

        $report_count = Report::select('id')
            ->where('target_feed_comment_id', $target_feed_comment_id)
            ->where('target_mission_comment_id', $target_mission_comment_id)
            ->where('target_notice_comment_id', $target_notice_comment_id)
            ->where('target_feed_id', $target_feed_id)
            ->where('target_user_id', $target_user_id)
            ->where('target_mission_id', $target_mission_id)
            ->where('reason', $reason)
            ->where('user_id', $user_id)
            ->get()->count();

        if ($report_count > 0) {
            // abort(400, '이미 신고가 완료되어 운영진이 검토중입니다.');
            return success(['result' => false, 'reason' => '이미 신고가 완료되어 운영진이 검토중입니다.']);
        }

        $data = [
            'target_feed_comment_id' => $target_feed_comment_id,
            'target_mission_comment_id' => $target_mission_comment_id,
            'target_notice_comment_id' => $target_notice_comment_id,
            'target_feed_id' => $target_feed_id,
            'target_user_id' => $target_user_id,
            'target_mission_id' => $target_mission_id,
            'reason' => $reason,
            'user_id' => $user_id
        ];

        Report::create($data);
        try {
            DB::commit();
            return success(['result' => true]);
        } catch(Exception $e) {
            DB::rollBack();
            return exceped($e);
        }
    }
}
