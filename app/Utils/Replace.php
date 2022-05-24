<?php

namespace App\Utils;

use App\Models\Feed;
use App\Models\MissionStat;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class Replace
{
    private int $user_id;
    private mixed $mission;
    private string $status;

    private array $data = [];

    public function __construct($mission, string $status = 'ongoing')
    {
        $this->user_id = token_option()?->uid;
        $this->mission = $mission;
        $this->status = $status;
    }

    public function get(string $key)
    {
        if (Arr::has($this->data, $key)) return $this->data[$key];

        $res = match ($key) {
            default => null,
            #region user
            'users_count' => ($this->status === 'end' ? MissionStat::withTrashed() : MissionStat::withoutTrashed())
                ->where('mission_id', $this->mission->id)
                ->distinct()
                ->count('user_id'),
            #endregion

            #region feeds
            'feeds_count' => Feed::join('feed_missions', 'feed_id', 'feeds.id')
                ->where('mission_id', $this->mission->id)
                ->where('user_id', $this->user_id)
                ->distinct()
                ->count('feeds.id'),
            'today_feeds_count' => Feed::join('feed_missions', 'feed_id', 'feeds.id')
                ->where('mission_id', $this->mission->id)
                ->where('user_id', $this->user_id)
                ->where('feeds.created_at', '>=', date('Y-m-d'))
                ->distinct()
                ->count('feeds.id'),
            'all_feeds_count' => Feed::join('feed_missions', 'feed_id', 'feeds.id')
                ->where('mission_id', $this->mission->id)
                ->distinct()
                ->count('feeds.id'),
            'all_today_feeds_count' => Feed::join('feed_missions', 'feed_id', 'feeds.id')
                ->where('mission_id', $this->mission->id)
                ->where('feeds.created_at', '>=', date('Y-m-d'))
                ->distinct()
                ->count('feeds.id'),
            #endregion

            #region feed_places : 장소 인증된 피드 수
            'feed_places_count' => Feed::join('feed_missions', 'feed_id', 'feeds.id')
                ->join('feed_places', 'feed_places.feed_id', 'feeds.id')
                ->where('mission_id', $this->mission->id)
                ->where('user_id', $this->user_id)
                ->distinct()
                ->count('feeds.id'),
            'today_feed_places_count' => Feed::join('feed_missions', 'feed_id', 'feeds.id')
                ->join('feed_places', 'feed_places.feed_id', 'feeds.id')
                ->where('mission_id', $this->mission->id)
                ->where('user_id', $this->user_id)
                ->where('feeds.created_at', '>=', date('Y-m-d'))
                ->distinct()
                ->count('feeds.id'),
            'all_feed_places_count' => Feed::join('feed_missions', 'feed_id', 'feeds.id')
                ->join('feed_places', 'feed_places.feed_id', 'feeds.id')
                ->where('mission_id', $this->mission->id)
                ->distinct()
                ->count('feeds.id'),
            'all_today_feed_places_count' => Feed::join('feed_missions', 'feed_id', 'feeds.id')
                ->join('feed_places', 'feed_places.feed_id', 'feeds.id')
                ->where('mission_id', $this->mission->id)
                ->where('feeds.created_at', '>=', date('Y-m-d'))
                ->distinct()
                ->count('feeds.id'),
            #endregion

            #region complete_days_count
            'all_complete_days_count' => Feed::select([
                'feeds.user_id',
                DB::raw("CAST(feeds.created_at as DATE) d"),
                DB::raw("SUM(feeds.distance) as s"),
            ])
                ->join('feed_missions', 'feed_id', 'feeds.id')
                ->where('mission_id', $this->mission->id)
                ->having('s', '>=', $this->mission->goal_distance ?? 0)
                ->groupBy(DB::raw("CAST(feeds.created_at as DATE)"), 'feeds.user_id')
                ->count(),
            'complete_days_count' => Feed::select([
                'feeds.user_id',
                DB::raw("CAST(feeds.created_at as DATE) d"),
                DB::raw("SUM(feeds.distance) as s"),
            ])
                ->join('feed_missions', 'feed_id', 'feeds.id')
                ->where('mission_id', $this->mission->id)
                ->where('feeds.user_id', $this->user_id)
                ->having('s', '>=', $this->mission->goal_distance ?? 0)
                ->groupBy(DB::raw("CAST(feeds.created_at as DATE)"), 'feeds.user_id')
                ->count(),
            #endregion

            'code' => $this->mission->code ?? null,
            'entry_no' => $this->mission->entry_no ?? null,
            'remaining_day' => now()->setTime(0, 0)
                ->diff((new Carbon($this->mission->ended_at))->setTime(0, 0))->d,
        };

        $this->data[$key] = $res;
        return $res;
    }
}
