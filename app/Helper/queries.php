<?php

use App\Models\MissionStat;
use Illuminate\Support\Facades\DB;

function selectMissionGround(int $user_id): array
{
    return [
        'missions.is_ocr',
        'missions.reserve_started_at',
        'missions.reserve_ended_at',
        'missions.started_at',
        'missions.ended_at',
        is_available(),
        DB::raw("CASE WHEN
                    (missions.started_at is null or missions.started_at <= now()) and
                    (missions.ended_at is null or missions.ended_at >= now())
                THEN 'ongoing'
                WHEN (missions.reserve_started_at is null or missions.reserve_started_at <= now()) and
                    (missions.reserve_ended_at is null or missions.reserve_ended_at >= now())
                THEN 'reserve'
                WHEN missions.reserve_started_at >= now() THEN 'before' ELSE 'end' END as `status`"),
        'goal_distance' => MissionStat::select('goal_distance')
            ->whereColumn('mission_id', 'missions.id')
            ->where('user_id', $user_id)
            ->orderBy('id', 'desc')
            ->take(1),
        'code' => MissionStat::select('code')
            ->whereColumn('mission_id', 'missions.id')
            ->where('user_id', $user_id)
            ->orderBy('id', 'desc')
            ->take(1),
        'entry_no' => MissionStat::select('entry_no')
            ->whereColumn('mission_id', 'missions.id')
            ->where('user_id', $user_id)
            ->orderBy('id', 'desc')
            ->take(1),
        'mission_grounds.*',
        'missions.id',
    ];
}
