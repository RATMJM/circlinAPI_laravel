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
        'missions.late_bookmarkable',
        is_available(),
        DB::raw(
        "CASE
                    WHEN
                        (missions.started_at is null or missions.started_at <= now()) and
                        (missions.ended_at is null or missions.ended_at >= now())
                    THEN 'ongoing'
                    WHEN
                        (missions.reserve_started_at is null or missions.reserve_started_at <= now()) and
                        (missions.reserve_ended_at is null or missions.reserve_ended_at >= now())
                    THEN 'reserve'
                    WHEN
                        missions.reserve_started_at >= now()
                    THEN 'before'
                    WHEN
                        missions.reserve_started_at <= now() AND missions.reserve_ended_at < now() AND missions.started_at > now()
                    THEN 'before'
                    ELSE 'end'
                END as `status`"),
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

function selectMissionPlayground(int $user_id): array
{
    return [
        'missions.is_ocr',
        'missions.reserve_started_at',
        'missions.reserve_ended_at',
        'missions.started_at',
        'missions.ended_at',
        'missions.late_bookmarkable',
        is_available(),
        DB::raw(
            "CASE
                    WHEN
                        (missions.reserve_started_at IS NULL) AND
                        (missions.reserve_ended_at IS NULL)
                    THEN
                        CASE
                            WHEN
                                missions.started_at > NOW()
                            THEN 'before_ongoing'
                            WHEN
                                (missions.started_at <= NOW()) AND (missions.ended_at > NOW())
                            THEN 'ongoing'
                            ELSE 'end'
                        END
                    ELSE
                        CASE
                            WHEN
                                missions.reserve_started_at > NOW()
                            THEN 'before_reserve'
                            WHEN
                                (missions.reserve_started_at < missions.reserve_ended_at) AND
                                (missions.reserve_ended_at <= missions.started_at) AND
                                (missions.reserve_started_at <= NOW()) AND
                                (NOW() < missions.reserve_ended_at)
                            THEN 'reserve'
                            WHEN
                                (missions.reserve_started_at < missions.reserve_ended_at) AND
                                (missions.started_at <= missions.reserve_ended_at) AND
                                (missions.reserve_started_at <= NOW()) AND
                                (NOW() < missions.started_at)
                            THEN 'reserve'
                            WHEN
                                (missions.reserve_started_at < missions.reserve_ended_at) AND
                                (missions.reserve_ended_at < missions.started_at) AND
                                (missions.reserve_ended_at <= NOW()) AND
                                (NOW() < missions.started_at)
                            THEN 'before_ongoing'
                            WHEN
                                (missions.reserve_started_at < missions.reserve_ended_at) AND
                                (missions.reserve_ended_at <= missions.started_at) AND
                                (missions.started_at <= NOW()) AND
                                (NOW() < missions.ended_at)
                            THEN 'ongoing'
                            WHEN
                                (missions.reserve_started_at < missions.reserve_ended_at) AND
                                (missions.started_at <= missions.reserve_ended_at) AND
                                (missions.started_at <= NOW()) AND
                                (NOW() < missions.ended_at)
                            THEN 'ongoing'
                            ELSE 'end'
                        END
                END
             AS `status`"),
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

function selectBanner(): array
{
    return [
        'id',
        'created_at',
        'type',
        'sort_num',
        'name',
        'description',
        'started_at',
        'ended_at',
        DB::raw("(started_at is null OR started_at <= now()) AND (ended_at is null OR ended_at >= now()) as `is_available`"),
        'image',
        'link_type',
        'mission_id',
        'feed_id',
        'product_id',
        'notice_id',
        'link_url',
    ];
}
