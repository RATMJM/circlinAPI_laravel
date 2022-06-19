<?php

namespace App\Utils;

use App\Models\Feed;
use App\Models\Mission;
use App\Models\MissionStat;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use stdClass;

class Replace
{
    private int $user_id;
    private mixed $mission;
    private string $status;

    private array $data = [];

    public function __construct($mission, string $status = 'ongoing', $replaces = [])
    {
        $this->user_id = token_option()?->uid;
        $this->mission = $mission;
        $this->status = $status;
        $this->data = array_merge($this->data, $replaces);
    }

    /**
     * @param $array
     *
     * @return array
     */
    public function replace($array): array
    {
        if ($array instanceof Collection || $array instanceof Model) {
            $array = $array->toArray();
        }
        $res = [];
        foreach ($array as $key => $item) {
            $res[$key] = Arr::accessible($item) || $item instanceof StdClass
                ? $this->replace($item)
                : (is_string($item) ? code_replace($item, $this) : $item);
        }
        return $res;
    }

    /**
     * @param array $array
     *
     * @return void
     */
    public function set(array $array): void
    {
        $this->data = array_merge($this->data, $array);
    }

    /**
     * @param string|null $key
     *
     * @return int|string|null
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function get(string|null $key): int|string|null
    {
        if (Arr::has($this->data, $key)) return $this->data[$key];

        if ($res = Cache::get($this->mission->id . $key)) {
            $this->data[$key] = $res;
            return $res;
        }

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
            'today_all_feeds_count' => Feed::join('feed_missions', 'feed_id', 'feeds.id')
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
            'today_all_feed_places_count' => Feed::join('feed_missions', 'feed_id', 'feeds.id')
                ->join('feed_places', 'feed_places.feed_id', 'feeds.id')
                ->where('mission_id', $this->mission->id)
                ->where('feeds.created_at', '>=', date('Y-m-d'))
                ->distinct()
                ->count('feeds.id'),
            #endregion

            #region feed_users : 피드 올린 유저 수
            'feed_users_count' => Feed::join('feed_missions', 'feed_id', 'feeds.id')
                ->where('mission_id', $this->mission->id)
                ->distinct()
                ->count('feeds.user_id'),
            'today_cert_count', 'today_feed_users_count' => Feed::join('feed_missions', 'feed_id', 'feeds.id')
                ->where('mission_id', $this->mission->id)
                ->where('feeds.created_at', '>=', date('Y-m-d'))
                ->distinct()
                ->count('feeds.user_id'),
            #endregion

            #region distance
            'total_distance', 'distance_summation' => Feed::join('feed_missions', 'feed_id', 'feeds.id')
                ->where('mission_id', $this->mission->id)
                ->where('user_id', $this->user_id)
                ->sum('distance'),
            'all_distance', 'all_distance_summation' => Feed::join('feed_missions', 'feed_id', 'feeds.id')
                ->where('mission_id', $this->mission->id)
                ->sum('distance'),
            'today_total_distance', 'today_distance_summation' => Feed::join('feed_missions', 'feed_id', 'feeds.id')
                ->where('mission_id', $this->mission->id)
                ->where('user_id', $this->user_id)
                ->where('feeds.created_at', '>=', date('Y-m-d'))
                ->sum('distance'),
            'today_all_distance', 'today_all_distance_summation' => Feed::join('feed_missions', 'feed_id', 'feeds.id')
                ->where('mission_id', $this->mission->id)
                ->where('feeds.created_at', '>=', date('Y-m-d'))
                ->sum('distance'),
            #endregion

            #region complete_days_count
            'total_complete_day', 'complete_days_count' => Feed::select([
                'feeds.user_id',
                DB::raw("CAST(feeds.created_at as DATE) d"),
                DB::raw("SUM(feeds.distance) as s"),
            ])
                ->join('feed_missions', 'feed_id', 'feeds.id')
                ->where('mission_id', $this->mission->id)
                ->where('feeds.user_id', $this->user_id)
                ->having('s', '>=', $this->mission->goal_distance ?? 0)
                ->orHavingNull('s')
                ->groupBy(DB::raw("CAST(feeds.created_at as DATE)"), 'feeds.user_id')
                ->count(),
            'all_complete_day', 'all_complete_days_count' => Feed::select([
                'feeds.user_id',
                DB::raw("CAST(feeds.created_at as DATE) d"),
                DB::raw("SUM(feeds.distance) as s"),
            ])
                ->join('feed_missions', 'feed_id', 'feeds.id')
                ->where('mission_id', $this->mission->id)
                ->having('s', '>=', $this->mission->goal_distance ?? 0)
                ->orHavingNull('s')
                ->groupBy(DB::raw("CAST(feeds.created_at as DATE)"), 'feeds.user_id')
                ->count(),
            #endregion

            #region singular
            'feed_places_count_3' => Feed::join('feed_missions', 'feed_id', 'feeds.id')
                ->join('feed_places', 'feed_places.feed_id', 'feeds.id')
                ->where('mission_id', $this->mission->id)
                ->where('user_id', $this->user_id)
                ->distinct()
                ->count('feeds.id') >= 3 ? '성공' : '도전 중',
            'feed_places_count_6' => Feed::join('feed_missions', 'feed_id', 'feeds.id')
                ->join('feed_places', 'feed_places.feed_id', 'feeds.id')
                ->where('mission_id', $this->mission->id)
                ->where('user_id', $this->user_id)
                ->distinct()
                ->count('feeds.id') >= 6 ? '성공' : '도전 중',
            'feed_places_count_9' => Feed::join('feed_missions', 'feed_id', 'feeds.id')
                ->join('feed_places', 'feed_places.feed_id', 'feeds.id')
                ->where('mission_id', $this->mission->id)
                ->where('user_id', $this->user_id)
                ->distinct()
                ->count('feeds.id') >= 9 ? '성공' : '도전 중',
            #endregion

            #region date
            'mission_starts_at' => explode(' ', Mission::select('started_at')
                    ->where('id', $this->mission->id)
                    ->value('started_at'))[0],
            'mission_ends_at' => Mission::select('ended_at')
                ->where('id', $this->mission->id)
                ->get(),
            'mission_dday_end' => now()->setTime(0, 0)
                ->diff((new Carbon($this->mission->ended_at))->setTime(0, 0))->days,

            #endregion
            'code' => $this->mission->code ?? null,
            'entry_no' => $this->mission->entry_no ?? null,
            'remaining_day' => now()->setTime(0, 0)
                ->diff((new Carbon($this->mission->ended_at))->setTime(0, 0))->days,
        };



        if (str_contains($key, 'all')) {
            Cache::set($this->mission->id . $key, $res, 600);
        }

        $this->data[$key] = $res;
        return $res;
    }
}
